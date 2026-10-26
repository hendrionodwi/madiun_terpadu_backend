<?php

use App\Models\AdminDlhProfile;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin dlh can view admin dashboard with cross-district recap and complaint stats (DSH-F-004)', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    Complaint::factory()->count(2)->create(['kecamatan' => 'Kartoharjo']);
    Complaint::factory()->create(['kecamatan' => 'Taman']);

    $response = $this->actingAs($admin)->get(route('dashboard.admin'));

    $response->assertOk()
        ->assertSee('Dashboard Admin DLH')
        ->assertSee('Kecamatan Kartoharjo')
        ->assertSee('Kecamatan Manguharjo')
        ->assertSee('Kecamatan Taman')
        ->assertSee('Status Pengaduan Masyarakat');
});

test('non-admin user is forbidden from admin dashboard', function () {
    $warga = User::factory()->warga()->create();

    $response = $this->actingAs($warga)->get(route('dashboard.admin'));

    $response->assertForbidden();
});

test('pengelola tps3r can view tps3r dashboard with waste volume and marketplace metrics (DSH-F-003)', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Madiun Indah Kartoharjo',
    ]);

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $tps3rProfile->id,
        'berat_kg' => 250,
        'tanggal_setor' => now()->toDateString(),
    ]);

    WasteProduct::factory()->create([
        'seller_id' => $pengelola->id,
        'nama_produk' => 'Pupuk Kompos Madiun',
    ]);

    $response = $this->actingAs($pengelola)->get(route('dashboard.tps3r'));

    $response->assertOk()
        ->assertSee('TPS3R Madiun Indah Kartoharjo')
        ->assertSee('Marketplace Daur Ulang')
        ->assertSee('Pupuk Kompos Madiun');
});

test('pengelola tps3r can record a waste deposit from the web dashboard', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    $response = $this->actingAs($pengelola)->post(route('dashboard.tps3r.store-deposit'), [
        'sumber_sampah' => 'warga_mandiri',
        'jenis_sampah' => 'anorganik_daur_ulang',
        'berat_kg' => 75.5,
        'tanggal_setor' => '2026-10-20',
        'catatan' => 'Setoran botol plastik warga.',
    ]);

    $response->assertRedirect(route('dashboard.tps3r.deposits'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'sumber_sampah' => 'warga_mandiri',
        'berat_kg' => 75.5,
    ]);
});

test('pengelola tps3r can verify incoming waste deposit from field officer on web dashboard', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);
    $petugas = User::factory()->petugasLapangan()->create();

    $deposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'petugas_id' => $petugas->id,
        'sumber_sampah' => 'petugas_kebersihan',
        'status' => 'menunggu_konfirmasi',
        'berat_kg' => 200.0,
    ]);

    $response = $this->actingAs($pengelola)->patch(route('dashboard.tps3r.verify-deposit', $deposit), [
        'status' => 'diterima',
        'berat_konfirmasi_kg' => 195.5,
        'catatan_verifikasi' => 'Muatan sampah basah telah ditimbang riil di pos gerbang.',
    ]);

    $response->assertSessionHas('status');

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'id' => $deposit->id,
        'status' => 'diterima',
        'berat_konfirmasi_kg' => 195.5,
        'catatan_verifikasi' => 'Muatan sampah basah telah ditimbang riil di pos gerbang.',
    ]);
});

test('pengelola tps3r can filter their deposit history by period', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);
    $ownDeposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-OWN-PERIOD',
        'tanggal_setor' => '2026-10-15',
    ]);
    $otherPengelola = User::factory()->pengelolaTps3r()->create();
    $otherProfile = PengelolaTps3rProfile::factory()->create(['user_id' => $otherPengelola->id]);
    $otherDeposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $otherPengelola->id,
        'tps3r_profile_id' => $otherProfile->id,
        'nomor_resi' => 'DEP-OTHER-PERIOD',
        'tanggal_setor' => '2026-10-15',
    ]);

    $this->actingAs($pengelola)
        ->get(route('dashboard.tps3r.deposits', ['start_date' => '2026-10-01', 'end_date' => '2026-10-31']))
        ->assertOk()
        ->assertSee($ownDeposit->nomor_resi)
        ->assertDontSee($otherDeposit->nomor_resi);
});

