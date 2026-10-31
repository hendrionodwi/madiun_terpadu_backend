<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WasteProduct;
use Laravel\Sanctum\Sanctum;

test('single store checkout creates one fulfillment for its tps3r seller', function () {
    $buyer = User::factory()->warga()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $firstProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'harga' => 20000,
        'stok' => 5,
    ]);
    $secondProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'harga' => 15000,
        'stok' => 5,
    ]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [
            ['product_id' => $firstProduct->id, 'jumlah' => 1],
            ['product_id' => $secondProduct->id, 'jumlah' => 1],
        ],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ]);

    $response->assertCreated()
        ->assertJsonCount(1, 'data.fulfillments');
    $this->assertDatabaseHas('order_fulfillments', [
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu->value,
    ]);
});

test('seller fulfillment list only exposes their own items and subtotal', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create(['total_harga' => 35000]);
    $ownProduct = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Milik Seller Login',
    ]);
    $otherProduct = WasteProduct::factory()->create([
        'seller_id' => $otherSeller->id,
        'nama_produk' => 'Produk Seller Lain',
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
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $otherSeller->id,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->getJson('/api/v1/marketplace/fulfillments');

    $response->assertOk()
        ->assertJsonPath('data.0.subtotal', 20000)
        ->assertJsonPath('data.0.items.0.nama_produk', 'Kompos Milik Seller Login')
        ->assertJsonMissing(['nama_produk' => 'Produk Seller Lain']);
});

test('seller can advance their fulfillment and the parent order status is synchronized', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create(['status' => 'dibayar']);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Diproses->value,
        'catatan_status' => 'Pesanan sedang dikemas.',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', OrderFulfillmentStatus::Diproses->value)
        ->assertJsonPath('data.catatan_status', 'Pesanan sedang dikemas.');
    expect($fulfillment->fresh()->processed_at)->not->toBeNull();
    expect($order->fresh()->status)->toBe('diproses');
});

test('parent order is completed after every seller fulfillment reaches a terminal status', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create(['status' => 'diproses']);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::SiapDikirim,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $otherSeller->id,
        'status' => OrderFulfillmentStatus::Dibatalkan,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Selesai->value,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', OrderFulfillmentStatus::Selesai->value);
    expect($order->fresh()->status)->toBe('selesai');
});

test('parent order is cancelled when every seller fulfillment is cancelled', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create(['status' => 'pending']);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Dibatalkan->value,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', OrderFulfillmentStatus::Dibatalkan->value);
    expect($order->fresh()->status)->toBe('dibatalkan');
});

test('seller cannot skip an order fulfillment transition', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $fulfillment = OrderFulfillment::factory()->create([
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", [
        'status' => OrderFulfillmentStatus::Selesai->value,
    ]);

    $response->assertUnprocessable()
        ->assertInvalid(['status' => 'tidak dapat langsung diubah']);
    expect($fulfillment->fresh()->status)->toBe(OrderFulfillmentStatus::Menunggu);
});

test('seller receives not found when accessing another seller fulfillment', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $fulfillment = OrderFulfillment::factory()->create(['seller_id' => $otherSeller->id]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $response = $this->getJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}");

    $response->assertNotFound();
});

test('warga is forbidden from seller fulfillment endpoints', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->getJson('/api/v1/marketplace/fulfillments');

    $response->assertForbidden();
});
