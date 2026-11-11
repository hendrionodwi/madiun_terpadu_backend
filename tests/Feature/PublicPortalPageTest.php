<?php

use App\Models\BroadcastAnnouncement;
use App\Models\EducationArticle;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PortalSetting;
use App\Models\PublicDocument;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\WasteProduct;

test('public portal pages are accessible without authentication', function () {
    $this->get(route('home'))->assertOk()->assertSee('Bersama Menjaga Madiun Bersih dan Berkelanjutan');
    $this->get(route('public.services'))->assertOk()->assertSeeText('Jadwal & peta layanan');
    $this->get(route('public.education'))->assertOk()->assertSee('Pilah lebih tepat');
    $this->get(route('public.marketplace'))->assertOk()->assertSee('Marketplace daur ulang');
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
