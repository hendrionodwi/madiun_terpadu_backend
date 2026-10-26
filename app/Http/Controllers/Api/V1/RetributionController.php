<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MasterData;
use App\Models\PointLedger;
use App\Models\RetributionBill;
use App\Models\User;
use App\Models\WargaProfile;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RetributionController extends Controller
{
    /**
     * List retribution bills (TRK-F-013).
     * Warga gets their bills; Admin DLH can view city-wide bills with filters.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $query = RetributionBill::query()->with('warga:id,name,email,phone_number');

        if ($currentUser->isWarga()) {
            $query->where('warga_id', $currentUser->id);
        } elseif ($currentUser->isAdminDlh()) {
            if ($request->filled('warga_id')) {
                $query->where('warga_id', (int) $request->warga_id);
            }
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_tagihan', 'like', "%{$search}%")
                        ->orWhereHas('warga', function ($w) use ($search) {
                            $w->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone_number', 'like', "%{$search}%");
                        });
                });
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Akses retribusi hanya untuk warga dan Admin DLH.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', (int) $request->periode_tahun);
        }

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', (int) $request->periode_bulan);
        }

        $wargaId = $currentUser->isWarga() ? $currentUser->id : (int) ($request->warga_id ?? 0);
        $overdueCount = $currentUser->isWarga()
            ? RetributionBill::where('warga_id', $wargaId)->overdue()->count()
            : RetributionBill::query()->overdue()->count();

        $bills = $query->latest('jatuh_tempo')->paginate((int) ($request->per_page ?? 15));

        $rate = (float) (MasterData::query()
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('numeric_value') ?? 10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar tagihan retribusi sampah berhasil diambil.',
            'data' => [
                'has_overdue_blocked' => $overdueCount > 0,
                'overdue_count' => $overdueCount,
                'point_conversion_rate' => $rate,
                'bills' => $bills->items(),
            ],
            'meta' => [
                'current_page' => $bills->currentPage(),
                'last_page' => $bills->lastPage(),
                'per_page' => $bills->perPage(),
                'total' => $bills->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get single retribution bill details.
     */
    public function show(Request $request, RetributionBill $retributionBill): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isWarga() && $retributionBill->warga_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Tagihan ini bukan milik akun Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        $retributionBill->loadMissing('warga:id,name,email,phone_number');

        $rate = (float) (MasterData::query()
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('numeric_value') ?? 10);

        return response()->json([
            'success' => true,
            'message' => 'Detail tagihan retribusi berhasil diambil.',
            'data' => array_merge($retributionBill->toArray(), [
                'is_overdue' => $retributionBill->isOverdue(),
                'is_paid' => $retributionBill->isPaid(),
                'periode_label' => $retributionBill->periode_label,
                'point_conversion_rate' => $rate,
                'warga_saldo_poin' => (int) (WargaProfile::query()
                    ->where('user_id', $retributionBill->warga_id)
                    ->value('saldo_poin') ?? 0),
            ]),
        ], Response::HTTP_OK);
    }

    /**
     * Pay a retribution bill (TRK-F-013).
     * Supports:
     * 1. 100% Points deduction (if points sufficient)
     * 2. 100% Midtrans (QRIS/E-Wallet/Bank Transfer)
     * 3. Hybrid (Partial points deduction + remaining via Midtrans)
     */
    public function pay(Request $request, RetributionBill $retributionBill, MidtransService $midtransService): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($retributionBill->warga_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk membayar tagihan ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'use_points' => ['nullable', 'boolean'],
            'points_to_use' => ['nullable', 'integer', 'min:0'],
        ]);

        $usePoints = (bool) ($validated['use_points'] ?? false);
        $rate = max(0.01, (float) (MasterData::query()
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('numeric_value') ?? 10));
        $maxPointDiscountPercent = min(100.0, max(0.0, (float) RetributionBill::getConfig('max_point_discount_percent', 100)));

        $payment = DB::transaction(function () use ($retributionBill, $currentUser, $validated, $usePoints, $rate, $maxPointDiscountPercent): array {
            $lockedBill = RetributionBill::query()->whereKey($retributionBill->id)->lockForUpdate()->firstOrFail();

            if ($lockedBill->isPaid()) {
                throw ValidationException::withMessages([
                    'payment' => ['Tagihan retribusi ini sudah berstatus lunas.'],
                ]);
            }

            if ($lockedBill->status === 'menunggu_pembayaran') {
                throw ValidationException::withMessages([
                    'payment' => ['Tagihan ini sudah memiliki pembayaran yang menunggu penyelesaian. Selesaikan atau tunggu transaksi tersebut kedaluwarsa.'],
                ]);
            }

            if ($lockedBill->status !== 'belum_dibayar') {
                throw ValidationException::withMessages([
                    'payment' => ['Tagihan ini tidak tersedia untuk pembayaran.'],
                ]);
            }

            $profile = WargaProfile::query()
                ->where('user_id', $currentUser->id)
                ->lockForUpdate()
                ->first();
            $wargaSaldoPoin = $profile === null ? 0 : (int) $profile->saldo_poin;
            $nominalTagihan = (float) $lockedBill->nominal_tagihan;
            $maxDiscountRupiah = round($nominalTagihan * ($maxPointDiscountPercent / 100), 2);
            $maxPointsAllowed = (int) floor($maxDiscountRupiah / $rate);
            $requestedPoints = (int) ($validated['points_to_use'] ?? 0);
            $pointsToUse = 0;

            if ($usePoints) {
                if ($requestedPoints > $wargaSaldoPoin) {
                    throw ValidationException::withMessages([
                        'points_to_use' => ["Saldo poin Anda ({$wargaSaldoPoin} poin) tidak mencukupi untuk penggunaan {$requestedPoints} poin."],
                    ]);
                }

                if ($requestedPoints > $maxPointsAllowed) {
                    throw ValidationException::withMessages([
                        'points_to_use' => ["Penggunaan poin maksimal untuk tagihan ini adalah {$maxPointsAllowed} poin."],
                    ]);
                }

                $pointsToUse = $requestedPoints > 0
                    ? $requestedPoints
                    : min($wargaSaldoPoin, $maxPointsAllowed);
            }

            $potonganRupiah = min($nominalTagihan, round($pointsToUse * $rate, 2));
            $sisaRupiah = max(0.0, round($nominalTagihan - $potonganRupiah, 2));
            $isFullPointsPayment = $sisaRupiah <= 0;

            if ($pointsToUse > 0) {
                PointLedger::record(
                    $currentUser->id,
                    'debit',
                    'bayar_retribusi',
                    $pointsToUse,
                    $isFullPointsPayment
                        ? "Pelunasan retribusi sampah {$lockedBill->periode_label} (100% Poin)"
                        : "Reservasi poin pembayaran retribusi sampah {$lockedBill->periode_label} (Hibrida)",
                    $lockedBill
                );
            }

            $lockedBill->update([
                'status' => $isFullPointsPayment ? 'lunas' : 'menunggu_pembayaran',
                'metode_pembayaran' => $isFullPointsPayment ? 'poin_sampah' : ($pointsToUse > 0 ? 'hibrida' : 'midtrans'),
                'poin_digunakan' => $pointsToUse,
                'nominal_potongan_poin' => $potonganRupiah,
                'nominal_dibayar' => $sisaRupiah,
                'paid_at' => $isFullPointsPayment ? Carbon::now() : null,
                'midtrans_order_id' => $isFullPointsPayment
                    ? null
                    : $lockedBill->nomor_tagihan.'-'.Str::upper(Str::random(8)),
                'snap_token' => null,
                'snap_redirect_url' => null,
                'payment_gateway_response' => null,
            ]);

            return [
                'bill' => $lockedBill->fresh(),
                'payment_type' => $isFullPointsPayment ? 'full_points' : ($pointsToUse > 0 ? 'hybrid' : 'midtrans'),
                'points_used' => $pointsToUse,
                'point_discount' => $potonganRupiah,
                'amount_due' => $sisaRupiah,
            ];
        }, 3);

        /** @var RetributionBill $preparedBill */
        $preparedBill = $payment['bill'];

        if ($payment['payment_type'] === 'full_points') {
            return response()->json([
                'success' => true,
                'message' => 'Tagihan retribusi sampah berhasil dilunasi 100% menggunakan poin sampah! Layanan penjemputan sampah aktif sepenuhnya.',
                'data' => [
                    'payment_type' => 'full_points',
                    'bill' => $preparedBill,
                    'poin_digunakan' => $payment['points_used'],
                    'sisa_saldo_poin' => (int) $currentUser->wargaProfile?->fresh()->saldo_poin,
                ],
            ], Response::HTTP_OK);
        }

        try {
            $snap = $midtransService->createRetributionSnapTransaction($preparedBill);
            $updatedBill = DB::transaction(function () use ($preparedBill, $snap): RetributionBill {
                $lockedBill = RetributionBill::query()->whereKey($preparedBill->id)->lockForUpdate()->firstOrFail();

                if ($lockedBill->status === 'menunggu_pembayaran') {
                    $lockedBill->update([
                        'snap_token' => $snap['token'],
                        'snap_redirect_url' => $snap['redirect_url'],
                    ]);
                }

                return $lockedBill->fresh();
            }, 3);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembayaran retribusi berhasil disiapkan. Silakan selesaikan pembayaran.',
                'data' => [
                    'payment_type' => $payment['payment_type'],
                    'bill' => $updatedBill,
                    'poin_digunakan' => $payment['points_used'],
                    'nominal_potongan_poin' => $payment['point_discount'],
                    'nominal_harus_dibayar' => $payment['amount_due'],
                    'snap_token' => $snap['token'],
                    'snap_redirect_url' => $snap['redirect_url'],
                ],
            ], Response::HTTP_OK);
        } catch (Throwable $exception) {
            report($exception);

            DB::transaction(function () use ($preparedBill): void {
                $lockedBill = RetributionBill::query()->whereKey($preparedBill->id)->lockForUpdate()->firstOrFail();

                if ($lockedBill->status === 'menunggu_pembayaran') {
                    $this->resetPendingPayment(
                        $lockedBill,
                        'Pengembalian reservasi poin karena transaksi Midtrans gagal dibuat.'
                    );
                }
            }, 3);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan pembayaran Midtrans. Reservasi poin telah dikembalikan; silakan coba kembali.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Webhook notification handler from Midtrans for retribution payments.
     */
    public function handleMidtransNotification(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();
        $orderId = (string) ($payload['order_id'] ?? '');

        $serverKey = $midtransService->retributionServerKey();
        if ($serverKey === '' || ! $midtransService->verifySignature($payload, $serverKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Signature notifikasi Midtrans tidak valid.',
            ], Response::HTTP_FORBIDDEN);
        }

        $bill = RetributionBill::query()
            ->where('midtrans_order_id', $orderId)
            ->orWhere(function ($query) use ($orderId) {
                $query->whereNull('midtrans_order_id')->where('nomor_tagihan', $orderId);
            })
            ->first();
        if (! $bill) {
            return response()->json([
                'success' => false,
                'message' => "Tagihan retribusi dengan nomor {$orderId} tidak ditemukan.",
            ], Response::HTTP_NOT_FOUND);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $isCancellation = in_array($transactionStatus, ['deny', 'expire', 'cancel'], true);

        if ($isCancellation && $bill->status !== 'menunggu_pembayaran') {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi pembatalan Midtrans sebelumnya sudah diproses.',
            ]);
        }

        $receivedAmountInCents = (int) round(((float) ($payload['gross_amount'] ?? 0)) * 100);
        $expectedAmountInCents = (int) round(((float) $bill->nominal_dibayar) * 100);

        if ($receivedAmountInCents !== $expectedAmountInCents) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal pembayaran Midtrans tidak sesuai dengan tagihan.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            if ($fraudStatus !== 'challenge') {
                $accepted = DB::transaction(function () use ($bill, $payload): bool {
                    $lockedBill = RetributionBill::query()->whereKey($bill->id)->lockForUpdate()->firstOrFail();

                    if ($lockedBill->isPaid()) {
                        return true;
                    }

                    if ($lockedBill->status !== 'menunggu_pembayaran') {
                        return false;
                    }

                    $lockedBill->update([
                        'status' => 'lunas',
                        'paid_at' => Carbon::now(),
                        'payment_gateway_response' => $payload,
                    ]);

                    return true;
                }, 3);

                if (! $accepted) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tagihan tidak memiliki pembayaran Midtrans yang sedang menunggu.',
                    ], Response::HTTP_CONFLICT);
                }
            }
        } elseif ($isCancellation) {
            DB::transaction(function () use ($bill, $payload): void {
                $lockedBill = RetributionBill::query()->whereKey($bill->id)->lockForUpdate()->firstOrFail();

                if ($lockedBill->status === 'menunggu_pembayaran') {
                    $this->resetPendingPayment(
                        $lockedBill,
                        'Pengembalian reservasi poin karena pembayaran Midtrans dibatalkan atau kedaluwarsa.',
                        $payload
                    );
                }
            }, 3);
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi Midtrans retribusi berhasil diproses.']);
    }

    /**
     * Release reserved points and restore a bill to its unpaid state.
     *
     * @param  array<string, mixed>|null  $gatewayResponse
     */
    private function resetPendingPayment(
        RetributionBill $bill,
        string $description,
        ?array $gatewayResponse = null
    ): void {
        $reservedDebitTotal = (int) PointLedger::query()
            ->where('warga_id', $bill->warga_id)
            ->where('tipe', 'debit')
            ->where('kategori', 'bayar_retribusi')
            ->where('referensi_tipe', RetributionBill::class)
            ->where('referensi_id', $bill->id)
            ->sum('jumlah_poin');
        $refundedTotal = (int) PointLedger::query()
            ->where('warga_id', $bill->warga_id)
            ->where('tipe', 'kredit')
            ->where('kategori', 'refund_retribusi')
            ->where('referensi_tipe', RetributionBill::class)
            ->where('referensi_id', $bill->id)
            ->sum('jumlah_poin');
        $pointsToRefund = min($bill->poin_digunakan, max(0, $reservedDebitTotal - $refundedTotal));

        if ($pointsToRefund > 0) {
            PointLedger::record(
                $bill->warga_id,
                'kredit',
                'refund_retribusi',
                $pointsToRefund,
                $description,
                $bill
            );
        }

        $bill->update([
            'status' => 'belum_dibayar',
            'metode_pembayaran' => 'belum_dipilih',
            'poin_digunakan' => 0,
            'nominal_potongan_poin' => 0,
            'nominal_dibayar' => $bill->nominal_tagihan,
            'paid_at' => null,
            'snap_token' => null,
            'snap_redirect_url' => null,
            'payment_gateway_response' => $gatewayResponse,
        ]);
    }
}
