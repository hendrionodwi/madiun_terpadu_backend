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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        } elseif (! $currentUser->isPemrakarsa()) {
            $query->where('status', '!=', 'draft');
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

    public function show(EnvironmentalReport $environmentalReport): JsonResponse
    {
        Gate::authorize('view', $environmentalReport);

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan lingkungan berhasil diambil.',
            'data' => new EnvironmentalReportResource($environmentalReport->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_OK);
    }

    public function store(StoreEnvironmentalReportRequest $request): JsonResponse
    {
        Gate::authorize('create', EnvironmentalReport::class);

        $validated = $request->validated();
        /** @var User $pemrakarsa */
        $pemrakarsa = $request->user();

        $isDraft = $request->boolean('is_draft') || ($validated['status'] ?? null) === 'draft';
        $path = $request->hasFile('file_laporan')
            ? $request->file('file_laporan')->store('environmental_reports', 'public')
            : null;

        $report = EnvironmentalReport::create([
            'pemrakarsa_id' => $pemrakarsa->id,
            'judul_laporan' => $validated['judul_laporan'],
            'periode_semester' => $validated['periode_semester'],
            'tahun' => (int) $validated['tahun'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'timbulan_sampah_kg' => isset($validated['timbulan_sampah_kg']) ? (float) $validated['timbulan_sampah_kg'] : null,
            'metode_pengelolaan' => $validated['metode_pengelolaan'] ?? null,
            'monitoring_data' => $validated['monitoring_data'] ?? null,
            'file_path' => $path,
            'status' => $isDraft ? 'draft' : 'menunggu_verifikasi',
        ]);

        return response()->json([
            'success' => true,
            'message' => $isDraft
                ? 'Draft laporan lingkungan berhasil disimpan.'
                : 'Laporan lingkungan berhasil diunggah dan menunggu verifikasi.',
            'data' => new EnvironmentalReportResource($report->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_CREATED);
    }

    public function update(StoreEnvironmentalReportRequest $request, EnvironmentalReport $environmentalReport): JsonResponse
    {
        Gate::authorize('update', $environmentalReport);

        if (! in_array($environmentalReport->status, ['draft', 'perlu_revisi'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan yang sedang diverifikasi atau sudah disetujui tidak dapat diubah.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();
        $targetStatus = $validated['status'] ?? null;
        if ($targetStatus === null && array_key_exists('is_draft', $validated)) {
            $targetStatus = $request->boolean('is_draft') ? 'draft' : 'menunggu_verifikasi';
        }

        if ($targetStatus === 'menunggu_verifikasi' && ! $request->hasFile('file_laporan') && empty($environmentalReport->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File laporan wajib tersedia sebelum mengajukan laporan untuk verifikasi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updateData = collect($validated)
            ->only(['judul_laporan', 'periode_semester', 'tahun', 'jenis_dokumen', 'timbulan_sampah_kg', 'metode_pengelolaan', 'monitoring_data'])
            ->all();

        if ($request->hasFile('file_laporan')) {
            if (! empty($environmentalReport->file_path)) {
                Storage::disk('public')->delete($environmentalReport->file_path);
            }
            $updateData['file_path'] = $request->file('file_laporan')->store('environmental_reports', 'public');
        }

        if ($targetStatus !== null) {
            $updateData['status'] = $targetStatus;
        }

        $environmentalReport->update($updateData);

        return response()->json([
            'success' => true,
            'message' => ($updateData['status'] ?? $environmentalReport->status) === 'menunggu_verifikasi'
                ? 'Laporan lingkungan berhasil dikirim untuk verifikasi.'
                : 'Draft laporan lingkungan berhasil diperbarui.',
            'data' => new EnvironmentalReportResource($environmentalReport->fresh()->load(['pemrakarsa:id,name,email', 'assignedPetugas:id,name,phone_number'])),
        ], Response::HTTP_OK);
    }

    public function destroy(EnvironmentalReport $environmentalReport): JsonResponse
    {
        Gate::authorize('delete', $environmentalReport);

        if ($environmentalReport->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya laporan berstatus draft yang dapat dihapus.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! empty($environmentalReport->file_path)) {
            Storage::disk('public')->delete($environmentalReport->file_path);
        }

        $environmentalReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft laporan lingkungan berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    public function verify(VerifyEnvironmentalReportRequest $request, EnvironmentalReport $environmentalReport): JsonResponse
    {
        Gate::authorize('verify', $environmentalReport);

        if ($environmentalReport->status === 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan yang masih berstatus draft belum diajukan dan tidak dapat diverifikasi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();
        $isNewFieldAssignment = $validated['status'] === 'perlu_cek_lapangan'
            && ($environmentalReport->status !== 'perlu_cek_lapangan'
                || $environmentalReport->assigned_petugas_id !== (int) $validated['assigned_petugas_id']);

        $updateData = [
            'status' => $validated['status'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'verified_at' => Carbon::now(),
        ];

        if ($validated['status'] === 'disetujui' && empty($environmentalReport->nomor_bap)) {
            $updateData['nomor_bap'] = 'BAP-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
        }

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
        Gate::authorize('fieldCheck', $environmentalReport);

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
            'draft' => EnvironmentalReport::where('status', 'draft')->count(),
            'menunggu_verifikasi' => EnvironmentalReport::where('status', 'menunggu_verifikasi')->count(),
            'perlu_cek_lapangan' => EnvironmentalReport::where('status', 'perlu_cek_lapangan')->count(),
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
