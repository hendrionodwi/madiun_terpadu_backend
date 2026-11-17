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

test('seller can view order detail across all fulfillment statuses without unhandled match error', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);

    foreach (OrderFulfillmentStatus::cases() as $case) {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'jumlah' => 2,
            'harga_satuan' => 25000,
            'subtotal' => 50000,
        ]);
        OrderFulfillment::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'status' => $case,
            'shipped_at' => $case === OrderFulfillmentStatus::Dikirim ? now() : null,
        ]);

        $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders.show', $order));

        $response->assertOk()
            ->assertSee($case->label());
    }
});

test('admin dlh can view order monitoring across all fulfillment statuses without unhandled match error', function () {
    $admin = User::factory()->adminDlh()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);

    foreach (OrderFulfillmentStatus::cases() as $case) {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'jumlah' => 1,
            'harga_satuan' => 15000,
            'subtotal' => 15000,
        ]);
        OrderFulfillment::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'status' => $case,
            'shipped_at' => $case === OrderFulfillmentStatus::Dikirim ? now() : null,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.tps3r.orders.show', $order));

        $response->assertOk()
            ->assertSee($case->label());
    }
});

test('tps3r orders index supports dikirim tab filter', function () {
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
        'status' => OrderFulfillmentStatus::Dikirim,
        'shipped_at' => now(),
    ]);

    $response = $this->actingAs($seller)->get(route('dashboard.tps3r.orders', ['status' => 'dikirim']));

    $response->assertOk()
        ->assertSee('Dalam Pengiriman')
        ->assertSee($order->nomor_pesanan);
});
