<?php

use App\Models\Order;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use Laravel\Sanctum\Sanctum;

test('users can browse recycled product catalog (MKT-F-001)', function () {
    WasteProduct::factory()->count(3)->create(['is_available' => true, 'stok' => 10]);

    $response = $this->getJson('/api/v1/marketplace/products');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'data',
            'meta',
        ]);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(3);
});

test('pengelola tps3r can add product to catalog (MKT-F-001)', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $payload = [
        'nama_produk' => 'Kompos Organik Madiun Berseri 5kg',
        'deskripsi' => 'Pupuk kompos berkualitas hasil olahan sampah organik TPS3R',
        'kategori' => 'pupuk_kompos',
        'harga' => 25000,
        'stok' => 50,
    ];

    $response = $this->postJson('/api/v1/marketplace/products', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'nama_produk' => 'Kompos Organik Madiun Berseri 5kg',
                'harga' => 25000,
                'stok' => 50,
            ],
        ]);

    $this->assertDatabaseHas('waste_products', [
        'nama_produk' => 'Kompos Organik Madiun Berseri 5kg',
        'seller_id' => $seller->id,
    ]);
});

test('buyer can checkout cart and product stock is deducted (MKT-F-003)', function () {
    $buyer = User::factory()->warga()->create();
    $product1 = WasteProduct::factory()->create(['harga' => 20000, 'stok' => 10]);
    $product2 = WasteProduct::factory()->create(['harga' => 15000, 'stok' => 5]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $payload = [
        'items' => [
            ['product_id' => $product1->id, 'jumlah' => 2], // 40.000
            ['product_id' => $product2->id, 'jumlah' => 1], // 15.000
        ],
        'metode_pembayaran' => 'transfer_bank',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ];

    $response = $this->postJson('/api/v1/marketplace/checkout', $payload);

    // Total = 55.000
    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'total_harga' => 55000,
                'metode_pembayaran' => 'transfer_bank',
                'status' => 'pending',
            ],
        ]);

    expect($product1->fresh()->stok)->toBe(8);
    expect($product2->fresh()->stok)->toBe(4);
});

test('buyer can pay using garbage points (poin_sampah) and balance is deducted (MKT-F-003)', function () {
    $buyer = User::factory()->warga()->create();
    $wargaProfile = WargaProfile::factory()->create([
        'user_id' => $buyer->id,
        'saldo_poin' => 50000,
    ]);

    $product = WasteProduct::factory()->create(['harga' => 30000, 'stok' => 5]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $payload = [
        'items' => [
            ['product_id' => $product->id, 'jumlah' => 1],
        ],
        'metode_pembayaran' => 'poin_sampah',
        'alamat_pengiriman' => 'Jl. Merak No. 12',
    ];

    $response = $this->postJson('/api/v1/marketplace/checkout', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'dibayar',
            ],
        ]);

    // 50.000 - 30.000 = 20.000
    expect($wargaProfile->fresh()->saldo_poin)->toBe(20000);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $buyer->id,
        'tipe' => 'debit',
        'kategori' => 'belanja_marketplace',
        'jumlah_poin' => 30000,
    ]);
});

test('checkout fails with 422 when stock is insufficient (MKT-F-003)', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['stok' => 2]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [
            ['product_id' => $product->id, 'jumlah' => 5],
        ],
        'metode_pembayaran' => 'transfer_bank',
        'alamat_pengiriman' => 'Alamat pengiriman',
    ]);

    $response->assertStatus(422);
});

test('checkout combines repeated product lines before checking stock', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['harga' => 12000, 'stok' => 3]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [
            ['product_id' => $product->id, 'jumlah' => 2],
            ['product_id' => $product->id, 'jumlah' => 2],
        ],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ]);

    $response->assertUnprocessable()->assertInvalid(['items' => 'Stok produk']);
    expect($product->fresh()->stok)->toBe(3);
    $this->assertDatabaseCount('orders', 0);
});

test('checkout saves repeated product lines as one order item', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['harga' => 12000, 'stok' => 3]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [
            ['product_id' => $product->id, 'jumlah' => 1],
            ['product_id' => $product->id, 'jumlah' => 1],
        ],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ]);

    $response->assertCreated()->assertJsonPath('data.total_harga', 24000);
    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'jumlah' => 2,
        'subtotal' => 24000,
    ]);
    $this->assertDatabaseCount('order_items', 1);
    expect($product->fresh()->stok)->toBe(1);
});

