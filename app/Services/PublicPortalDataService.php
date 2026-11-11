<?php

namespace App\Services;

use App\Models\BroadcastAnnouncement;
use App\Models\Complaint;
use App\Models\EducationArticle;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;
use App\Models\WasteWeighing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PublicPortalDataService
{
    /**
     * Build the full aggregated dataset for the public portal home page and overview API.
     *
     * @return array<string, mixed>
     */
    public function homeOverview(): array
    {
        $settings = PortalSetting::resolved();
        $bannerEnabled = ($settings['announcement_banner_enabled'] ?? '1') === '1';
        $statsEnabled = ($settings['show_stats_section'] ?? '1') === '1';
        $educationEnabled = ($settings['show_education_section'] ?? '1') === '1';
        $marketplaceEnabled = ($settings['show_marketplace_section'] ?? '1') === '1';

        $announcements = $bannerEnabled
            ? $this->publicAnnouncementsQuery(respectPortalSetting: false)->limit(5)->get()
            : collect();

        if ($statsEnabled) {
            $totalComplaints = Complaint::count();
            $handledComplaints = Complaint::whereNotIn('status', ['diterima', 'menunggu_verifikasi'])->count();

            $stats = [
                'active_facilities' => PengelolaTps3rProfile::query()
                    ->whereHas('user', fn (Builder $q) => $q->where('is_active', true))
                    ->count(),
                'active_routes' => RouteAssignment::query()->where('is_active', true)->count(),
                'managed_waste_tons' => round(
                    ((float) WasteWeighing::whereNull('tps3r_waste_deposit_id')->sum('berat_kg')
                        + (float) Tps3rWasteDeposit::sum('berat_kg')) / 1000,
                    1,
                ),
                'complaint_response_rate' => $totalComplaints > 0
                    ? round(($handledComplaints / $totalComplaints) * 100, 1)
                    : 100.0,
                'active_products' => WasteProduct::query()
                    ->where('is_available', true)
                    ->where('stok', '>', 0)
                    ->count(),
            ];
        } else {
            $stats = [
                'active_facilities' => 0,
                'active_routes' => 0,
                'managed_waste_tons' => 0.0,
                'complaint_response_rate' => 0.0,
                'active_products' => 0,
            ];
        }

        $routePreviews = RouteAssignment::query()
            ->where('is_active', true)
            ->with(['petugas:id,name', 'todaySession:id,route_assignment_id,status,waktu_mulai,waktu_selesai'])
            ->orderBy('district')
            ->orderBy('route_code')
            ->get()
            ->groupBy('district')
            ->map(fn (Collection $items) => $items->take(2)->values());

        $featuredFacilities = PengelolaTps3rProfile::query()
            ->whereHas('user', fn (Builder $q) => $q->where('is_active', true))
            ->with('user:id,name,phone_number')
            ->orderByDesc('is_featured_portal')
            ->orderBy('nama_tps3r')
            ->limit(3)
            ->get();

        $featuredProducts = $marketplaceEnabled
            ? WasteProduct::query()
                ->with([
                    'seller:id,name',
                    'seller.pengelolaTps3rProfile:id,public_id,user_id,nama_tps3r,kecamatan,kelurahan',
                ])
                ->where('is_available', true)
                ->where('stok', '>', 0)
                ->orderByDesc('is_featured')
                ->latest()
                ->limit(3)
                ->get()
            : collect();

        $latestArticles = $educationEnabled
            ? EducationArticle::published()
                ->with('creator:id,name')
                ->orderByDesc('is_featured')
                ->latest('published_at')
                ->limit(3)
                ->get()
            : collect();

        return [
            'settings' => $settings,
            'banner_announcement' => $announcements->first(),
            'announcements' => $announcements,
            'stats' => $stats,
            'route_previews' => $routePreviews,
            'featured_facilities' => $featuredFacilities,
            'featured_products' => $featuredProducts,
            'latest_articles' => $latestArticles,
            'waste_categories' => MasterData::query()
                ->where('group', 'waste_category')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['code', 'pillar', 'name', 'numeric_value', 'unit', 'description']),
        ];
    }

    /** @return Builder<BroadcastAnnouncement> */
    public function publicAnnouncementsQuery(?string $search = null, ?string $priority = null, bool $respectPortalSetting = true): Builder
    {
        if ($respectPortalSetting && (PortalSetting::resolved()['announcement_banner_enabled'] ?? '1') !== '1') {
            return BroadcastAnnouncement::query()->whereRaw('1 = 0');
        }

        $query = BroadcastAnnouncement::published()
            ->with('creator:id,name')
            ->where('show_on_portal', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('status')
                ->orWhere('status', 'published')
                ->orWhere(fn (Builder $scheduled) => $scheduled
                    ->where('status', 'scheduled')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())))
            ->where(fn (Builder $q) => $q
                ->whereNull('audience_role')
                ->orWhereIn('audience_role', ['all', 'warga']))
            ->when($priority, fn (Builder $q) => $q->where('priority', $priority));

        PortalCmsService::applyEscapedLike($query, ['title', 'summary', 'content'], $search);

        return $query
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'important' THEN 2 ELSE 3 END")
            ->latest('published_at');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, PengelolaTps3rProfile>
     */
    public function paginateFacilities(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $perPage = min(50, max(1, $perPage));
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        $kecamatan = isset($filters['kecamatan']) ? trim((string) $filters['kecamatan']) : null;
        $kelurahan = isset($filters['kelurahan']) ? trim((string) $filters['kelurahan']) : null;
        $jenis = isset($filters['jenis_fasilitas']) ? trim((string) $filters['jenis_fasilitas']) : null;
        $kategoriSampah = isset($filters['kategori_sampah']) ? trim((string) $filters['kategori_sampah']) : null;

        $query = PengelolaTps3rProfile::query()
            ->whereHas('user', fn (Builder $q) => $q->where('is_active', true))
            ->with('user:id,name,phone_number')
            ->when($kecamatan, fn (Builder $q) => $q->where('kecamatan', $kecamatan))
            ->when($kelurahan, fn (Builder $q) => $q->where('kelurahan', $kelurahan))
            ->when($jenis, fn (Builder $q) => $q->where('jenis_fasilitas', $jenis));

        if ($kategoriSampah) {
            PortalCmsService::applyEscapedLike($query, ['kategori_sampah_diterima'], $kategoriSampah);
        }

        PortalCmsService::applyEscapedLike($query, ['nama_tps3r', 'kode_tps3r', 'alamat', 'kelurahan', 'kecamatan'], $search);

        return $query
            ->orderByDesc('is_featured_portal')
            ->orderBy('nama_tps3r')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, RouteAssignment>
     */
    public function activeRoutes(array $filters = []): Collection
    {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        $kecamatan = isset($filters['kecamatan']) ? trim((string) $filters['kecamatan']) : null;
        $kelurahan = isset($filters['kelurahan']) ? trim((string) $filters['kelurahan']) : null;
        $hari = isset($filters['hari']) ? trim((string) $filters['hari']) : null;

        $query = RouteAssignment::query()
            ->where('is_active', true)
            ->with(['petugas:id,name,phone_number', 'todaySession:id,route_assignment_id,status,waktu_mulai,waktu_selesai'])
            ->when($kecamatan, fn (Builder $q) => $q->where('district', $kecamatan));

        PortalCmsService::applyEscapedLike($query, ['subdistrict'], $kelurahan);
        PortalCmsService::applyEscapedLike($query, ['schedule_day'], $hari);
        PortalCmsService::applyEscapedLike($query, ['route_code', 'route_name', 'district', 'subdistrict', 'end_facility'], $search);

        return $query
            ->orderBy('district')
            ->orderBy('route_code')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, EducationArticle>
     */
    public function paginateArticles(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $perPage = min(50, max(1, $perPage));
        $kategori = isset($filters['kategori']) ? trim((string) $filters['kategori']) : null;
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;

        $query = EducationArticle::published()
            ->with('creator:id,name')
            ->when((PortalSetting::resolved()['show_education_section'] ?? '1') !== '1', fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->when($kategori && $kategori !== 'semua', fn (Builder $q) => $q->where('kategori', $kategori));

        PortalCmsService::applyEscapedLike($query, ['judul', 'ringkasan', 'isi'], $search);

        return $query
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, PublicDocument>
     */
    public function paginateDocuments(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $perPage = min(50, max(1, $perPage));
        $kategori = isset($filters['kategori']) ? trim((string) $filters['kategori']) : null;
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;

        $query = PublicDocument::published()
            ->with('creator:id,name')
            ->where(fn (Builder $q) => $q
                ->whereNull('tanggal_rilis')
                ->orWhereDate('tanggal_rilis', '<=', now()->toDateString()))
            ->when((PortalSetting::resolved()['show_education_section'] ?? '1') !== '1', fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->when($kategori && $kategori !== 'semua', fn (Builder $q) => $q->where('kategori', $kategori));

        PortalCmsService::applyEscapedLike($query, ['judul', 'deskripsi'], $search);

        return $query
            ->latest('tanggal_rilis')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, WasteProduct>
     */
    public function paginateMarketplaceProducts(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $perPage = min(50, max(1, $perPage));
        $kategori = isset($filters['kategori']) ? trim((string) $filters['kategori']) : null;
        $search = isset($filters['search']) ? trim((string) $filters['search']) : null;
        $sellerId = isset($filters['seller_id']) ? (int) $filters['seller_id'] : null;
        $hargaMin = isset($filters['harga_min']) && $filters['harga_min'] !== '' ? (float) $filters['harga_min'] : null;
        $hargaMax = isset($filters['harga_max']) && $filters['harga_max'] !== '' ? (float) $filters['harga_max'] : null;
        $includeUnavailable = (bool) ($filters['include_unavailable'] ?? false);
        $sort = isset($filters['sort']) ? (string) $filters['sort'] : 'terbaru';

        $query = WasteProduct::query()
            ->with([
                'seller:id,name,email,role',
                'seller.pengelolaTps3rProfile:id,public_id,user_id,nama_tps3r,kode_tps3r,kelurahan,kecamatan,midtrans_is_active',
            ])
            ->when(! $includeUnavailable, fn (Builder $q) => $q->where('is_available', true)->where('stok', '>', 0))
            ->when($kategori && $kategori !== 'semua', fn (Builder $q) => $q->where('kategori', $kategori))
            ->when($sellerId, fn (Builder $q) => $q->where('seller_id', $sellerId))
            ->when($hargaMin !== null, fn (Builder $q) => $q->where('harga', '>=', $hargaMin))
            ->when($hargaMax !== null, fn (Builder $q) => $q->where('harga', '<=', $hargaMax));

        PortalCmsService::applyEscapedLike($query, ['nama_produk', 'deskripsi'], $search);

        match ($sort) {
            'termurah' => $query->orderBy('harga', 'asc'),
            'termahal' => $query->orderBy('harga', 'desc'),
            default => $query->orderByDesc('is_featured')->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /** @return Collection<int, User> */
    public function activeMarketplaceStores(): Collection
    {
        return User::query()
            ->where('role', 'pengelola_tps3r')
            ->where('is_active', true)
            ->whereHas('pengelolaTps3rProfile')
            ->with('pengelolaTps3rProfile')
            ->withCount([
                'wasteProducts as total_products' => fn (Builder $q) => $q->where('is_available', true)->where('stok', '>', 0),
            ])
            ->orderBy('name')
            ->get();
    }
}
