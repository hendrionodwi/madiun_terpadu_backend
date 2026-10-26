<?php

use App\Models\Order;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use App\Models\WasteProduct;

test('midtrans:simulate command successfully settles a pending order', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $serverKey = 'SB-Mid-server-TESTKEY123';
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => $serverKey,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 45000, 'stok' => 10]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261017-SIM01',
        'total_harga' => 45000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 45000,
        'subtotal' => 45000,
    ]);

    $this->artisan('midtrans:simulate', ['order_number' => $order->nomor_pesanan])
        ->expectsOutputToContain('✓ SUKSES: Webhook notifikasi berhasil diproses!')
        ->assertSuccessful();

    $order->refresh();
    expect($order->status)->toBe('dibayar');
    expect($order->paid_at)->not->toBeNull();
});

test('midtrans:simulate command can simulate expire status and restore stock', function () {
    $seller = User::factory()->pengelolaTps3r()->create();
    $serverKey = 'SB-Mid-server-TESTKEY123';
    PengelolaTps3rProfile::factory()->withMidtrans()->create([
        'user_id' => $seller->id,
        'midtrans_server_key' => $serverKey,
    ]);

    $product = WasteProduct::factory()->create(['seller_id' => $seller->id, 'harga' => 20000, 'stok' => 5]);
    $order = Order::factory()->create([
        'nomor_pesanan' => 'ORD-20261017-SIM02',
        'total_harga' => 40000,
        'metode_pembayaran' => 'midtrans',
        'status' => 'pending',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'jumlah' => 2,
        'harga_satuan' => 20000,
        'subtotal' => 40000,
    ]);

    $this->artisan('midtrans:simulate', ['order_number' => $order->nomor_pesanan, '--status' => 'expire'])
        ->expectsOutputToContain('✓ SUKSES: Webhook notifikasi berhasil diproses!')
        ->assertSuccessful();

    $order->refresh();
    expect($order->status)->toBe('dibatalkan');
    expect($product->fresh()->stok)->toBe(7); // 5 + 2 restored
});