test('pengelola tps3r can add a recycled product from the web dashboard', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();

    $response = $this->actingAs($pengelola)->post(route('dashboard.tps3r.store-product'), [
        'nama_produk' => 'Ecobrick Madiun',
        'deskripsi' => 'Produk bangunan hasil olahan plastik.',
        'kategori' => 'ecobrick',
        'harga' => 15000,
        'stok' => 24,
        'is_available' => true,
    ]);

    $response->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('waste_products', [
        'seller_id' => $pengelola->id,
        'nama_produk' => 'Ecobrick Madiun',
        'stok' => 24,
    ]);
});

test('pengelola tps3r can access separated operational pages', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    $this->actingAs($pengelola)->get(route('dashboard.tps3r.deposits'))
        ->assertOk()
        ->assertSee('Setoran Sampah');
    $this->actingAs($pengelola)->get(route('dashboard.tps3r.volume-recap'))
        ->assertOk()
        ->assertSee('Rekap Volume Sampah');
    $this->actingAs($pengelola)->get(route('dashboard.tps3r.products'))
        ->assertOk()
        ->assertSee('Katalog Produk');
    $this->actingAs($pengelola)->get(route('dashboard.tps3r.orders'))
        ->assertOk()
        ->assertSee('Pesanan Marketplace');
});

test('pengelola tps3r can filter volume recap and see category breakdown', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'kapasitas_harian_kg' => 2000,
    ]);

    $deposit1 = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-TEST-001',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 250.0,
        'tanggal_setor' => '2026-10-10',
    ]);

    $deposit2 = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-TEST-002',
        'sumber_sampah' => 'warga_mandiri',
        'jenis_sampah' => 'anorganik_daur_ulang',
        'berat_kg' => 150.0,
        'tanggal_setor' => '2026-10-10',
    ]);

    $response = $this->actingAs($pengelola)->get(route('dashboard.tps3r.volume-recap', [
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-31',
    ]));

    $response->assertOk()
        ->assertSee('DEP-TEST-001')
        ->assertSee('DEP-TEST-002');
});

test('pengelola tps3r can update and delete their own marketplace product', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    $product = WasteProduct::factory()->create([
        'seller_id' => $pengelola->id,
        'nama_produk' => 'Kompos Asli Madiun',
        'harga' => 10000,
        'stok' => 50,
        'kategori' => 'pupuk_kompos',
    ]);

    $updateResponse = $this->actingAs($pengelola)->patch(route('dashboard.tps3r.products.update', $product), [
        'nama_produk' => 'Kompos Super Organik 5kg',
        'deskripsi' => 'Kemasan baru lebih hemat.',
        'kategori' => 'pupuk_kompos',
        'harga' => 12500,
        'stok' => 45,
        'is_available' => true,
    ]);

    $updateResponse->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('waste_products', [
        'id' => $product->id,
        'nama_produk' => 'Kompos Super Organik 5kg',
        'harga' => 12500,
        'stok' => 45,
    ]);

    $deleteResponse = $this->actingAs($pengelola)->delete(route('dashboard.tps3r.products.destroy', $product));

    $deleteResponse->assertRedirect(route('dashboard.tps3r.products'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('waste_products', [
        'id' => $product->id,
    ]);
});

test('pengelola tps3r cannot update or delete other seller products', function () {
    $pengelola1 = User::factory()->pengelolaTps3r()->create();
    $pengelola2 = User::factory()->pengelolaTps3r()->create();

    $product = WasteProduct::factory()->create([
        'seller_id' => $pengelola2->id,
        'nama_produk' => 'Produk Penjual Lain',
    ]);

    $this->actingAs($pengelola1)->patch(route('dashboard.tps3r.products.update', $product), [
        'nama_produk' => 'Coba bajak',
        'kategori' => 'pupuk_kompos',
        'harga' => 5000,
        'stok' => 10,
    ])->assertForbidden();

    $this->actingAs($pengelola1)->delete(route('dashboard.tps3r.products.destroy', $product))
        ->assertForbidden();
});

test('tps3r dashboard only includes marketplace data belonging to the logged in seller', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $buyer = User::factory()->warga()->create();
    $ownProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Milik TPS3R Login',
        'stok' => 8,
    ]);
    $otherProduct = WasteProduct::factory()->create([
        'seller_id' => $otherSeller->id,
        'nama_produk' => 'Produk TPS3R Lain',
        'stok' => 99,
    ]);
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 35000,
        'status' => 'dibayar',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $ownProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $otherProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 15000,
        'subtotal' => 15000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
    ]);
    $pendingOrder = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 10000,
        'status' => 'pending',
    ]);
    OrderItem::factory()->create([
        'order_id' => $pendingOrder->id,
        'product_id' => $ownProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 10000,
        'subtotal' => 10000,
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r'));

    $response->assertSee('Kompos Milik TPS3R Login')
        ->assertSee('Rp 20.000')
        ->assertDontSee('Produk TPS3R Lain')
        ->assertDontSee('Rp 35.000');
    expect($response->viewData('marketplace')['total_pendapatan'])->toBe(20000.0);
});

