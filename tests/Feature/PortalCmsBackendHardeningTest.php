<?php

use App\Models\AdminDlhProfile;
use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\WasteProduct;
use App\Services\PortalCmsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

dataset('non_admin_cms_roles', [
    'warga' => fn (): User => User::factory()->warga()->create(),
    'petugas_lapangan' => fn (): User => User::factory()->petugasLapangan()->create(),
    'pengelola_tps3r' => fn (): User => User::factory()->pengelolaTps3r()->create(),
    'pemrakarsa' => fn (): User => User::factory()->pemrakarsa()->create(),
]);

test('all cms api and web mutations reject non admin roles and enforce policies', function (User $actor): void {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    $announcement = BroadcastAnnouncement::factory()->create(['created_by' => $admin->id]);
    $article = EducationArticle::factory()->create(['created_by' => $admin->id]);
    $document = PublicDocument::factory()->create(['created_by' => $admin->id]);

    expect(Gate::forUser($actor)->allows('viewAny', BroadcastAnnouncement::class))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('create', BroadcastAnnouncement::class))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('update', $announcement))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('delete', $announcement))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('create', EducationArticle::class))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('update', $article))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('delete', $article))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('create', PublicDocument::class))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('update', $document))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('delete', $document))->toBeFalse()
        ->and(Gate::forUser($actor)->allows('update', PortalSetting::class))->toBeFalse();

    $ability = 'role:'.$actor->role->value;
    Sanctum::actingAs($actor, [$ability]);

    $this->getJson('/api/v1/admin/cms/summary')->assertForbidden();
    $this->getJson('/api/v1/admin/cms/announcements')->assertForbidden();
    $this->postJson('/api/v1/admin/cms/announcements', [
        'title' => 'Ilegal',
        'content' => 'Ditolak.',
        'priority' => 'normal',
    ])->assertForbidden();
    $this->patchJson("/api/v1/admin/cms/announcements/{$announcement->id}", ['title' => 'Ubah'])->assertForbidden();
    $this->deleteJson("/api/v1/admin/cms/announcements/{$announcement->id}")->assertForbidden();

    $this->getJson('/api/v1/admin/cms/articles')->assertForbidden();
    $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Ilegal',
        'kategori' => 'organik',
        'isi' => 'Konten',
    ])->assertForbidden();
    $this->patchJson("/api/v1/admin/cms/articles/{$article->slug}", ['judul' => 'Ubah'])->assertForbidden();
    $this->deleteJson("/api/v1/admin/cms/articles/{$article->slug}")->assertForbidden();

    $this->getJson('/api/v1/admin/cms/documents')->assertForbidden();
    $this->postJson('/api/v1/admin/cms/documents', [
        'judul' => 'Ilegal',
        'kategori' => 'panduan',
        'file_dokumen' => UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
    ])->assertForbidden();
    $this->patchJson("/api/v1/admin/cms/documents/{$document->slug}", ['judul' => 'Ubah'])->assertForbidden();
    $this->deleteJson("/api/v1/admin/cms/documents/{$document->slug}")->assertForbidden();

    $this->getJson('/api/v1/admin/cms/portal-settings')->assertForbidden();
    $this->patchJson('/api/v1/admin/cms/portal-settings', ['hero_title' => 'Ilegal'])->assertForbidden();

    $this->actingAs($actor)->get(route('dashboard.admin.broadcasts'))->assertForbidden();
    $this->actingAs($actor)->post(route('dashboard.admin.broadcasts.store'), [
        'title' => 'Ilegal',
        'content' => 'Konten',
        'audience_role' => 'all',
        'priority' => 'normal',
    ])->assertForbidden();
    $this->actingAs($actor)->patch(route('dashboard.admin.broadcasts.update', $announcement->id), ['title' => 'Ubah'])->assertForbidden();
    $this->actingAs($actor)->patch(route('dashboard.admin.broadcasts.toggle', $announcement->id))->assertForbidden();
    $this->actingAs($actor)->delete(route('dashboard.admin.broadcasts.destroy', $announcement->id))->assertForbidden();

    $this->actingAs($actor)->get(route('dashboard.admin.articles'))->assertForbidden();
    $this->actingAs($actor)->post(route('dashboard.admin.articles.store'), [
        'judul' => 'Ilegal',
        'kategori' => 'organik',
        'isi' => 'Konten',
    ])->assertForbidden();
    $this->actingAs($actor)->patch(route('dashboard.admin.articles.update', $article->slug), ['judul' => 'Ubah'])->assertForbidden();
    $this->actingAs($actor)->delete(route('dashboard.admin.articles.destroy', $article->slug))->assertForbidden();

    $this->actingAs($actor)->get(route('dashboard.admin.documents'))->assertForbidden();
    $this->actingAs($actor)->post(route('dashboard.admin.documents.store'), [
        'judul' => 'Ilegal',
        'kategori' => 'panduan',
        'file_dokumen' => UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf'),
    ])->assertForbidden();
    $this->actingAs($actor)->patch(route('dashboard.admin.documents.update', $document->slug), ['judul' => 'Ubah'])->assertForbidden();
    $this->actingAs($actor)->delete(route('dashboard.admin.documents.destroy', $document->slug))->assertForbidden();

    $this->actingAs($actor)->get(route('dashboard.admin.portal-settings'))->assertForbidden();
    $this->actingAs($actor)->patch(route('dashboard.admin.portal-settings.update'), ['hero_title' => 'Ilegal'])->assertForbidden();
})->with('non_admin_cms_roles');

