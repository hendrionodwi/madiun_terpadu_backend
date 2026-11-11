<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\SaveAnnouncementRequest;
use App\Http\Requests\Cms\SaveEducationArticleRequest;
use App\Http\Requests\Cms\SavePublicDocumentRequest;
use App\Http\Requests\Cms\UpdatePortalSettingsRequest;
use App\Http\Resources\Api\BroadcastAnnouncementResource;
use App\Http\Resources\Api\EducationArticleResource;
use App\Http\Resources\Api\PublicDocumentResource;
use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\User;
use App\Services\PortalCmsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AdminCmsController extends Controller
{
    public function __construct(public PortalCmsService $cms) {}

    public function summary(): JsonResponse
    {
        Gate::authorize('viewAny', BroadcastAnnouncement::class);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan konten portal publik berhasil diambil.',
            'data' => $this->cms->adminSummary(),
        ], Response::HTTP_OK);
    }

    public function indexAnnouncements(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', BroadcastAnnouncement::class);

        $search = $request->filled('search') ? (string) $request->query('search') : null;

        $items = BroadcastAnnouncement::query()
            ->with('creator:id,name')
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', (string) $request->query('status')))
            ->when($request->filled('priority'), fn (Builder $q) => $q->where('priority', (string) $request->query('priority')))
            ->when($search !== null, fn (Builder $q) => PortalCmsService::applyEscapedLike($q, ['title'], $search))
            ->latest('id')
            ->paginate(min(50, max(1, (int) $request->query('per_page', 15))));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumuman CMS berhasil diambil.',
            'data' => BroadcastAnnouncementResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function storeAnnouncement(SaveAnnouncementRequest $request): JsonResponse
    {
        Gate::authorize('create', BroadcastAnnouncement::class);

        /** @var User $admin */
        $admin = $request->user();
        $announcement = $this->cms->createAnnouncement($admin, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dibuat.',
            'data' => new BroadcastAnnouncementResource($announcement),
        ], Response::HTTP_CREATED);
    }

    public function updateAnnouncement(SaveAnnouncementRequest $request, BroadcastAnnouncement $broadcastAnnouncement): JsonResponse
    {
        Gate::authorize('update', $broadcastAnnouncement);

        $announcement = $this->cms->updateAnnouncement($broadcastAnnouncement, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diperbarui.',
            'data' => new BroadcastAnnouncementResource($announcement),
        ], Response::HTTP_OK);
    }

    public function destroyAnnouncement(BroadcastAnnouncement $broadcastAnnouncement): JsonResponse
    {
        Gate::authorize('delete', $broadcastAnnouncement);

        $broadcastAnnouncement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    public function indexArticles(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', EducationArticle::class);

        $search = $request->filled('search') ? (string) $request->query('search') : null;

        $items = EducationArticle::query()
            ->with('creator:id,name')
            ->when($request->filled('kategori'), fn (Builder $q) => $q->where('kategori', (string) $request->query('kategori')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', (string) $request->query('status')))
            ->when($search !== null, fn (Builder $q) => PortalCmsService::applyEscapedLike($q, ['judul'], $search))
            ->latest('id')
            ->paginate(min(50, max(1, (int) $request->query('per_page', 15))));

        return response()->json([
            'success' => true,
            'message' => 'Daftar artikel edukasi CMS berhasil diambil.',
            'data' => EducationArticleResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function storeArticle(SaveEducationArticleRequest $request): JsonResponse
    {
        Gate::authorize('create', EducationArticle::class);

        /** @var User $admin */
        $admin = $request->user();
        $article = $this->cms->createArticle($admin, $request->validated(), $request->file('cover_image'));

        return response()->json([
            'success' => true,
            'message' => 'Artikel edukasi berhasil dibuat.',
            'data' => new EducationArticleResource($article),
        ], Response::HTTP_CREATED);
    }

    public function updateArticle(SaveEducationArticleRequest $request, EducationArticle $educationArticle): JsonResponse
    {
        Gate::authorize('update', $educationArticle);

        $article = $this->cms->updateArticle($educationArticle, $request->validated(), $request->file('cover_image'));

        return response()->json([
            'success' => true,
            'message' => 'Artikel edukasi berhasil diperbarui.',
            'data' => new EducationArticleResource($article),
        ], Response::HTTP_OK);
    }

    public function destroyArticle(EducationArticle $educationArticle): JsonResponse
    {
        Gate::authorize('delete', $educationArticle);

        $this->cms->deleteArticle($educationArticle);

        return response()->json([
            'success' => true,
            'message' => 'Artikel edukasi berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    public function indexDocuments(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', PublicDocument::class);

        $search = $request->filled('search') ? (string) $request->query('search') : null;

        $items = PublicDocument::query()
            ->with('creator:id,name')
            ->when($request->filled('kategori'), fn (Builder $q) => $q->where('kategori', (string) $request->query('kategori')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', (string) $request->query('status')))
            ->when($search !== null, fn (Builder $q) => PortalCmsService::applyEscapedLike($q, ['judul'], $search))
            ->latest('id')
            ->paginate(min(50, max(1, (int) $request->query('per_page', 15))));

        return response()->json([
            'success' => true,
            'message' => 'Daftar dokumen publik CMS berhasil diambil.',
            'data' => PublicDocumentResource::collection($items->items()),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function storeDocument(SavePublicDocumentRequest $request): JsonResponse
    {
        Gate::authorize('create', PublicDocument::class);

        /** @var User $admin */
        $admin = $request->user();
        $document = $this->cms->createDocument($admin, $request->validated(), $request->file('file_dokumen'));

        return response()->json([
            'success' => true,
            'message' => 'Dokumen publik berhasil diunggah.',
            'data' => new PublicDocumentResource($document),
        ], Response::HTTP_CREATED);
    }

    public function updateDocument(SavePublicDocumentRequest $request, PublicDocument $publicDocument): JsonResponse
    {
        Gate::authorize('update', $publicDocument);

        $document = $this->cms->updateDocument($publicDocument, $request->validated(), $request->file('file_dokumen'));

        return response()->json([
            'success' => true,
            'message' => 'Dokumen publik berhasil diperbarui.',
            'data' => new PublicDocumentResource($document),
        ], Response::HTTP_OK);
    }

    public function destroyDocument(PublicDocument $publicDocument): JsonResponse
    {
        Gate::authorize('delete', $publicDocument);

        $this->cms->deleteDocument($publicDocument);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen publik berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    public function portalSettings(): JsonResponse
    {
        Gate::authorize('viewAny', PortalSetting::class);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan tampilan portal berhasil diambil.',
            'data' => PortalSetting::resolved(),
        ], Response::HTTP_OK);
    }

    public function updatePortalSettings(UpdatePortalSettingsRequest $request): JsonResponse
    {
        Gate::authorize('update', PortalSetting::class);

        /** @var User $admin */
        $admin = $request->user();
        $resolved = $this->cms->updateSettings($admin, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan tampilan portal berhasil diperbarui.',
            'data' => $resolved,
        ], Response::HTTP_OK);
    }
}