test('tps3r order list only shows line items and subtotal belonging to the logged in seller', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $ownProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Pupuk Kompos Seller Utama',
    ]);
    $otherProduct = WasteProduct::factory()->create([
        'seller_id' => $otherSeller->id,
        'nama_produk' => 'Kerajinan Seller Lain',
    ]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-MULTI-SELLER',
        'total_harga' => 35000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $ownProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $otherProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 15000,
        'subtotal' => 15000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders'));

    $response->assertSee('ORD-MULTI-SELLER')
        ->assertSee('Pupuk Kompos Seller Utama')
        ->assertSee('Rp 20.000')
        ->assertSee(route('dashboard.tps3r.orders.show', $order))
        ->assertDontSee('Kerajinan Seller Lain')
        ->assertDontSee('Rp 35.000');
});

test('tps3r order detail only renders the logged in seller line items and escapes product names', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $buyer = User::factory()->warga()->create([
        'name' => 'Hendri Pembeli',
        'phone_number' => '081234567890',
    ]);
    $dangerousProductName = 'Kompos <script>alert("xss")</script>';
    $ownProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => $dangerousProductName,
    ]);
    $otherProduct = WasteProduct::factory()->create([
        'seller_id' => $otherSeller->id,
        'nama_produk' => 'Produk Rahasia Seller Lain',
    ]);
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'alamat_pengiriman' => 'Jalan Pahlawan, Kota Madiun',
        'total_harga' => 35000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $ownProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $otherProduct->id,
        'jumlah' => 1,
        'harga_satuan' => 15000,
        'subtotal' => 15000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders.show', $order));

    $response->assertSee($dangerousProductName)
        ->assertDontSee($dangerousProductName, false)
        ->assertSee('Hendri Pembeli')
        ->assertSee('081234567890')
        ->assertSee('Jalan Pahlawan, Kota Madiun')
        ->assertSee('Rp 20.000')
        ->assertDontSee('Produk Rahasia Seller Lain')
        ->assertDontSee('Rp 35.000');
});

test('tps3r seller receives not found for an order that contains no products they own', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherProduct = WasteProduct::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $otherProduct->id,
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders.show', $order));

    $response->assertNotFound();
});

test('warga is forbidden from viewing tps3r order details', function () {
    $warga = User::factory()->warga()->create();
    $order = Order::factory()->create();

    $response = $this->actingAs($warga)->get(route('dashboard.tps3r.orders.show', $order));

    $response->assertForbidden();
});

