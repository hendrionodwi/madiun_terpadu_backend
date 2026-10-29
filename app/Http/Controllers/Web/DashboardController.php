<?php

namespace App\Http\Controllers\Web;

use App\Enums\OrderFulfillmentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Marketplace\StoreProductRequest;
use App\Http\Requests\Api\Tps3r\StoreTps3rDepositRequest;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\MasterData;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\PickupSchedule;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;
use App\Models\WasteWeighing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dispatch user to their role-specific web dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        return match ($user->role) {
            UserRole::AdminDlh => $this->admin($request),
            UserRole::PengelolaTps3r => $this->tps3r($request),
            UserRole::Pemrakarsa => $this->pemrakarsa($request),
            default => $this->generalDashboard($user),
        };
    }

    /**
     * General Dashboard for Warga, Petugas Lapangan, or other users.
     */
    protected function generalDashboard(User $user): View
    {
        $wargaData = [];
        $petugasData = [];

        if ($user->isWarga()) {
            $wargaData = [
                'profile' => $user->wargaProfile,
                'riwayatTimbangan' => WasteWeighing::where('warga_id', $user->id)
                    ->with('petugas:id,name')
                    ->latest()
                    ->take(5)
                    ->get(),
                'pengaduanSaya' => Complaint::where('pelapor_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get(),
                'jadwalWilayah' => PickupSchedule::where('status', '!=', 'selesai')
                    ->latest('tanggal_penjemputan')
                    ->take(4)
                    ->get(),
                'produkKatalog' => WasteProduct::where('is_available', true)
                    ->latest()
                    ->take(4)
                    ->get(),
            ];
        } elseif ($user->isPetugasLapangan()) {
            $petugasData = [
                'profile' => $user->petugasLapanganProfile,
                'jadwalTugas' => PickupSchedule::where('petugas_id', $user->id)
                    ->latest('tanggal_penjemputan')
                    ->take(5)
                    ->get(),
                'timbanganTerakhir' => WasteWeighing::where('petugas_id', $user->id)
                    ->with('warga:id,name')
                    ->latest()
                    ->take(5)
                    ->get(),
                'totalTimbanganHariIni' => (float) WasteWeighing::where('petugas_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->sum('berat_kg'),
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'wargaData' => $wargaData,
            'petugasData' => $petugasData,
        ]);
    }

    /**
     * Dashboard Admin DLH Kota Madiun (DSH-F-004).
     *
     * Rekap lintas wilayah, status pengaduan, dan verifikasi laporan lingkungan.
     */
    public function admin(Request $request): View
    {
        return app(AdminDashboardController::class)->index();
    }

    /**
     * Dashboard Pengelola TPS3R (DSH-F-003).
     *
     * Memantau volume sampah dan transaksi marketplace produk daur ulang.
     */
    public function tps3r(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $tps3rProfile = $user->pengelolaTps3rProfile ?? PengelolaTps3rProfile::first();

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $period = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $startDate = $period['start_date'] ?? $startOfMonth;
        $endDate = $period['end_date'] ?? Carbon::now()->toDateString();

        // 1. Volume Sampah
        $depositQuery = Tps3rWasteDeposit::query();
        if ($tps3rProfile !== null) {
            $depositQuery->where('tps3r_profile_id', $tps3rProfile->id);
        }

        $volumeHariIni = (float) (clone $depositQuery)->whereDate('tanggal_setor', $today)->sum('berat_kg');
        $volumeBulanIni = (float) (clone $depositQuery)->whereBetween('tanggal_setor', [$startOfMonth, Carbon::now()->toDateString()])->sum('berat_kg');
        $kapasitasHarian = $tps3rProfile ? (float) $tps3rProfile->kapasitas_harian_kg : 1500.0;
        $persenKapasitasHariIni = $kapasitasHarian > 0 ? min(100, round(($volumeHariIni / $kapasitasHarian) * 100, 1)) : 0;

        $filteredDepositQuery = (clone $depositQuery)->whereBetween('tanggal_setor', [$startDate, $endDate]);

        $rincianJenisSampah = (clone $filteredDepositQuery)->selectRaw('jenis_sampah, SUM(berat_kg) as total_kg')
            ->groupBy('jenis_sampah')
            ->get();

        $setoranTerbaru = (clone $depositQuery)->with(['petugas:id,name', 'pengelola:id,name'])
            ->latest('tanggal_setor')
            ->latest('id')
            ->take(6)
            ->get();
        $riwayatSetoran = (clone $filteredDepositQuery)->with(['petugas:id,name', 'pengelola:id,name'])
            ->latest('tanggal_setor')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // 2. Transaksi Marketplace Produk Daur Ulang
        $sellerId = $user->id;
        $isAdminOverview = $user->isAdminDlh();
        $katalogProduk = WasteProduct::query()
            ->when(! $isAdminOverview, fn ($query) => $query->where('seller_id', $sellerId))
            ->latest()
            ->get();

        $totalProduk = $katalogProduk->count();
        $totalStok = (int) $katalogProduk->sum('stok');

        $pesananTerbaru = ($isAdminOverview
            ? Order::with(['buyer:id,name,phone_number', 'items.product'])
            : $this->sellerOrdersQuery($sellerId))
            ->latest()
            ->take(6)
            ->get();

        $totalPendapatan = $isAdminOverview
            ? (float) Order::whereIn('status', Order::REVENUE_STATUSES)->sum('total_harga')
            : (float) OrderItem::query()
                ->whereHas('product', fn ($query) => $query->where('seller_id', $sellerId))
                ->whereHas('order', fn ($query) => $query->whereIn('status', Order::REVENUE_STATUSES))
                ->sum('subtotal');

        return view('dashboard.tps3r', [
            'tps3rProfile' => $tps3rProfile,
            'volume' => [
                'hari_ini_kg' => $volumeHariIni,
                'bulan_ini_kg' => $volumeBulanIni,
                'kapasitas_harian_kg' => $kapasitasHarian,
                'persen_utilisasi' => $persenKapasitasHariIni,
                'rincian_jenis' => $rincianJenisSampah,
                'setoran_terbaru' => $setoranTerbaru,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'riwayat_setoran' => $riwayatSetoran,
            ],
            'marketplace' => [
                'total_produk' => $totalProduk,
                'total_stok' => $totalStok,
                'total_pendapatan' => $totalPendapatan,
                'katalog' => $katalogProduk,
                'pesanan_terbaru' => $pesananTerbaru,
            ],
        ]);
    }

    /**
     * Record an incoming waste deposit through the TPS3R web dashboard.
     */
    public function storeTps3rDeposit(StoreTps3rDepositRequest $request): RedirectResponse
    {
        /** @var User $pengelola */
        $pengelola = $request->user();
        $tps3rProfile = $pengelola->pengelolaTps3rProfile;

        abort_unless($tps3rProfile !== null, 403);

        $validated = $request->validated();
        Tps3rWasteDeposit::create([
            'pengelola_id' => $pengelola->id,
            'tps3r_profile_id' => $tps3rProfile->id,
            'nomor_resi' => 'DEP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'sumber_sampah' => $validated['sumber_sampah'],
            'jenis_sampah' => $validated['jenis_sampah'],
            'berat_kg' => (float) $validated['berat_kg'],
            'status' => 'diterima',
            'verified_at' => now(),
            'tanggal_setor' => $validated['tanggal_setor'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return to_route('dashboard.tps3r.deposits')->with('status', 'Setoran sampah berhasil dicatat.');
    }

    /**
     * Confirm/verify incoming waste deposit from field officers.
     */
    public function verifyTps3rDeposit(Request $request, Tps3rWasteDeposit $deposit): RedirectResponse
    {
        /** @var User $pengelola */
        $pengelola = $request->user();
        $tps3rProfile = $pengelola->pengelolaTps3rProfile;

        abort_unless($pengelola->isAdminDlh() || ($tps3rProfile !== null && $deposit->tps3r_profile_id === $tps3rProfile->id), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:diterima,ditolak'],
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

        if (array_key_exists('berat_organik_pilah_kg', $validated)) {
            $updateData['berat_organik_pilah_kg'] = $validated['berat_organik_pilah_kg'];
        }
        if (array_key_exists('berat_anorganik_pilah_kg', $validated)) {
            $updateData['berat_anorganik_pilah_kg'] = $validated['berat_anorganik_pilah_kg'];
        }
        if (array_key_exists('berat_residu_pilah_kg', $validated)) {
            $updateData['berat_residu_pilah_kg'] = $validated['berat_residu_pilah_kg'];
        }

        $deposit->update($updateData);

        $msg = $status === 'diterima'
            ? "Setoran {$deposit->nomor_resi} berhasil diverifikasi dan diterima."
            : "Setoran {$deposit->nomor_resi} ditolak.";

        return back()->with('status', $msg);
    }

    /**
     * Halaman Detail Laporan / Setoran Sampah TPS3R.
     */
    public function tps3rDepositDetail(Request $request, Tps3rWasteDeposit $deposit): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile ?? PengelolaTps3rProfile::first();

        abort_unless($user->isAdminDlh() || ($profile !== null && $deposit->tps3r_profile_id === $profile->id), 403);

        $deposit->load([
            'petugas.petugasLapanganProfile',
            'pengelola.pengelolaTps3rProfile',
            'tps3rProfile',
            'weighings.warga.wargaProfile',
        ]);

        // Hitung neraca komposisi aktual muatan truk
        $composition = [
            'organik' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
            'anorganik_daur_ulang' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
            'residu' => ['weight' => 0.0, 'percentage' => 0.0, 'count' => 0],
        ];

        // Jika sudah dilakukan pemilahan lanjutan di fasilitas TPS3R
        if ($deposit->berat_organik_pilah_kg !== null || $deposit->berat_anorganik_pilah_kg !== null || $deposit->berat_residu_pilah_kg !== null) {
            $composition['organik']['weight'] = (float) ($deposit->berat_organik_pilah_kg ?? 0);
            $composition['anorganik_daur_ulang']['weight'] = (float) ($deposit->berat_anorganik_pilah_kg ?? 0);
            $composition['residu']['weight'] = (float) ($deposit->berat_residu_pilah_kg ?? 0);
            $totalPilah = $composition['organik']['weight'] + $composition['anorganik_daur_ulang']['weight'] + $composition['residu']['weight'];
            if ($totalPilah > 0) {
                foreach ($composition as $k => $v) {
                    $composition[$k]['percentage'] = round(($v['weight'] / $totalPilah) * 100, 1);
                }
            }
        } elseif ($deposit->weighings->isNotEmpty()) {
            $totalWeighingWeight = (float) $deposit->weighings->sum('berat_kg');
            foreach ($deposit->weighings as $w) {
                $pillar = MasterData::resolvePillar($w->jenis_sampah);
                $composition[$pillar]['weight'] += (float) $w->berat_kg;
                $composition[$pillar]['count']++;
            }
            if ($totalWeighingWeight > 0) {
                foreach ($composition as $k => $v) {
                    $composition[$k]['percentage'] = round(($v['weight'] / $totalWeighingWeight) * 100, 1);
                }
            }
        } else {
            $pillar = MasterData::resolvePillar($deposit->jenis_sampah);
            $composition[$pillar]['weight'] = (float) $deposit->berat_kg;
            $composition[$pillar]['percentage'] = 100.0;
            $composition[$pillar]['count'] = 1;
        }

        return view('dashboard.tps3r.deposit-detail', [
            'deposit' => $deposit,
            'profile' => $profile,
            'composition' => $composition,
        ]);
    }

    /**
     * Show TPS3R deposit entry and history.
     */
    public function tps3rDeposits(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile;
        $period = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
        $startDate = $period['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $period['end_date'] ?? Carbon::now()->toDateString();

        $deposits = Tps3rWasteDeposit::query()
            ->where('tps3r_profile_id', $profile?->id)
            ->whereBetween('tanggal_setor', [$startDate, $endDate])
            ->with(['petugas:id,name', 'pengelola:id,name', 'weighings.warga:id,name,phone_number'])
            ->latest('tanggal_setor')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.tps3r.deposits', compact('profile', 'deposits', 'startDate', 'endDate'));
    }

    /**
     * Show the TPS3R recycled product catalog management page.
     */
    public function tps3rProducts(Request $request): View
    {
        /** @var User $seller */
        $seller = $request->user();
        $query = WasteProduct::where('seller_id', $seller->id);

        $totalProduk = (clone $query)->count();
        $totalStok = (int) (clone $query)->sum('stok');
        $produkAktif = (clone $query)->where('is_available', true)->count();
        $estimasiNilaiInventaris = (float) (clone $query)->selectRaw('SUM(harga * stok) as total_nilai')->value('total_nilai');

        $products = (clone $query)->latest()->paginate(15);

        return view('dashboard.tps3r.products', compact(
            'products',
            'totalProduk',
            'totalStok',
            'produkAktif',
            'estimasiNilaiInventaris'
        ));
    }

    /**
     * Show orders containing products sold by the logged-in TPS3R manager.
     */
    public function tps3rOrders(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $isAdmin = $user->isAdminDlh();

        $status = $request->query('status');
        $validStatuses = ['menunggu', 'diproses', 'siap_dikirim', 'selesai', 'dibatalkan'];

        $fulfillmentsQuery = OrderFulfillment::query()
            ->with([
                'order.buyer:id,name,phone_number,email',
                'order.items.product:id,seller_id,nama_produk',
                'seller:id,name',
                'seller.pengelolaTps3rProfile:id,user_id,nama_tps3r',
            ])
            ->when(! $isAdmin, fn (Builder $query) => $query->where('seller_id', $user->id));

        if ($status && in_array($status, $validStatuses, true)) {
            $fulfillmentsQuery->where('status', $status);
        }

        $fulfillments = $fulfillmentsQuery->latest()->paginate(15)->withQueryString();

        $baseCountsQuery = OrderFulfillment::query()
            ->when(! $isAdmin, fn (Builder $query) => $query->where('seller_id', $user->id));
        $statusCounts = [
            'semua' => (clone $baseCountsQuery)->count(),
            'menunggu' => (clone $baseCountsQuery)->where('status', OrderFulfillmentStatus::Menunggu)->count(),
            'diproses' => (clone $baseCountsQuery)->where('status', OrderFulfillmentStatus::Diproses)->count(),
            'siap_dikirim' => (clone $baseCountsQuery)->where('status', OrderFulfillmentStatus::SiapDikirim)->count(),
            'selesai' => (clone $baseCountsQuery)->where('status', OrderFulfillmentStatus::Selesai)->count(),
            'dibatalkan' => (clone $baseCountsQuery)->where('status', OrderFulfillmentStatus::Dibatalkan)->count(),
        ];

        return view('dashboard.tps3r.orders', compact('fulfillments', 'statusCounts', 'status', 'isAdmin'));
    }

    /**
     * Show an order with only the line items belonging to the logged-in TPS3R manager.
     */
    public function tps3rOrder(Request $request, Order $order): View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isAdminDlh()) {
            $order = Order::with([
                'buyer:id,name,phone_number,email',
                'items.product',
                'fulfillments.seller:id,name',
                'fulfillments.seller.pengelolaTps3rProfile:id,user_id,nama_tps3r',
            ])
                ->findOrFail($order->getKey());

            return view('dashboard.tps3r.order-admin', compact('order'));
        }

        $seller = $user;
        $order = $this->sellerOrdersQuery($seller->id)
            ->findOrFail($order->getKey());
        $fulfillment = $order->fulfillments->first();

        abort_if($fulfillment === null, 404);

        return view('dashboard.tps3r.order', compact('order', 'fulfillment'));
    }

    /**
     * Add a recycled product to the TPS3R marketplace catalog.
     */
    public function storeMarketplaceProduct(StoreProductRequest $request): RedirectResponse
    {
        /** @var User $seller */
        $seller = $request->user();
        $validated = $request->validated();

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('products', 'public');
        } elseif (isset($validated['foto']) && is_string($validated['foto'])) {
            $fotoPath = $validated['foto'];
        }

        WasteProduct::create([
            'seller_id' => $seller->id,
            'nama_produk' => $validated['nama_produk'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori' => $validated['kategori'],
            'harga' => (float) $validated['harga'],
            'stok' => (int) $validated['stok'],
            'foto' => $fotoPath,
            'is_available' => $validated['is_available'] ?? true,
        ]);

        return to_route('dashboard.tps3r.products')->with('status', 'Produk daur ulang berhasil ditambahkan ke katalog.');
    }

    /**
     * Halaman Rekap Volume Sampah per Kategori TPS3R.
     */
    public function tps3rVolumeRecap(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->pengelolaTps3rProfile ?? PengelolaTps3rProfile::first();

        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();

        $period = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'jenis_sampah' => ['nullable', 'string', 'in:organik,anorganik_daur_ulang,residu'],
            'sumber_sampah' => ['nullable', 'string', 'in:armada_kelurahan,warga_mandiri,pemrakarsa_komersial'],
        ]);

        $startDate = $period['start_date'] ?? $startOfMonth;
        $endDate = $period['end_date'] ?? Carbon::now()->toDateString();

        $depositQuery = Tps3rWasteDeposit::query();
        if ($profile !== null) {
            $depositQuery->where('tps3r_profile_id', $profile->id);
        }

        $basePeriodQuery = (clone $depositQuery)->whereBetween('tanggal_setor', [$startDate, $endDate]);

        // Total metrik periode
        $totalVolumeKg = (float) (clone $basePeriodQuery)->sum('berat_kg');
        $totalVolumeTon = round($totalVolumeKg / 1000, 3);
        $totalSetoranCount = (int) (clone $basePeriodQuery)->count();

        // Breakdown per Jenis Sampah secara Akurat (Komposisi Nyata dari Hasil Pilah & Timbangan Warga)
        $allDeposits = (clone $basePeriodQuery)
            ->with('weighings:id,tps3r_waste_deposit_id,jenis_sampah,berat_kg')
            ->get();

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

        // Breakdown per Sumber Sampah
        $breakdownSumber = (clone $basePeriodQuery)
            ->selectRaw('sumber_sampah, SUM(berat_kg) as total_kg, COUNT(id) as count')
            ->groupBy('sumber_sampah')
            ->get();

        // Metrik Kapasitas & Utilisasi
        $kapasitasHarian = $profile ? (float) $profile->kapasitas_harian_kg : 1500.0;
        $dateDiffDays = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
        $rataRataHarianKg = round($totalVolumeKg / $dateDiffDays, 1);
        $persenUtilisasi = $kapasitasHarian > 0 ? min(100, round(($rataRataHarianKg / $kapasitasHarian) * 100, 1)) : 0;

        // Rekap Harian (Daily breakdown)
        $dailyRecords = (clone $basePeriodQuery)
            ->selectRaw('tanggal_setor, SUM(berat_kg) as total_kg, COUNT(id) as total_rit')
            ->groupBy('tanggal_setor')
            ->orderByDesc('tanggal_setor')
            ->take(14)
            ->get();

        // Riwayat Setoran Paginasi dengan filter opsional
        $filteredQuery = clone $basePeriodQuery;
        if (! empty($period['jenis_sampah'])) {
            $filteredQuery->where('jenis_sampah', $period['jenis_sampah']);
        }
        if (! empty($period['sumber_sampah'])) {
            $filteredQuery->where('sumber_sampah', $period['sumber_sampah']);
        }

        $riwayatSetoran = $filteredQuery
            ->with(['petugas:id,name', 'pengelola:id,name', 'weighings.warga:id,name,phone_number'])
            ->latest('tanggal_setor')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.tps3r.volume-recap', [
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
            'breakdownSumber' => $breakdownSumber,
            'kapasitasHarian' => $kapasitasHarian,
            'rataRataHarianKg' => $rataRataHarianKg,
            'persenUtilisasi' => $persenUtilisasi,
            'dailyRecords' => $dailyRecords,
            'riwayatSetoran' => $riwayatSetoran,
            'selectedJenis' => $period['jenis_sampah'] ?? null,
            'selectedSumber' => $period['sumber_sampah'] ?? null,
        ]);
    }

    /**
     * Perbarui data produk daur ulang katalog TPS3R.
     */
    public function updateMarketplaceProduct(Request $request, WasteProduct $wasteProduct): RedirectResponse
    {
        /** @var User $seller */
        $seller = $request->user();
        abort_unless($wasteProduct->seller_id === $seller->id, 403);

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'kategori' => ['required', 'string', 'in:pupuk_kompos,kerajinan_plastik,maggot_pakan,ecobrick,lainnya'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'stok' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $updateData = [
            'nama_produk' => $validated['nama_produk'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'kategori' => $validated['kategori'],
            'harga' => (float) $validated['harga'],
            'stok' => (int) $validated['stok'],
            'is_available' => (bool) ($validated['is_available'] ?? false),
        ];

        if ($request->hasFile('foto')) {
            if ($wasteProduct->foto && ! str_starts_with($wasteProduct->foto, 'http')) {
                Storage::disk('public')->delete($wasteProduct->foto);
            }
            $updateData['foto'] = $request->file('foto')->store('products', 'public');
        }

        $wasteProduct->update($updateData);

        return to_route('dashboard.tps3r.products')->with('status', 'Produk daur ulang berhasil diperbarui.');
    }

    /**
     * Hapus produk daur ulang dari katalog TPS3R.
     */
    public function destroyMarketplaceProduct(Request $request, WasteProduct $wasteProduct): RedirectResponse
    {
        /** @var User $seller */
        $seller = $request->user();
        abort_unless($wasteProduct->seller_id === $seller->id, 403);

        $namaProduk = $wasteProduct->nama_produk;
        if ($wasteProduct->foto && ! str_starts_with($wasteProduct->foto, 'http')) {
            Storage::disk('public')->delete($wasteProduct->foto);
        }
        $wasteProduct->delete();

        return to_route('dashboard.tps3r.products')->with('status', "Produk \"{$namaProduk}\" berhasil dihapus dari katalog.");
    }

    /**
     * Dashboard & Form Unggah Laporan Semester Pemrakarsa (PPL-F-008).
     */
    public function pemrakarsa(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $pemrakarsaProfile = $user->pemrakarsaProfile;

        $riwayatLaporan = EnvironmentalReport::where('pemrakarsa_id', $user->id)
            ->latest()
            ->get();

        $totalLaporan = $riwayatLaporan->count();
        $disetujuiCount = $riwayatLaporan->where('status', 'disetujui')->count();
        $menungguCount = $riwayatLaporan->where('status', 'menunggu_verifikasi')->count();
        $perluRevisiCount = $riwayatLaporan->where('status', 'perlu_revisi')->count();

        return view('dashboard.pemrakarsa', [
            'pemrakarsaProfile' => $pemrakarsaProfile,
            'riwayatLaporan' => $riwayatLaporan,
            'stats' => [
                'total' => $totalLaporan,
                'disetujui' => $disetujuiCount,
                'menunggu' => $menungguCount,
                'perlu_revisi' => $perluRevisiCount,
            ],
        ]);
    }

    /**
     * Halaman Riwayat & Pemantauan Status Verifikasi Pelaporan Dokumen Lingkungan (PPL-F-008).
     */
    public function pemrakarsaReports(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $pemrakarsaProfile = $user->pemrakarsaProfile;

        $statusFilter = $request->query('status');
        $jenisFilter = $request->query('jenis_dokumen');
        $search = $request->query('q');

        $query = EnvironmentalReport::where('pemrakarsa_id', $user->id);

        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($jenisFilter && $jenisFilter !== 'all') {
            $query->where('jenis_dokumen', $jenisFilter);
        }

        if ($search) {
            $query->where('judul_laporan', 'like', "%{$search}%");
        }

        $reports = $query->latest()->paginate(10)->withQueryString();

        $baseCountQuery = EnvironmentalReport::where('pemrakarsa_id', $user->id);
        $reportCounts = [
            'all' => (clone $baseCountQuery)->count(),
            'menunggu_verifikasi' => (clone $baseCountQuery)->where('status', 'menunggu_verifikasi')->count(),
            'disetujui' => (clone $baseCountQuery)->where('status', 'disetujui')->count(),
            'perlu_revisi' => (clone $baseCountQuery)->where('status', 'perlu_revisi')->count(),
        ];

        return view('dashboard.pemrakarsa.reports', [
            'pemrakarsaProfile' => $pemrakarsaProfile,
            'reports' => $reports,
            'reportCounts' => $reportCounts,
            'currentStatus' => $statusFilter ?? 'all',
            'currentJenis' => $jenisFilter ?? 'all',
            'search' => $search,
        ]);
    }

    /**
     * Halaman Formulir Laporan Pelaksanaan Dokumen Lingkungan Semester Pemrakarsa.
     */
    public function pemrakarsaCreateReport(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $pemrakarsaProfile = $user->pemrakarsaProfile;

        return view('dashboard.pemrakarsa.create-report', [
            'pemrakarsaProfile' => $pemrakarsaProfile,
        ]);
    }

    /**
     * Store uploaded semester environmental report from Pemrakarsa (PPL-F-008).
     */
    public function storeLaporanPemrakarsa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul_laporan' => ['required', 'string', 'max:255'],
            'periode_semester' => ['required', 'string', 'in:Semester 1 (Jan - Jun),Semester 2 (Jul - Des)'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2035'],
            'jenis_dokumen' => ['required', 'string', 'in:SPPL,UKL-UPL,AMDAL'],
            'timbulan_sampah_kg' => ['nullable', 'numeric', 'min:0'],
            'metode_pengelolaan' => ['nullable', 'string', 'max:2000'],
            'file_laporan' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $path = $request->file('file_laporan')->store('environmental_reports', 'public');

        EnvironmentalReport::create([
            'pemrakarsa_id' => $user->id,
            'judul_laporan' => $validated['judul_laporan'],
            'periode_semester' => $validated['periode_semester'],
            'tahun' => (int) $validated['tahun'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'timbulan_sampah_kg' => isset($validated['timbulan_sampah_kg']) ? (float) $validated['timbulan_sampah_kg'] : null,
            'metode_pengelolaan' => $validated['metode_pengelolaan'] ?? null,
            'file_path' => $path,
            'status' => 'menunggu_verifikasi',
        ]);

        return redirect()->route('dashboard.pemrakarsa')
            ->with('status', 'Laporan pengelolaan lingkungan hidup semesteran berhasil diunggah dan siap diverifikasi oleh DLH Kota Madiun.');
    }

    /**
     * Admin DLH verifies environmental report.
     */
    public function verifyLaporan(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:disetujui,perlu_revisi'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = EnvironmentalReport::findOrFail($id);
        $report->update([
            'status' => $validated['status'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'verified_at' => Carbon::now(),
        ]);

        return redirect()->back()
            ->with('status', 'Verifikasi dokumen laporan lingkungan berhasil diperbarui.');
    }

    /**
     * Build the seller-scoped marketplace order query.
     *
     * @return Builder<Order>
     */
    private function sellerOrdersQuery(int $sellerId): Builder
    {
        return Order::query()
            ->whereHas('items.product', fn ($query) => $query->where('seller_id', $sellerId))
            ->with([
                'buyer:id,name,phone_number,email',
                'items' => fn ($query) => $query
                    ->whereHas('product', fn ($productQuery) => $productQuery->where('seller_id', $sellerId))
                    ->with('product:id,seller_id,nama_produk'),
                'fulfillments' => fn ($query) => $query->where('seller_id', $sellerId),
            ]);
    }
}