test('admin dlh passes all cms policies', function (): void {
    $admin = User::factory()->adminDlh()->create();
    $announcement = BroadcastAnnouncement::factory()->create(['created_by' => $admin->id]);
    $article = EducationArticle::factory()->create(['created_by' => $admin->id]);
    $document = PublicDocument::factory()->create(['created_by' => $admin->id]);

    expect(Gate::forUser($admin)->allows('viewAny', BroadcastAnnouncement::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $announcement))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $announcement))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', EducationArticle::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $article))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', PublicDocument::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $document))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', PortalSetting::class))->toBeTrue();
});

test('public endpoints only surface active content inside valid publication windows', function (): void {
    Storage::fake('public');
    Storage::fake('local');
    $admin = User::factory()->adminDlh()->create();

    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Tayang Sah',
        'status' => 'published',
        'is_active' => true,
        'show_on_portal' => true,
        'audience_role' => 'all',
        'published_at' => now()->subHour(),
        'expires_at' => now()->addDay(),
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Terjadwal Matang',
        'status' => 'scheduled',
        'is_active' => true,
        'show_on_portal' => true,
        'published_at' => now()->subMinute(),
        'expires_at' => now()->addDay(),
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Nonaktif',
        'status' => 'published',
        'is_active' => false,
        'show_on_portal' => true,
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Internal Portal Off',
        'status' => 'published',
        'is_active' => true,
        'show_on_portal' => false,
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Masa Depan',
        'status' => 'scheduled',
        'is_active' => true,
        'show_on_portal' => true,
        'published_at' => now()->addDay(),
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Terjadwal Tanpa Tanggal',
        'status' => 'scheduled',
        'is_active' => true,
        'show_on_portal' => true,
        'published_at' => null,
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Kedaluwarsa',
        'status' => 'published',
        'is_active' => true,
        'show_on_portal' => true,
        'published_at' => now()->subDays(5),
        'expires_at' => now()->subMinute(),
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Khusus Petugas',
        'status' => 'published',
        'is_active' => true,
        'show_on_portal' => true,
        'audience_role' => 'petugas_lapangan',
    ]);

    $liveArticle = EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Tayang Sah',
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);
    $futureArticle = EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Jadwal Depan',
        'status' => 'published',
        'published_at' => now()->addDays(2),
    ]);
    $archivedArticle = EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Arsip',
        'status' => 'archived',
        'published_at' => now()->subDay(),
    ]);

    Storage::disk('local')->put('documents/sah.pdf', '%PDF-1.4 sah');
    Storage::disk('local')->put('documents/depan.pdf', '%PDF-1.4 depan');
    Storage::disk('local')->put('documents/arsip.pdf', '%PDF-1.4 arsip');

    $liveDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Rilis Sah',
        'file_path' => 'documents/sah.pdf',
        'status' => 'published',
        'tanggal_rilis' => now()->subDay()->toDateString(),
    ]);
    $futureDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Rilis Masa Depan',
        'file_path' => 'documents/depan.pdf',
        'status' => 'published',
        'tanggal_rilis' => now()->addDays(3)->toDateString(),
    ]);
    $archivedDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Arsip',
        'file_path' => 'documents/arsip.pdf',
        'status' => 'archived',
        'tanggal_rilis' => now()->subDay()->toDateString(),
    ]);

    $this->getJson('/api/v1/public/announcements')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['title' => 'Pengumuman Tayang Sah'])
        ->assertJsonFragment(['title' => 'Pengumuman Terjadwal Matang'])
        ->assertJsonMissing(['title' => 'Pengumuman Nonaktif'])
        ->assertJsonMissing(['title' => 'Pengumuman Internal Portal Off'])
        ->assertJsonMissing(['title' => 'Pengumuman Masa Depan'])
        ->assertJsonMissing(['title' => 'Pengumuman Terjadwal Tanpa Tanggal'])
        ->assertJsonMissing(['title' => 'Pengumuman Kedaluwarsa'])
        ->assertJsonMissing(['title' => 'Pengumuman Khusus Petugas']);

    $this->getJson('/api/v1/public/articles')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.judul', 'Artikel Tayang Sah');

    $this->getJson("/api/v1/public/articles/{$liveArticle->slug}")->assertOk();
    $this->getJson("/api/v1/public/articles/{$futureArticle->slug}")->assertNotFound();
    $this->getJson("/api/v1/public/articles/{$archivedArticle->slug}")->assertNotFound();
    $this->getJson("/api/v1/public/articles/{$liveArticle->id}")->assertNotFound();

    $documentsResponse = $this->getJson('/api/v1/public/documents')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.judul', 'Dokumen Rilis Sah')
        ->assertJsonMissingPath('data.0.file_path')
        ->assertJsonMissingPath('data.0.file_url');

    expect($documentsResponse->json('data.0.download_url'))
        ->toEndWith("/api/v1/public/documents/{$liveDoc->slug}/download");

    $this->getJson("/api/v1/public/documents/{$liveDoc->slug}/download")->assertOk();
    $this->getJson("/api/v1/public/documents/{$futureDoc->slug}/download")->assertNotFound();
    $this->getJson("/api/v1/public/documents/{$archivedDoc->slug}/download")->assertNotFound();
    $this->getJson("/api/v1/public/documents/{$liveDoc->id}/download")->assertNotFound();

    $this->get(route('portal.documents.download', $liveDoc->slug))->assertOk();
    $this->get(route('portal.documents.download', $futureDoc->slug))->assertNotFound();
    $this->get(route('portal.documents.download', $archivedDoc->slug))->assertNotFound();
});

