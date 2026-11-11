<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\WasteProcessingController;
use App\Http\Requests\Api\Tps3r\StoreTps3rDepositRequest;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProcessingRecord;
use App\Models\WasteWeighing;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class Tps3rController extends Controller
{
    public function facilities(): JsonResponse
    {
        $facilities = PengelolaTps3rProfile::query()
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->with('user:id,name,phone_number')
            ->orderBy('nama_tps3r')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar fasilitas TPS3R berhasil diambil.',
            'data' => $facilities,
        ]);
    }

    /**
     * Get list of waste deposits into TPS3R (TPS-F-002).
     */
    public function indexDeposits(Request $request): JsonResponse
    {
        $query = Tps3rWasteDeposit::query()->with([
            'pengelola:id,name,phone_number',
            'petugas:id,name,phone_number',
            'tps3rProfile',
            'weighings.warga:id,name,phone_number',
        ]);
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPengelolaTps3r()) {
            $query->where('pengelola_id', $currentUser->id);
        } elseif ($currentUser->isPetugasLapangan()) {
            $query->where('petugas_id', $currentUser->id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_setor', (string) $request->tanggal);
        }

        if ($request->filled('jenis_sampah')) {
            $query->where('jenis_sampah', (string) $request->jenis_sampah);
        }

        if ($request->filled('sumber_sampah')) {
            $query->where('sumber_sampah', (string) $request->sumber_sampah);
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        $perPage = min(max((int) ($request->per_page ?? 15), 1), 100);

        $deposits = $query->latest('tanggal_setor')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar setoran sampah TPS3R berhasil diambil.',
            'data' => $deposits->items(),
            'meta' => [
                'current_page' => $deposits->currentPage(),
                'last_page' => $deposits->lastPage(),
                'per_page' => $deposits->perPage(),
                'total' => $deposits->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show deposit details including citizen weighing evidence and photos.
     */
    public function showDeposit(Tps3rWasteDeposit $deposit): JsonResponse
    {
        Gate::authorize('view', $deposit);

        $deposit->load([
            'pengelola:id,name,phone_number',
            'petugas:id,name,phone_number',
            'tps3rProfile',
            'weighings.warga:id,name,phone_number',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail setoran sampah TPS3R berhasil diambil.',
            'data' => $deposit,
        ], Response::HTTP_OK);
    }

    /**
     * Store a new incoming waste deposit at TPS3R (TPS-F-002).
     */
    public function storeDeposit(StoreTps3rDepositRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPengelolaTps3r()) {
            $profile = $currentUser->pengelolaTps3rProfile;
            if ($profile === null) {
                throw ValidationException::withMessages([
                    'tps3r_profile_id' => ['Profil fasilitas TPS3R pengelola tidak ditemukan.'],
                ]);
            }

            if (! empty($validated['tps3r_profile_id']) && (int) $validated['tps3r_profile_id'] !== (int) $profile->id) {
                abort(
                    Response::HTTP_FORBIDDEN,
                    'Akses ditolak. Anda hanya dapat mencatat setoran untuk fasilitas TPS3R Anda sendiri.'
                );
            }
        } else {
            $profile = PengelolaTps3rProfile::find($validated['tps3r_profile_id'] ?? null);
            if ($profile === null) {
                throw ValidationException::withMessages([
                    'tps3r_profile_id' => ['Fasilitas TPS3R tujuan wajib dipilih.'],
                ]);
            }
        }

        $profile->loadMissing('user');
        if (! $profile->user || ! $profile->user->is_active) {
            throw ValidationException::withMessages([
                'tps3r_profile_id' => ['Fasilitas TPS3R tujuan sedang tidak aktif.'],
            ]);
        }

        $petugasId = $currentUser->isPetugasLapangan()
            ? $currentUser->id
            : ($validated['petugas_id'] ?? null);

        if (! $currentUser->isPetugasLapangan() && $petugasId !== null) {
            $assignedOfficer = User::find($petugasId);
            if ($assignedOfficer === null || ! $assignedOfficer->isPetugasLapangan() || ! $assignedOfficer->is_active) {
                throw ValidationException::withMessages([
                    'petugas_id' => ['Petugas pengirim sampah harus merupakan akun petugas lapangan yang aktif.'],
                ]);
            }
        }

        $clientReferenceId = isset($validated['client_reference_id']) && trim((string) $validated['client_reference_id']) !== ''
            ? trim((string) $validated['client_reference_id'])
            : null;

        if ($clientReferenceId !== null) {
            $replayResponse = $this->findIdempotentDepositResponse(
                $petugasId !== null ? (int) $petugasId : null,
                (int) $profile->id,
                $clientReferenceId
            );
            if ($replayResponse !== null) {
                return $replayResponse;
            }
        }

        $nomorResi = 'DEP-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $status = $currentUser->isPetugasLapangan() ? 'menunggu_konfirmasi' : 'diterima';
        $verifiedAt = $currentUser->isPengelolaTps3r() ? now() : null;
        $weighingIds = array_values(array_unique(array_map('intval', $validated['weighing_ids'] ?? [])));

        try {
            $deposit = DB::transaction(function () use ($validated, $currentUser, $profile, $petugasId, $clientReferenceId, $nomorResi, $status, $verifiedAt, $weighingIds) {
                if ($clientReferenceId !== null) {
                    $existingDeposit = $this->findIdempotentDepositModel(
                        $petugasId !== null ? (int) $petugasId : null,
                        (int) $profile->id,
                        $clientReferenceId
                    );
                    if ($existingDeposit !== null) {
                        return ['replay' => $existingDeposit];
                    }
                }

                // Sinkronkan 3 pilar komposisi muatan secara akurat dari Data Master
                if (! empty($weighingIds)) {
                    $weighings = WasteWeighing::whereIn('id', $weighingIds)->lockForUpdate()->get();

                    if ($weighings->count() !== count($weighingIds)) {
                        throw ValidationException::withMessages([
                            'weighing_ids' => ['Sebagian data timbangan tidak ditemukan.'],
                        ]);
                    }

                    if ($currentUser->isPetugasLapangan() && $weighings->contains(fn (WasteWeighing $w): bool => (int) $w->petugas_id !== (int) $currentUser->id)) {
                        abort(
                            Response::HTTP_FORBIDDEN,
                            'Akses ditolak. Anda hanya dapat menyetorkan hasil timbangan milik Anda sendiri.'
                        );
                    }

                    if (! $currentUser->isPetugasLapangan() && $petugasId !== null && $weighings->contains(fn (WasteWeighing $w): bool => (int) $w->petugas_id !== (int) $petugasId)) {
                        throw ValidationException::withMessages([
                            'weighing_ids' => ['Sebagian data timbangan bukan milik petugas pengirim yang dipilih.'],
                        ]);
                    }

                    if ($weighings->contains(fn (WasteWeighing $w): bool => $w->tps3r_waste_deposit_id !== null)) {
                        throw ValidationException::withMessages([
                            'weighing_ids' => ['Sebagian data timbangan sudah terhubung ke setoran TPS3R sebelumnya.'],
                        ]);
                    }
                } elseif ($currentUser->isPetugasLapangan()) {
                    $weighings = WasteWeighing::query()
                        ->where('petugas_id', $currentUser->id)
                        ->whereNull('tps3r_waste_deposit_id')
                        ->whereDate('created_at', now()->toDateString())
                        ->lockForUpdate()
                        ->get();
                } else {
                    $weighings = collect();
                }

                $orgPilah = isset($validated['berat_organik_pilah_kg']) ? (float) $validated['berat_organik_pilah_kg'] : null;
                $anorgPilah = isset($validated['berat_anorganik_pilah_kg']) ? (float) $validated['berat_anorganik_pilah_kg'] : null;
                $residuPilah = isset($validated['berat_residu_pilah_kg']) ? (float) $validated['berat_residu_pilah_kg'] : null;

                if ($weighings->isNotEmpty()) {
                    $calcOrg = 0.0;
                    $calcAnorg = 0.0;
                    $calcResidu = 0.0;

                    foreach ($weighings as $w) {
                        $pillar = MasterData::resolvePillar($w->jenis_sampah);
                        if ($pillar === 'organik') {
                            $calcOrg += (float) $w->berat_kg;
                        } elseif ($pillar === 'residu') {
                            $calcResidu += (float) $w->berat_kg;
                        } else {
                            $calcAnorg += (float) $w->berat_kg;
                        }
                    }

                    $orgPilah = $calcOrg;
                    $anorgPilah = $calcAnorg;
                    $residuPilah = $calcResidu;
                }

                $deposit = Tps3rWasteDeposit::create([
                    'pengelola_id' => $profile->user_id,
                    'tps3r_profile_id' => $profile->id,
                    'petugas_id' => $petugasId,
                    'nomor_resi' => $nomorResi,
                    'client_reference_id' => $clientReferenceId,
                    'route_code' => $validated['route_code'] ?? null,
                    'sumber_sampah' => $validated['sumber_sampah'],
                    'jenis_sampah' => $validated['jenis_sampah'],
                    'berat_kg' => (float) $validated['berat_kg'],
                    'berat_bruto_kg' => isset($validated['berat_bruto_kg']) ? (float) $validated['berat_bruto_kg'] : null,
                    'berat_tara_kg' => isset($validated['berat_tara_kg']) ? (float) $validated['berat_tara_kg'] : null,
                    'berat_organik_pilah_kg' => $orgPilah,
                    'berat_anorganik_pilah_kg' => $anorgPilah,
                    'berat_residu_pilah_kg' => $residuPilah,
                    'status' => $status,
                    'verified_at' => $verifiedAt,
                    'tanggal_setor' => $validated['tanggal_setor'],
                    'catatan' => $validated['catatan'] ?? null,
                ]);

                // Hubungkan timbangan warga dengan setoran truk ini
                if ($weighings->isNotEmpty()) {
                    WasteWeighing::whereIn('id', $weighings->pluck('id')->all())
                        ->update(['tps3r_waste_deposit_id' => $deposit->id]);
                }

                return $deposit->load(['pengelola:id,name', 'petugas:id,name', 'tps3rProfile', 'weighings.warga:id,name']);
            });
        } catch (UniqueConstraintViolationException|QueryException $exception) {
            if ($clientReferenceId !== null && (string) $exception->getCode() === '23000') {
                $replayResponse = $this->findIdempotentDepositResponse(
                    $petugasId !== null ? (int) $petugasId : null,
                    (int) $profile->id,
                    $clientReferenceId
                );
                if ($replayResponse !== null) {
                    return $replayResponse;
                }
            }

            throw $exception;
        }

        if (is_array($deposit) && isset($deposit['replay'])) {
            return $this->buildIdempotentDepositResponse($deposit['replay']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Penerimaan setoran sampah di TPS3R berhasil dicatat.',
            'data' => $deposit,
        ], Response::HTTP_CREATED);
    }

    /**
     * Find an existing deposit matching an offline sync client reference.
     */
    private function findIdempotentDepositModel(?int $petugasId, int $profileId, string $clientReferenceId): ?Tps3rWasteDeposit
    {
        $query = Tps3rWasteDeposit::query()
            ->where('client_reference_id', $clientReferenceId)
            ->with(['pengelola:id,name', 'petugas:id,name', 'tps3rProfile', 'weighings.warga:id,name']);

        if ($petugasId !== null) {
            $query->where('petugas_id', $petugasId);
        } else {
            $query->whereNull('petugas_id')
                ->where('tps3r_profile_id', $profileId);
        }

        return $query->first();
    }

    /**
     * Return an idempotent HTTP response if the client_reference_id was already stored.
     */
    private function findIdempotentDepositResponse(?int $petugasId, int $profileId, string $clientReferenceId): ?JsonResponse
    {
        $existingDeposit = $this->findIdempotentDepositModel($petugasId, $profileId, $clientReferenceId);

        if ($existingDeposit === null) {
            return null;
        }

        return $this->buildIdempotentDepositResponse($existingDeposit);
    }

    /**
     * Format an idempotent TPS3R deposit replay payload.
     */
    private function buildIdempotentDepositResponse(Tps3rWasteDeposit $existingDeposit): JsonResponse
    {
        return response()->json([
            'success' => true,
            'idempotent_replay' => true,
            'message' => 'Setoran sampah TPS3R dari sinkronisasi offline sudah tercatat sebelumnya.',
            'data' => $existingDeposit,
        ], Response::HTTP_OK);
    }

    /**
     * Verify / confirm waste deposit reception by TPS3R operator.
     */
    public function verifyDeposit(Request $request, Tps3rWasteDeposit $deposit): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPengelolaTps3r()) {
            $profile = $currentUser->pengelolaTps3rProfile;
            abort_unless($profile && $deposit->tps3r_profile_id === $profile->id, Response::HTTP_FORBIDDEN, 'Anda tidak berwenang mengonfirmasi setoran fasilitas lain.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:diterima,ditolak,perlu_perbaikan'],
            'berat_konfirmasi_kg' => ['nullable', 'numeric', 'min:0.1', 'max:999999.99'],
            'berat_organik_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'berat_anorganik_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'berat_residu_pilah_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $status = (string) $validated['status'];
        $beratKonfirmasi = isset($validated['berat_konfirmasi_kg'])
            ? (float) $validated['berat_konfirmasi_kg']
            : ($status === 'diterima' ? $deposit->berat_kg : null);

        $updateData = [
            'status' => $status,
            'berat_konfirmasi_kg' => $beratKonfirmasi,
            'verified_at' => now(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
        ];

        foreach (['berat_organik_pilah_kg', 'berat_anorganik_pilah_kg', 'berat_residu_pilah_kg'] as $pillarField) {
            if (array_key_exists($pillarField, $validated)) {
                $updateData[$pillarField] = $validated[$pillarField];
            }
        }

        $deposit->update($updateData);

        $deposit->load(['pengelola:id,name', 'petugas:id,name', 'tps3rProfile']);

        $statusText = match ($status) {
            'diterima' => 'diterima dan diverifikasi',
            'perlu_perbaikan' => 'dikembalikan untuk koreksi data',
            default => 'ditolak',
        };

        return response()->json([
            'success' => true,
            'message' => "Setoran sampah berhasil {$statusText}.",
            'data' => $deposit,
        ], Response::HTTP_OK);
    }

    /**
     * Get volume recap and capacity statistics (TPS-F-003).
     */
    public function volumeRecap(Request $request): JsonResponse
    {
        $query = Tps3rWasteDeposit::query();

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $query->whereDate('tanggal_setor', '>=', $startDate)
            ->whereDate('tanggal_setor', '<=', $endDate);

        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPengelolaTps3r() && $currentUser->pengelolaTps3rProfile) {
            $query->where('tps3r_profile_id', $currentUser->pengelolaTps3rProfile->id);
        } elseif ($request->filled('tps3r_profile_id')) {
            $query->where('tps3r_profile_id', (int) $request->tps3r_profile_id);
        }

        // Aggregate by waste type secara akurat (komposisi manifest & pemilahan riil)
        $allDeposits = (clone $query)->with('weighings:id,tps3r_waste_deposit_id,jenis_sampah,berat_kg')->get();

        $organikKg = 0.0;
        $anorganikKg = 0.0;
        $residuKg = 0.0;
        $organikCount = 0;
        $anorganikCount = 0;
        $residuCount = 0;

        foreach ($allDeposits as $dep) {
            if ($dep->berat_organik_pilah_kg !== null || $dep->berat_anorganik_pilah_kg !== null || $dep->berat_residu_pilah_kg !== null) {
                $organikKg += (float) ($dep->berat_organik_pilah_kg ?? 0);
                $anorganikKg += (float) ($dep->berat_anorganik_pilah_kg ?? 0);
                $residuKg += (float) ($dep->berat_residu_pilah_kg ?? 0);
                if (($dep->berat_organik_pilah_kg ?? 0) > 0) {
                    $organikCount++;
                }
                if (($dep->berat_anorganik_pilah_kg ?? 0) > 0) {
                    $anorganikCount++;
                }
                if (($dep->berat_residu_pilah_kg ?? 0) > 0) {
                    $residuCount++;
                }
            } elseif ($dep->weighings->isNotEmpty()) {
                $weighingSum = (float) $dep->weighings->sum('berat_kg');
                $depOrganik = 0.0;
                $depAnorganik = 0.0;
                $depResidu = 0.0;

                foreach ($dep->weighings as $w) {
                    $pillar = MasterData::resolvePillar($w->jenis_sampah);
                    $wWeight = (float) $w->berat_kg;
                    if ($pillar === 'organik') {
                        $depOrganik += $wWeight;
                    } elseif ($pillar === 'residu') {
                        $depResidu += $wWeight;
                    } else {
                        $depAnorganik += $wWeight;
                    }
                }

                if ($weighingSum > 0 && abs((float) $dep->berat_kg - $weighingSum) > 0.01) {
                    $ratio = (float) $dep->berat_kg / $weighingSum;
                    $organikKg += round($depOrganik * $ratio, 2);
                    $anorganikKg += round($depAnorganik * $ratio, 2);
                    $residuKg += round($depResidu * $ratio, 2);
                } else {
                    $organikKg += $depOrganik;
                    $anorganikKg += $depAnorganik;
                    $residuKg += $depResidu;
                }

                if ($depOrganik > 0) {
                    $organikCount++;
                }
                if ($depAnorganik > 0) {
                    $anorganikCount++;
                }
                if ($depResidu > 0) {
                    $residuCount++;
                }
            } else {
                $pillar = MasterData::resolvePillar($dep->jenis_sampah);
                $dWeight = (float) $dep->berat_kg;
                if ($pillar === 'organik') {
                    $organikKg += $dWeight;
                    $organikCount++;
                } elseif ($pillar === 'residu') {
                    $residuKg += $dWeight;
                    $residuCount++;
                } else {
                    $anorganikKg += $dWeight;
                    $anorganikCount++;
                }
            }
        }

        $byJenisSampah = [
            ['jenis_sampah' => 'organik', 'total_berat_kg' => round($organikKg, 2), 'total_transaksi' => $organikCount],
            ['jenis_sampah' => 'anorganik_daur_ulang', 'total_berat_kg' => round($anorganikKg, 2), 'total_transaksi' => $anorganikCount],
            ['jenis_sampah' => 'residu', 'total_berat_kg' => round($residuKg, 2), 'total_transaksi' => $residuCount],
        ];

        // Aggregate by waste source
        $bySumberSampah = (clone $query)->selectRaw('sumber_sampah, SUM(berat_kg) as total_berat_kg, COUNT(*) as total_transaksi')
            ->groupBy('sumber_sampah')
            ->get();

        // Total overall weight
        $totalBerat = (float) (clone $query)->sum('berat_kg');
        $totalTransaksi = (int) (clone $query)->count();

        // TPS3R Profile info if user is Pengelola
        $tps3rProfile = $currentUser->pengelolaTps3rProfile;
        $kapasitasHarian = $tps3rProfile ? (float) $tps3rProfile->kapasitas_harian_kg : null;

        return response()->json([
            'success' => true,
            'message' => 'Rekapitulasi volume sampah TPS3R berhasil diambil.',
            'data' => [
                'periode' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'ringkasan' => [
                    'total_volume_kg' => round($totalBerat, 2),
                    'total_transaksi_setoran' => $totalTransaksi,
                    'kapasitas_harian_tps3r_kg' => $kapasitasHarian,
                ],
                'rincian_per_jenis_sampah' => $byJenisSampah,
                'rincian_per_sumber_sampah' => $bySumberSampah,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * List waste processing batches for TPS3R API parity.
     */
    public function indexProcessing(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = WasteProcessingRecord::query()
            ->with(['deposit:id,nomor_resi,jenis_sampah,berat_kg', 'tps3rProfile:id,public_id,nama_tps3r']);

        if ($user->isPengelolaTps3r()) {
            $query->where('pengelola_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        $records = $query->latest('started_on')->latest('id')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Daftar batch pengolahan sampah TPS3R berhasil diambil.',
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'process_methods' => WasteProcessingController::processMethods(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store a new TPS3R waste processing batch via API.
     */
    public function storeProcessing(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile;
        abort_if($profile === null, Response::HTTP_UNPROCESSABLE_ENTITY, 'Lengkapi profil TPS3R terlebih dahulu.');

        $masterCategoryCodes = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->pluck('code')
            ->all();

        $allowedWasteTypes = array_unique(array_merge(
            ['organik', 'anorganik_daur_ulang', 'residu'],
            $masterCategoryCodes,
        ));

        $validated = $request->validate([
            'tps3r_waste_deposit_id' => ['nullable', 'integer', 'exists:tps3r_waste_deposits,id'],
            'process_method' => ['required', Rule::in(array_keys(WasteProcessingController::processMethods()))],
            'waste_type' => ['required', Rule::in($allowedWasteTypes)],
            'input_weight_kg' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
            'started_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($validated['tps3r_waste_deposit_id'])) {
            Tps3rWasteDeposit::query()
                ->where('pengelola_id', $user->id)
                ->findOrFail($validated['tps3r_waste_deposit_id']);
        }

        $record = WasteProcessingRecord::create([
            ...$validated,
            'pengelola_id' => $user->id,
            'tps3r_profile_id' => $profile->id,
            'status' => 'planned',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batch pengolahan sampah berhasil dibuat.',
            'data' => $record->load('deposit:id,nomor_resi,jenis_sampah,berat_kg'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a TPS3R waste processing batch status via API.
     */
    public function updateProcessing(Request $request, WasteProcessingRecord $wasteProcessingRecord): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->isAdminDlh() || $wasteProcessingRecord->pengelola_id === $user->id, Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['processing', 'completed', 'cancelled'])],
            'output_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $allowed = [
            'planned' => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
        ];

        if (! in_array($validated['status'], $allowed[$wasteProcessingRecord->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => ['Perubahan status pengolahan tidak valid.']]);
        }

        $wasteProcessingRecord->update([
            ...$validated,
            'completed_on' => $validated['status'] === 'completed' ? now()->toDateString() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status pengolahan berhasil diperbarui.',
            'data' => $wasteProcessingRecord->fresh('deposit:id,nomor_resi,jenis_sampah,berat_kg'),
        ], Response::HTTP_OK);
    }
}
