<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteWeighing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    /**
     * Export & Cetak Laporan Resmi Rekapitulasi Timbulan Sampah DLH Kota Madiun.
     */
    public function exportAdminWasteRecap(Request $request): Response|StreamedResponse|View
    {
        $validated = $request->validate([
            'format' => ['nullable', 'string', 'in:print,pdf,csv,excel'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'kecamatan' => ['nullable', 'string', 'in:Kartoharjo,Manguharjo,Taman'],
        ]);

        $format = $validated['format'] ?? 'print';
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->toDateString();
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();
        $kecamatanFilter = $validated['kecamatan'] ?? null;

        // Query timbangan sampah warga
        $weighingQuery = WasteWeighing::query()
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['warga.wargaProfile', 'petugas']);

        if ($kecamatanFilter !== null) {
            $weighingQuery->whereHas('warga.wargaProfile', function ($q) use ($kecamatanFilter): void {
                $q->where('kecamatan', $kecamatanFilter);
            });
        }

        $allWeighings = (clone $weighingQuery)->latest('created_at')->latest('id')->get();
        $totalTimbanganKg = (float) $allWeighings->sum('berat_kg');

        // Query setoran fasilitas TPS3R
        $depositQuery = Tps3rWasteDeposit::query()
            ->whereBetween('tanggal_setor', [$startDate, $endDate])
            ->with(['tps3rProfile', 'petugas']);

        if ($kecamatanFilter !== null) {
            $depositQuery->whereHas('tps3rProfile', function ($q) use ($kecamatanFilter): void {
                $q->where('kecamatan', $kecamatanFilter);
            });
        }

        $allDeposits = (clone $depositQuery)->latest('tanggal_setor')->latest('id')->get();
        $totalSetoranTps3rKg = (float) $allDeposits->sum('berat_kg');
        $totalSampahTerkelolaKg = $totalTimbanganKg + $totalSetoranTps3rKg;
        $totalSampahTerkelolaTon = round($totalSampahTerkelolaKg / 1000, 3);

        // Komposisi 3 Pilar (Organik, Anorganik Daur Ulang, Residu)
        $composition = [
            'organik' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
            'anorganik_daur_ulang' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
            'residu' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
        ];

        foreach ($allWeighings as $w) {
            $pillar = MasterData::resolvePillar($w->jenis_sampah);
            $weight = (float) $w->berat_kg;
            $composition[$pillar]['weight'] += $weight;
            $composition[$pillar]['count']++;
        }

        foreach ($allDeposits as $d) {
            if ($d->berat_organik_pilah_kg !== null || $d->berat_anorganik_pilah_kg !== null || $d->berat_residu_pilah_kg !== null) {
                $composition['organik']['weight'] += (float) ($d->berat_organik_pilah_kg ?? 0);
                $composition['anorganik_daur_ulang']['weight'] += (float) ($d->berat_anorganik_pilah_kg ?? 0);
                $composition['residu']['weight'] += (float) ($d->berat_residu_pilah_kg ?? 0);
            } else {
                $pillar = MasterData::resolvePillar($d->jenis_sampah);
                $composition[$pillar]['weight'] += (float) $d->berat_kg;
            }
        }

        if ($totalSampahTerkelolaKg > 0) {
            foreach ($composition as $k => $v) {
                $composition[$k]['percentage'] = round(($v['weight'] / $totalSampahTerkelolaKg) * 100, 1);
            }
        }

        // Rekapitulasi per Kecamatan
        $districts = ['Kartoharjo', 'Manguharjo', 'Taman'];
        $wilayahSummary = [];
        foreach ($districts as $dist) {
            $distWeighingKg = (float) WasteWeighing::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->whereHas('warga.wargaProfile', fn ($q) => $q->where('kecamatan', $dist))
                ->sum('berat_kg');

            $distDepositKg = (float) Tps3rWasteDeposit::whereBetween('tanggal_setor', [$startDate, $endDate])
                ->whereHas('tps3rProfile', fn ($q) => $q->where('kecamatan', $dist))
                ->sum('berat_kg');

            $totalDistKg = $distWeighingKg + $distDepositKg;
            $aduanCount = Complaint::where('kecamatan', $dist)
                ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                ->count();
            $aduanSelesai = Complaint::where('kecamatan', $dist)
                ->where('status', 'selesai')
                ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                ->count();

            $wilayahSummary[$dist] = [
                'nama' => 'Kecamatan '.$dist,
                'timbangan_warga_kg' => $distWeighingKg,
                'setoran_tps3r_kg' => $distDepositKg,
                'total_sampah_kg' => $totalDistKg,
                'total_sampah_ton' => round($totalDistKg / 1000, 3),
                'total_aduan' => $aduanCount,
                'total_aduan_selesai' => $aduanSelesai,
            ];
        }

        // Metrik tambahan
        $totalAduan = Complaint::whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])->count();
        $totalAduanSelesai = Complaint::where('status', 'selesai')->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])->count();
        $totalTps3rAktif = PengelolaTps3rProfile::count();
        $totalPetugasAktif = User::where('role', UserRole::PetugasLapangan)->count();
        $totalWargaAktif = User::where('role', UserRole::Warga)->count();

        // 1. Export Format CSV / Excel
        if ($format === 'csv' || $format === 'excel') {
            $filename = 'rekap-timbulan-sampah-dlh-kota-madiun-'.$startDate.'-sd-'.$endDate.'.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            return response()->stream(function () use ($startDate, $endDate, $totalSampahTerkelolaKg, $totalSampahTerkelolaTon, $composition, $wilayahSummary, $allDeposits): void {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }

                // UTF-8 BOM agar Excel membaca karakter dengan benar
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($handle, ['PEMERINTAH KOTA MADIUN - DINAS LINGKUNGAN HIDUP']);
                fputcsv($handle, ['LAPORAN RESMI REKAPITULASI TIMBULAN & NERACA SAMPAH KOTA MADIUN']);
                fputcsv($handle, ['Periode Laporan', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Tanggal Unduh', now()->translatedFormat('d F Y H:i').' WIB']);
                fputcsv($handle, []);

                // Ringkasan Eksekutif
                fputcsv($handle, ['=== RINGKASAN EKSEKUTIF KOTA MADIUN ===']);
                fputcsv($handle, ['Total Sampah Terkelola (Kg)', number_format($totalSampahTerkelolaKg, 2, ',', '.')]);
                fputcsv($handle, ['Total Sampah Terkelola (Ton)', number_format($totalSampahTerkelolaTon, 3, ',', '.')]);
                fputcsv($handle, ['Komposisi Organik (Kg)', number_format($composition['organik']['weight'], 2, ',', '.'), $composition['organik']['percentage'].'%']);
                fputcsv($handle, ['Komposisi Anorganik Daur Ulang (Kg)', number_format($composition['anorganik_daur_ulang']['weight'], 2, ',', '.'), $composition['anorganik_daur_ulang']['percentage'].'%']);
                fputcsv($handle, ['Komposisi Residu TPA (Kg)', number_format($composition['residu']['weight'], 2, ',', '.'), $composition['residu']['percentage'].'%']);
                fputcsv($handle, []);

                // Rekap Wilayah Kecamatan
                fputcsv($handle, ['=== REKAPITULASI PER WILAYAH KECAMATAN ===']);
                fputcsv($handle, ['Kecamatan', 'Timbangan Warga (Kg)', 'Setoran TPS3R (Kg)', 'Total Sampah (Kg)', 'Total Sampah (Ton)', 'Total Pengaduan', 'Pengaduan Selesai']);
                foreach ($wilayahSummary as $w) {
                    fputcsv($handle, [
                        $w['nama'],
                        number_format($w['timbangan_warga_kg'], 2, ',', '.'),
                        number_format($w['setoran_tps3r_kg'], 2, ',', '.'),
                        number_format($w['total_sampah_kg'], 2, ',', '.'),
                        number_format($w['total_sampah_ton'], 3, ',', '.'),
                        $w['total_aduan'],
                        $w['total_aduan_selesai'],
                    ]);
                }
                fputcsv($handle, []);

                // Detail Log Setoran TPS3R
                fputcsv($handle, ['=== RINCIAN LOG SETORAN TPS3R PERIODE BERJALAN ===']);
                fputcsv($handle, ['No', 'Nomor Resi', 'Tanggal Setor', 'Waktu', 'Fasilitas TPS3R', 'Kecamatan', 'Petugas Pengantar', 'Sumber Sampah', 'Jenis Sampah', 'Berat (Kg)', 'Status']);
                $no = 1;
                foreach ($allDeposits as $dep) {
                    fputcsv($handle, [
                        $no++,
                        $dep->nomor_resi,
                        $dep->tanggal_setor,
                        $dep->waktu_setor ?? '-',
                        $dep->tps3rProfile->nama_tps3r ?? 'TPS3R Madiun',
                        $dep->tps3rProfile->kecamatan ?? '-',
                        $dep->petugas->name ?? 'Petugas Lapangan',
                        ucwords(str_replace('_', ' ', $dep->sumber_sampah)),
                        ucwords(str_replace('_', ' ', $dep->jenis_sampah)),
                        number_format((float) $dep->berat_kg, 2, ',', '.'),
                        strtoupper($dep->status),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        // 2. Format Cetak Resmi Ber-Kop (Printable PDF View)
        return view('reports.admin-waste-recap-print', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'kecamatanFilter' => $kecamatanFilter,
            'totalSampahTerkelolaKg' => $totalSampahTerkelolaKg,
            'totalSampahTerkelolaTon' => $totalSampahTerkelolaTon,
            'totalTimbanganKg' => $totalTimbanganKg,
            'totalSetoranTps3rKg' => $totalSetoranTps3rKg,
            'composition' => $composition,
            'wilayahSummary' => $wilayahSummary,
            'totalAduan' => $totalAduan,
            'totalAduanSelesai' => $totalAduanSelesai,
            'totalTps3rAktif' => $totalTps3rAktif,
            'totalPetugasAktif' => $totalPetugasAktif,
            'totalWargaAktif' => $totalWargaAktif,
            'allDeposits' => $allDeposits->take(50),
            'docNumber' => '660.1/'.Carbon::parse($startDate)->format('Ym').'/401.114/'.Carbon::now()->format('Y'),
        ]);
    }

    /**
     * Export & Cetak Laporan Resmi Rekapitulasi Neraca Sampah TPS3R.
     */
    public function exportTps3rVolumeRecap(Request $request): Response|StreamedResponse|View
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'format' => ['nullable', 'string', 'in:print,pdf,csv,excel'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'jenis_sampah' => ['nullable', 'string', 'in:organik,anorganik_daur_ulang,residu'],
            'sumber_sampah' => ['nullable', 'string', 'in:armada_kelurahan,warga_mandiri,pemrakarsa_komersial'],
            'tps3r_profile_id' => ['nullable', 'integer', 'exists:pengelola_tps3r_profiles,id'],
        ]);

        $format = $validated['format'] ?? 'print';
        $startDate = $validated['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? Carbon::now()->toDateString();

        // Tentukan profile TPS3R
        $profile = null;
        if ($user->isPengelolaTps3r() && $user->pengelolaTps3rProfile !== null) {
            $profile = $user->pengelolaTps3rProfile;
        } elseif (! empty($validated['tps3r_profile_id'])) {
            $profile = PengelolaTps3rProfile::find($validated['tps3r_profile_id']);
        }

        if ($profile === null) {
            $profile = PengelolaTps3rProfile::first();
        }

        $depositQuery = Tps3rWasteDeposit::query()
            ->whereBetween('tanggal_setor', [$startDate, $endDate])
            ->with(['petugas:id,name', 'pengelola:id,name', 'weighings.warga:id,name']);

        if ($profile !== null) {
            $depositQuery->where('tps3r_profile_id', $profile->id);
        }

        if (! empty($validated['jenis_sampah'])) {
            $depositQuery->where('jenis_sampah', $validated['jenis_sampah']);
        }

        if (! empty($validated['sumber_sampah'])) {
            $depositQuery->where('sumber_sampah', $validated['sumber_sampah']);
        }

        $allDeposits = $depositQuery->latest('tanggal_setor')->latest('id')->get();

        $totalVolumeKg = (float) $allDeposits->sum('berat_kg');
        $totalVolumeTon = round($totalVolumeKg / 1000, 3);
        $totalSetoranCount = $allDeposits->count();

        // Neraca Pemilahan Akurat
        $organikKg = 0.0;
        $anorganikKg = 0.0;
        $residuKg = 0.0;

        foreach ($allDeposits as $dep) {
            if ($dep->berat_organik_pilah_kg !== null || $dep->berat_anorganik_pilah_kg !== null || $dep->berat_residu_pilah_kg !== null) {
                $organikKg += (float) ($dep->berat_organik_pilah_kg ?? 0);
                $anorganikKg += (float) ($dep->berat_anorganik_pilah_kg ?? 0);
                $residuKg += (float) ($dep->berat_residu_pilah_kg ?? 0);
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
            } else {
                $pillar = MasterData::resolvePillar($dep->jenis_sampah);
                $dWeight = (float) $dep->berat_kg;
                if ($pillar === 'organik') {
                    $organikKg += $dWeight;
                } elseif ($pillar === 'residu') {
                    $residuKg += $dWeight;
                } else {
                    $anorganikKg += $dWeight;
                }
            }
        }

        $organikPct = $totalVolumeKg > 0 ? round(($organikKg / $totalVolumeKg) * 100, 1) : 0;
        $anorganikPct = $totalVolumeKg > 0 ? round(($anorganikKg / $totalVolumeKg) * 100, 1) : 0;
        $residuPct = $totalVolumeKg > 0 ? round(($residuKg / $totalVolumeKg) * 100, 1) : 0;

        // Metrik Utilisasi Kapasitas
        $kapasitasHarian = $profile ? (float) $profile->kapasitas_harian_kg : 1500.0;
        $dateDiffDays = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
        $rataRataHarianKg = round($totalVolumeKg / $dateDiffDays, 1);
        $persenUtilisasi = $kapasitasHarian > 0 ? min(100, round(($rataRataHarianKg / $kapasitasHarian) * 100, 1)) : 0;

        // Breakdown Sumber Sampah
        $sumberBreakdown = [];
        foreach (['armada_kelurahan', 'warga_mandiri', 'pemrakarsa_komersial'] as $src) {
            $filtered = $allDeposits->where('sumber_sampah', $src);
            $weight = (float) $filtered->sum('berat_kg');
            $sumberBreakdown[$src] = [
                'nama' => ucwords(str_replace('_', ' ', $src)),
                'total_kg' => $weight,
                'count' => $filtered->count(),
                'percentage' => $totalVolumeKg > 0 ? round(($weight / $totalVolumeKg) * 100, 1) : 0,
            ];
        }

        // 1. Export CSV / Excel
        if ($format === 'csv' || $format === 'excel') {
            $kodeTps = $profile->kode_tps3r ?? 'TPS3R';
            $filename = 'rekap-volume-sampah-'.$kodeTps.'-'.$startDate.'-sd-'.$endDate.'.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            return response()->stream(function () use ($profile, $startDate, $endDate, $totalVolumeKg, $totalVolumeTon, $organikKg, $organikPct, $anorganikKg, $anorganikPct, $residuKg, $residuPct, $kapasitasHarian, $persenUtilisasi, $allDeposits): void {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }

                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($handle, ['PEMERINTAH KOTA MADIUN - DINAS LINGKUNGAN HIDUP']);
                fputcsv($handle, ['UNIT PENGOLAHAN SAMPAH 3R: '.($profile->nama_tps3r ?? 'TPS3R KOTA MADIUN')]);
                fputcsv($handle, ['Kode Fasilitas', $profile->kode_tps3r ?? '-']);
                fputcsv($handle, ['Wilayah Kecamatan', $profile->kecamatan ?? '-']);
                fputcsv($handle, ['Periode Laporan', $startDate.' s/d '.$endDate]);
                fputcsv($handle, ['Tanggal Cetak', now()->translatedFormat('d F Y H:i').' WIB']);
                fputcsv($handle, []);

                // Neraca Pengolahan
                fputcsv($handle, ['=== NERACA OPERASIONAL & KAPASITAS FASILITAS ===']);
                fputcsv($handle, ['Total Sampah Masuk (Kg)', number_format($totalVolumeKg, 2, ',', '.')]);
                fputcsv($handle, ['Total Sampah Masuk (Ton)', number_format($totalVolumeTon, 3, ',', '.')]);
                fputcsv($handle, ['Kapasitas Desain Harian (Kg)', number_format($kapasitasHarian, 2, ',', '.')]);
                fputcsv($handle, ['Tingkat Utilisasi Rata-Rata', $persenUtilisasi.'%']);
                fputcsv($handle, ['Komposisi Organik (Kg)', number_format($organikKg, 2, ',', '.'), $organikPct.'%']);
                fputcsv($handle, ['Komposisi Anorganik Daur Ulang (Kg)', number_format($anorganikKg, 2, ',', '.'), $anorganikPct.'%']);
                fputcsv($handle, ['Komposisi Residu TPA (Kg)', number_format($residuKg, 2, ',', '.'), $residuPct.'%']);
                fputcsv($handle, []);

                // Rincian Buku Ekspedisi Setoran
                fputcsv($handle, ['=== BUKU EKSPEDISI SETORAN TRUK ARMADA & WARGA ===']);
                fputcsv($handle, [
                    'No',
                    'Nomor Resi',
                    'Tanggal Setor',
                    'Waktu Setor',
                    'Petugas Pengantar',
                    'Sumber Sampah',
                    'Jenis Sampah',
                    'Berat Timbangan (Kg)',
                    'Organik Terpilah (Kg)',
                    'Anorganik Terpilah (Kg)',
                    'Residu (Kg)',
                    'Status',
                    'Catatan Verifikasi',
                ]);

                $no = 1;
                foreach ($allDeposits as $dep) {
                    fputcsv($handle, [
                        $no++,
                        $dep->nomor_resi,
                        $dep->tanggal_setor,
                        $dep->waktu_setor ?? '-',
                        $dep->petugas->name ?? 'Petugas Lapangan',
                        ucwords(str_replace('_', ' ', $dep->sumber_sampah)),
                        ucwords(str_replace('_', ' ', $dep->jenis_sampah)),
                        number_format((float) $dep->berat_kg, 2, ',', '.'),
                        number_format((float) ($dep->berat_organik_pilah_kg ?? 0), 2, ',', '.'),
                        number_format((float) ($dep->berat_anorganik_pilah_kg ?? 0), 2, ',', '.'),
                        number_format((float) ($dep->berat_residu_pilah_kg ?? 0), 2, ',', '.'),
                        strtoupper($dep->status),
                        $dep->catatan_verifikasi ?? '-',
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        // 2. Format Cetak Resmi Ber-Kop (Printable PDF View)
        return view('reports.tps3r-volume-recap-print', [
            'profile' => $profile,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalVolumeKg' => $totalVolumeKg,
            'totalVolumeTon' => $totalVolumeTon,
            'totalSetoranCount' => $totalSetoranCount,
            'organikKg' => $organikKg,
            'organikPct' => $organikPct,
            'anorganikKg' => $anorganikKg,
            'anorganikPct' => $anorganikPct,
            'residuKg' => $residuKg,
            'residuPct' => $residuPct,
            'kapasitasHarian' => $kapasitasHarian,
            'rataRataHarianKg' => $rataRataHarianKg,
            'persenUtilisasi' => $persenUtilisasi,
            'sumberBreakdown' => $sumberBreakdown,
            'allDeposits' => $allDeposits,
            'docNumber' => 'BA-TPS3R/'.($profile->kode_tps3r ?? 'TPS').'/'.Carbon::parse($startDate)->format('Ym').'/'.Carbon::now()->format('Y'),
        ]);
    }
}
