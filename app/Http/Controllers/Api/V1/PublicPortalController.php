<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BroadcastAnnouncementResource;
use App\Http\Resources\Api\EducationArticleResource;
use App\Http\Resources\Api\PublicDocumentResource;
use App\Models\EducationArticle;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Services\PublicPortalDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPortalController extends Controller
{
    public function __construct(public PublicPortalDataService $portalData) {}

    public function overview(): JsonResponse
    {
        $data = $this->portalData->homeOverview();

        return response()->json([
            'success' => true,
            'message' => 'Data ringkasan portal publik berhasil diambil.',
            'data' => [
                'settings' => $data['settings'],
                'banner_announcement' => $data['banner_announcement']
                    ? new BroadcastAnnouncementResource($data['banner_announcement'])
                    : null,
                'announcements' => BroadcastAnnouncementResource::collection($data['announcements']),
                'stats' => $data['stats'],
                'route_previews' => $data['route_previews'],
                'featured_facilities' => $data['featured_facilities'],
                'featured_products' => $data['featured_products'],
                'latest_articles' => EducationArticleResource::collection($data['latest_articles']),
                'waste_categories' => $data['waste_categories'],
            ],
        ], Response::HTTP_OK);
    }

    public function announcements(Request $request): JsonResponse
    {
        $items = $this->portalData->publicAnnouncementsQuery(
            $request->query('search') ? (string) $request->query('search') : null,
            $request->query('priority') ? (string) $request->query('priority') : null,
        )->paginate(min(50, max(1, (int) $request->query('per_page', 10))));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumuman publik berhasil diambil.',
            'data' => BroadcastAnnouncementResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function articles(Request $request): JsonResponse
    {
        $articles = $this->portalData->paginateArticles(
            $request->only(['kategori', 'search']),
            min(50, max(1, (int) $request->query('per_page', 9))),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar artikel edukasi berhasil diambil.',
            'data' => EducationArticleResource::collection($articles->items()),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function showArticle(EducationArticle $educationArticle): JsonResponse
    {
        abort_if(
            (PortalSetting::resolved()['show_education_section'] ?? '1') !== '1'
            || $educationArticle->status !== 'published'
            || ($educationArticle->published_at !== null && $educationArticle->published_at->isFuture()),
            Response::HTTP_NOT_FOUND,
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail artikel edukasi berhasil diambil.',
            'data' => new EducationArticleResource($educationArticle),
        ], Response::HTTP_OK);
    }

    public function documents(Request $request): JsonResponse
    {
        $documents = $this->portalData->paginateDocuments(
            $request->only(['kategori', 'search']),
            min(50, max(1, (int) $request->query('per_page', 10))),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar dokumen publik berhasil diambil.',
            'data' => PublicDocumentResource::collection($documents->items()),
            'meta' => [
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function downloadDocument(PublicDocument $publicDocument): StreamedResponse|JsonResponse
    {
        abort_if(
            (PortalSetting::resolved()['show_education_section'] ?? '1') !== '1'
            || $publicDocument->status !== 'published'
            || ($publicDocument->tanggal_rilis !== null && $publicDocument->tanggal_rilis->toDateString() > now()->toDateString()),
            Response::HTTP_NOT_FOUND,
        );

        if (! Storage::disk('local')->exists($publicDocument->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas dokumen tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        $publicDocument->increment('download_count');

        return Storage::disk('local')->download(
            $publicDocument->file_path,
            $publicDocument->slug.'.'.strtolower($publicDocument->file_format ?: 'pdf'),
        );
    }

    public function facilities(Request $request): JsonResponse
    {
        $facilities = $this->portalData->paginateFacilities(
            $request->only(['search', 'kecamatan', 'kelurahan', 'jenis_fasilitas', 'kategori_sampah']),
            min(50, max(1, (int) $request->query('per_page', 12))),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar fasilitas TPS3R dan Bank Sampah berhasil diambil.',
            'data' => $facilities->items(),
            'meta' => [
                'current_page' => $facilities->currentPage(),
                'last_page' => $facilities->lastPage(),
                'per_page' => $facilities->perPage(),
                'total' => $facilities->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function showFacility(PengelolaTps3rProfile $pengelolaTps3rProfile): JsonResponse
    {
        $pengelolaTps3rProfile->load([
            'user:id,name,phone_number,is_active',
            'user.wasteProducts' => fn ($q) => $q->where('is_available', true)->where('stok', '>', 0)->latest(),
        ]);

        abort_unless((bool) $pengelolaTps3rProfile->user?->is_active, Response::HTTP_NOT_FOUND);

        return response()->json([
            'success' => true,
            'message' => 'Detail fasilitas TPS3R berhasil diambil.',
            'data' => $pengelolaTps3rProfile,
        ], Response::HTTP_OK);
    }

    public function routes(Request $request): JsonResponse
    {
        $routes = $this->portalData->activeRoutes(
            $request->only(['search', 'kecamatan', 'kelurahan', 'hari']),
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar jadwal dan rute layanan kota berhasil diambil.',
            'data' => $routes,
        ], Response::HTTP_OK);
    }
}