test('pemrakarsa can view upload form for semester environmental report (PPL-F-008)', function () {
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create([
        'user_id' => $pemrakarsa->id,
        'nama_instansi' => 'PT Madiun Mega Mall',
    ]);

    $response = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa'));

    $response->assertOk()
        ->assertSee('PT Madiun Mega Mall')
        ->assertSee('Unggah Laporan Semester Baru')
        ->assertSee('Periode Semester')
        ->assertSee('Jenis Dokumen Lingkungan', false);
});

test('pemrakarsa can upload semester environmental report (PPL-F-008)', function () {
    Storage::fake('public');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create(['user_id' => $pemrakarsa->id]);

    $file = UploadedFile::fake()->create('laporan_semester_1.pdf', 1024, 'application/pdf');

    $payload = [
        'judul_laporan' => 'Laporan Pemantauan Lingkungan Mall S1 2026',
        'periode_semester' => 'Semester 1 (Jan - Jun)',
        'tahun' => 2026,
        'jenis_dokumen' => 'AMDAL',
        'timbulan_sampah_kg' => 3500.5,
        'metode_pengelolaan' => 'Kerjasama pemilahan dan pengangkutan berkala dengan DLH Kota Madiun',
        'file_laporan' => $file,
    ];

    $response = $this->actingAs($pemrakarsa)->post(route('dashboard.pemrakarsa.store-laporan'), $payload);

    $response->assertRedirect(route('dashboard.pemrakarsa'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('environmental_reports', [
        'pemrakarsa_id' => $pemrakarsa->id,
        'judul_laporan' => 'Laporan Pemantauan Lingkungan Mall S1 2026',
        'jenis_dokumen' => 'AMDAL',
        'status' => 'menunggu_verifikasi',
    ]);
});

test('admin dlh can verify pemrakarsa environmental report', function () {
    $admin = User::factory()->adminDlh()->create();
    $report = EnvironmentalReport::factory()->create(['status' => 'menunggu_verifikasi']);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.verify-laporan', $report->id), [
        'status' => 'disetujui',
        'catatan_verifikasi' => 'Dokumen lengkap dan sesuai baku mutu lingkungan DLH Kota Madiun.',
    ]);

    $response->assertRedirect();

    expect($report->fresh()->status)->toBe('disetujui');
    expect($report->fresh()->catatan_verifikasi)->toContain('baku mutu');
});

test('general dashboard route dispatches role correctly', function () {
    $admin = User::factory()->adminDlh()->create();
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee('Dashboard Admin DLH');

    $tps3r = User::factory()->pengelolaTps3r()->create();
    $this->actingAs($tps3r)->get(route('dashboard'))->assertOk()->assertSee('Dashboard Fasilitas TPS3R');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $this->actingAs($pemrakarsa)->get(route('dashboard'))->assertOk()->assertSee('Portal Pelaporan Lingkungan');
});

test('pemrakarsa can view reports list and filter reports by status and jenis dokumen', function () {
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create(['user_id' => $pemrakarsa->id]);

    $report1 = EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsa->id,
        'judul_laporan' => 'Laporan AMDAL Semester 1 2026',
        'jenis_dokumen' => 'AMDAL',
        'status' => 'disetujui',
    ]);

    $report2 = EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsa->id,
        'judul_laporan' => 'Laporan UKL-UPL Semester 2 2026',
        'jenis_dokumen' => 'UKL-UPL',
        'status' => 'menunggu_verifikasi',
    ]);

    // View all reports
    $response = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa.reports'));
    $response->assertOk()
        ->assertSee('Riwayat & Status Verifikasi Pelaporan', false)
        ->assertSee('Laporan AMDAL Semester 1 2026')
        ->assertSee('Laporan UKL-UPL Semester 2 2026');

    // Filter by status
    $responseStatus = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa.reports', ['status' => 'disetujui']));
    $responseStatus->assertOk()
        ->assertSee('Laporan AMDAL Semester 1 2026')
        ->assertDontSee('Laporan UKL-UPL Semester 2 2026');

    // Filter by jenis dokumen
    $responseJenis = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa.reports', ['jenis_dokumen' => 'UKL-UPL']));
    $responseJenis->assertOk()
        ->assertSee('Laporan UKL-UPL Semester 2 2026')
        ->assertDontSee('Laporan AMDAL Semester 1 2026');
});

