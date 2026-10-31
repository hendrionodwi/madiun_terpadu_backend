<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Complaint\StoreComplaintRequest;
use App\Http\Requests\Api\Complaint\VerifyComplaintRequest;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\ComplaintVerification;
use App\Models\MasterData;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ComplaintController extends Controller
{
    /**
     * Get list of complaints.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $query = Complaint::query()->with([
            'pelapor:id,name,phone_number',
            'assignedPetugas:id,name,phone_number',
            'verifications.petugas:id,name,phone_number',
        ]);

        // If normal citizen, list only their complaints
        if ($currentUser->isWarga()) {
            $query->where('pelapor_id', $currentUser->id);
        }

        // If field officer, ONLY list complaints assigned to them (PPL-F-003)
        if ($currentUser->isPetugasLapangan()) {
            $query->where('assigned_petugas_id', $currentUser->id);
        }

        // Officer / admin filtering for assigned tasks
        if ($currentUser->isAdminDlh()) {
            if ($request->boolean('assigned_to_me')) {
                $query->where('assigned_petugas_id', $currentUser->id);
            } elseif ($request->filled('assigned_petugas_id')) {
                $query->where('assigned_petugas_id', (int) $request->assigned_petugas_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('kelurahan')) {
            $query->where('kelurahan', (string) $request->kelurahan);
        }

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', (string) $request->kecamatan);
        }

        if ($request->filled('kategori_masalah')) {
            $query->where('kategori_masalah', (string) $request->kategori_masalah);
        }

        $complaints = $query->latest()->paginate((int) ($request->per_page ?? 15));
        collect($complaints->items())->each(
            fn (Complaint $complaint) => $this->protectAnonymousReporter($complaint, $currentUser),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengaduan masyarakat berhasil diambil.',
            'data' => $complaints->items(),
            'meta' => [
                'current_page' => $complaints->currentPage(),
                'last_page' => $complaints->lastPage(),
                'per_page' => $complaints->perPage(),
                'total' => $complaints->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show complaint details and verification history.
     */
    public function show(Request $request, Complaint $complaint): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $complaint->load([
            'pelapor:id,name,email,phone_number',
            'assignedPetugas:id,name,email,phone_number',
            'verifications.petugas:id,name,phone_number',
        ]);

        if ($currentUser->isWarga() && $complaint->pelapor_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($currentUser->isPetugasLapangan() && $complaint->assigned_petugas_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengaduan ini tidak ditugaskan kepada Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        $this->protectAnonymousReporter($complaint, $currentUser);

        return response()->json([
            'success' => true,
            'message' => 'Detail pengaduan berhasil diambil.',
            'data' => $complaint,
        ], Response::HTTP_OK);
    }

    /**
     * Submit citizen complaint form (PPL-F-001 & PPL-F-007).
     */
    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $pelapor */
        $pelapor = $request->user();

        $nomorTiket = 'TKT-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('complaints', 'public');
        } elseif (isset($validated['foto']) && is_string($validated['foto'])) {
            $fotoPath = $validated['foto'];
        }

        $complaint = Complaint::create([
            'nomor_tiket' => $nomorTiket,
            'pelapor_id' => $pelapor->id,
            'judul' => $validated['judul'],
            'kategori_masalah' => $validated['kategori_masalah']
                ?? MasterData::whereIn('group', ['complaint_categories', 'complaint_type'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->value('code')
                ?? 'Sampah_Liar',
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'kelurahan' => $validated['kelurahan'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'latitude' => isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            'foto' => $fotoPath,
            'is_anonim' => $request->boolean('is_anonim'),
            'status' => 'diterima',
        ]);

        // PPL-F-006: Log initial notification for citizen tracking
        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $pelapor->id,
            'status' => 'diterima',
            'judul_notifikasi' => 'Pengaduan Diterima Sistem',
            'pesan' => 'Pengaduan Anda (#'.$nomorTiket.') berhasil didaftarkan dan menunggu disposisi verifikasi oleh DLH Kota Madiun.',
        ]);

        $complaint->load(['pelapor:id,name,email,phone_number', 'statusLogs']);

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan berhasil diajukan dengan nomor tiket '.$nomorTiket.'.',
            'data' => $complaint,
        ], Response::HTTP_CREATED);
    }

    /**
     * Input field verification result by officer (PPL-F-004).
     */
    public function verify(VerifyComplaintRequest $request, Complaint $complaint): JsonResponse
    {
        $validated = $request->validated();
        /** @var User $petugas */
        $petugas = $request->user();

        if ($petugas->isPetugasLapangan() && $complaint->assigned_petugas_id !== $petugas->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda hanya dapat memverifikasi pengaduan yang ditugaskan kepada Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        $result = DB::transaction(function () use ($complaint, $petugas, $validated) {
            $verification = ComplaintVerification::create([
                'complaint_id' => $complaint->id,
                'petugas_id' => $petugas->id,
                'status_verifikasi' => $validated['status_verifikasi'],
                'catatan_petugas' => $validated['catatan_petugas'],
                'rekomendasi_tindak_lanjut' => $validated['rekomendasi_tindak_lanjut'] ?? null,
                'foto_bukti' => $validated['foto_bukti'] ?? null,
                'tanggal_verifikasi' => Carbon::now(),
            ]);

            // Update complaint master status based on verification outcome (PPL-F-002)
            $newComplaintStatus = match ($validated['status_verifikasi']) {
                'selesai_dibersihkan' => 'selesai',
                'valid' => 'diverifikasi_lapangan',
                'tidak_valid' => 'ditolak',
                default => 'diverifikasi_lapangan',
            };

            $complaint->update(['status' => $newComplaintStatus]);

            // PPL-F-006: Log notification for citizen
            ComplaintStatusLog::create([
                'complaint_id' => $complaint->id,
                'user_id' => $complaint->pelapor_id,
                'status' => $newComplaintStatus,
                'judul_notifikasi' => 'Hasil Verifikasi Lapangan Dicatat',
                'pesan' => 'Petugas telah melakukan pengecekan ke lokasi: '.$validated['catatan_petugas'].(! empty($validated['rekomendasi_tindak_lanjut']) ? ' (Rekomendasi: '.$validated['rekomendasi_tindak_lanjut'].')' : ''),
            ]);

            return $verification->load(['complaint', 'petugas:id,name,phone_number']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hasil verifikasi lapangan pengaduan berhasil dicatat.',
            'data' => [
                'verifikasi' => $result,
                'status_pengaduan_terbaru' => $complaint->fresh()->status,
                'status_label' => $complaint->fresh()->status_label,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Get complaint status tracking notifications for citizen (PPL-F-006).
     */
    public function notifications(Request $request, Complaint $complaint): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->isWarga() && $complaint->pelapor_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($currentUser->isPetugasLapangan() && $complaint->assigned_petugas_id !== $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengaduan ini tidak ditugaskan kepada Anda.',
            ], Response::HTTP_FORBIDDEN);
        }

        $logs = ComplaintStatusLog::where('complaint_id', $complaint->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Histori notifikasi status pengaduan berhasil diambil.',
            'data' => $logs,
        ], Response::HTTP_OK);
    }

    private function protectAnonymousReporter(Complaint $complaint, User $viewer): void
    {
        if (! $viewer->isPetugasLapangan() || ! $complaint->is_anonim) {
            return;
        }

        $complaint->unsetRelation('pelapor');
        $complaint->makeHidden(['pelapor_id']);
    }
}
