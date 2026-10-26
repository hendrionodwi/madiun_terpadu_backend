<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EnvironmentalReport\StoreEnvironmentalReportRequest;
use App\Http\Requests\Api\EnvironmentalReport\VerifyEnvironmentalReportRequest;
use App\Http\Resources\Api\EnvironmentalReportResource;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteWeighing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnvironmentalReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $query = EnvironmentalReport::query()->with([
            'pemrakarsa:id,name,email',
            'assignedPetugas:id,name,phone_number',
        ]);

        if ($currentUser->isPemrakarsa()) {
            $query->whereBelongsTo($currentUser, 'pemrakarsa');
        } elseif ($currentUser->isPetugasLapangan()) {
            $query->where('assigned_petugas_id', $currentUser->id);
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->boolean('assigned_to_me') && ! $currentUser->isPemrakarsa() && ! $currentUser->isPetugasLapangan()) {
            $query->where('assigned_petugas_id', $currentUser->id);
        }

        $reports = $query->latest()->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan lingkungan berhasil diambil.',
            'data' => EnvironmentalReportResource::collection($reports->items()),
            'meta' => ['current_page' => $reports->currentPage(), 'last_page' => $reports->lastPage(), 'per_page' => $reports->perPage(), 'total' => $reports->total()],
        ], Response::HTTP_OK);
    }

    public function show(Request $request, EnvironmentalReport $environmentalReport): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isPemrakarsa() && $environmentalReport->pemrakarsa_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($currentUser->isPetugasLapangan() && $environmentalReport->assigned_petugas_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan dokumen ini tidak ditugaskan kepada Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan lingkungan berhasil diambil.',
            'data' => new EnvironmentalReportResource($environmentalReport->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_OK);
    }

    public function store(StoreEnvironmentalReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $pemrakarsa */
        $pemrakarsa = $request->user();
        $path = $request->file('file_laporan')->store('environmental_reports', 'public');

        $report = EnvironmentalReport::create([
            'pemrakarsa_id' => $pemrakarsa->id,
            'judul_laporan' => $validated['judul_laporan'],
            'periode_semester' => $validated['periode_semester'],
            'tahun' => (int) $validated['tahun'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'timbulan_sampah_kg' => isset($validated['timbulan_sampah_kg']) ? (float) $validated['timbulan_sampah_kg'] : null,
            'metode_pengelolaan' => $validated['metode_pengelolaan'] ?? null,
            'file_path' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan lingkungan berhasil diunggah dan menunggu verifikasi.',
            'data' => new EnvironmentalReportResource($report->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_CREATED);
    }

    public function verify(VerifyEnvironmentalReportRequest $request, EnvironmentalReport $environmentalReport): JsonResponse
    {
        $validated = $request->validated();
        $isNewFieldAssignment = $validated['status'] === 'perlu_cek_lapangan'
            && ($environmentalReport->status !== 'perlu_cek_lapangan'
                || $environmentalReport->assigned_petugas_id !== (int) $validated['assigned_petugas_id']);

        $updateData = [
            'status' => $validated['status'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'verified_at' => Carbon::now(),
        ];

        if ($validated['status'] === 'perlu_cek_lapangan') {
            $updateData['assigned_petugas_id'] = $validated['assigned_petugas_id'];
            $updateData['instruksi_lapangan'] = $validated['instruksi_lapangan'] ?? null;
            $updateData['assigned_at'] = Carbon::now();

            if ($isNewFieldAssignment) {
                $updateData['catatan_inspeksi_petugas'] = null;
                $updateData['hasil_inspeksi_lapangan'] = null;
                $updateData['inspeksi_at'] = null;
            }
        }

        $environmentalReport->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi laporan lingkungan berhasil diperbarui.',
            'data' => new EnvironmentalReportResource($environmentalReport->fresh()->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_OK);
    }

    /**
     * Submit field inspection finding by officer (SRS Butir 2 & 3).
     */
    public function fieldCheck(Request $request, EnvironmentalReport $environmentalReport): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if (! $currentUser->isAdminDlh() && $environmentalReport->assigned_petugas_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Penugasan inspeksi lapangan ini bukan untuk akun Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($environmentalReport->status !== 'perlu_cek_lapangan') {
            return response()->json([
                'success' => false,
                'message' => 'Hasil inspeksi hanya dapat diperbarui saat laporan berstatus perlu cek lapangan.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'catatan_inspeksi' => ['required', 'string', 'max:2000'],
            'hasil_rekomendasi' => ['required', 'string', 'in:sesuai,perlu_perbaikan,tidak_sesuai'],
        ]);

        $environmentalReport->update([
            'catatan_inspeksi_petugas' => $validated['catatan_inspeksi'],
            'hasil_inspeksi_lapangan' => $validated['hasil_rekomendasi'],
            'inspeksi_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil inspeksi dan verifikasi lapangan berhasil disimpan.',
            'data' => new EnvironmentalReportResource($environmentalReport->fresh()->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_OK);
    }

    /**
     * Get macro KPI monitoring summary for Admin DLH.
     */
    public function monitoringSummary(Request $request): JsonResponse
    {
        $totalTimbanganSampah = (float) WasteWeighing::whereNull('tps3r_waste_deposit_id')->sum('berat_kg');
        $totalSetoranTps3r = (float) Tps3rWasteDeposit::sum('berat_kg');
        $totalSampahTerkelolaKg = round($totalTimbanganSampah + $totalSetoranTps3r, 2);

        $totalAduanAktif = Complaint::whereIn('status', ['menunggu_verifikasi', 'dalam_proses'])->count();
        $totalTps3rAktif = PengelolaTps3rProfile::count();
        $totalLaporanSemesterMasuk = EnvironmentalReport::count();

        $statusAduan = [
            'menunggu_verifikasi' => Complaint::where('status', 'menunggu_verifikasi')->count(),
            'dalam_proses' => Complaint::where('status', 'dalam_proses')->count(),
            'selesai' => Complaint::where('status', 'selesai')->count(),
            'ditolak' => Complaint::where('status', 'ditolak')->count(),
        ];

        $statusLaporan = [
            'menunggu_verifikasi' => EnvironmentalReport::where('status', 'menunggu_verifikasi')->count(),
            'disetujui' => EnvironmentalReport::where('status', 'disetujui')->count(),
            'perlu_revisi' => EnvironmentalReport::where('status', 'perlu_revisi')->count(),
        ];

        $rekapWilayah = [
            'Kartoharjo' => [
                'aduan' => Complaint::where('kecamatan', 'Kartoharjo')->count(),
                'tps3r' => 2,
            ],
            'Manguharjo' => [
                'aduan' => Complaint::where('kecamatan', 'Manguharjo')->count(),
                'tps3r' => 2,
            ],
            'Taman' => [
                'aduan' => Complaint::where('kecamatan', 'Taman')->count(),
                'tps3r' => 3,
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring ringkas kota berhasil diambil.',
            'data' => [
                'total_sampah_terkelola_kg' => $totalSampahTerkelolaKg,
                'total_aduan_aktif' => $totalAduanAktif,
                'total_laporan_semester' => $totalLaporanSemesterMasuk,
                'total_tps3r_aktif' => $totalTps3rAktif,
                'status_aduan' => $statusAduan,
                'status_laporan' => $statusLaporan,
                'rekap_wilayah' => $rekapWilayah,
            ],
        ], Response::HTTP_OK);
    }
}
