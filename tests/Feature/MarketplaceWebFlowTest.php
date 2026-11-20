<?php

use App\Models\Order;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;

test('checkout page renders approved delivery zones without a Blade parse error', function () {
    $buyer = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $buyer->id]);
    $seller = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    Tps3rDeliveryZone::factory()->approved()->create([
        'pengelola_tps3r_profile_id' => $profile->id,
        'kelurahan' => 'Pandean',
        'kecamatan' => 'Taman',
        'delivery_fee' => 7000,
    ]);
    $product = WasteProduct::factory()->create(['seller_id' => $seller->id]);

    $this->actingAs($buyer)
        ->post(route('marketplace.cart.store', $product), ['jumlah' => 1]);

    $this->actingAs($buyer)
        ->get(route('marketplace.checkout'))
        ->assertViewIs('marketplace.checkout')
        ->assertViewHas('checkoutConfig', fn (array $config): bool => $config['zones'] === [[
            'key' => 'Pandean|Taman',
            'kelurahan' => 'Pandean',
            'kecamatan' => 'Taman',
            'fee' => 7000,
        ]])
        ->assertSee('Ringkasan pesanan');
});

test('warga can add a product to cart and checkout with points', function () {
    $buyer = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $buyer->id,
        'saldo_poin' => 100000,
    ]);
    $seller = User::factory()->pengelolaTps3r()->create();
    $product = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'harga' => 25000,
        'stok' => 5,
    ]);

    $this->actingAs($buyer)
        ->post(route('marketplace.cart.store', $product), ['jumlah' => 2])
        ->assertRedirect(route('marketplace.cart'));

    $this->actingAs($buyer)
        ->get(route('marketplace.cart'))
        ->assertOk()
        ->assertSee($product->nama_produk)
        ->assertSee('Rp50.000');

    $response = $this->actingAs($buyer)->post(route('marketplace.checkout.store'), [
        'metode_pengiriman' => 'ambil_mandiri',
        'metode_pembayaran' => 'poin_sampah',
    ]);

    $order = Order::query()->where('buyer_id', $buyer->id)->sole();
    $response->assertRedirect(route('marketplace.orders.show', $order));
    expect($order->total_harga)->toBe(50000.0)
        ->and($order->status)->toBe('dibayar')
        ->and($product->fresh()->stok)->toBe(3)
        ->and($buyer->wargaProfile->fresh()->saldo_poin)->toBe(50000);
});

test('web checkout adds delivery fee and requires an address', function () {
    $buyer = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $buyer->id, 'saldo_poin' => 100000]);
    $seller = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    Tps3rDeliveryZone::factory()->approved()->create([
        'pengelola_tps3r_profile_id' => $profile->id,
        'kelurahan' => 'Pandean',
        'kecamatan' => 'Taman',
        'delivery_fee' => 7000,
    ]);
    $product = WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'harga' => 20000,
        'stok' => 2,
    ]);

    $this->actingAs($buyer)
        ->post(route('marketplace.cart.store', $product), ['jumlah' => 1]);

    $this->actingAs($buyer)
        ->post(route('marketplace.checkout.store'), [
            'metode_pengiriman' => 'diantar',
            'metode_pembayaran' => 'poin_sampah',
        ])
        ->assertSessionHasErrors('alamat_pengiriman');

    $this->actingAs($buyer)
        ->post(route('marketplace.checkout.store'), [
            'metode_pengiriman' => 'diantar',
            'alamat_pengiriman' => 'Jl. Pahlawan No. 10, Kota Madiun',
            'shipping_source' => 'local_tps3r',
            'destination_kelurahan' => 'Pandean',
            'destination_kecamatan' => 'Taman',
            'metode_pembayaran' => 'poin_sampah',
        ])
        ->assertRedirect();

    $order = Order::query()->where('buyer_id', $buyer->id)->sole();
    expect($order->biaya_pengiriman)->toBe(7000.0)
        ->and($order->total_harga)->toBe(27000.0)
        ->and($order->shipping_source)->toBe('local_tps3r')
        ->and($order->destination_kelurahan)->toBe('Pandean')
        ->and($order->alamat_pengiriman)->toBe('Jl. Pahlawan No. 10, Kota Madiun');
});

test('cart groups different tps3r sellers and checks them out separately', function () {
    $buyer = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $buyer->id, 'saldo_poin' => 100000]);
    $firstSeller = User::factory()->pengelolaTps3r()->create(['name' => 'TPS3R Utara']);
    $secondSeller = User::factory()->pengelolaTps3r()->create(['name' => 'TPS3R Selatan']);
    $firstProduct = WasteProduct::factory()->create([
        'seller_id' => $firstSeller->id,
        'nama_produk' => 'Kompos TPS3R Utara',
        'harga' => 15000,
    ]);
    $secondProduct = WasteProduct::factory()->create([
        'seller_id' => $secondSeller->id,
        'nama_produk' => 'Kerajinan TPS3R Selatan',
        'harga' => 20000,
    ]);

    $this->actingAs($buyer)
        ->post(route('marketplace.cart.store', $firstProduct), ['jumlah' => 1])
        ->assertRedirect(route('marketplace.cart'));

    $this->actingAs($buyer)
        ->post(route('marketplace.cart.store', $secondProduct), ['jumlah' => 1])
        ->assertRedirect(route('marketplace.cart'));

    $this->actingAs($buyer)
        ->get(route('marketplace.cart'))
        ->assertOk()
        ->assertSee($firstProduct->nama_produk)
        ->assertSee($secondProduct->nama_produk)
        ->assertSee('Checkout toko ini', false);

    $this->actingAs($buyer)
        ->get(route('marketplace.checkout'))
        ->assertSessionHasErrors('cart');

    $this->actingAs($buyer)
        ->post(route('marketplace.checkout.store'), [
            'seller_id' => $firstSeller->id,
            'metode_pengiriman' => 'ambil_mandiri',
            'metode_pembayaran' => 'poin_sampah',
        ])
        ->assertRedirect();

    $this->actingAs($buyer)
        ->get(route('marketplace.cart'))
        ->assertOk()
        ->assertDontSee($firstProduct->nama_produk)
        ->assertSee($secondProduct->nama_produk);

    $order = Order::query()->where('buyer_id', $buyer->id)->sole();
    expect($order->items)->toHaveCount(1)
        ->and($order->items->first()->product_id)->toBe($firstProduct->id);
});

test('non warga cannot access buyer marketplace flow', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();

    $this->actingAs($pengelola)
        ->get(route('marketplace.cart'))
        ->assertForbidden();
});

test('buyer cannot confirm an order before it is shipped', function () {
    $buyer = User::factory()->warga()->create();
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'status' => 'siap_dikirim',
    ]);

    $this->actingAs($buyer)
        ->patch(route('marketplace.orders.confirm', $order))
        ->assertSessionHasErrors('order');

    expect($order->fresh()->status)->toBe('siap_dikirim');
});
