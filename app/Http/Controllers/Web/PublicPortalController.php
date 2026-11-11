<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\EducationArticle;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\WasteProduct;
use App\Services\PublicPortalDataService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPortalController extends Controller
{
    public function __construct(public PublicPortalDataService $portalData) {}

    public function index(): View
    {
        $overview = $this->portalData->homeOverview();
        $portalSettings = $overview['settings'];
        $announcement = ($portalSettings['announcement_banner_enabled'] ?? '1') === '1'
            ? $overview['banner_announcement']
            : null;

        $statistics = [
            'facilities' => $overview['stats']['active_facilities'],
            'routes' => $overview['stats']['active_routes'],
            'products' => $overview['stats']['active_products'],
            'residents' => User::query()->where('role', UserRole::Warga->value)->count(),
        ];

        return view('public.home', [
            ...$overview,
            'portalSettings' => $portalSettings,
            'announcement' => $announcement,
            'statistics' => $statistics,
            'articles' => $overview['latest_articles'],
            'facilities' => $overview['featured_facilities'],
            'featuredProducts' => $overview['featured_products'],
        ]);
    }

    public function services(Request $request): View
    {
        $district = trim((string) $request->query('district', $request->query('kecamatan', '')));
        $search = trim((string) $request->query('search', ''));

        $serviceFilters = [
            'search' => $search,
            'kecamatan' => $district,
            'kelurahan' => trim((string) $request->query('kelurahan', '')),
            'jenis_fasilitas' => trim((string) $request->query('jenis_fasilitas', '')),
            'hari' => trim((string) $request->query('hari', '')),
            'kategori_sampah' => trim((string) $request->query('kategori_sampah', '')),
        ];

        $overview = $this->portalData->homeOverview();
        $facilities = $this->portalData->paginateFacilities($serviceFilters, 12);
        $routes = $this->portalData->activeRoutes($serviceFilters);

        $masterDistricts = MasterData::query()
            ->where('group', 'districts')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name');

        $districts = $masterDistricts
            ->merge(PengelolaTps3rProfile::query()->whereNotNull('kecamatan')->distinct()->pluck('kecamatan'))
            ->merge(RouteAssignment::query()->whereNotNull('district')->distinct()->pluck('district'))
            ->filter(fn (?string $value): bool => filled($value))
            ->unique()
            ->sort()
            ->values();

        return view('public.services', [
            ...$overview,
            'portalSettings' => $overview['settings'],
            'facilities' => $facilities,
            'routes' => $routes,
            'districts' => $districts,
            'filters' => [
                ...$serviceFilters,
                'district' => $district,
            ],
        ]);
    }

    public function education(Request $request): View
    {
        $filters = $request->only(['kategori', 'search']);
        $overview = $this->portalData->homeOverview();

        $categories = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $articles = $this->portalData->paginateArticles($filters, 9);
        $documents = $this->portalData->paginateDocuments([
            'kategori' => $request->query('doc_kategori'),
            'search' => $request->query('search'),
        ], 10);
        $facilities = $this->portalData->paginateFacilities([], 6);

        $serviceSteps = $this->formatServiceSteps($overview['settings']['service_steps'] ?? []);

        return view('public.education', [
            ...$overview,
            'portalSettings' => $overview['settings'],
            'categories' => $categories,
            'articles' => $articles,
            'documents' => $documents,
            'facilities' => $facilities,
            'serviceSteps' => $serviceSteps,
            'filters' => $filters,
        ]);
    }

    public function showArticle(EducationArticle $educationArticle): View
    {
        abort_if(
            $educationArticle->status !== 'published'
            || ($educationArticle->published_at !== null && $educationArticle->published_at->isFuture()),
            Response::HTTP_NOT_FOUND,
        );

        $overview = $this->portalData->homeOverview();
        $relatedArticles = EducationArticle::published()
            ->where('id', '!=', $educationArticle->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        if (ViewFacade::exists('portal.article-detail')) {
            return view('portal.article-detail', [
                ...$overview,
                'portalSettings' => $overview['settings'],
                'article' => $educationArticle,
                'relatedArticles' => $relatedArticles,
            ]);
        }

        $categories = MasterData::query()
            ->where('group', 'waste_category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.education', [
            ...$overview,
            'portalSettings' => $overview['settings'],
            'article' => $educationArticle,
            'relatedArticles' => $relatedArticles,
            'categories' => $categories,
            'articles' => $this->portalData->paginateArticles([], 9),
            'documents' => $this->portalData->paginateDocuments([], 10),
            'facilities' => $this->portalData->paginateFacilities([], 6),
            'serviceSteps' => $this->formatServiceSteps($overview['settings']['service_steps'] ?? []),
            'filters' => [],
        ]);
    }

    public function marketplace(Request $request): View
    {
        $category = trim((string) $request->query('category', $request->query('kategori', '')));
        $storePublicId = trim((string) $request->query('store', ''));
        $search = trim((string) $request->query('search', ''));
        $priceRange = trim((string) $request->query('price_range', ''));
        $sort = trim((string) $request->query('sort', 'terbaru'));

        $hargaMin = $request->query('harga_min');
        $hargaMax = $request->query('harga_max');
        if ($hargaMin === null && $hargaMax === null && $priceRange !== '') {
            [$hargaMin, $hargaMax] = match ($priceRange) {
                'under_25k' => [null, 25000],
                '25k_50k' => [25000, 50000],
                'above_50k' => [50000, null],
                default => [null, null],
            };
        }

        $sellerId = $request->query('seller_id');
        if ($storePublicId !== '') {
            $resolvedSellerId = PengelolaTps3rProfile::query()
                ->where('public_id', $storePublicId)
                ->value('user_id');

            $sellerId = $resolvedSellerId ?? -1;
        }

        $productFilters = [
            'search' => $search,
            'kategori' => $category,
            'seller_id' => $sellerId,
            'harga_min' => $hargaMin,
            'harga_max' => $hargaMax,
            'sort' => $sort ?: 'terbaru',
            'include_unavailable' => $request->boolean('include_unavailable'),
        ];

        $overview = $this->portalData->homeOverview();
        $products = $this->portalData->paginateMarketplaceProducts($productFilters, 12);
        $stores = PengelolaTps3rProfile::query()
            ->whereHas('user', fn (Builder $query) => $query->where('is_active', true))
            ->with([
                'user' => fn ($query) => $query->withCount([
                    'wasteProducts as active_products_count' => fn (Builder $productQuery) => $productQuery
                        ->where('is_available', true)
                        ->where('stok', '>', 0),
                ]),
            ])
            ->orderBy('nama_tps3r')
            ->get();

        $categoryCounts = WasteProduct::query()
            ->where('is_available', true)
            ->where('stok', '>', 0)
            ->whereNotNull('kategori')
            ->selectRaw('kategori, count(*) as total')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->pluck('total', 'kategori');

        $categories = $categoryCounts->keys();
        $hasActiveFilter = $search !== '' || $category !== '' || $storePublicId !== '' || $priceRange !== '' || $hargaMin !== null || $hargaMax !== null;

        return view('public.marketplace', [
            ...$overview,
            'portalSettings' => $overview['settings'],
            'products' => $products,
            'stores' => $stores,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'totalAvailableProducts' => (int) $categoryCounts->sum(),
            'featuredProduct' => $hasActiveFilter ? null : $overview['featured_products']->first(),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'store' => $storePublicId,
                'price_range' => $priceRange,
                'sort' => $sort ?: 'terbaru',
            ],
        ]);
    }

    public function downloadDocument(PublicDocument $publicDocument): StreamedResponse
    {
        abort_if(
            (PortalSetting::resolved()['show_education_section'] ?? '1') !== '1'
            || $publicDocument->status !== 'published'
            || ($publicDocument->tanggal_rilis !== null && $publicDocument->tanggal_rilis->isFuture()),
            Response::HTTP_NOT_FOUND,
        );
        abort_if(! Storage::disk('local')->exists($publicDocument->file_path), Response::HTTP_NOT_FOUND);

        $publicDocument->increment('download_count');

        return Storage::disk('local')->download(
            $publicDocument->file_path,
            $publicDocument->slug.'.'.strtolower($publicDocument->file_format ?: 'pdf'),
        );
    }

    /**
     * Format service workflow steps consistently with 2-digit step prefixes.
     *
     * @param  array<int, array<string, mixed>>  $steps
     * @return array<int, array{step: string, title: string, description: string}>
     */
    private function formatServiceSteps(array $steps): array
    {
        return collect($steps)
            ->map(fn (array $step): array => [
                'step' => str_pad((string) ($step['step'] ?? ''), 2, '0', STR_PAD_LEFT),
                'title' => (string) ($step['title'] ?? ''),
                'description' => (string) ($step['description'] ?? ''),
            ])
            ->all();
    }
}