test('public portal endpoints honor portal setting section visibility toggles', function (): void {
    Storage::fake('public');
    Storage::fake('local');
    $admin = User::factory()->adminDlh()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Unggulan',
        'is_available' => true,
        'stok' => 5,
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Pengumuman Aktif',
        'status' => 'published',
        'is_active' => true,
        'show_on_portal' => true,
    ]);
    $article = EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Aktif',
        'status' => 'published',
    ]);
    Storage::disk('local')->put('documents/aktif.pdf', '%PDF-1.4 aktif');
    $document = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Dokumen Aktif',
        'file_path' => 'documents/aktif.pdf',
        'status' => 'published',
    ]);

    app(PortalCmsService::class)->updateSettings($admin, [
        'announcement_banner_enabled' => false,
        'show_stats_section' => false,
        'show_education_section' => false,
        'show_marketplace_section' => false,
    ]);

    $this->getJson('/api/v1/public/overview')
        ->assertOk()
        ->assertJsonPath('data.banner_announcement', null)
        ->assertJsonCount(0, 'data.announcements')
        ->assertJsonCount(0, 'data.latest_articles')
        ->assertJsonCount(0, 'data.featured_products')
        ->assertJsonPath('data.stats.active_facilities', 0)
        ->assertJsonPath('data.stats.active_products', 0);

    $this->getJson('/api/v1/public/announcements')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->getJson('/api/v1/public/articles')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->getJson("/api/v1/public/articles/{$article->slug}")->assertNotFound();

    $this->getJson('/api/v1/public/documents')
        ->assertOk()
        ->assertJsonCount(0, 'data');

    $this->getJson("/api/v1/public/documents/{$document->slug}/download")->assertNotFound();
    $this->get(route('portal.documents.download', $document->slug))->assertNotFound();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Pengumuman Aktif')
        ->assertDontSee('Artikel Aktif')
        ->assertDontSee('Kompos Unggulan');
});

