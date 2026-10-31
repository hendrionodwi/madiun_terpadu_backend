<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tracking\StorePickupScheduleRequest;
use App\Http\Requests\Api\Tracking\StoreWasteWeighingRequest;
use App\Http\Requests\Api\Tracking\UpdatePickupScheduleRequest;
use App\Http\Requests\Api\Tracking\UpdatePickupScheduleStatusRequest;
use App\Models\MasterData;
use App\Models\PickupSchedule;
use App\Models\PointLedger;
use App\Models\RetributionBill;
use App\Models\RouteAssignment;
use App\Models\RouteSession;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteWeighing;
use App\Services\WeighingProofService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    /**
     * Search active warga for use when recording a waste weighing.
     */
    public function indexWarga(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $search = trim($validated['search']);
        $warga = User::query()
            ->select(['id', 'name', 'phone_number'])
            ->where('role', UserRole::Warga)
            ->where('is_active', true)
            ->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar warga berhasil diambil.',
            'data' => $warga,
        ], Response::HTTP_OK);
    }

    /**
     * Get available operational routes and schedules in Kota Madiun.
     */
    public function indexRoutes(Request $request): JsonResponse
    {
        $query = RouteAssignment::query()
            ->where('is_active', true)
            ->with(['petugas:id,name,phone_number', 'todaySession.petugas:id,name,phone_number'])
            ->orderBy('route_code');

        /** @var User|null $currentUser */
        $currentUser = $request->user();
        if ($request->filled('petugas_id')) {
            $query->where('petugas_id', (int) $request->petugas_id);
        } elseif ($request->boolean('my_routes') && $currentUser) {
            $query->where('petugas_id', $currentUser->id);
        }

        $routes = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar rute penjemputan berhasil diambil.',
            'data' => $routes,
        ], Response::HTTP_OK);
    }

    /**
     * Get list of pickup schedules (TRK-F-001).
     */
    public function indexSchedules(Request $request): JsonResponse
    {
        $query = PickupSchedule::query()->with([
            'petugas:id,name,phone_number',
            'warga:id,name,phone_number',
        ]);
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPetugasLapangan()) {
            $query->where('petugas_id', $currentUser->id);
        }

        if ($currentUser->isWarga()) {
            $query->where('warga_id', $currentUser->id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_penjemputan', (string) $request->tanggal);
        } elseif ($request->boolean('active_manifest') || $request->boolean('active_only') || ($currentUser->isPetugasLapangan() && ! $request->filled('status'))) {
            $today = now()->toDateString();
            $query->where(function ($q) use ($today) {
                $q->whereNotIn('status', ['selesai', 'dibatalkan', 'batal'])
                    ->orWhere(function ($sub) use ($today) {
                        $sub->where('status', 'selesai')
                            ->whereDate('tanggal_penjemputan', $today);
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('id_wilayah_rute')) {
            $query->where('id_wilayah_rute', (string) $request->id_wilayah_rute);
        }

        $schedules = $query->orderBy('tanggal_penjemputan', 'desc')
            ->orderBy('waktu_mulai', 'asc')
            ->paginate((int) ($request->per_page ?? 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar jadwal penjemputan berhasil diambil.',
            'data' => $schedules->items(),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'total' => $schedules->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Dapatkan status sesi perjalanan armada hari ini untuk rute tertentu.
     */
    public function showRouteSession(Request $request, string $routeCode): JsonResponse
    {
        $route = RouteAssignment::where('route_code', $routeCode)->firstOrFail();
        $today = now()->toDateString();

        $session = RouteSession::where('route_assignment_id', $route->id)
            ->whereDate('tanggal', $today)
            ->with('petugas:id,name,phone_number')
            ->first();

        if (! $session) {
            $session = new RouteSession([
                'route_assignment_id' => $route->id,
                'petugas_id' => $route->petugas_id,
                'route_code' => $route->route_code,
                'tanggal' => $today,
                'status' => 'terjadwal',
                'start_location' => $route->start_location,
                'end_facility' => $route->end_facility,
                'start_latitude' => $route->latitude,
                'start_longitude' => $route->longitude,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status sesi rute berhasil dimuat.',
            'data' => $session,
        ], Response::HTTP_OK);
    }

    /**
     * Mulai sesi perjalanan armada penjemputan di Depo.
     */
    public function startRouteSession(Request $request, string $routeCode): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        abort_unless(
            $currentUser->isPetugasLapangan() || $currentUser->isAdminDlh(),
            Response::HTTP_FORBIDDEN,
            'Hanya petugas lapangan atau Admin DLH yang dapat memulai sesi rute.'
        );

        $route = RouteAssignment::where('route_code', $routeCode)->firstOrFail();
        $today = now()->toDateString();
        $nowTime = now()->format('H:i');

        $startLat = $route->latitude;
        $startLng = $route->longitude;
        $endLat = $route->latitude;
        $endLng = $route->longitude;

        if (! empty($route->waypoints) && is_array($route->waypoints)) {
            $waypoints = $route->waypoints;
            $first = $waypoints[0] ?? null;
            $last = end($waypoints) ?? null;
            if (is_array($first) && count($first) >= 2) {
                $startLat = (float) $first[0];
                $startLng = (float) $first[1];
            }
            if (is_array($last) && count($last) >= 2) {
                $endLat = (float) $last[0];
                $endLng = (float) $last[1];
            }
        }

        $session = RouteSession::updateOrCreate(
            [
                'route_assignment_id' => $route->id,
                'tanggal' => $today,
            ],
            [
                'petugas_id' => $currentUser->isPetugasLapangan() ? $currentUser->id : $route->petugas_id,
                'route_code' => $route->route_code,
                'status' => 'berlangsung',
                'waktu_mulai' => $nowTime,
                'start_location' => $route->start_location ?: 'Depo Wilayah '.$route->district,
                'end_facility' => $route->end_facility ?: 'TPS3R Tujuan Akhir',
                'start_latitude' => $startLat,
                'start_longitude' => $startLng,
                'end_latitude' => $endLat,
                'end_longitude' => $endLng,
                'catatan' => $request->input('catatan'),
            ]
        );

        $session->load('petugas:id,name,phone_number');

        return response()->json([
            'success' => true,
            'message' => 'Sesi penjemputan armada rute '.$route->route_code.' resmi dimulai.',
            'data' => $session,
        ], Response::HTTP_OK);
    }

    /**
     * Selesaikan sesi perjalanan armada penjemputan di TPS3R.
     */
    public function finishRouteSession(Request $request, string $routeCode): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        abort_unless(
            $currentUser->isPetugasLapangan() || $currentUser->isAdminDlh(),
            Response::HTTP_FORBIDDEN,
            'Hanya petugas lapangan atau Admin DLH yang dapat menyelesaikan sesi rute.'
        );

        $route = RouteAssignment::where('route_code', $routeCode)->firstOrFail();
        $today = now()->toDateString();
        $nowTime = now()->format('H:i');

        $session = RouteSession::where('route_assignment_id', $route->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (! $session) {
            $session = RouteSession::create([
                'route_assignment_id' => $route->id,
                'petugas_id' => $currentUser->isPetugasLapangan() ? $currentUser->id : $route->petugas_id,
                'route_code' => $route->route_code,
                'tanggal' => $today,
                'status' => 'selesai',
                'waktu_mulai' => $route->start_time,
                'waktu_selesai' => $nowTime,
                'start_location' => $route->start_location,
                'end_facility' => $route->end_facility,
                'start_latitude' => $route->latitude,
                'start_longitude' => $route->longitude,
                'catatan' => $request->input('catatan'),
            ]);
        } else {
            $session->update([
                'status' => 'selesai',
                'waktu_selesai' => $nowTime,
                'catatan' => $request->input('catatan') ?? $session->catatan,
            ]);
        }

        $session->load('petugas:id,name,phone_number');

        return response()->json([
            'success' => true,
            'message' => 'Sesi penjemputan armada rute '.$route->route_code.' telah selesai di TPS3R.',
            'data' => $session,
        ], Response::HTTP_OK);
    }

    /**
     * Start a new pickup session or reset daily route pickup points (TRK-F-001).
     */
    public function renewRouteSession(Request $request, string $routeCode): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        abort_unless(
            $currentUser->isPetugasLapangan() || $currentUser->isAdminDlh(),
            Response::HTTP_FORBIDDEN,
            'Hanya petugas lapangan atau Admin DLH yang dapat memperbarui sesi rute.'
        );

        $today = now()->toDateString();
        $targetPetugasId = $currentUser->isPetugasLapangan() ? $currentUser->id : null;

        $route = RouteAssignment::query()
            ->where('route_code', $routeCode)
            ->where('is_active', true)
            ->firstOrFail();

        if ($currentUser->isPetugasLapangan()) {
            abort_unless(
                $route->petugas_id === $currentUser->id,
                Response::HTTP_FORBIDDEN,
                'Petugas hanya dapat memperbarui sesi rute yang ditugaskan kepadanya.'
            );
        }

        $query = PickupSchedule::query()
            ->where('id_wilayah_rute', $routeCode)
            ->whereDate('tanggal_penjemputan', $today);

        if ($targetPetugasId) {
            $query->where('petugas_id', $targetPetugasId);
        }

        $existing = $query->get();

        foreach ($existing as $schedule) {
            WasteWeighing::where('pickup_schedule_id', $schedule->id)
                ->update(['pickup_schedule_id' => null]);

            $schedule->update(['status' => 'terjadwal']);
        }

        $refreshed = (clone $query)->with(['petugas:id,name', 'warga:id,name'])->get();

        return response()->json([
            'success' => true,
            'message' => $existing->isEmpty()
                ? "Tidak ada jadwal penjemputan rute {$routeCode} untuk diperbarui hari ini."
                : "Sesi penjemputan rute {$routeCode} berhasil diperbarui. Titik penjemputan warga telah aktif kembali.",
            'data' => $refreshed,
        ], Response::HTTP_OK);
    }

    /**
     * Update the progress status of a pickup schedule owned by the petugas.
     *
     * @throws ValidationException
     */
    public function updateScheduleStatus(UpdatePickupScheduleStatusRequest $request, PickupSchedule $pickupSchedule): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        abort_unless(
            $currentUser->isAdminDlh() || $pickupSchedule->petugas_id === $currentUser->id,
            Response::HTTP_FORBIDDEN,
        );

        $validated = $request->validated();
        $status = $validated['status'];
        $allowedTransitions = [
            'terjadwal' => ['berlangsung', 'batal'],
            'berlangsung' => ['selesai', 'batal'],
        ];

        if (! in_array($status, $allowedTransitions[$pickupSchedule->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => ['Perubahan status jadwal tidak valid.'],
            ]);
        }

        $updateData = ['status' => $status];
        if (array_key_exists('keterangan', $validated) && $validated['keterangan'] !== null) {
            $updateData['keterangan'] = $validated['keterangan'];
        }

        $pickupSchedule->update($updateData);
        $pickupSchedule->load('petugas:id,name,phone_number');

        return response()->json([
            'success' => true,
            'message' => 'Status jadwal penjemputan berhasil diperbarui.',
            'data' => $pickupSchedule,
        ], Response::HTTP_OK);
    }

    /**
     * Create a new pickup schedule (TRK-F-001).
     */
    public function storeSchedule(StorePickupScheduleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $currentUser */
        $currentUser = $request->user();

        // Assign petugas and warga based on authenticated role
        if ($currentUser->isWarga()) {
            // TRK-F-013: Security gate - block scheduling if citizen has overdue unpaid retribution bills
            $hasOverdueBill = RetributionBill::where('warga_id', $currentUser->id)
                ->overdue()
                ->first();

            if ($hasOverdueBill) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'RETRIBUTION_OVERDUE_LOCKED',
                    'message' => "Layanan penjemputan sampah ditangguhkan sementara karena terdapat tagihan retribusi sampah yang melewati tanggal jatuh tempo ({$hasOverdueBill->nomor_tagihan}, Periode {$hasOverdueBill->periode_label}). Silakan lunasi tagihan melalui menu Retribusi Warga untuk membuka kembali akses penjemputan.",
                    'data' => [
                        'overdue_bill' => $hasOverdueBill,
                    ],
                ], Response::HTTP_FORBIDDEN);
            }

            $wargaId = $currentUser->id;
            $petugasId = $validated['petugas_id'] ?? null;
            if (! $petugasId) {
                $assignment = RouteAssignment::where('route_code', $validated['id_wilayah_rute'])->first();
                $petugasId = $assignment?->petugas_id ?? User::where('role', UserRole::PetugasLapangan)->value('id');
            }
            $alamatPenjemputan = $validated['alamat_penjemputan'] ?? $currentUser->wargaProfile?->alamat;
            $latitude = $validated['latitude'] ?? $currentUser->wargaProfile?->latitude;
            $longitude = $validated['longitude'] ?? $currentUser->wargaProfile?->longitude;
        } else {
            $wargaId = $validated['warga_id'] ?? null;
            $petugasId = $validated['petugas_id'] ?? $currentUser->id;
            $alamatPenjemputan = $validated['alamat_penjemputan'] ?? null;
            $latitude = $validated['latitude'] ?? null;
            $longitude = $validated['longitude'] ?? null;
        }

        if (! $petugasId) {
            $petugasId = User::where('role', UserRole::PetugasLapangan)->value('id');
        }

        $schedule = PickupSchedule::create([
            'petugas_id' => $petugasId,
            'warga_id' => $wargaId,
            'id_wilayah_rute' => $validated['id_wilayah_rute'],
            'nama_wilayah_rute' => $validated['nama_wilayah_rute'] ?? null,
            'jenis_sampah' => $validated['jenis_sampah'] ?? null,
            'tanggal_penjemputan' => $validated['tanggal_penjemputan'],
            'waktu_mulai' => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'status' => $validated['status'] ?? 'terjadwal',
            'keterangan' => $validated['keterangan'] ?? null,
            'alamat_penjemputan' => $alamatPenjemputan,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        $schedule->load(['petugas:id,name,phone_number', 'warga:id,name,phone_number']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal penjemputan sampah berhasil dibuat.',
            'data' => $schedule,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update an existing pickup schedule (only when status is 'terjadwal').
     */
    public function updateSchedule(UpdatePickupScheduleRequest $request, PickupSchedule $pickupSchedule): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        abort_unless(
            $currentUser->isAdminDlh() || $pickupSchedule->warga_id === $currentUser->id || $pickupSchedule->petugas_id === $currentUser->id,
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses untuk mengubah jadwal ini.'
        );

        if (in_array($pickupSchedule->status, ['berlangsung', 'selesai', 'dibatalkan', 'batal'], true)) {
            throw ValidationException::withMessages([
                'status' => ["Jadwal dengan status '{$pickupSchedule->status}' tidak dapat diubah lagi."],
            ]);
        }

        $validated = $request->validated();
        $pickupSchedule->update(array_filter($validated, fn ($val) => $val !== null));
        $pickupSchedule->load(['petugas:id,name,phone_number', 'warga:id,name,phone_number']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal penjemputan sampah berhasil diperbarui.',
            'data' => $pickupSchedule,
        ], Response::HTTP_OK);
    }

    /**
     * Delete or cancel a pickup schedule (allowed unless ongoing or completed).
     */
    public function destroySchedule(Request $request, PickupSchedule $pickupSchedule): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        abort_unless(
            $currentUser->isAdminDlh() || $pickupSchedule->warga_id === $currentUser->id,
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki izin untuk membatalkan atau menghapus jadwal ini.'
        );

        if (in_array($pickupSchedule->status, ['berlangsung', 'selesai'], true)) {
            throw ValidationException::withMessages([
                'status' => ["Jadwal penjemputan dengan status '{$pickupSchedule->status}' tidak dapat dibatalkan atau dihapus karena sedang dalam proses atau sudah selesai."],
            ]);
        }

        WasteWeighing::where('pickup_schedule_id', $pickupSchedule->id)
            ->update(['pickup_schedule_id' => null]);

        $pickupSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal penjemputan sampah berhasil dibatalkan dan dihapus.',
        ], Response::HTTP_OK);
    }

    /**
     * Record waste weighing results and automatically add citizen reward points (TRK-F-006 & TRK-F-009).
     * Supports both single-item and multi-item submissions.
     */
    public function storeWeighing(StoreWasteWeighingRequest $request, WeighingProofService $proofService): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $petugas */
        $petugas = $request->user();

        $items = $validated['items'] ?? [
            [
                'jenis_sampah' => $validated['jenis_sampah'] ?? 'organik',
                'berat_kg' => $validated['berat_kg'] ?? 0,
                'poin_per_kg' => $validated['poin_per_kg'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'foto_bukti' => $validated['foto_bukti'] ?? null,
            ],
        ];

        $warga = User::find($validated['warga_id']);

        $result = DB::transaction(function () use ($validated, $petugas, $items, $proofService, $warga) {
            $createdWeighings = [];
            $totalPoinDidapat = 0;

            foreach ($items as $item) {
                $beratKg = (float) $item['berat_kg'];
                $poinPerKg = (int) (MasterData::query()
                    ->where('group', 'waste_category')
                    ->where(function ($q) use ($item) {
                        $q->where('code', $item['jenis_sampah'])
                            ->orWhere('name', $item['jenis_sampah']);
                    })
                    ->where('is_active', true)
                    ->value('numeric_value') ?? ($item['poin_per_kg'] ?? 100));
                $poinDidapat = (int) round($beratKg * $poinPerKg);
                $totalPoinDidapat += $poinDidapat;

                $fotoBukti = $item['foto_bukti'] ?? null;
                if ($fotoBukti) {
                    $fotoBukti = $proofService->processProofInput($fotoBukti, [
                        'berat_kg' => $beratKg,
                        'jenis_sampah' => $item['jenis_sampah'],
                        'warga_name' => $warga?->name,
                        'petugas_name' => $petugas->name,
                        'timestamp' => now()->format('d M Y, H:i').' WIB',
                    ]);
                }

                $weighing = WasteWeighing::create([
                    'warga_id' => $validated['warga_id'],
                    'petugas_id' => $petugas->id,
                    'pickup_schedule_id' => $validated['pickup_schedule_id'] ?? null,
                    'jenis_sampah' => $item['jenis_sampah'],
                    'berat_kg' => $beratKg,
                    'poin_per_kg' => $poinPerKg,
                    'poin_didapat' => $poinDidapat,
                    'catatan' => $item['catatan'] ?? null,
                    'foto_bukti' => $fotoBukti,
                ]);

                $createdWeighings[] = $weighing->load([
                    'warga:id,name,email,phone_number',
                    'petugas:id,name,phone_number',
                    'pickupSchedule',
                ]);
            }

            // Otomatis update status jadwal penjemputan menjadi selesai
            if (! empty($validated['pickup_schedule_id'])) {
                PickupSchedule::where('id', $validated['pickup_schedule_id'])
                    ->update(['status' => 'selesai']);
            }

            // TRK-F-009 & TRK-F-010: Otomatis menambahkan saldo poin ke profil warga dan buku kas mutasi
            if ($totalPoinDidapat > 0) {
                PointLedger::record(
                    (int) $validated['warga_id'],
                    'kredit',
                    'setor_sampah',
                    $totalPoinDidapat,
                    "Penerimaan insentif pilah sampah sebesar {$totalPoinDidapat} poin",
                    $createdWeighings[0] ?? null
                );
            }
            $wargaProfile = WargaProfile::firstOrCreate(
                ['user_id' => $validated['warga_id']],
                ['saldo_poin' => 0]
            );

            return [
                'weighings' => $createdWeighings,
                'first_weighing' => $createdWeighings[0] ?? null,
                'total_poin_didapat' => $totalPoinDidapat,
                'saldo_poin_terbaru' => $wargaProfile->fresh()->saldo_poin,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Hasil timbangan berhasil dicatat. Poin warga bertambah sebanyak {$result['total_poin_didapat']} poin.",
            'data' => [
                'timbangan' => $result['first_weighing'],
                'items' => $result['weighings'],
                'poin_didapat' => $result['total_poin_didapat'],
                'total_saldo_poin_warga' => $result['saldo_poin_terbaru'],
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Get history of waste weighings.
     */
    public function indexWeighings(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $query = WasteWeighing::query()->with([
            'warga:id,name,phone_number',
            'petugas:id,name,phone_number',
            'pickupSchedule',
        ]);

        if ($currentUser->isWarga()) {
            $query->where('warga_id', $currentUser->id);
        } elseif ($currentUser->isPetugasLapangan()) {
            $query->where('petugas_id', $currentUser->id);
        }

        if ($request->filled('warga_id')) {
            $query->where('warga_id', (int) $request->warga_id);
        }

        if ($request->filled('jenis_sampah')) {
            $query->where('jenis_sampah', (string) $request->jenis_sampah);
        }

        $weighings = $query->latest()->paginate((int) ($request->per_page ?? 15));

        return response()->json([
            'success' => true,
            'message' => 'Riwayat hasil penimbangan sampah berhasil diambil.',
            'data' => $weighings->items(),
            'meta' => [
                'current_page' => $weighings->currentPage(),
                'last_page' => $weighings->lastPage(),
                'per_page' => $weighings->perPage(),
                'total' => $weighings->total(),
            ],
        ], Response::HTTP_OK);
    }
}
