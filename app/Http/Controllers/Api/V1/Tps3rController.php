<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tps3r\StoreTps3rDepositRequest;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteWeighing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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

        $deposits = $query->latest('tanggal_setor')
            ->latest('id')
            ->paginate((int) ($request->per_page ?? 15));

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
        $profile = $currentUser->isPengelolaTps3r()
            ? $currentUser->pengelolaTps3rProfile
            : PengelolaTps3rProfile::find($validated['tps3r_profile_id'] ?? null);

        if ($profile === null) {
            throw ValidationException::withMessages([
                'tps3r_profile_id' => ['Fasilitas TPS3R tujuan wajib dipilih.'],
            ]);
        }

        $petugasId = $currentUser->isPetugasLapangan()
            ? $currentUser->id
            : ($validated['petugas_id'] ?? null);

        $nomorResi = 'DEP-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $status = $currentUser->isPetugasLapangan() ? 'menunggu_konfirmasi' : 'diterima';
        $verifiedAt = $currentUser->isPengelolaTps3r() ? now() : null;

        // Sinkronkan 3 pilar komposisi muatan secara akurat dari Data Master
        $weighings = ! empty($validated['weighing_ids'])
            ? WasteWeighing::whereIn('id', $validated['weighing_ids'])->get()
            : ($currentUser->isPetugasLapangan()
                ? WasteWeighing::query()
                    ->where('petugas_id', $currentUser->id)
                    ->whereNull('tps3r_waste_deposit_id')
                    ->whereDate('created_at', now()->toDateString())
                    ->get()
                : collect());

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

            $validated['berat_organik_pilah_kg'] = $calcOrg;
            $validated['berat_anorganik_pilah_kg'] = $calcAnorg;
            $validated['berat_residu_pilah_kg'] = $calcResidu;
        }

        $deposit = Tps3rWasteDeposit::create([
            'pengelola_id' => $profile->user_id,
            'tps3r_profile_id' => $profile->id,
            'petugas_id' => $petugasId,
            'nomor_resi' => $nomorResi,
            'route_code' => $validated['route_code'] ?? null,
            'sumber_sampah' => $validated['sumber_sampah'],
            'jenis_sampah' => $validated['jenis_sampah'],
            'berat_kg' => (float) $validated['berat_kg'],
            'berat_organik_pilah_kg' => isset($validated['berat_organik_pilah_kg']) ? (float) $validated['berat_organik_pilah_kg'] : null,
            'berat_anorganik_pilah_kg' => isset($validated['berat_anorganik_pilah_kg']) ? (float) $validated['berat_anorganik_pilah_kg'] : null,
            'berat_residu_pilah_kg' => isset($validated['berat_residu_pilah_kg']) ? (float) $validated['berat_residu_pilah_kg'] : null,
            'status' => $status,
            'verified_at' => $verifiedAt,
            'tanggal_setor' => $validated['tanggal_setor'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        // Hubungkan timbangan warga dengan setoran truk ini
        if (! empty($validated['weighing_ids'])) {
            WasteWeighing::whereIn('id', $validated['weighing_ids'])
                ->update(['tps3r_waste_deposit_id' => $deposit->id]);
        } elseif ($currentUser->isPetugasLapangan()) {
            WasteWeighing::query()
                ->where('petugas_id', $currentUser->id)
                ->whereNull('tps3r_waste_deposit_id')
                ->whereDate('created_at', now()->toDateString())
                ->update(['tps3r_waste_deposit_id' => $deposit->id]);
        }

        $deposit->load(['pengelola:id,name', 'petugas:id,name', 'tps3rProfile', 'weighings.warga:id,name']);

        return response()->json([
            'success' => true,
            'message' => 'Penerimaan setoran sampah di TPS3R berhasil dicatat.',
            'data' => $deposit,
        ], Response::HTTP_CREATED);
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
            'status' => ['required', 'string', 'in:diterima,ditolak'],
            'berat_konfirmasi_kg' => ['nullable', 'numeric', 'min:0.1', 'max:999999.99'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:500'],
        ]);

        $status = (string) $validated['status'];
        $beratKonfirmasi = isset($validated['berat_konfirmasi_kg'])
            ? (float) $validated['berat_konfirmasi_kg']
            : ($status === 'diterima' ? $deposit->berat_kg : null);

        $deposit->update([
            'status' => $status,
            'berat_konfirmasi_kg' => $beratKonfirmasi,
            'verified_at' => now(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
        ]);

        $deposit->load(['pengelola:id,name', 'petugas:id,name', 'tps3rProfile']);

        $statusText = $status === 'diterima' ? 'diterima dan diverifikasi' : 'ditolak';

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

        $query->whereBetween('tanggal_setor', [$startDate, $endDate]);

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
}
