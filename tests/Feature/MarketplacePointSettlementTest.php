<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\MarketplaceSettlementItem;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\PengelolaTps3rProfile;
use App\Models\RewardFundAllocation;
use App\Models\User;
use App\Models\WasteProduct;

test('tps3r settlement only includes completed marketplace orders paid with points', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    RewardFundAllocation::factory()->create(['amount' => 100000]);
    $product = WasteProduct::factory()->create(['seller_id' => $manager->id]);

    $pointOrder = Order::factory()->create([
        'metode_pembayaran' => 'poin_sampah',
        'status' => 'selesai',
        'total_harga' => 25000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $pointOrder->id,
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 25000,
        'subtotal' => 25000,
    ]);
    $pointFulfillment = OrderFulfillment::factory()->create([
        'order_id' => $pointOrder->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);

    $midtransOrder = Order::factory()->create([
        'metode_pembayaran' => 'midtrans',
        'status' => 'selesai',
        'total_harga' => 40000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $midtransOrder->id,
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 40000,
        'subtotal' => 40000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $midtransOrder->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);

    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), [
        'amount' => 25000,
        'destination_type' => 'bank_transfer',
        'destination_provider' => 'Bank Jatim',
        'destination_account' => '1234567890',
        'destination_account_name' => 'TPS3R Madiun',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $withdrawal = FundWithdrawalRequest::query()->sole();
    expect($withdrawal->source)->toBe('marketplace_points')
        ->and($withdrawal->amount)->toBe(25000.0)
        ->and($withdrawal->marketplaceSettlementItems)->toHaveCount(1)
        ->and($withdrawal->marketplaceSettlementItems->first()->order_fulfillment_id)->toBe($pointFulfillment->id);

    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), [
        'amount' => 25000,
        'destination_type' => 'bank_transfer',
        'destination_provider' => 'Bank Jatim',
        'destination_account' => '1234567890',
        'destination_account_name' => 'TPS3R Madiun',
    ])->assertSessionHasErrors('settlement');
});

test('rejected marketplace point settlement releases orders for a new request', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    RewardFundAllocation::factory()->create(['amount' => 100000]);
    $product = WasteProduct::factory()->create(['seller_id' => $manager->id]);
    $order = Order::factory()->create([
        'metode_pembayaran' => 'poin_sampah',
        'status' => 'selesai',
        'total_harga' => 20000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);

    $payload = [
        'amount' => 20000,
        'destination_type' => 'bank_transfer',
        'destination_provider' => 'Bank Jatim',
        'destination_account' => '1234567890',
        'destination_account_name' => 'TPS3R Madiun',
    ];
    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), $payload)->assertSessionHasNoErrors();
    $withdrawal = FundWithdrawalRequest::query()->sole();

    $admin = User::factory()->adminDlh()->create();
    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Data rekening perlu diperbaiki.',
    ])->assertRedirect();

    expect(MarketplaceSettlementItem::query()->firstOrFail()->released_at)->not->toBeNull();

    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), $payload)->assertSessionHasNoErrors();
    expect(FundWithdrawalRequest::query()->count())->toBe(2);
});

test('tps3r can request partial marketplace point settlements without exposing the dlh reward fund', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    RewardFundAllocation::factory()->create(['amount' => 100000]);
    $product = WasteProduct::factory()->create(['seller_id' => $manager->id]);
    $order = Order::factory()->create([
        'metode_pembayaran' => 'poin_sampah',
        'status' => 'selesai',
        'total_harga' => 25000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'jumlah' => 1,
        'harga_satuan' => 25000,
        'subtotal' => 25000,
    ]);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);
    $payload = [
        'destination_type' => 'bank_transfer',
        'destination_provider' => 'Bank Jatim',
        'destination_account' => '1234567890',
        'destination_account_name' => 'TPS3R Madiun',
    ];

    $this->actingAs($manager)
        ->get(route('dashboard.tps3r.withdrawals.index'))
        ->assertOk()
        ->assertSee('Nominal pengajuan')
        ->assertDontSee('Dana kas reward tersedia');

    $this->actingAs($manager)
        ->post(route('dashboard.tps3r.withdrawals.store'), [...$payload, 'amount' => 10000])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $firstWithdrawal = FundWithdrawalRequest::query()->oldest('id')->firstOrFail();
    expect($firstWithdrawal->amount)->toBe(10000.0)
        ->and($firstWithdrawal->marketplaceSettlementItems)->toHaveCount(1)
        ->and($firstWithdrawal->marketplaceSettlementItems->first()->amount)->toBe(10000.0)
        ->and($firstWithdrawal->marketplaceSettlementItems->first()->order_fulfillment_id)->toBe($fulfillment->id);

    $this->actingAs($manager)
        ->post(route('dashboard.tps3r.withdrawals.store'), [...$payload, 'amount' => 15000])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(FundWithdrawalRequest::query()->count())->toBe(2)
        ->and((float) MarketplaceSettlementItem::query()->sum('amount'))->toBe(25000.0);

    $this->actingAs($manager)
        ->post(route('dashboard.tps3r.withdrawals.store'), [...$payload, 'amount' => 10000])
        ->assertSessionHasErrors('settlement');
});

test('tps3r cannot request more than its marketplace point settlement balance', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    RewardFundAllocation::factory()->create(['amount' => 100000]);
    $product = WasteProduct::factory()->create(['seller_id' => $manager->id]);
    $order = Order::factory()->create([
        'metode_pembayaran' => 'poin_sampah',
        'status' => 'selesai',
        'total_harga' => 20000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'subtotal' => 20000,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $manager->id,
        'status' => OrderFulfillmentStatus::Selesai,
        'completed_at' => now(),
    ]);

    $this->actingAs($manager)->post(route('dashboard.tps3r.withdrawals.store'), [
        'amount' => 25000,
        'destination_type' => 'bank_transfer',
        'destination_provider' => 'Bank Jatim',
        'destination_account' => '1234567890',
        'destination_account_name' => 'TPS3R Madiun',
    ])->assertSessionHasErrors('amount');

    expect(FundWithdrawalRequest::query()->count())->toBe(0)
        ->and(MarketplaceSettlementItem::query()->count())->toBe(0);
});
