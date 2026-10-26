<?php

namespace App\Http\Controllers\Web;

use App\Actions\ReviewFundWithdrawalRequest;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminDlhProfile;
use App\Models\BroadcastAnnouncement;
use App\Models\Complaint;
use App\Models\ComplaintStatusLog;
use App\Models\EntityVerification;
use App\Models\EnvironmentalReport;
use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\Order;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\PickupSchedule;
use App\Models\RewardFundAllocation;
use App\Models\RouteAssignment;
use App\Models\RouteSession;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use App\Models\WasteWeighing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    /**
     * 1. Halaman Dashboard Rekapitulasi Lintas Wilayah Kota Madiun (Executive Overview).
     */
    public function index(): View
    {
        $totalTimbanganSampah = (float) WasteWeighing::whereNull('tps3r_waste_deposit_id')->sum('berat_kg');
        $totalSetoranTps3r = (float) Tps3rWasteDeposit::sum('berat_kg');
        $totalSampahTerkelolaKg = $totalTimbanganSampah + $totalSetoranTps3r;

        $totalAduanAktif = Complaint::whereNotIn('status', ['selesai', 'ditolak'])->count();
        $totalTps3rAktif = PengelolaTps3rProfile::count();
        $totalLaporanSemesterMasuk = EnvironmentalReport::count();
        $totalWargaTerdaftar = User::where('role', UserRole::Warga)->count();
        $totalPetugasAktif = User::where('role', UserRole::PetugasLapangan)->count();

        // Rekapitulasi Wilayah Kecamatan Terdaftar Kota Madiun
        $masterDistricts = MasterData::where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $districtNames = ! empty($masterDistricts) ? $masterDistricts : ['Kartoharjo', 'Manguharjo', 'Taman'];

        $wilayahMadiun = [];
        foreach ($districtNames as $d) {
            $wilayahMadiun[$d] = [
                'nama' => 'Kecamatan '.ucfirst($d),
                'total_sampah_kg' => (float) WasteWeighing::whereHas('warga.wargaProfile', fn ($q) => $q->where('kecamatan', $d))->sum('berat_kg'),
                'total_aduan' => Complaint::where('kecamatan', $d)->count(),
                'total_aduan_selesai' => Complaint::where('kecamatan', $d)->where('status', 'selesai')->count(),
            ];
        }

        $pengaduanTerbaru = Complaint::with(['pelapor:id,name,phone_number', 'verifications.petugas:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $laporanSemesterMenunggu = EnvironmentalReport::with('pemrakarsa:id,name,email')
            ->latest()
            ->take(5)
            ->get();

        $pengumumanTerbaru = BroadcastAnnouncement::where('is_active', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        // Transaksi Marketplace TPS3R se-Kota (SRS Butir 5)
        $totalPesananMarketplace = Order::count();
        $totalOmsetMarketplace = (float) Order::whereIn('status', Order::REVENUE_STATUSES)->sum('total_harga');
        $totalProdukMarketplace = WasteProduct::where('is_available', true)->count();
        $pesananTerbaru = Order::with(['buyer:id,name', 'items.product'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.admin', [
            'macro' => [
                'total_sampah_terkelola_kg' => $totalSampahTerkelolaKg,
                'total_aduan_aktif' => $totalAduanAktif,
                'total_tps3r_aktif' => $totalTps3rAktif,
                'total_laporan_semester' => $totalLaporanSemesterMasuk,
                'total_warga' => $totalWargaTerdaftar,
                'total_petugas' => $totalPetugasAktif,
                'total_pesanan_marketplace' => $totalPesananMarketplace,
                'total_omset_marketplace' => $totalOmsetMarketplace,
                'total_produk_marketplace' => $totalProdukMarketplace,
            ],
            'wilayah' => $wilayahMadiun,
            'pengaduanTerbaru' => $pengaduanTerbaru,
            'laporanSemesterMenunggu' => $laporanSemesterMenunggu,
            'pengumumanTerbaru' => $pengumumanTerbaru,
            'pesananTerbaru' => $pesananTerbaru,
        ]);
    }

    /**
     * 2. Halaman Kelola Hak Akses (RBAC) & Akun Pengguna.
     */
    public function users(Request $request): View
    {
        $roleFilter = $request->query('role');
        $search = $request->query('q');

        $query = User::with([
            'wargaProfile',
            'petugasLapanganProfile',
            'pengelolaTps3rProfile',
            'pemrakarsaProfile',
            'adminDlhProfile',
        ]);

        if ($roleFilter && in_array($roleFilter, array_column(UserRole::cases(), 'value'), true)) {
            $query->where('role', $roleFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $roleCounts = [
            'all' => User::count(),
            'warga' => User::where('role', UserRole::Warga)->count(),
            'petugas_lapangan' => User::where('role', UserRole::PetugasLapangan)->count(),
            'pengelola_tps3r' => User::where('role', UserRole::PengelolaTps3r)->count(),
            'pemrakarsa' => User::where('role', UserRole::Pemrakarsa)->count(),
            'admin_dlh' => User::where('role', UserRole::AdminDlh)->count(),
        ];

        return view('dashboard.admin.users', [
            'users' => $users,
            'roleCounts' => $roleCounts,
            'currentRole' => $roleFilter,
            'search' => $search,
        ]);
    }

    /**
     * Toggle status aktif akun pengguna.
     */
    public function toggleUserStatus(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, Response::HTTP_UNPROCESSABLE_ENTITY, 'Admin tidak dapat menonaktifkan akunnya sendiri.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'Status akun '.$user->name.' berhasil diperbarui.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);
        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->ensureRoleProfile($user);

        return back()->with('status', 'Akun dan hak akses berhasil dibuat.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($request->user()->is($user) && ($validated['role'] !== UserRole::AdminDlh->value || ! $validated['is_active'])) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Admin tidak dapat menonaktifkan atau mengubah role akunnya sendiri.');
        }

        $user->update($validated);
        $this->ensureRoleProfile($user->fresh());

        return back()->with('status', 'Akun dan hak akses berhasil diperbarui.');
    }

    /**
     * 3. Halaman Penugasan Wilayah & Rute Tetap Petugas Lapangan.
     */
    public function routes(Request $request): View
    {
        $today = now()->toDateString();

        $routes = RouteAssignment::with(['petugas', 'assigner', 'todaySession.petugas'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $activeRouteCodes = RouteAssignment::where('is_active', true)->pluck('route_code');

        $schedules = PickupSchedule::with('petugas')
            ->whereIn('id_wilayah_rute', $activeRouteCodes)
            ->where(function ($query) use ($today) {
                // Tampilkan semua jadwal yang belum diambil/selesai dari hari apapun
                $query->whereNotIn('status', ['selesai', 'dibatalkan', 'batal'])
                    // Atau jadwal yang sudah diambil/selesai HANYA jika jadwalnya hari ini
                    ->orWhere(function ($sub) use ($today) {
                        $sub->where('status', 'selesai')
                            ->whereDate('tanggal_penjemputan', $today);
                    });
            })
            ->latest('tanggal_penjemputan')
            ->orderBy('waktu_mulai', 'asc')
            ->take(20)
            ->get();

        $routeSessions = RouteSession::whereDate('tanggal', $today)
            ->with(['routeAssignment', 'petugas'])
            ->latest()
            ->get();

        return view('dashboard.admin.routes', [
            'routes' => $routes,
            'schedules' => $schedules,
            'routeSessions' => $routeSessions,
        ]);
    }

    /**
     * Halaman Tambah Penugasan Rute Baru & Editor Lintasan Peta.
     */
    public function createRoute(): View
    {
        $petugasList = User::where('role', UserRole::PetugasLapangan)->where('is_active', true)->get();
        $districts = MasterData::where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('dashboard.admin.routes.create', [
            'petugasList' => $petugasList,
            'districts' => $districts,
            'tps3rFacilities' => $this->getAvailableEndFacilities(),
        ]);
    }

    /**
     * Halaman Edit Penugasan Rute & Editor Lintasan Peta.
     */
    public function editRoute(RouteAssignment $route): View
    {
        $route->load(['petugas', 'assigner']);
        $petugasList = User::where('role', UserRole::PetugasLapangan)->where('is_active', true)->get();
        $districts = MasterData::where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('dashboard.admin.routes.edit', [
            'route' => $route,
            'petugasList' => $petugasList,
            'districts' => $districts,
            'tps3rFacilities' => $this->getAvailableEndFacilities(),
        ]);
    }

    /**
     * Daftar fasilitas tujuan akhir armada (TPS3R terdaftar + TPA Winongo).
     *
     * @return array<int, array{name: string, location: string, code: string, district: string, tag: string}>
     */
    protected function getAvailableEndFacilities(): array
    {
        $facilities = PengelolaTps3rProfile::with('user:id,name,is_active')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('nama_tps3r')
            ->get()
            ->map(function (PengelolaTps3rProfile $tps): array {
                $location = 'Kec. '.($tps->kecamatan ?? '-');
                if ($tps->kelurahan) {
                    $location .= ', Kel. '.$tps->kelurahan;
                }

                return [
                    'name' => $tps->nama_tps3r,
                    'location' => $location,
                    'code' => $tps->kode_tps3r ?? '',
                    'district' => $tps->kecamatan ?? '',
                    'tag' => 'TPS3R',
                ];
            })
            ->values()
            ->all();

        array_unshift($facilities, [
            'name' => 'TPA Winongo (Residu Kota Madiun)',
            'location' => 'Kec. Manguharjo - Tempat Pemrosesan Akhir',
            'code' => 'TPA-WINONGO',
            'district' => 'Manguharjo',
            'tag' => 'TPA Akhir',
        ]);

        return $facilities;
    }

    /**
     * Simpan penugasan rute tetap baru.
     */
    public function storeRoute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::PetugasLapangan->value)
                    ->where('is_active', true),
            ],
            'route_code' => ['required', 'string', 'max:50', Rule::unique('route_assignments', 'route_code')],
            'route_name' => ['required', 'string', 'max:150'],
            'start_location' => ['nullable', 'string', 'max:150'],
            'end_facility' => ['nullable', 'string', 'max:150'],
            'district' => ['required', 'string'],
            'subdistrict' => ['nullable', 'string'],
            'schedule_day' => ['required', 'string'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if (empty($validated['latitude']) || empty($validated['longitude'])) {
            $districtCoords = [
                'Kartoharjo' => [-7.6180, 111.5300],
                'Taman' => [-7.6380, 111.5250],
                'Manguharjo' => [-7.6250, 111.5120],
            ];
            $coords = $districtCoords[$validated['district']] ?? [-7.6298, 111.5239];
            $validated['latitude'] = $coords[0];
            $validated['longitude'] = $coords[1];
        }

        if ($request->filled('waypoints')) {
            $rawWaypoints = $request->input('waypoints');
            if (is_string($rawWaypoints)) {
                $decoded = json_decode($rawWaypoints, true);
                $validated['waypoints'] = is_array($decoded) ? $decoded : null;
            } elseif (is_array($rawWaypoints)) {
                $validated['waypoints'] = $rawWaypoints;
            }
        }

        $validated['assigned_by'] = $request->user()->id;
        $validated['is_active'] = true;

        $newRoute = RouteAssignment::create($validated);

        $petugas = User::query()->where('role', UserRole::PetugasLapangan)->findOrFail($validated['petugas_id']);
        $petugas->petugasLapanganProfile()->updateOrCreate(
            ['user_id' => $petugas->id],
            ['id_wilayah_rute' => $validated['route_code'], 'nama_wilayah_tugas' => $validated['route_name']],
        );

        return redirect()->route('dashboard.admin.routes')->with('status', 'Penugasan rute operasional '.$validated['route_code'].' berhasil ditambahkan.');
    }

    /**
     * Perbarui penugasan rute operasional armada.
     */
    public function updateRoute(Request $request, RouteAssignment $route): RedirectResponse
    {
        $validated = $request->validate([
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::PetugasLapangan->value)
                    ->where('is_active', true),
            ],
            'route_code' => ['required', 'string', 'max:50', Rule::unique('route_assignments', 'route_code')->ignore($route->id)],
            'route_name' => ['required', 'string', 'max:150'],
            'start_location' => ['nullable', 'string', 'max:150'],
            'end_facility' => ['nullable', 'string', 'max:150'],
            'district' => ['required', 'string'],
            'subdistrict' => ['nullable', 'string'],
            'schedule_day' => ['required', 'string'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->filled('waypoints')) {
            $rawWaypoints = $request->input('waypoints');
            if (is_string($rawWaypoints)) {
                $decoded = json_decode($rawWaypoints, true);
                $validated['waypoints'] = is_array($decoded) ? $decoded : null;
            } elseif (is_array($rawWaypoints)) {
                $validated['waypoints'] = $rawWaypoints;
            }
        }

        $oldCode = $route->route_code;
        $route->update($validated);

        // Sinkronkan ke profil petugas
        $petugas = User::query()->where('role', UserRole::PetugasLapangan)->findOrFail($validated['petugas_id']);
        $petugas->petugasLapanganProfile()->updateOrCreate(
            ['user_id' => $petugas->id],
            ['id_wilayah_rute' => $validated['route_code'], 'nama_wilayah_tugas' => $validated['route_name']],
        );

        // Sinkronkan jadwal penjemputan terkait jika kode atau nama rute berubah
        if ($oldCode !== $validated['route_code'] || $route->route_name !== $validated['route_name'] || (int) $route->petugas_id !== (int) $validated['petugas_id']) {
            PickupSchedule::where('id_wilayah_rute', $oldCode)->update([
                'id_wilayah_rute' => $validated['route_code'],
                'nama_wilayah_rute' => $validated['route_name'],
                'petugas_id' => $validated['petugas_id'],
            ]);
        }

        return redirect()->route('dashboard.admin.routes')->with('status', 'Penugasan rute '.$route->route_code.' berhasil diperbarui.');
    }

    /**
     * Hapus rute operasional armada.
     */
    public function destroyRoute(RouteAssignment $route): RedirectResponse
    {
        $routeCode = $route->route_code;
        PickupSchedule::where('id_wilayah_rute', $routeCode)->delete();
        $route->delete();

        return back()->with('status', 'Rute operasional '.$routeCode.' berhasil dihapus.');
    }

    /**
     * 4. Halaman Kelola Daftar Pengaduan Masyarakat.
     */
    public function complaints(Request $request): View
    {
        $statusFilter = $request->query('status');
        $districtFilter = $request->query('kecamatan');
        $categoryFilter = $request->query('kategori');

        $query = Complaint::with(['pelapor', 'assignedPetugas', 'verifications.petugas']);

        if ($statusFilter) {
            if (in_array($statusFilter, ['menunggu_verifikasi', 'diterima'])) {
                $query->whereIn('status', ['menunggu_verifikasi', 'diterima']);
            } elseif (in_array($statusFilter, ['dalam_proses', 'ditugaskan', 'diverifikasi_lapangan', 'ditindaklanjuti'])) {
                $query->whereIn('status', ['dalam_proses', 'ditugaskan', 'diverifikasi_lapangan', 'ditindaklanjuti']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($districtFilter) {
            $query->where('kecamatan', $districtFilter);
        }

        if ($categoryFilter) {
            $query->where('kategori_masalah', $categoryFilter);
        }

        $complaints = $query->latest()->paginate(10)->withQueryString();

        $statusCounts = [
            'all' => Complaint::count(),
            'menunggu_verifikasi' => Complaint::whereIn('status', ['menunggu_verifikasi', 'diterima'])->count(),
            'dalam_proses' => Complaint::whereIn('status', ['dalam_proses', 'ditugaskan', 'diverifikasi_lapangan', 'ditindaklanjuti'])->count(),
            'selesai' => Complaint::where('status', 'selesai')->count(),
            'ditolak' => Complaint::where('status', 'ditolak')->count(),
        ];

        $petugasList = User::where('role', UserRole::PetugasLapangan)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $districts = MasterData::where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $complaintCategories = MasterData::whereIn('group', ['complaint_categories', 'complaint_type'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.complaints', [
            'complaints' => $complaints,
            'statusCounts' => $statusCounts,
            'currentStatus' => $statusFilter,
            'currentDistrict' => $districtFilter,
            'currentCategory' => $categoryFilter,
            'petugasList' => $petugasList,
            'districts' => $districts,
            'complaintCategories' => $complaintCategories,
        ]);
    }

    /**
     * Disposisi penugasan verifikasi lapangan ke Petugas Lapangan (SRS Butir 2).
     */
    public function assignComplaint(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'petugas_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where('role', UserRole::PetugasLapangan->value)
                    ->where('is_active', true),
            ],
            'instruksi_admin' => ['nullable', 'string', 'max:500'],
        ]);

        $complaint->update([
            'assigned_petugas_id' => $validated['petugas_id'],
            'instruksi_admin' => $validated['instruksi_admin'],
            'assigned_at' => Carbon::now(),
            'status' => 'ditugaskan',
        ]);

        $petugas = User::findOrFail($validated['petugas_id']);

        // PPL-F-006: Log notification for pelapor
        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $complaint->pelapor_id,
            'status' => 'ditugaskan',
            'judul_notifikasi' => 'Petugas Lapangan Ditugaskan',
            'pesan' => 'Admin DLH telah menugaskan petugas '.$petugas->name.' untuk mengecek lokasi pengaduan.'.($validated['instruksi_admin'] ? ' Instruksi: "'.$validated['instruksi_admin'].'"' : ''),
        ]);

        return back()->with('status', 'Pengaduan tiket #'.$complaint->nomor_tiket.' berhasil didisposisikan kepada petugas: '.$petugas->name);
    }

    /**
     * Finalisasi status pengaduan oleh Admin DLH.
     */
    public function finalizeComplaint(Request $request, int $id): RedirectResponse
    {
        $complaint = Complaint::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:selesai,ditolak,ditindaklanjuti,dalam_proses'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $updateData = ['status' => $validated['status']];
        if (! empty($validated['admin_note'])) {
            $updateData['instruksi_admin'] = $validated['admin_note'];
        }

        $complaint->update($updateData);

        // PPL-F-006: Log notification for pelapor
        ComplaintStatusLog::create([
            'complaint_id' => $complaint->id,
            'user_id' => $complaint->pelapor_id,
            'status' => $validated['status'],
            'judul_notifikasi' => 'Status Pengaduan: '.$complaint->fresh()->status_label,
            'pesan' => 'Status pengaduan Anda diperbarui menjadi '.$complaint->fresh()->status_label.'.'.(! empty($validated['admin_note']) ? ' Catatan DLH: '.$validated['admin_note'] : ''),
        ]);

        return back()->with('status', 'Status pengaduan tiket #'.$complaint->nomor_tiket.' berhasil diperbarui ke: '.$complaint->fresh()->status_label);
    }

    /**
     * 5. Halaman Verifikasi Laporan Dokumen Lingkungan Pemrakarsa.
     */
    public function reports(Request $request): View
    {
        $statusFilter = $request->query('status');
        $jenisFilter = $request->query('jenis_dokumen');

        $query = EnvironmentalReport::with(['pemrakarsa.pemrakarsaProfile', 'assignedPetugas']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($jenisFilter) {
            $query->where('jenis_dokumen', $jenisFilter);
        }

        $reports = $query->latest()->paginate(10)->withQueryString();

        $reportCounts = [
            'all' => EnvironmentalReport::count(),
            'menunggu_verifikasi' => EnvironmentalReport::where('status', 'menunggu_verifikasi')->count(),
            'perlu_cek_lapangan' => EnvironmentalReport::where('status', 'perlu_cek_lapangan')->count(),
            'disetujui' => EnvironmentalReport::where('status', 'disetujui')->count(),
            'perlu_revisi' => EnvironmentalReport::where('status', 'perlu_revisi')->count(),
        ];

        $petugasList = User::where('role', UserRole::PetugasLapangan)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.reports', [
            'reports' => $reports,
            'reportCounts' => $reportCounts,
            'currentStatus' => $statusFilter,
            'currentJenis' => $jenisFilter,
            'petugasList' => $petugasList,
        ]);
    }

    /**
     * Verifikasi laporan semester dokumen lingkungan oleh Admin DLH (SRS Butir 2 & 4).
     */
    public function verifyReport(Request $request, int $id): RedirectResponse
    {
        $report = EnvironmentalReport::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,perlu_revisi,perlu_cek_lapangan'],
            'catatan_verifikasi' => ['nullable', 'string'],
            'assigned_petugas_id' => [
                'nullable',
                Rule::requiredIf($request->input('status') === 'perlu_cek_lapangan'),
                Rule::exists('users', 'id')
                    ->where('role', UserRole::PetugasLapangan->value)
                    ->where('is_active', true),
            ],
            'instruksi_lapangan' => ['nullable', 'string', 'max:500'],
        ]);

        $isNewFieldAssignment = $validated['status'] === 'perlu_cek_lapangan'
            && ($report->status !== 'perlu_cek_lapangan'
                || $report->assigned_petugas_id !== (int) $validated['assigned_petugas_id']);

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

        $report->update($updateData);

        return back()->with('status', 'Dokumen '.$report->judul_laporan.' telah ditandai: '.str_replace('_', ' ', $validated['status']));
    }

    /**
     * 6. Halaman Validasi & Manajemen Entitas Pemrakarsa serta Pengelola TPS3R.
     */
    public function entities(Request $request): View
    {
        $activeTab = $request->query('tab', 'pemrakarsa');

        $pemrakarsaList = PemrakarsaProfile::with('user')->paginate(8, ['*'], 'pemrakarsa_page');
        $tps3rList = PengelolaTps3rProfile::with('user')->paginate(8, ['*'], 'tps3r_page');
        $entityVerifications = EntityVerification::with(['user', 'reviewer'])->latest()->take(6)->get();

        return view('dashboard.admin.entities', [
            'activeTab' => $activeTab,
            'pemrakarsaList' => $pemrakarsaList,
            'tps3rList' => $tps3rList,
            'entityVerifications' => $entityVerifications,
        ]);
    }

    public function reviewEntity(Request $request, EntityVerification $entityVerification): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $entityVerification->update([
            ...$validated,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Validasi entitas berhasil diperbarui.');
    }

    public function downloadEntityDocument(EntityVerification $entityVerification): StreamedResponse
    {
        abort_if($entityVerification->document_path === null || ! Storage::exists($entityVerification->document_path), Response::HTTP_NOT_FOUND);

        return Storage::download($entityVerification->document_path);
    }

    /**
     * 7. Halaman Manajemen Persetujuan Pencairan Dana / Poin Warga.
     */
    public function points(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = FundWithdrawalRequest::with(['user', 'reviewer'])
            ->where('source', 'points');

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $withdrawals = $query->latest()->paginate(10)->withQueryString();

        $totalAnggaranMasuk = (float) RewardFundAllocation::sum('amount');
        $totalPencairanSukses = (float) FundWithdrawalRequest::where('status', 'approved')->where('source', 'points')->sum('amount');
        $totalPendingAmount = (float) FundWithdrawalRequest::where('status', 'pending')->where('source', 'points')->sum('amount');
        $saldoAnggaranTersedia = max(0.0, $totalAnggaranMasuk - ($totalPencairanSukses + $totalPendingAmount));

        $stats = [
            'total_poin_beredar' => (int) WargaProfile::sum('saldo_poin'),
            'total_poin_diberikan' => (int) WasteWeighing::sum('poin_didapat'),
            'total_anggaran_masuk' => $totalAnggaranMasuk,
            'total_pencairan_sukses' => $totalPencairanSukses,
            'pending_requests' => FundWithdrawalRequest::where('status', 'pending')->where('source', 'points')->count(),
            'total_pending_amount' => $totalPendingAmount,
            'saldo_anggaran_tersedia' => $saldoAnggaranTersedia,
        ];

        $statusCounts = [
            'all' => FundWithdrawalRequest::where('source', 'points')->count(),
            'pending' => FundWithdrawalRequest::where('source', 'points')->where('status', 'pending')->count(),
            'approved' => FundWithdrawalRequest::where('source', 'points')->where('status', 'approved')->count(),
            'rejected' => FundWithdrawalRequest::where('source', 'points')->where('status', 'rejected')->count(),
        ];

        $pointRate = (float) (MasterData::query()
            ->whereIn('group', ['point_conversion', 'point_conversions'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('numeric_value') ?? 10);

        $topWarga = WargaProfile::with('user')
            ->orderByDesc('saldo_poin')
            ->take(5)
            ->get();

        $allocations = RewardFundAllocation::with('creator')
            ->latest('allocated_at')
            ->take(10)
            ->get();

        return view('dashboard.admin.points', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'allocations' => $allocations,
            'topWarga' => $topWarga,
            'currentStatus' => $statusFilter,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'pointRate' => $pointRate,
        ]);
    }

    /**
     * Simpan alokasi anggaran kas reward baru.
     */
    public function storeRewardAllocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_type' => ['required', 'string', 'in:apbd,penjualan_daur_ulang,csr_kemitraan,retribusi,lainnya'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'fiscal_year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'allocated_at' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        RewardFundAllocation::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Alokasi anggaran kas reward sebesar Rp '.number_format((float) $validated['amount'], 0, ',', '.').' berhasil dicatat ke sistem.');
    }

    /**
     * Update status pencairan saldo / dana poin.
     */
    public function updateWithdrawalStatus(
        Request $request,
        FundWithdrawalRequest $fundWithdrawalRequest,
        ReviewFundWithdrawalRequest $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $action->handle($request->user(), $fundWithdrawalRequest, $validated['status'], $validated['admin_note'] ?? null);

        return back()->with('status', 'Permohonan pencairan #'.$fundWithdrawalRequest->id.' berhasil diperbarui: '.$validated['status']);
    }

    /**
     * 8. Halaman Kelola Data Master.
     */
    public function masterData(Request $request): View
    {
        $groupFilter = $request->query('group');

        $query = MasterData::query();
        if ($groupFilter) {
            $query->where('group', $groupFilter);
        } else {
            // Sembunyikan retribution_config dari tampilan default agar tidak mencemari tabel master operasional
            $query->where('group', '!=', 'retribution_config');
        }

        $masterDataItems = $query->orderBy('group')->orderBy('sort_order')->paginate(12)->withQueryString();

        $supportedGroups = [
            'waste_category' => 'Kategori Sampah',
            'districts' => 'Kecamatan',
            'point_conversion' => 'Konversi Poin',
            'complaint_categories' => 'Kategori Aduan',
        ];

        return view('dashboard.admin.master-data', [
            'items' => $masterDataItems,
            'groups' => $supportedGroups,
            'currentGroup' => $groupFilter,
        ]);
    }

    /**
     * Simpan data master baru.
     */
    public function storeMasterData(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:50'],
            'pillar' => ['nullable', 'string', 'max:50', 'in:organik,anorganik_daur_ulang,residu'],
            'name' => ['required', 'string', 'max:100'],
            'numeric_value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($validated['group'] === 'waste_categories') {
            $validated['group'] = 'waste_category';
        } elseif ($validated['group'] === 'point_rates') {
            $validated['group'] = 'point_conversion';
        }

        if ($validated['group'] === 'waste_category' && empty($validated['pillar'])) {
            $validated['pillar'] = MasterData::resolvePillar($validated['code']);
        }

        $validated['is_active'] = true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        MasterData::create($validated);

        return back()->with('status', 'Data master '.$validated['name'].' berhasil ditambahkan.');
    }

    /**
     * Perbarui data master / kategori sampah.
     */
    public function updateMasterData(Request $request, MasterData $masterData): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:50'],
            'code' => ['required', 'string', 'max:50'],
            'pillar' => ['nullable', 'string', 'max:50', 'in:organik,anorganik_daur_ulang,residu'],
            'name' => ['required', 'string', 'max:100'],
            'numeric_value' => ['nullable', 'numeric'],
            'unit' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validated['group'] === 'waste_categories') {
            $validated['group'] = 'waste_category';
        } elseif ($validated['group'] === 'point_rates') {
            $validated['group'] = 'point_conversion';
        }

        if ($validated['group'] === 'waste_category' && empty($validated['pillar'])) {
            $validated['pillar'] = MasterData::resolvePillar($validated['code']);
        }

        $masterData->update($validated);

        return back()->with('status', 'Data master '.$masterData->name.' berhasil diperbarui.');
    }

    /**
     * Hapus data master / kategori sampah.
     */
    public function destroyMasterData(MasterData $masterData): RedirectResponse
    {
        if ($masterData->group === 'retribution_config') {
            return back()->withErrors(['error' => 'Data konfigurasi retribusi dilindungi dan tidak dapat dihapus dari menu ini. Gunakan halaman Tarif Acuan Retribusi.']);
        }

        if ($masterData->group === 'districts') {
            $isUsedInRoutes = RouteAssignment::where('district', $masterData->name)->exists();
            $isUsedInComplaints = Complaint::where('kecamatan', $masterData->name)->exists();
            $isUsedInTps3r = PengelolaTps3rProfile::where('kecamatan', $masterData->name)->exists();

            if ($isUsedInRoutes || $isUsedInComplaints || $isUsedInTps3r) {
                return back()->withErrors(['error' => 'Kecamatan '.$masterData->name.' tidak dapat dihapus karena masih digunakan pada data operasional sistem (rute armada, TPS3R, atau pengaduan).']);
            }
        }

        $name = $masterData->name;
        $masterData->delete();

        return back()->with('status', 'Data master '.$name.' berhasil dihapus.');
    }

    /**
     * 9. Halaman Pemantauan Log Aktivitas Pengguna (Audit Trail).
     */
    public function activityLogs(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
            'unique_users' => ActivityLog::distinct('user_id')->count('user_id'),
        ];

        return view('dashboard.admin.activity-logs', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    /**
     * 10. Halaman Sistem Siaran Notifikasi Pengumuman / Broadcast.
     */
    public function broadcasts(Request $request): View
    {
        $broadcasts = BroadcastAnnouncement::with('creator')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.admin.broadcasts', [
            'broadcasts' => $broadcasts,
        ]);
    }

    /**
     * Simpan siaran pengumuman baru.
     */
    public function storeBroadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string'],
            'audience_role' => ['required', 'string', 'in:all,warga,petugas_lapangan,pengelola_tps3r,pemrakarsa'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['is_active'] = true;
        $validated['published_at'] = Carbon::now();

        BroadcastAnnouncement::create($validated);

        return back()->with('status', 'Siaran pengumuman "'.$validated['title'].'" berhasil dipublikasikan.');
    }

    private function ensureRoleProfile(User $user): void
    {
        match ($user->role) {
            UserRole::Warga => WargaProfile::firstOrCreate(['user_id' => $user->id]),
            UserRole::PetugasLapangan => PetugasLapanganProfile::firstOrCreate(['user_id' => $user->id]),
            UserRole::PengelolaTps3r => PengelolaTps3rProfile::firstOrCreate(['user_id' => $user->id], ['nama_tps3r' => $user->name]),
            UserRole::Pemrakarsa => PemrakarsaProfile::firstOrCreate(['user_id' => $user->id], ['nama_instansi' => $user->name]),
            UserRole::AdminDlh => AdminDlhProfile::firstOrCreate(['user_id' => $user->id], ['bidang' => 'Belum ditentukan']),
        };
    }
}
