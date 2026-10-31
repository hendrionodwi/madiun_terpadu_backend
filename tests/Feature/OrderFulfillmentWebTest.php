<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WasteProduct;

test('seller order detail shows its fulfillment status and available actions', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);
    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders.show', $order));

    $response->assertSee('Menunggu diproses')
        ->assertSee('Ubah menjadi Sedang diproses')
        ->assertSee('Ubah menjadi Dibatalkan');
});

test('seller can update fulfillment status from the web dashboard', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $fulfillment = OrderFulfillment::factory()->create([
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);

    $response = $this->actingAs($seller)
        ->patch(route('dashboard.tps3r.fulfillments.update', $fulfillment), [
            'status' => OrderFulfillmentStatus::Diproses->value,
            'catatan_status' => 'Produk sedang disiapkan petugas TPS3R.',
        ]);

    $response->assertRedirect(route('dashboard.tps3r.orders.show', $fulfillment->order))
        ->assertSessionHas('status');
    $this->assertDatabaseHas('order_fulfillments', [
        'id' => $fulfillment->id,
        'status' => OrderFulfillmentStatus::Diproses->value,
        'catatan_status' => 'Produk sedang disiapkan petugas TPS3R.',
    ]);
});

test('seller receives not found when updating another seller fulfillment from web', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    $fulfillment = OrderFulfillment::factory()->create(['seller_id' => $otherSeller->id]);

    $response = $this->actingAs($seller)
        ->patch(route('dashboard.tps3r.fulfillments.update', $fulfillment), [
            'status' => OrderFulfillmentStatus::Diproses->value,
        ]);

    $response->assertNotFound();
    expect($fulfillment->fresh()->status)->toBe(OrderFulfillmentStatus::Menunggu);
});
