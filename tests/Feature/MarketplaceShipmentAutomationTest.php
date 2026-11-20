<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

test('polling marks a delivered shipment and completes it after the confirmation window', function (): void {
    $this->travelTo('2026-11-17 10:00:00');
    config()->set([
        'services.rajaongkir.enabled' => true,
        'services.rajaongkir.api_key' => 'test-api-key',
        'services.rajaongkir.base_url' => 'https://rajaongkir.test/api/v1',
        'services.rajaongkir.tracking_poll_minutes' => 180,
        'services.rajaongkir.tracking_daily_limit' => 80,
        'services.rajaongkir.tracking_batch_size' => 20,
        'services.rajaongkir.auto_complete_hours' => 48,
    ]);
    RateLimiter::clear('rajaongkir-tracking:2026-11-17');
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/track/waybill*' => Http::response([
            'data' => [
                'delivered' => true,
                'summary' => ['status' => 'DELIVERED'],
                'delivery_status' => [
                    'status' => 'DELIVERED',
                    'pod_receiver' => 'Siti Aminah',
                    'pod_date' => '2026-11-17',
                    'pod_time' => '10:15:00',
                ],
                'manifest' => [[
                    'manifest_description' => 'Paket diterima oleh penerima.',
                ]],
            ],
        ]),
    ]);
    $buyer = User::factory()->warga()->create(['phone_number' => '081234567890']);
    $seller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'status' => 'dikirim',
        'shipping_source' => 'rajaongkir',
        'shipping_courier' => 'jne',
    ]);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Dikirim,
        'tracking_number' => 'JNE123456789',
        'shipped_at' => now(),
        'next_tracking_check_at' => now(),
    ]);

    $this->artisan('marketplace:poll-shipments')->assertSuccessful();

    $fulfillment->refresh();
    expect($fulfillment)
        ->courier_status->toBe('DELIVERED')
        ->courier_status_description->toBe('Paket diterima oleh penerima.')
        ->delivered_at->format('Y-m-d H:i:s')->toBe('2026-11-17 10:15:00')
        ->auto_complete_at->format('Y-m-d H:i:s')->toBe('2026-11-19 10:15:00')
        ->status->toBe(OrderFulfillmentStatus::Dikirim);
    expect($buyer->notifications()->first()?->data)
        ->toMatchArray([
            'event' => 'courier_marked_delivered',
            'audience' => 'buyer',
            'order_number' => $order->nomor_pesanan,
            'tracking_number' => 'JNE123456789',
        ])
        ->and($seller->notifications()->first()?->data)
        ->toMatchArray([
            'event' => 'courier_marked_delivered',
            'audience' => 'seller',
            'order_number' => $order->nomor_pesanan,
        ]);
    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'last_phone_number=67890'));

    $this->travelTo('2026-11-19 10:14:00');
    $this->artisan('marketplace:auto-complete-deliveries')->assertSuccessful();
    expect($fulfillment->fresh()->status)->toBe(OrderFulfillmentStatus::Dikirim);

    $this->travelTo('2026-11-19 10:16:00');
    $this->artisan('marketplace:auto-complete-deliveries')->assertSuccessful();

    expect($fulfillment->fresh())
        ->status->toBe(OrderFulfillmentStatus::Selesai)
        ->catatan_status->toBe('Pesanan diselesaikan otomatis setelah masa konfirmasi penerimaan berakhir.')
        ->and($order->fresh()->status)->toBe('selesai');
});

test('local simulator accelerates the delivery confirmation window', function (): void {
    $this->travelTo('2026-11-17 12:00:00');
    config()->set('services.rajaongkir.simulation_auto_complete_minutes', 2);
    $buyer = User::factory()->warga()->create();
    $seller = User::factory()->pengelolaTps3r()->create();
    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'status' => 'dikirim',
        'shipping_source' => 'rajaongkir',
        'shipping_courier' => 'jne',
    ]);
    $fulfillment = OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $seller->id,
        'status' => OrderFulfillmentStatus::Dikirim,
        'tracking_number' => 'SIM123456789',
        'shipped_at' => now(),
    ]);

    $this->artisan('marketplace:simulate-delivery', ['order' => $order->nomor_pesanan])
        ->expectsOutputToContain('otomatis selesai dalam 2 menit')
        ->assertSuccessful();

    expect($fulfillment->fresh())
        ->courier_status->toBe('DELIVERED')
        ->delivered_at->format('Y-m-d H:i:s')->toBe('2026-11-17 12:00:00')
        ->auto_complete_at->format('Y-m-d H:i:s')->toBe('2026-11-17 12:02:00');
    expect($buyer->notifications()->first()?->data['event'])->toBe('courier_marked_delivered')
        ->and($seller->notifications()->first()?->data['event'])->toBe('courier_marked_delivered');

    $this->travelTo('2026-11-17 12:03:00');
    $this->artisan('marketplace:auto-complete-deliveries')->assertSuccessful();

    expect($fulfillment->fresh()->status)->toBe(OrderFulfillmentStatus::Selesai)
        ->and($order->fresh()->status)->toBe('selesai');
});
