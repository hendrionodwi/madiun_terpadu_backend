<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\BroadcastAnnouncement;
use App\Models\MasterData;
use App\Models\PointLedger;
use App\Models\RetributionBill;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminRetributionController extends Controller
{
    /**
     * Show retribution bills management dashboard for Admin DLH (TRK-F-013).
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');
        $month = $request->query('month');
        $year = $request->query('year');

        $retributionConfig = RetributionBill::getAllConfig();
        $graceDays = (int) ($retributionConfig['grace_period_days'] ?? 0);
        $effectiveDueDate = now()->subDays($graceDays)->toDateString();

        $query = RetributionBill::with('warga.wargaProfile');

        if ($statusFilter === 'lunas') {
            $query->where('status', 'lunas');
        } elseif ($statusFilter === 'belum_dibayar') {
            $query->where('status', 'belum_dibayar')->where('jatuh_tempo', '>=', $effectiveDueDate);
        } elseif ($statusFilter === 'overdue') {
            $query->overdue($graceDays);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_tagihan', 'like', "%{$search}%")
                    ->orWhereHas('warga', function ($w) use ($search) {
                        $w->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($month) {
            $query->where('periode_bulan', (int) $month);
        }
        if ($year) {
            $query->where('periode_tahun', (int) $year);
        }

        $bills = $query->latest('jatuh_tempo')->paginate(15)->withQueryString();

        $totalTagihanRp = (float) RetributionBill::sum('nominal_tagihan');
        $totalLunasRp = (float) RetributionBill::where('status', 'lunas')->sum('nominal_tagihan');
        $totalTunggakanRp = (float) RetributionBill::whereIn('status', RetributionBill::OUTSTANDING_STATUSES)->sum('nominal_tagihan');

        $stats = [
            'total_bills' => RetributionBill::count(),
            'total_tagihan_rp' => $totalTagihanRp,
            'total_lunas_rp' => $totalLunasRp,
            'total_tunggakan_rp' => $totalTunggakanRp,
            'lunas_count' => RetributionBill::where('status', 'lunas')->count(),
            'belum_dibayar_count' => RetributionBill::where('status', 'belum_dibayar')->where('jatuh_tempo', '>=', $effectiveDueDate)->count(),
            'overdue_count' => RetributionBill::query()->overdue($graceDays)->count(),
        ];

        return view('dashboard.admin.retributions', [
            'bills' => $bills,
            'stats' => $stats,
            'retributionConfig' => $retributionConfig,
            'statusFilter' => $statusFilter,
            'currentMonth' => $month,
            'currentYear' => $year,
            'search' => $search,
        ]);
    }

    /**
     * Generate monthly retribution bills for active citizens.
     */
    public function generateBills(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2025', 'max:2030'],
            'nominal_tagihan' => ['required', 'numeric', 'min:1000'],
            'jatuh_tempo' => ['required', 'date'],
        ]);

        $month = (int) $validated['periode_bulan'];
        $year = (int) $validated['periode_tahun'];
        $nominal = (float) $validated['nominal_tagihan'];
        $dueDate = Carbon::parse($validated['jatuh_tempo'])->endOfDay();

        $wargaList = User::query()
            ->where('role', UserRole::Warga)
            ->where('is_active', true)
            ->get();

        $created = 0;
        foreach ($wargaList as $warga) {
            $bill = RetributionBill::firstOrCreate(
                [
                    'warga_id' => $warga->id,
                    'periode_bulan' => $month,
                    'periode_tahun' => $year,
                ],
                [
                    'nomor_tagihan' => RetributionBill::generateNomorTagihan($year, $month, $warga->id),
                    'nominal_tagihan' => $nominal,
                    'poin_digunakan' => 0,
                    'nominal_potongan_poin' => 0,
                    'nominal_dibayar' => $nominal,
                    'metode_pembayaran' => 'belum_dipilih',
                    'status' => 'belum_dibayar',
                    'jatuh_tempo' => $dueDate->toDateString(),
                    'catatan' => "Iuran retribusi pelayanan persampahan Kota Madiun Periode {$month}/{$year}",
                ]
            );

            if ($bill->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect()->route('dashboard.admin.retributions')
            ->with('status', "Berhasil menerbitkan {$created} tagihan retribusi sampah untuk periode {$month}/{$year}.");
    }

    /**
     * Confirm offline/cash manual payment at DLH counter.
     */
    public function confirmManualPayment(RetributionBill $retributionBill): RedirectResponse
    {
        $wasAlreadyPaid = DB::transaction(function () use ($retributionBill): bool {
            $lockedBill = RetributionBill::query()->whereKey($retributionBill->id)->lockForUpdate()->firstOrFail();

            if ($lockedBill->isPaid()) {
                return true;
            }

            $reservedDebitTotal = (int) PointLedger::query()
                ->where('warga_id', $lockedBill->warga_id)
                ->where('tipe', 'debit')
                ->where('kategori', 'bayar_retribusi')
                ->where('referensi_tipe', RetributionBill::class)
                ->where('referensi_id', $lockedBill->id)
                ->sum('jumlah_poin');
            $refundedTotal = (int) PointLedger::query()
                ->where('warga_id', $lockedBill->warga_id)
                ->where('tipe', 'kredit')
                ->where('kategori', 'refund_retribusi')
                ->where('referensi_tipe', RetributionBill::class)
                ->where('referensi_id', $lockedBill->id)
                ->sum('jumlah_poin');
            $pointsToRefund = min($lockedBill->poin_digunakan, max(0, $reservedDebitTotal - $refundedTotal));

            if ($pointsToRefund > 0) {
                PointLedger::record(
                    $lockedBill->warga_id,
                    'kredit',
                    'refund_retribusi',
                    $pointsToRefund,
                    'Pengembalian reservasi poin karena tagihan dilunasi manual di loket DLH.',
                    $lockedBill
                );
            }

            $lockedBill->update([
                'status' => 'lunas',
                'metode_pembayaran' => 'manual_dlh',
                'poin_digunakan' => 0,
                'nominal_potongan_poin' => 0,
                'nominal_dibayar' => $lockedBill->nominal_tagihan,
                'paid_at' => Carbon::now(),
                'midtrans_order_id' => null,
                'snap_token' => null,
                'snap_redirect_url' => null,
                'catatan' => ($lockedBill->catatan ? $lockedBill->catatan.' | ' : '').'Pelunasan manual tunai di loket DLH oleh Admin pada '.now()->format('d M Y, H:i'),
            ]);

            return false;
        }, 3);

        if ($wasAlreadyPaid) {
            return back()->with('error', 'Tagihan ini sudah berstatus lunas.');
        }

        return redirect()->route('dashboard.admin.retributions')
            ->with('status', "Tagihan retribusi {$retributionBill->nomor_tagihan} berhasil dikonfirmasi lunas via loket kas manual DLH.");
    }

    /**
     * Update retribution tariff policies, grace period, and notification template.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_tariff' => ['required', 'numeric', 'min:0'],
            'standard_due_day' => ['required', 'integer', 'min:1', 'max:28'],
            'max_point_discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:30'],
            'point_conversion_rate' => ['required', 'numeric', 'min:1'],
            'reminder_template' => ['required', 'string', 'max:1000'],
        ]);

        MasterData::updateOrCreate(
            ['group' => 'retribution_config', 'code' => 'monthly_tariff'],
            ['name' => 'Tarif Retribusi Bulanan', 'numeric_value' => $validated['monthly_tariff'], 'is_active' => true]
        );

        MasterData::updateOrCreate(
            ['group' => 'retribution_config', 'code' => 'standard_due_day'],
            ['name' => 'Tanggal Jatuh Tempo Standar', 'numeric_value' => $validated['standard_due_day'], 'is_active' => true]
        );

        MasterData::updateOrCreate(
            ['group' => 'retribution_config', 'code' => 'max_point_discount_percent'],
            ['name' => 'Maksimal Diskon Poin (%)', 'numeric_value' => $validated['max_point_discount_percent'], 'is_active' => true]
        );

        MasterData::updateOrCreate(
            ['group' => 'retribution_config', 'code' => 'grace_period_days'],
            ['name' => 'Masa Tenggang Keterlambatan (Hari)', 'numeric_value' => $validated['grace_period_days'], 'is_active' => true]
        );

        MasterData::updateOrCreate(
            ['group' => 'retribution_config', 'code' => 'reminder_template'],
            ['name' => 'Template Pesan Pengingat Tagihan', 'description' => $validated['reminder_template'], 'is_active' => true]
        );

        MasterData::updateOrCreate(
            ['group' => 'point_conversion', 'code' => 'idr'],
            ['name' => 'Nilai Tukar Poin ke Rupiah', 'numeric_value' => $validated['point_conversion_rate'], 'unit' => 'IDR/Poin', 'is_active' => true]
        );

        return redirect()->route('dashboard.admin.retributions')
            ->with('status', 'Pengaturan tarif retribusi dan kebijakan pengingat berhasil disimpan.');
    }

    /**
     * Send bulk reminder broadcast notifications to citizens.
     */
    public function sendReminders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_filter' => ['required', 'string', 'in:all_unpaid,overdue_only'],
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:2000'],
            'priority' => ['required', 'string', 'in:normal,high,urgent'],
        ]);

        $retributionConfig = RetributionBill::getAllConfig();
        $graceDays = (int) ($retributionConfig['grace_period_days'] ?? 0);
        $effectiveDueDate = now()->subDays($graceDays)->toDateString();

        $query = RetributionBill::with('warga')->whereIn('status', RetributionBill::OUTSTANDING_STATUSES);

        if ($validated['target_filter'] === 'overdue_only') {
            $query->where('jatuh_tempo', '<', $effectiveDueDate);
        }

        $bills = $query->get();
        $count = $bills->count();

        // Create broadcast announcement in the system for warga
        BroadcastAnnouncement::create([
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'audience_role' => 'warga',
            'priority' => $validated['priority'],
            'is_active' => true,
            'published_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $targetLabel = $validated['target_filter'] === 'overdue_only'
            ? 'warga dengan tunggakan (overdue)'
            : 'seluruh warga dengan tagihan belum lunas';

        return redirect()->route('dashboard.admin.retributions')
            ->with('status', "Pengingat massal berhasil disiarkan ke {$count} tagihan ({$targetLabel}) melalui notifikasi aplikasi.");
    }
}
