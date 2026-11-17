<?php

use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use App\Models\WasteProduct;

test('public portal pages are accessible without authentication', function () {
    $this->get(route('home'))->assertOk()->assertSee('Bersama Menjaga Madiun Bersih dan Berkelanjutan');
    $this->get(route('public.services'))->assertOk()->assertSeeText('Jadwal & peta layanan');
    $this->get(route('public.education'))->assertOk()->assertSee('Pilah lebih tepat');
    $this->get(route('public.marketplace'))->assertOk()->assertSee('Marketplace daur ulang');
});

test('home does not advertise a missing default application download as active', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Unduhan belum tersedia')
        ->assertSee('Menunggu tautan resmi')
        ->assertDontSee('/madiun-terpadu-release.apk');
});

test('public portal generates secure assets behind the local ngrok proxy', function () {
    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->withHeaders([
            'X-Forwarded-Host' => 'madiun-terpadu.ngrok-free.dev',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ])
        ->get('/');

    $response
        ->assertOk()
        ->assertSee('https://madiun-terpadu.ngrok-free.dev/build/assets/', false)
        ->assertDontSee('http://madiun-terpadu.ngrok-free.dev/build/assets/', false);
});

test('portal uses published cms content and configurable hero copy', function () {
    PortalSetting::query()->create([
        'key' => 'hero_title',
        'group' => 'hero',
        'label' => 'Judul utama',
        'type' => 'text',
        'value' => 'Portal Bersih Madiun',
    ]);
    EducationArticle::factory()->create(['judul' => 'Cara Memilah Sampah dari Rumah']);
    EducationArticle::factory()->draft()->create(['judul' => 'Artikel Belum Terbit']);
    PublicDocument::factory()->create(['judul' => 'Panduan Pemilahan Resmi']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Portal Bersih Madiun')
        ->assertSee('Cara Memilah Sampah dari Rumah')
        ->assertDontSee('Artikel Belum Terbit');

    $this->get(route('public.education'))
        ->assertOk()
        ->assertSee('Cara Memilah Sampah dari Rumah')
        ->assertSee('Panduan Pemilahan Resmi')
        ->assertDontSee('Artikel Belum Terbit');
});

test('home page presents active database content', function () {
    $admin = User::factory()->adminDlh()->create();
    $seller = User::factory()->pengelolaTps3r()->create(['name' => 'Pengelola Kartoharjo']);

    BroadcastAnnouncement::factory()->create([
        'created_by' => $admin->id,
        'title' => 'Jadwal layanan akhir pekan diperbarui',
        'audience_role' => 'all',
    ]);
    PengelolaTps3rProfile::factory()->create([
        'user_id' => $seller->id,
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Organik Kartoharjo',
        'harga' => 25000,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Jadwal layanan akhir pekan diperbarui')
        ->assertSee('TPS3R Kartoharjo Berseri')
        ->assertSee('Kompos Organik Kartoharjo')
        ->assertSee('Rp25.000');
});

test('service page filters facilities and shows active routes', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $kartoharjoSeller = User::factory()->pengelolaTps3r()->create();
    $tamanSeller = User::factory()->pengelolaTps3r()->create();

    PengelolaTps3rProfile::factory()->create([
        'user_id' => $kartoharjoSeller->id,
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
        'kecamatan' => 'Kartoharjo',
    ]);
    PengelolaTps3rProfile::factory()->create([
        'user_id' => $tamanSeller->id,
        'nama_tps3r' => 'TPS3R Taman Lestari',
        'kecamatan' => 'Taman',
    ]);
    RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'assigned_by' => $admin->id,
        'route_name' => 'Rute Kartoharjo Utara',
        'district' => 'Kartoharjo',
        'is_active' => true,
    ]);

    $this->get(route('public.services', ['district' => 'Kartoharjo']))
        ->assertOk()
        ->assertSee('TPS3R Kartoharjo Berseri')
        ->assertSee('Rute Kartoharjo Utara')
        ->assertDontSee('TPS3R Taman Lestari');
});

