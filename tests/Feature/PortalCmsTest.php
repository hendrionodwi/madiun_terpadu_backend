<?php

use App\Models\AdminDlhProfile;
use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('public portal apis expose published cms content and reject draft or numeric lookups with 404', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    $tps3r = User::factory()->pengelolaTps3r()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    $facility = PengelolaTps3rProfile::factory()->create([
        'user_id' => $tps3r->id,
        'nama_tps3r' => 'TPS3R Manguharjo Bersih',
        'kecamatan' => 'Manguharjo',
    ]);
    RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'assigned_by' => $admin->id,
        'district' => 'Manguharjo',
        'is_active' => true,
    ]);
    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Operasional TPS3R Aktif Normal',
        'show_on_portal' => true,
        'status' => 'published',
    ]);
    $publishedArticle = EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Panduan Pemilahan Rumah Tangga',
        'status' => 'published',
    ]);
    $draftArticle = EducationArticle::factory()->draft()->create([
        'created_by' => $admin->id,
        'judul' => 'Artikel Konsep Internal',
    ]);

    Storage::disk('local')->put('public_documents/regulasi-madiun.pdf', '%PDF-1.4 dummy');
    $publishedDoc = PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Perwali Pengelolaan Sampah',
        'file_path' => 'public_documents/regulasi-madiun.pdf',
        'status' => 'published',
        'download_count' => 0,
    ]);

    $this->getJson('/api/v1/public/overview')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                'settings',
                'stats',
                'banner_announcement',
                'featured_facilities',
                'featured_products',
                'latest_articles',
            ],
        ]);

    $this->getJson('/api/v1/public/announcements')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Operasional TPS3R Aktif Normal']);

    $this->getJson("/api/v1/public/articles/{$publishedArticle->slug}")
        ->assertOk()
        ->assertJsonPath('data.public_id', $publishedArticle->public_id);
    $this->getJson("/api/v1/public/articles/{$draftArticle->slug}")->assertNotFound();
    $this->getJson("/api/v1/public/articles/{$publishedArticle->id}")->assertNotFound();

    $this->getJson("/api/v1/public/documents/{$publishedDoc->slug}/download")
        ->assertOk();
    expect($publishedDoc->fresh()->download_count)->toBe(1);
    $this->getJson("/api/v1/public/documents/{$publishedDoc->id}/download")->assertNotFound();

    $this->getJson("/api/v1/public/facilities/{$facility->public_id}")
        ->assertOk()
        ->assertJsonPath('data.nama_tps3r', 'TPS3R Manguharjo Bersih');
    $this->getJson("/api/v1/public/facilities/{$facility->id}")->assertNotFound();

    $this->getJson('/api/v1/public/routes?kecamatan=Manguharjo')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('public web article detail and document download honor publication status', function () {
    Storage::fake('public');
    Storage::fake('local');

    $publishedArticle = EducationArticle::factory()->create([
        'judul' => 'Panduan Kompos Pekarangan',
        'status' => 'published',
    ]);
    $draftArticle = EducationArticle::factory()->draft()->create([
        'judul' => 'Artikel Belum Siap',
    ]);

    Storage::disk('local')->put('public_documents/sop-pilah.pdf', 'SOP content');
    $document = PublicDocument::factory()->create([
        'judul' => 'SOP Pemilahan TPS3R',
        'file_path' => 'public_documents/sop-pilah.pdf',
        'status' => 'published',
        'download_count' => 2,
    ]);

    $this->get(route('portal.articles.show', $publishedArticle->slug))->assertOk();
    $this->get(route('portal.articles.show', $draftArticle->slug))->assertNotFound();

    $this->get(route('portal.documents.download', $document->slug))->assertOk();
    expect($document->fresh()->download_count)->toBe(3);
});

