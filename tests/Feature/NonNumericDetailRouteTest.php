<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;

test('tps3r deposit detail route resolves using nomor_resi and renders correctly', function () {
    $tps3rUser = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $tps3rUser->id,
        'nama_tps3r' => 'TPS3R Madiun Berseri',
    ]);
    $petugas = User::factory()->petugasLapangan()->create();

    $deposit = Tps3rWasteDeposit::factory()->create([
        'tps3r_profile_id' => $tps3rProfile->id,
        'pengelola_id' => $tps3rUser->id,
        'petugas_id' => $petugas->id,
        'nomor_resi' => 'DEP-20261029-TEST01',
        'status' => 'terverifikasi',
        'berat_kg' => 120.5,
    ]);

    $url = route('dashboard.tps3r.deposits.show', $deposit);
    expect($url)->toContain('DEP-20261029-TEST01');
    expect($url)->not->toContain('/deposits/'.$deposit->id);

    $response = $this->actingAs($tps3rUser)->get($url);

    $response->assertOk()
        ->assertSee('DEP-20261029-TEST01')
        ->assertSee('TPS3R Madiun Berseri')
        ->assertSee($petugas->name);
});

test('tps3r deposit detail route returns 404 for invalid nomor_resi', function () {
    $tps3rUser = User::factory()->pengelolaTps3r()->create();

    $response = $this->actingAs($tps3rUser)->get('/dashboard/tps3r/deposits/DEP-NONEXISTENT-999999');

    $response->assertNotFound();
});

test('tps3r deposit detail route returns 404 when accessed with legacy numeric id', function () {
    $tps3rUser = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3rUser->id]);
    $petugas = User::factory()->petugasLapangan()->create();

    $deposit = Tps3rWasteDeposit::factory()->create([
        'tps3r_profile_id' => $tps3rProfile->id,
        'pengelola_id' => $tps3rUser->id,
        'petugas_id' => $petugas->id,
        'nomor_resi' => 'DEP-20261029-TEST02',
    ]);

    $response = $this->actingAs($tps3rUser)->get('/dashboard/tps3r/deposits/'.$deposit->id);

    $response->assertNotFound();
});

test('tps3r deposit detail denies access to non-admin and different tps3r profile', function () {
    $ownerTps3r = User::factory()->pengelolaTps3r()->create();
    $ownerProfile = PengelolaTps3rProfile::factory()->create(['user_id' => $ownerTps3r->id]);

    $otherTps3r = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $otherTps3r->id]);

    $deposit = Tps3rWasteDeposit::factory()->create([
        'tps3r_profile_id' => $ownerProfile->id,
        'pengelola_id' => $ownerTps3r->id,
        'nomor_resi' => 'DEP-20261029-TEST03',
    ]);

    $response = $this->actingAs($otherTps3r)->get(route('dashboard.tps3r.deposits.show', $deposit));

    $response->assertForbidden();
});

test('marketplace order detail resolves using nomor_pesanan for seller and admin', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261029-MADIUN99',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);

    $url = route('dashboard.tps3r.orders.show', $order);
    expect($url)->toContain('ORD-20261029-MADIUN99');
    expect($url)->not->toContain('/orders/'.$order->id);

    // Seller view
    $sellerResponse = $this->actingAs($seller)->get($url);
    $sellerResponse->assertOk()
        ->assertSee('ORD-20261029-MADIUN99')
        ->assertSee($product->name);

    // Admin view
    $admin = User::factory()->adminDlh()->create();
    $adminResponse = $this->actingAs($admin)->get($url);
    $adminResponse->assertOk()
        ->assertSee('ORD-20261029-MADIUN99')
        ->assertSee($seller->name);
});

test('marketplace order detail returns 404 for invalid nomor_pesanan', function () {
    $seller = User::factory()->pengelolaTps3r()->create();

    $response = $this->actingAs($seller)->get('/dashboard/tps3r/orders/ORD-NONEXISTENT-999999');

    $response->assertNotFound();
});

test('marketplace order detail returns 404 when accessed with legacy numeric id', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261029-BACKCOMPAT',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
    ]);

    $response = $this->actingAs($seller)->get('/dashboard/tps3r/orders/'.$order->id);

    $response->assertNotFound();
});

test('marketplace order detail prevents seller from viewing another seller order', function () {
    $sellerA = User::factory()->pengelolaTps3r()->create();
    $sellerB = User::factory()->pengelolaTps3r()->create();

    $productB = WasteProduct::factory()->create(['seller_id' => $sellerB->id]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261029-SECRET01',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $sellerB->id,
    ]);

    $response = $this->actingAs($sellerA)->get(route('dashboard.tps3r.orders.show', $order));

    $response->assertNotFound();
});

test('admin can finalize complaint using nomor_tiket route model binding', function () {
    $admin = User::factory()->adminDlh()->create();
    $complaint = Complaint::factory()->create([
        'nomor_tiket' => 'TKT-20261029-CLEAN01',
        'status' => 'dalam_proses',
    ]);

    $finalizeUrl = route('dashboard.admin.complaints.finalize', $complaint);
    expect($finalizeUrl)->toContain('TKT-20261029-CLEAN01');
    expect($finalizeUrl)->not->toContain('/complaints/'.$complaint->id.'/finalize');

    $response = $this->actingAs($admin)->patch($finalizeUrl, [
        'status' => 'selesai',
        'admin_note' => 'Penanganan sampah liar selesai dibersihkan.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'nomor_tiket' => 'TKT-20261029-CLEAN01',
        'status' => 'selesai',
        'instruksi_admin' => 'Penanganan sampah liar selesai dibersihkan.',
    ]);
});

test('admin complaint finalize returns 404 for invalid nomor_tiket', function () {
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)->patch('/dashboard/admin/complaints/TKT-INVALID-999999/finalize', [
        'status' => 'selesai',
        'admin_note' => 'Test invalid.',
    ]);

    $response->assertNotFound();
});

test('admin complaint finalize returns 404 when accessed with legacy numeric id', function () {
    $admin = User::factory()->adminDlh()->create();
    $complaint = Complaint::factory()->create([
        'nomor_tiket' => 'TKT-20261029-COMPAT01',
        'status' => 'dalam_proses',
    ]);

    $response = $this->actingAs($admin)->patch('/dashboard/admin/complaints/'.$complaint->id.'/finalize', [
        'status' => 'selesai',
        'admin_note' => 'Update via legacy numeric id.',
    ]);

    $response->assertNotFound();
});
