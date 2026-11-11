<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\SaveAnnouncementRequest;
use App\Http\Requests\Cms\SaveEducationArticleRequest;
use App\Http\Requests\Cms\SavePublicDocumentRequest;
use App\Http\Requests\Cms\UpdatePortalSettingsRequest;
use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\User;
use App\Services\PortalCmsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function __construct(public PortalCmsService $cms) {}

    public function updateBroadcast(SaveAnnouncementRequest $request, BroadcastAnnouncement $broadcastAnnouncement): RedirectResponse
    {
        Gate::authorize('update', $broadcastAnnouncement);

        $this->cms->updateAnnouncement($broadcastAnnouncement, $request->validated());

        return back()->with('status', 'Pengumuman "'.$broadcastAnnouncement->title.'" berhasil diperbarui.');
    }

    public function toggleBroadcast(BroadcastAnnouncement $broadcastAnnouncement): RedirectResponse
    {
        Gate::authorize('update', $broadcastAnnouncement);

        $nextActive = ! $broadcastAnnouncement->is_active;
        $this->cms->updateAnnouncement($broadcastAnnouncement, [
            'is_active' => $nextActive,
            'status' => $nextActive ? 'published' : 'archived',
        ]);

        return back()->with('status', 'Status pengumuman "'.$broadcastAnnouncement->title.'" berhasil diperbarui.');
    }

    public function destroyBroadcast(BroadcastAnnouncement $broadcastAnnouncement): RedirectResponse
    {
        Gate::authorize('delete', $broadcastAnnouncement);

        $title = $broadcastAnnouncement->title;
        $broadcastAnnouncement->delete();

        return back()->with('status', 'Pengumuman "'.$title.'" berhasil dihapus.');
    }

    public function articles(Request $request): View
    {
        Gate::authorize('viewAny', EducationArticle::class);

        $kategori = $request->filled('kategori') ? trim((string) $request->query('kategori')) : null;
        $status = $request->filled('status') ? trim((string) $request->query('status')) : null;
        $search = $request->filled('search') ? trim((string) $request->query('search')) : null;
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $articles = EducationArticle::query()
            ->with('creator:id,name')
            ->when($kategori, fn (Builder $q) => $q->where('kategori', $kategori))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($search !== null, fn (Builder $q) => PortalCmsService::applyEscapedLike($q, ['judul'], $search))
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $viewName = ViewFacade::exists('dashboard.admin.articles')
            ? 'dashboard.admin.articles'
            : 'dashboard.admin.broadcasts';

        return view($viewName, [
            'articles' => $articles,
            'broadcasts' => BroadcastAnnouncement::with('creator:id,name')->latest()->paginate(10),
            'cmsSummary' => $this->cms->adminSummary(),
        ]);
    }

    public function storeArticle(SaveEducationArticleRequest $request): RedirectResponse
    {
        Gate::authorize('create', EducationArticle::class);

        /** @var User $admin */
        $admin = $request->user();
        $article = $this->cms->createArticle($admin, $request->validated(), $request->file('cover_image'));

        return back()->with('status', 'Artikel edukasi "'.$article->judul.'" berhasil disimpan.');
    }

    public function updateArticle(SaveEducationArticleRequest $request, EducationArticle $educationArticle): RedirectResponse
    {
        Gate::authorize('update', $educationArticle);

        $article = $this->cms->updateArticle($educationArticle, $request->validated(), $request->file('cover_image'));

        return back()->with('status', 'Artikel edukasi "'.$article->judul.'" berhasil diperbarui.');
    }

    public function destroyArticle(EducationArticle $educationArticle): RedirectResponse
    {
        Gate::authorize('delete', $educationArticle);

        $judul = $educationArticle->judul;
        $this->cms->deleteArticle($educationArticle);

        return back()->with('status', 'Artikel edukasi "'.$judul.'" berhasil dihapus.');
    }

    public function documents(Request $request): View
    {
        Gate::authorize('viewAny', PublicDocument::class);

        $kategori = $request->filled('kategori') ? trim((string) $request->query('kategori')) : null;
        $status = $request->filled('status') ? trim((string) $request->query('status')) : null;
        $search = $request->filled('search') ? trim((string) $request->query('search')) : null;
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $documents = PublicDocument::query()
            ->with('creator:id,name')
            ->when($kategori, fn (Builder $q) => $q->where('kategori', $kategori))
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($search !== null, fn (Builder $q) => PortalCmsService::applyEscapedLike($q, ['judul'], $search))
            ->latest('tanggal_rilis')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $viewName = ViewFacade::exists('dashboard.admin.documents')
            ? 'dashboard.admin.documents'
            : 'dashboard.admin.broadcasts';

        return view($viewName, [
            'documents' => $documents,
            'broadcasts' => BroadcastAnnouncement::with('creator:id,name')->latest()->paginate(10),
            'cmsSummary' => $this->cms->adminSummary(),
        ]);
    }

    public function storeDocument(SavePublicDocumentRequest $request): RedirectResponse
    {
        Gate::authorize('create', PublicDocument::class);

        /** @var User $admin */
        $admin = $request->user();
        $document = $this->cms->createDocument($admin, $request->validated(), $request->file('file_dokumen'));

        return back()->with('status', 'Dokumen publik "'.$document->judul.'" berhasil diunggah.');
    }

    public function updateDocument(SavePublicDocumentRequest $request, PublicDocument $publicDocument): RedirectResponse
    {
        Gate::authorize('update', $publicDocument);

        $document = $this->cms->updateDocument($publicDocument, $request->validated(), $request->file('file_dokumen'));

        return back()->with('status', 'Dokumen publik "'.$document->judul.'" berhasil diperbarui.');
    }

    public function destroyDocument(PublicDocument $publicDocument): RedirectResponse
    {
        Gate::authorize('delete', $publicDocument);

        $judul = $publicDocument->judul;
        $this->cms->deleteDocument($publicDocument);

        return back()->with('status', 'Dokumen publik "'.$judul.'" berhasil dihapus.');
    }

    public function portalSettings(): View
    {
        Gate::authorize('viewAny', PortalSetting::class);

        $viewName = ViewFacade::exists('dashboard.admin.portal-settings')
            ? 'dashboard.admin.portal-settings'
            : 'dashboard.admin.broadcasts';

        return view($viewName, [
            'portalSettings' => PortalSetting::resolved(),
            'broadcasts' => BroadcastAnnouncement::with('creator:id,name')->latest()->paginate(10),
            'cmsSummary' => $this->cms->adminSummary(),
        ]);
    }

    public function updatePortalSettings(UpdatePortalSettingsRequest $request): RedirectResponse
    {
        Gate::authorize('update', PortalSetting::class);

        /** @var User $admin */
        $admin = $request->user();
        $this->cms->updateSettings($admin, $request->validated());

        return back()->with('status', 'Pengaturan tampilan portal publik berhasil diperbarui.');
    }
}