test('cms validates announcement and article publication schedules and active flags accurately', function (): void {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->postJson('/api/v1/admin/cms/announcements', [
        'title' => 'Terjadwal Tanpa Waktu',
        'content' => 'Harus ditolak',
        'priority' => 'normal',
        'status' => 'scheduled',
    ])->assertUnprocessable()->assertJsonValidationErrors(['published_at']);

    $this->postJson('/api/v1/admin/cms/announcements', [
        'title' => 'Kedaluwarsa Lebih Awal',
        'content' => 'Harus ditolak',
        'priority' => 'normal',
        'status' => 'published',
        'published_at' => now()->addDays(5)->toISOString(),
        'expires_at' => now()->addDay()->toISOString(),
    ])->assertUnprocessable()->assertJsonValidationErrors(['expires_at']);

    $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Artikel Terjadwal Tanpa Tanggal',
        'kategori' => 'organik',
        'isi' => 'Konten',
        'status' => 'scheduled',
    ])->assertUnprocessable()->assertJsonValidationErrors(['published_at']);

    $created = $this->postJson('/api/v1/admin/cms/announcements', [
        'title' => 'Pengumuman Awal Nonaktif',
        'content' => 'Konten',
        'priority' => 'normal',
        'status' => 'published',
        'is_active' => false,
    ])->assertCreated();

    $announcementId = $created->json('data.id');
    expect($created->json('data.is_active'))->toBeFalse();

    $this->patchJson("/api/v1/admin/cms/announcements/{$announcementId}", [
        'title' => 'Judul Diperbarui Tanpa Mengaktifkan',
    ])->assertOk()
        ->assertJsonPath('data.is_active', false);
});

test('cms rejects invalid article cover images and document uploads without storing files', function (): void {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Artikel File Tidak Valid',
        'kategori' => 'organik',
        'isi' => 'Konten',
        'cover_image' => UploadedFile::fake()->create('script.txt', 20, 'text/plain'),
    ])->assertUnprocessable()->assertJsonValidationErrors(['cover_image']);

    $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Artikel Gambar Terlalu Besar',
        'kategori' => 'organik',
        'isi' => 'Konten',
        'cover_image' => UploadedFile::fake()->image('oversize.jpg')->size(6000),
    ])->assertUnprocessable()->assertJsonValidationErrors(['cover_image']);

    expect(Storage::disk('public')->allFiles('articles'))->toBe([]);

    $this->postJson('/api/v1/admin/cms/documents', [
        'judul' => 'Dokumen Ekstensi Ilegal',
        'kategori' => 'panduan',
        'file_dokumen' => UploadedFile::fake()->create('payload.txt', 50, 'text/plain'),
    ])->assertUnprocessable()->assertJsonValidationErrors(['file_dokumen']);

    $this->postJson('/api/v1/admin/cms/documents', [
        'judul' => 'Dokumen Melebihi Batas',
        'kategori' => 'panduan',
        'file_dokumen' => UploadedFile::fake()->create('huge.pdf', 11000, 'application/pdf'),
    ])->assertUnprocessable()->assertJsonValidationErrors(['file_dokumen']);

    expect(Storage::disk('local')->allFiles('documents'))->toBe([]);
});

