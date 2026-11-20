<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PortalSetting;
use App\Services\PublicPortalDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicSearchController extends Controller
{
    public function __construct(public PublicPortalDataService $portalData) {}

    public function __invoke(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $facilities = new Collection;
        $routes = new Collection;
        $articles = new Collection;
        $documents = new Collection;
        $products = new Collection;

        if ($query !== '') {
            $facilities = $this->portalData->paginateFacilities(['search' => $query], 6)->getCollection();
            $routes = $this->portalData->activeRoutes(['search' => $query])->take(6)->values();
            $articles = $this->portalData->paginateArticles(['search' => $query], 6)->getCollection();
            $documents = $this->portalData->paginateDocuments(['search' => $query], 6)->getCollection();
            $products = $this->portalData->paginateMarketplaceProducts(['search' => $query], 6)->getCollection();
        }

        return view('public.search', [
            'portalSettings' => PortalSetting::resolved(),
            'query' => $query,
            'facilities' => $facilities,
            'routes' => $routes,
            'articles' => $articles,
            'documents' => $documents,
            'products' => $products,
            'resultCount' => $facilities->count()
                + $routes->count()
                + $articles->count()
                + $documents->count()
                + $products->count(),
        ]);
    }
}