test('pemrakarsa can view standalone create report form', function () {
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create([
        'user_id' => $pemrakarsa->id,
        'nama_instansi' => 'PT Ramayana Madiun',
    ]);

    $response = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa.reports.create'));

    $response->assertOk()
        ->assertSee('Formulir PPL-F-008')
        ->assertSee('Unggah Laporan Pelaksanaan Dokumen Lingkungan')
        ->assertSee('PT Ramayana Madiun');
});

test('pemrakarsa can view and update company profile and submit legal document', function () {
    Storage::fake('public');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $profile = PemrakarsaProfile::factory()->create([
        'user_id' => $pemrakarsa->id,
        'nama_instansi' => 'PT Awal Sejahtera',
    ]);

    $responseView = $this->actingAs($pemrakarsa)->get(route('dashboard.pemrakarsa.profile.edit'));
    $responseView->assertOk()
        ->assertSee('Profil Perusahaan & Legalitas', false)
        ->assertSee('PT Awal Sejahtera');

    $file = UploadedFile::fake()->create('nib_legalitas.pdf', 500, 'application/pdf');

    $responseUpdate = $this->actingAs($pemrakarsa)->patch(route('dashboard.pemrakarsa.profile.update'), [
        'nama_instansi' => 'PT Madiun Central Plaza',
        'jenis_instansi' => 'Pusat Perbelanjaan',
        'nib' => '9120001234567',
        'penanggung_jawab' => 'Budi Sudarsono',
        'alamat_instansi' => 'Jl. Pahlawan No. 12, Kota Madiun',
        'latitude' => -7.6288,
        'longitude' => 111.5240,
        'legal_document' => $file,
    ]);

    $responseUpdate->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('pemrakarsa_profiles', [
        'user_id' => $pemrakarsa->id,
        'nama_instansi' => 'PT Madiun Central Plaza',
        'nib' => '9120001234567',
        'penanggung_jawab' => 'Budi Sudarsono',
    ]);

    $this->assertDatabaseHas('entity_verifications', [
        'user_id' => $pemrakarsa->id,
        'entity_type' => 'pemrakarsa',
        'document_number' => '9120001234567',
        'status' => 'submitted',
    ]);
});

test('unauthorized users cannot access pemrakarsa dedicated routes', function () {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)->get(route('dashboard.pemrakarsa'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.pemrakarsa.reports'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.pemrakarsa.reports.create'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.pemrakarsa.profile.edit'))->assertForbidden();
});

test('pengelola tps3r can view dedicated deposit detail page and update sorting breakdown', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    $deposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-DETAIL-001',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'anorganik_daur_ulang',
        'berat_kg' => 70.0,
        'tanggal_setor' => '2026-10-17',
    ]);

    $response = $this->actingAs($pengelola)->get(route('dashboard.tps3r.deposits.show', $deposit));

    $response->assertOk()
        ->assertSee('DEP-DETAIL-001')
        ->assertSee('Manifest Setoran Sampah');

    // Verify and update sorting weights
    $verifyResponse = $this->actingAs($pengelola)->patch(route('dashboard.tps3r.verify-deposit', $deposit), [
        'status' => 'diterima',
        'berat_konfirmasi_kg' => 70.0,
        'berat_organik_pilah_kg' => 35.0,
        'berat_anorganik_pilah_kg' => 25.0,
        'berat_residu_pilah_kg' => 10.0,
        'catatan_verifikasi' => 'Pemilahan lanjutan TPS3R selesai',
    ]);

    $verifyResponse->assertRedirect();

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'id' => $deposit->id,
        'berat_organik_pilah_kg' => 35.0,
        'berat_anorganik_pilah_kg' => 25.0,
        'berat_residu_pilah_kg' => 10.0,
    ]);
});
