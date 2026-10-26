<?php

use App\Models\Order;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

test('checkout with midtrans fails when items are from multiple tps3r sellers', function () {
    $buyer = User::factory()->warga()->create();
    Sanctum::actingAs($buyer, ['role:warga']);

    $sellerA = User::factory()->pengelolaTps3r()->create();
    $sellerB = User::factory()->pengelolaTps3r()->create();

    $productA = WasteProduct::factory()->create(['seller_id' => $sellerA->id, 'harga' => 20000, 'stok' => 5]);
    $productB = WasteProduct::factory()->create(['seller_id' => $sellerB->id, 'harga' => 30000, 'stok' => 5]);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [
            ['product_id' => $productA->id, 'jumlah' => 1],
            ['product_id' => $productB->id, 'jumlah' => 1],
        ],
        'metode_pembayaran' => 'midtrans',
        'alamat_pengiriman' => 'Jl. Pahlawan Kota Madiun',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['items']);
});

test('checkout with midtrans fails when tps3r has not configured midtrans', function () {
    $buyer = User::factory()->warga()->create();
    Sanctum::actingAs($buyer, ['role:warga']);

    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create([
        'user_id' => $seller->id,
        'midtrans_is_active' => false,
        'midtrans_server_key' => null,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 25000, 'stok' => 5]);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'midtrans',
        'alamat_pengiriman' => 'Jl. Pahlawan Kota Madiun',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['metode_pembayaran']);
});

test('checkout with midtrans creates snap token from tps3r merchant credentials', function () {
    $buyer = User::factory()->warga()->create();
    Sanctum::actingAs($buyer, ['role:warga']);

    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => 'SB-Mid-server-TEST1234',
        'midtrans_is_production' => false,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 35000, 'stok' => 5]);

    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'mock-snap-token-xyz-123',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-xyz-123',
        ], 201),
    ]);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'midtrans',
        'alamat_pengiriman' => 'Jl. Pahlawan Kota Madiun',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'pending',
                'metode_pembayaran' => 'midtrans',
                'snap_token' => 'mock-snap-token-xyz-123',
                'snap_redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/mock-snap-token-xyz-123',
            ],
        ]);

    $this->assertDatabaseHas('orders', [
        'snap_token' => 'mock-snap-token-xyz-123',
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
});

test('midtrans webhook notification processes settlement and marks order as dibayar', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $serverKey = 'SB-Mid-server-SECRET999';
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => $serverKey,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 50000, 'stok' => 5]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261017-TEST01',
        'total_harga' => 50000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 50000,
        'subtotal' => 50000,
    ]);

    $statusCode = '200';
    $grossAmount = '50000.00';
    $signature = hash('sha512', $order->nomor_pesanan.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $order->nomor_pesanan,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'transaction_status' => 'settlement',
        'signature_key' => $signature,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $order->refresh();
    expect($order->status)->toBe('dibayar');
    expect($order->paid_at)->not->toBeNull();
});

test('midtrans webhook notification rejects invalid signature with 403', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => 'SB-Mid-server-SECRET999',
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 50000, 'stok' => 5]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261017-TEST02',
        'total_harga' => 50000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 50000,
        'subtotal' => 50000,
    ]);

    $response = $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $order->nomor_pesanan,
        'status_code' => '200',
        'gross_amount' => '50000.00',
        'transaction_status' => 'settlement',
        'signature_key' => 'fake-invalid-signature',
    ]);

    $response->assertForbidden();
});

test('midtrans webhook notification restores stock when transaction is cancelled or expired', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $serverKey = 'SB-Mid-server-SECRET999';
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => $serverKey,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 50000, 'stok' => 3]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261017-TEST03',
        'total_harga' => 50000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'jumlah' => 2,
        'harga_satuan' => 25000,
        'subtotal' => 50000,
    ]);

    $statusCode = '200';
    $grossAmount = '50000.00';
    $signature = hash('sha512', $order->nomor_pesanan.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson('/api/v1/marketplace/midtrans/notification', [
        'order_id' => $order->nomor_pesanan,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'transaction_status' => 'expire',
        'signature_key' => $signature,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $order->refresh();
    expect($order->status)->toBe('dibatalkan');
    expect($product->fresh()->stok)->toBe(5); // 3 + 2 restored
});
