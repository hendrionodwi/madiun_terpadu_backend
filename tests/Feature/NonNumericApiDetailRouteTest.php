<?php

use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\PengelolaTps3rProfile;
use App\Models\RetributionBill;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;
use Laravel\Sanctum\Sanctum;

test('public marketplace detail endpoints use stable public identifiers', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $store = PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);

    $this->getJson("/api/v1/marketplace/stores/{$store->public_id}")
        ->assertOk()
        ->assertJsonPath('data.public_id', $store->public_id);
    $this->getJson("/api/v1/marketplace/stores/{$store->id}")->assertNotFound();

    $this->getJson("/api/v1/marketplace/products/{$product->public_id}")
        ->assertOk()
        ->assertJsonPath('data.public_id', $product->public_id);
    $this->getJson("/api/v1/marketplace/products/{$product->id}")->assertNotFound();
});

test('citizen detail endpoints use business reference numbers instead of database ids', function () {
    $warga = User::factory()->warga()->create();
    $complaint = Complaint::factory()->create(['pelapor_id' => $warga->id]);
    $order = Order::factory()->create(['buyer_id' => $warga->id]);
    $bill = RetributionBill::factory()->create(['warga_id' => $warga->id]);
    Sanctum::actingAs($warga, ['role:warga']);

    $this->getJson("/api/v1/complaints/{$complaint->nomor_tiket}")->assertOk();
    $this->getJson("/api/v1/complaints/{$complaint->id}")->assertNotFound();

    $this->getJson("/api/v1/marketplace/orders/{$order->nomor_pesanan}")->assertOk();
    $this->getJson("/api/v1/marketplace/orders/{$order->id}")->assertNotFound();

    $this->getJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}")->assertOk();
    $this->getJson("/api/v1/retributions/bills/{$bill->id}")->assertNotFound();
});

test('operational detail endpoints use receipt or generated public identifiers', function () {
    $admin = User::factory()->adminDlh()->create();
    $report = EnvironmentalReport::factory()->create();
    $deposit = Tps3rWasteDeposit::factory()->create();
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->getJson("/api/v1/environmental-reports/{$report->public_id}")->assertOk();
    $this->getJson("/api/v1/environmental-reports/{$report->id}")->assertNotFound();

    $this->getJson("/api/v1/tps3r/deposits/{$deposit->nomor_resi}")->assertOk();
    $this->getJson("/api/v1/tps3r/deposits/{$deposit->id}")->assertNotFound();
});

test('seller fulfillment detail endpoint uses a generated public identifier', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $fulfillment = OrderFulfillment::factory()->create(['seller_id' => $seller->id]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $this->getJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}")
        ->assertOk()
        ->assertJsonPath('data.public_id', $fulfillment->public_id);
    $this->getJson("/api/v1/marketplace/fulfillments/{$fulfillment->id}")->assertNotFound();
});