test('public facility profile presents official details products and approved delivery zones', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $facility = PengelolaTps3rProfile::factory()->create([
        'user_id' => $seller->id,
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
        'deskripsi' => 'Mengolah sampah organik warga menjadi kompos siap pakai.',
        'kontak_publik' => '0351-400001',
        'layanan_tersedia' => ['Penerimaan sampah pilah', 'Edukasi lingkungan'],
        'kategori_sampah_diterima' => ['Sampah organik', 'Plastik PET'],
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Kartoharjo',
        'is_available' => true,
        'stok' => 10,
    ]);
    Tps3rDeliveryZone::factory()->approved()->create([
        'pengelola_tps3r_profile_id' => $facility->id,
        'kelurahan' => 'Oro-Oro Ombo',
        'kecamatan' => 'Kartoharjo',
        'delivery_fee' => 5000,
    ]);

    $this->get(route('public.facilities.show', $facility))
        ->assertOk()
        ->assertSee('TPS3R Kartoharjo Berseri')
        ->assertSee('Mengolah sampah organik warga menjadi kompos siap pakai.')
        ->assertSee('Penerimaan sampah pilah')
        ->assertSee('Sampah organik')
        ->assertSee('Kompos Kartoharjo')
        ->assertSee('Oro-Oro Ombo');

    $this->get('/fasilitas/'.$facility->id)->assertNotFound();
});

test('education page uses active waste category master data', function () {
    MasterData::factory()->create([
        'group' => 'waste_category',
        'code' => 'minyak_jelantah',
        'name' => 'Minyak Jelantah',
        'description' => 'Simpan dalam botol tertutup sebelum diserahkan.',
        'is_active' => true,
    ]);

    $this->get(route('public.education'))
        ->assertOk()
        ->assertSee('Minyak Jelantah')
        ->assertSee('Simpan dalam botol tertutup sebelum diserahkan.');
});

test('marketplace only presents available products and supports category filter', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);

    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Siap Pakai',
        'kategori' => 'pupuk_kompos',
        'is_available' => true,
        'stok' => 10,
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Tas Daur Ulang',
        'kategori' => 'kerajinan_plastik',
        'is_available' => true,
        'stok' => 8,
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Produk Tidak Aktif',
        'kategori' => 'pupuk_kompos',
        'is_available' => false,
        'stok' => 10,
    ]);

    $this->get(route('public.marketplace', ['category' => 'pupuk_kompos']))
        ->assertOk()
        ->assertSee('Kompos Siap Pakai')
        ->assertDontSee('Tas Daur Ulang')
        ->assertDontSee('Produk Tidak Aktif');
});

test('marketplace supports price range sorting and store discovery', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $store = PengelolaTps3rProfile::factory()->create([
        'user_id' => $seller->id,
        'nama_tps3r' => 'TPS3R Taman Lestari',
    ]);

    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Ekonomis',
        'kategori' => 'pupuk_kompos',
        'harga' => 15000,
        'is_available' => true,
        'stok' => 10,
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Paket Taman',
        'kategori' => 'pupuk_kompos',
        'harga' => 45000,
        'is_available' => true,
        'stok' => 5,
    ]);

    $this->get(route('public.marketplace', ['price_range' => 'under_25k', 'sort' => 'termurah']))
        ->assertOk()
        ->assertSee('TPS3R Taman Lestari')
        ->assertSee('Kompos Ekonomis')
        ->assertDontSee('Kompos Paket Taman');

    $this->get(route('public.marketplace', ['store' => $store->public_id]))
        ->assertOk()
        ->assertSee('Kompos Ekonomis')
        ->assertSee('Kompos Paket Taman');
});

test('public home renders education article cover image and fixed height content slider', function () {
    $articleWithCover = EducationArticle::factory()->create([
        'judul' => 'Panduan Daur Ulang Mandiri',
        'cover_image' => 'articles/sample-cover.jpg',
        'status' => 'published',
    ]);

    expect($articleWithCover->cover_url)->toContain('articles/sample-cover.jpg')
        ->and($articleWithCover->thumbnail_url)->toEqual($articleWithCover->cover_url);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Panduan Daur Ulang Mandiri')
        ->assertSee('articles/sample-cover.jpg')
        ->assertSee('data-test="home-content-slider-viewport"', false)
        ->assertSee('relative h-[190px] sm:h-[124px]', false)
        ->assertSee('absolute inset-0 flex h-full w-full', false)
        ->assertSee('line-clamp-2 min-h-[2.5rem]', false);
});
