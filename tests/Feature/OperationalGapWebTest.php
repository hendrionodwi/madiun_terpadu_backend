<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\BroadcastAnnouncement;
use App\Models\EntityVerification;
use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProcessingRecord;
use App\Models\WasteProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin can open the seven operational administration pages', function () {
    $admin = User::factory()->adminDlh()->create();
    $routes = [
        'dashboard.admin.users' => 'Manajemen Pengguna',
        'dashboard.admin.routes' => 'Penugasan',
        'dashboard.admin.activity-logs' => 'Audit Trail',
        'dashboard.admin.master-data' => 'Data Master',
        'dashboard.admin.points' => 'Pencairan',
        'dashboard.admin.entities' => 'Validasi',
        'dashboard.admin.broadcasts' => 'Siaran Pengumuman',
    ];

    foreach ($routes as $route => $text) {
        $this->actingAs($admin)->get(route($route))->assertOk()->assertSee($text);
    }
});

test('admin can create an account and assign a fixed route', function () {
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)->post(route('dashboard.admin.users.store'), [
        'name' => 'Petugas Baru',
        'email' => 'petugas.baru@madiun.go.id',
        'phone_number' => '081234567890',
        'role' => 'petugas_lapangan',
        'password' => 'password123',
    ])->assertRedirect();

    $petugas = User::query()->where('email', 'petugas.baru@madiun.go.id')->firstOrFail();
    expect($petugas->role->value)->toBe('petugas_lapangan');
    $this->assertDatabaseHas('petugas_lapangan_profiles', ['user_id' => $petugas->id]);

    $this->actingAs($admin)->post(route('dashboard.admin.routes.store'), [
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TAMAN-01',
        'route_name' => 'Taman Barat',
        'district' => 'Taman',
        'subdistrict' => 'Pandean',
        'schedule_day' => 'Senin',
        'start_time' => '07:00',
        'end_time' => '11:00',
    ])->assertRedirect();

    $this->assertDatabaseHas('route_assignments', ['petugas_id' => $petugas->id, 'route_code' => 'RUTE-TAMAN-01']);
    expect($petugas->fresh()->petugasLapanganProfile?->id_wilayah_rute)->toBe('RUTE-TAMAN-01');
});

test('tps3r manager can update profile, submit legality, and record waste processing', function () {
    Storage::fake('local');
    $manager = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    $deposit = Tps3rWasteDeposit::factory()->create(['pengelola_id' => $manager->id, 'tps3r_profile_id' => $profile->id]);

    $this->actingAs($manager)->patch(route('dashboard.tps3r.profile.update'), [
        'nama_tps3r' => 'TPS3R Bersih Madiun',
        'kode_tps3r' => 'TPS-BERSIH-01',
        'kapasitas_harian_kg' => 2500,
        'document_number' => 'LEGAL-001',
        'legal_document' => UploadedFile::fake()->create('legalitas.pdf', 200, 'application/pdf'),
    ])->assertRedirect();

    $this->assertDatabaseHas('entity_verifications', ['user_id' => $manager->id, 'entity_type' => 'tps3r', 'status' => 'submitted']);

    $this->actingAs($manager)->post(route('dashboard.tps3r.processing.store'), [
        'tps3r_waste_deposit_id' => $deposit->id,
        'process_method' => 'composting',
        'waste_type' => 'organik',
        'input_weight_kg' => 125,
        'started_on' => '2026-10-10',
        'notes' => 'Batch kompos pertama.',
    ])->assertRedirect();

    $record = WasteProcessingRecord::query()->firstOrFail();
    $this->actingAs($manager)->patch(route('dashboard.tps3r.processing.update', $record), [
        'status' => 'processing',
        'notes' => 'Sedang difermentasi.',
    ])->assertRedirect();
    expect($record->fresh()->status)->toBe('processing');
});

test('pemrakarsa can submit company legality and admin can approve the entity', function () {
    Storage::fake('local');
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create(['user_id' => $pemrakarsa->id]);

    $this->actingAs($pemrakarsa)->patch(route('dashboard.pemrakarsa.profile.update'), [
        'nama_instansi' => 'PT Madiun Lestari',
        'jenis_instansi' => 'Industri',
        'nib' => 'NIB-2026-001',
        'penanggung_jawab' => 'Rina Madiun',
        'alamat_instansi' => 'Jalan Pahlawan, Madiun',
        'legal_document' => UploadedFile::fake()->create('nib.pdf', 100, 'application/pdf'),
    ])->assertRedirect();

    $verification = EntityVerification::query()->where('user_id', $pemrakarsa->id)->firstOrFail();
    $admin = User::factory()->adminDlh()->create();
    $this->actingAs($admin)->patch(route('dashboard.admin.entities.update', $verification), [
        'status' => 'approved',
        'admin_note' => 'Dokumen legalitas sesuai.',
    ])->assertRedirect();

    expect($verification->fresh()->status)->toBe('approved');
});

test('tps3r store revenue can be requested and approved by admin', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    $product = WasteProduct::factory()->create(['seller_id' => $manager->id]);
    $order = Order::factory()->create();
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'subtotal' => 50000]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);

    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), [
        'amount' => 30000,
        'destination_type' => 'bank_transfer',
        'destination_account' => '1234567890',
    ])->assertRedirect();

    $withdrawal = FundWithdrawalRequest::query()->firstOrFail();
    $admin = User::factory()->adminDlh()->create();
    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'admin_note' => 'Transfer diproses.',
    ])->assertRedirect();

    expect($withdrawal->fresh()->status)->toBe('approved');
});

test('admin can manage master data and publish a role targeted announcement', function () {
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)->post(route('dashboard.admin.master-data.store'), [
        'group' => 'waste_category',
        'code' => 'kaca',
        'name' => 'Kaca',
        'numeric_value' => 300,
        'unit' => 'poin/kg',
        'description' => 'Pisahkan kaca pecah dengan aman.',
        'sort_order' => 6,
    ])->assertRedirect();
    $this->actingAs($admin)->post(route('dashboard.admin.broadcasts.store'), [
        'title' => 'Jadwal khusus warga',
        'content' => 'Penjemputan hari Jumat dimulai pukul tujuh.',
        'audience_role' => 'warga',
        'priority' => 'high',
    ])->assertRedirect();

    expect(MasterData::query()->where('code', 'kaca')->exists())->toBeTrue();
    expect(BroadcastAnnouncement::query()->where('audience_role', 'warga')->exists())->toBeTrue();
});

test('non administrators cannot open administration pages', function () {
    $manager = User::factory()->pengelolaTps3r()->create();

    $this->actingAs($manager)->get(route('dashboard.admin.users'))->assertForbidden();
});