test('checkout rejects unavailable products without creating an order', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['stok' => 3, 'is_available' => false]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ]);

    $response->assertUnprocessable()->assertInvalid(['items' => 'tidak tersedia']);
    expect($product->fresh()->stok)->toBe(3);
    $this->assertDatabaseCount('orders', 0);
});

test('checkout with insufficient points preserves stock and balance', function () {
    $buyer = User::factory()->warga()->create();
    $profile = WargaProfile::factory()->create(['user_id' => $buyer->id, 'saldo_poin' => 5000]);
    $product = WasteProduct::factory()->create(['harga' => 12000, 'stok' => 3]);
    Sanctum::actingAs($buyer, ['role:warga']);

    $response = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'poin_sampah',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 45 Kota Madiun',
    ]);

    $response->assertUnprocessable()->assertInvalid(['metode_pembayaran' => 'Saldo poin']);
    expect($product->fresh()->stok)->toBe(3);
    expect($profile->fresh()->saldo_poin)->toBe(5000);
    $this->assertDatabaseCount('orders', 0);
});

test('buyer can cancel their order and stock is restored, points refunded', function () {
    $buyer = User::factory()->warga()->create();
    $profile = WargaProfile::factory()->create([
        'user_id' => $buyer->id,
        'saldo_poin' => 50000,
    ]);
    $product = WasteProduct::factory()->create(['harga' => 20000, 'stok' => 10]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $checkoutResponse = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 2]],
        'metode_pembayaran' => 'poin_sampah',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 10 Kota Madiun',
    ]);

    $checkoutResponse->assertCreated();
    $orderId = $checkoutResponse->json('data.id');

    expect($product->fresh()->stok)->toBe(8);
    expect($profile->fresh()->saldo_poin)->toBe(10000);

    $cancelResponse = $this->patchJson("/api/v1/marketplace/orders/{$orderId}/cancel", [
        'alasan' => 'Ingin menambah jumlah pesanan',
    ]);

    $cancelResponse->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $orderId,
                'status' => 'dibatalkan',
            ],
        ]);

    expect($product->fresh()->stok)->toBe(10);
    expect($profile->fresh()->saldo_poin)->toBe(50000);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $buyer->id,
        'tipe' => 'kredit',
        'kategori' => 'refund_marketplace',
        'jumlah_poin' => 40000,
    ]);

    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'status' => 'dibatalkan',
    ]);
    $this->assertDatabaseHas('order_fulfillments', [
        'order_id' => $orderId,
        'status' => 'dibatalkan',
        'catatan_status' => 'Ingin menambah jumlah pesanan',
    ]);
});

test('buyer cannot cancel order if already shipped or completed', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['harga' => 20000, 'stok' => 10]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $checkoutResponse = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 10 Kota Madiun',
    ]);

    $orderId = $checkoutResponse->json('data.id');
    Order::where('id', $orderId)->update(['status' => 'siap_dikirim']);

    $cancelResponse = $this->patchJson("/api/v1/marketplace/orders/{$orderId}/cancel");

    $cancelResponse->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('buyer can confirm order received and order status becomes selesai', function () {
    $buyer = User::factory()->warga()->create();
    $product = WasteProduct::factory()->create(['harga' => 20000, 'stok' => 10]);

    Sanctum::actingAs($buyer, ['role:warga']);

    $checkoutResponse = $this->postJson('/api/v1/marketplace/checkout', [
        'items' => [['product_id' => $product->id, 'jumlah' => 1]],
        'metode_pembayaran' => 'cod',
        'alamat_pengiriman' => 'Jl. Pahlawan No. 10 Kota Madiun',
    ]);

    $orderId = $checkoutResponse->json('data.id');
    Order::where('id', $orderId)->update(['status' => 'siap_dikirim']);

    $confirmResponse = $this->patchJson("/api/v1/marketplace/orders/{$orderId}/confirm-received");

    $confirmResponse->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $orderId,
                'status' => 'selesai',
            ],
        ]);

    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'status' => 'selesai',
    ]);
    $this->assertDatabaseHas('order_fulfillments', [
        'order_id' => $orderId,
        'status' => 'selesai',
    ]);
});