test('replacing or deleting documents and failing db persistence manage storage safely', function (): void {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $docRes = $this->postJson('/api/v1/admin/cms/documents', [
        'judul' => 'SOP Awal',
        'kategori' => 'sop',
        'status' => 'published',
        'file_dokumen' => UploadedFile::fake()->create('sop-v1.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $slug = $docRes->json('data.slug');
    $firstPath = PublicDocument::where('slug', $slug)->value('file_path');
    Storage::disk('local')->assertExists($firstPath);
    Storage::disk('public')->assertMissing($firstPath);

    $this->patchJson("/api/v1/admin/cms/documents/{$slug}", [
        'judul' => 'SOP Revisi 2',
        'file_dokumen' => UploadedFile::fake()->create('sop-v2.pdf', 120, 'application/pdf'),
    ])->assertOk();

    $secondPath = PublicDocument::where('slug', $slug)->value('file_path');
    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('local')->assertMissing($firstPath);
    Storage::disk('local')->assertExists($secondPath);
    Storage::disk('public')->assertMissing($secondPath);

    $cms = app(PortalCmsService::class);
    $document = PublicDocument::where('slug', $slug)->firstOrFail();

    expect(fn () => $cms->createDocument(
        $admin,
        [
            'judul' => 'Dokumen Slug Duplikat',
            'slug' => $slug,
            'kategori' => 'sop',
        ],
        UploadedFile::fake()->create('dup-slug.pdf', 60, 'application/pdf'),
    ))->toThrow(QueryException::class);

    expect(Storage::disk('local')->allFiles('documents'))->toBe([$secondPath]);

    $failOnce = true;
    PublicDocument::updating(function () use (&$failOnce): void {
        if ($failOnce) {
            $failOnce = false;
            throw new RuntimeException('Simulated DB update failure');
        }
    });

    expect(fn () => $cms->updateDocument(
        $document,
        ['judul' => 'SOP Gagal Update'],
        UploadedFile::fake()->create('sop-failed.pdf', 90, 'application/pdf'),
    ))->toThrow(RuntimeException::class);

    Storage::disk('local')->assertExists($secondPath);
    expect(Storage::disk('local')->allFiles('documents'))->toBe([$secondPath]);

    $this->deleteJson("/api/v1/admin/cms/documents/{$slug}")->assertOk();
    Storage::disk('local')->assertMissing($secondPath);
});

test('public portal avoids lazy loading bounds per_page and escapes like wildcards', function (): void {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    $petugas = User::factory()->petugasLapangan()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'assigned_by' => $admin->id,
        'district' => 'Taman',
        'is_active' => true,
    ]);
    WasteProduct::factory()->count(3)->create([
        'seller_id' => $seller->id,
        'is_available' => true,
        'stok' => 10,
    ]);
    BroadcastAnnouncement::factory()->count(3)->create(['created_by' => $admin->id]);
    EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Target 100% Terpilah',
        'status' => 'published',
    ]);
    EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Panduan Umum Tanpa Simbol',
        'status' => 'published',
    ]);
    PublicDocument::factory()->count(3)->create(['created_by' => $admin->id]);

    $wasPreventing = Model::preventsLazyLoading();
    Model::preventLazyLoading(true);

    try {
        $this->getJson('/api/v1/public/overview')->assertOk();
        $this->getJson('/api/v1/public/announcements?per_page=999')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 50);
        $this->getJson('/api/v1/public/articles?per_page=0')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 1);
        $this->getJson('/api/v1/public/documents?per_page=200')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 50);
        $this->getJson('/api/v1/public/facilities?per_page=200')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 50);
        $this->getJson('/api/v1/public/routes')->assertOk();
    } finally {
        Model::preventLazyLoading($wasPreventing);
    }

    $this->getJson('/api/v1/public/articles?search=100%25')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.judul', 'Target 100% Terpilah');

    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $this->getJson('/api/v1/admin/cms/articles?search=100%25')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.judul', 'Target 100% Terpilah');
});