test('admin dlh can manage announcements articles documents and portal settings via cms api', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    // Announcements CRUD
    $annCreate = $this->postJson('/api/v1/admin/cms/announcements', [
        'title' => 'Pengumuman Bank Sampah Baru',
        'summary' => 'Pembukaan layanan baru di Kelurahan Kanigoro.',
        'content' => 'Detail layanan pembukaan jadwal penimbangan setiap Selasa dan Jumat.',
        'priority' => 'high',
        'status' => 'published',
        'kategori' => 'jadwal',
        'show_on_portal' => true,
    ])->assertCreated();

    $annId = $annCreate->json('data.id');

    $this->patchJson("/api/v1/admin/cms/announcements/{$annId}", [
        'title' => 'Pengumuman Bank Sampah Diperbarui',
        'priority' => 'urgent',
    ])->assertOk()
        ->assertJsonPath('data.title', 'Pengumuman Bank Sampah Diperbarui');

    $this->deleteJson("/api/v1/admin/cms/announcements/{$annId}")->assertOk();
    expect(BroadcastAnnouncement::where('id', $annId)->exists())->toBeFalse();

    // Articles CRUD + cover cleanup
    $articleRes = $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Edukasi Mengurangi Sampah Plastik',
        'kategori' => 'plastik_kertas',
        'isi' => 'Gunakan tas belanja ulang dan wadah bersih saat berbelanja.',
        'status' => 'published',
        'cover_image' => UploadedFile::fake()->image('cover-1.jpg', 640, 360),
    ])->assertCreated();

    $articleSlug = $articleRes->json('data.slug');
    $firstCover = EducationArticle::where('slug', $articleSlug)->value('cover_image');
    Storage::disk('public')->assertExists($firstCover);

    $this->patchJson("/api/v1/admin/cms/articles/{$articleSlug}", [
        'judul' => 'Edukasi Mengurangi Plastik Revisi',
        'cover_image' => UploadedFile::fake()->image('cover-2.png', 640, 360),
    ])->assertOk();

    $secondCover = EducationArticle::where('slug', $articleSlug)->value('cover_image');
    Storage::disk('public')->assertMissing($firstCover);
    Storage::disk('public')->assertExists($secondCover);

    $this->deleteJson("/api/v1/admin/cms/articles/{$articleSlug}")->assertOk();
    Storage::disk('public')->assertMissing($secondCover);

    // Documents CRUD + file cleanup
    $docRes = $this->postJson('/api/v1/admin/cms/documents', [
        'judul' => 'Laporan Kinerja Kebersihan',
        'kategori' => 'laporan',
        'status' => 'published',
        'file_dokumen' => UploadedFile::fake()->create('kinerja.pdf', 150, 'application/pdf'),
    ])->assertCreated();

    $docSlug = $docRes->json('data.slug');
    $firstDocPath = PublicDocument::where('slug', $docSlug)->value('file_path');
    Storage::disk('local')->assertExists($firstDocPath);
    Storage::disk('public')->assertMissing($firstDocPath);

    $this->deleteJson("/api/v1/admin/cms/documents/{$docSlug}")->assertOk();
    Storage::disk('local')->assertMissing($firstDocPath);

    // Portal settings update
    $this->patchJson('/api/v1/admin/cms/portal-settings', [
        'hero_title' => 'Madiun Bersih & Hijau 2026',
        'show_stats_section' => false,
    ])->assertOk()
        ->assertJsonPath('data.hero_title', 'Madiun Bersih & Hijau 2026')
        ->assertJsonPath('data.show_stats_section', '0');

    expect(PortalSetting::where('key', 'hero_title')->value('value'))->toBe('Madiun Bersih & Hijau 2026');
});

test('non admin user cannot access cms api or web routes', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $this->getJson('/api/v1/admin/cms/summary')->assertForbidden();
    $this->postJson('/api/v1/admin/cms/articles', [
        'judul' => 'Akses Tidak Sah',
        'kategori' => 'organik',
        'isi' => 'Ditolak.',
    ])->assertForbidden();

    $this->actingAs($warga)
        ->get(route('dashboard.admin.portal-settings'))
        ->assertForbidden();
});

test('admin dlh can execute web cms management flows', function () {
    Storage::fake('public');
    Storage::fake('local');

    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    $announcement = BroadcastAnnouncement::factory()->create(['created_by' => $admin->id]);

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.broadcasts.toggle', $announcement->id))
        ->assertRedirect();

    expect($announcement->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->post(route('dashboard.admin.articles.store'), [
            'judul' => 'Panduan Bank Sampah Induk',
            'kategori' => 'ekonomi_sirkular',
            'isi' => 'Penjelasan alur penyetoran dan pencairan poin bank sampah.',
            'status' => 'published',
        ])->assertRedirect();

    expect(EducationArticle::where('judul', 'Panduan Bank Sampah Induk')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->post(route('dashboard.admin.documents.store'), [
            'judul' => 'SOP Penjemputan Terjadwal',
            'kategori' => 'sop',
            'status' => 'published',
            'file_dokumen' => UploadedFile::fake()->create('sop-pickup.pdf', 80, 'application/pdf'),
        ])->assertRedirect();

    expect(PublicDocument::where('judul', 'SOP Penjemputan Terjadwal')->exists())->toBeTrue();

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.portal-settings.update'), [
            'hero_badge_text' => 'Pelayanan 3 Kecamatan Aktif',
        ])->assertRedirect();

    expect(PortalSetting::where('key', 'hero_badge_text')->value('value'))->toBe('Pelayanan 3 Kecamatan Aktif');
});

test('admin dlh sees dedicated content management workspaces', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Penyesuaian Jadwal Layanan Kota',
    ]);
    EducationArticle::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'Panduan Pilah Sampah Warga',
    ]);
    PublicDocument::factory()->create([
        'created_by' => $admin->id,
        'judul' => 'SOP Layanan Persampahan',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard.admin.broadcasts'))
        ->assertOk()
        ->assertSee('Penyesuaian Jadwal Layanan Kota')
        ->assertSee('Artikel &amp; Edukasi', false)
        ->assertSee('Tampilan Portal');

    $this->actingAs($admin)
        ->get(route('dashboard.admin.articles'))
        ->assertOk()
        ->assertSee('Pustaka artikel')
        ->assertSee('Panduan Pilah Sampah Warga');

    $this->actingAs($admin)
        ->get(route('dashboard.admin.documents'))
        ->assertOk()
        ->assertSee('Pustaka dokumen')
        ->assertSee('SOP Layanan Persampahan');

    $this->actingAs($admin)
        ->get(route('dashboard.admin.portal-settings'))
        ->assertOk()
        ->assertSee('Bagian utama')
        ->assertSee('Kontak & transparansi', false);
});
