<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use App\Models\WasteProduct;
use App\Notifications\MarketplaceTransactionNotification;
use Laravel\Sanctum\Sanctum;

test('checkout notifies the buyer and tps3r seller about the new transaction', function () {
    $buyer = User::factory()->warga()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'harga' => 25000,
        'stok' => 5,
    ]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'cod',
        'metode_pengiriman' => 'ambil_mandiri',
    ]);

    $response->assertCreated();

    expect($buyer->notifications()->count())->toBe(1)
        ->and($seller->notifications()->count())->toBe(1)
        ->and($buyer->notifications()->first()->data)
        ->toMatchArray([
            'category' => 'marketplace_transaction',
            'event' => 'order_created',
            'audience' => 'buyer',
            'order_number' => $response->json('data.nomor_pesanan'),
        ])
        ->and($seller->notifications()->first()->data)
        ->toMatchArray([
            'event' => 'seller_order_created',
            'audience' => 'seller',
        ]);
});

test('fulfillment status change notifies both parties once for idempotent callbacks', function () {
    $buyer = User::factory()->warga()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'status' => 'dibayar',
    ]);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $payload = [
        'status' => OrderFulfillmentStatus::Diproses->value,
        'catatan_status' => 'Pesanan sedang dikemas.',
    ];

    $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", $payload)->assertOk();
    $this->patchJson("/api/v1/marketplace/fulfillments/{$fulfillment->public_id}", $payload)->assertOk();

    expect($buyer->notifications()->count())->toBe(1)
        ->and($seller->notifications()->count())->toBe(1)
        ->and($buyer->notifications()->first()->data)
        ->toMatchArray([
            'event' => 'fulfillment_status_changed',
            'status' => 'diproses',
            'fulfillment_id' => $fulfillment->public_id,
        ]);
});

test('notification inbox only exposes and updates the authenticated user notifications', function () {
    $user = User::factory()->warga()->create();
    $otherUser = User::factory()->warga()->create();
    $notification = new MarketplaceTransactionNotification(
        event: 'order_status_changed',
        title: 'Pembayaran berhasil',
        message: 'Pesanan ORD-TEST telah dibayar.',
        orderNumber: 'ORD-TEST',
        status: 'dibayar',
        statusLabel: 'Sudah dibayar',
        audience: 'buyer',
        webUrl: 'http://localhost/marketplace/pesanan/ORD-TEST',
    );
    $user->notify($notification);
    $otherUser->notify($notification);
    $ownNotification = $user->notifications()->firstOrFail();
    $foreignNotification = $otherUser->notifications()->firstOrFail();
    Sanctum::actingAs($user, ['role:warga']);

    $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownNotification->id)
        ->assertJsonPath('data.0.is_read', false)
        ->assertJsonPath('meta.unread_count', 1);

    $this->patchJson("/api/v1/notifications/{$foreignNotification->id}/read", [])
        ->assertNotFound();

    $this->patchJson("/api/v1/notifications/{$ownNotification->id}/read", [])
        ->assertOk()
        ->assertJsonPath('data.is_read', true)
        ->assertJsonPath('meta.unread_count', 0);
});

test('authenticated users can mark every notification as read', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $notification = new MarketplaceTransactionNotification(
        event: 'seller_order_created',
        title: 'Pesanan baru',
        message: 'Ada pesanan baru.',
        orderNumber: 'ORD-TEST',
        status: 'pending',
        statusLabel: 'Menunggu pembayaran',
        audience: 'seller',
        webUrl: 'http://localhost/dashboard/tps3r/orders/ORD-TEST',
    );
    $user->notify($notification);
    $user->notify($notification);
    Sanctum::actingAs($user, ['role:pengelola_tps3r']);

    $this->postJson('/api/v1/notifications/read-all')
        ->assertOk()
        ->assertJsonPath('meta.unread_count', 0);

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($user->readNotifications()->count())->toBe(2);
});

test('notification center renders for buyer and tps3r dashboard users', function () {
    $buyer = User::factory()->warga()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $buyer->notify(new MarketplaceTransactionNotification(
        event: 'order_status_changed',
        title: 'Pembayaran berhasil',
        message: 'Pesanan ORD-WEB telah dibayar.',
        orderNumber: 'ORD-WEB',
        status: 'dibayar',
        statusLabel: 'Sudah dibayar',
        audience: 'buyer',
        webUrl: 'http://localhost/marketplace/pesanan/ORD-WEB',
    ));

    $this->actingAs($buyer)
        ->get('/notifikasi')
        ->assertOk()
        ->assertSee('Aktivitas transaksi')
        ->assertSee('Pembayaran berhasil');

    $this->actingAs($seller)
        ->get('/notifikasi')
        ->assertOk()
        ->assertSee('Aktivitas transaksi')
        ->assertSee('Belum ada notifikasi');
});
