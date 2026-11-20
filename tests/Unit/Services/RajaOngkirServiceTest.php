<?php

use App\Exceptions\RajaOngkirException;
use App\Services\RajaOngkirService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config()->set([
        'services.rajaongkir.enabled' => true,
        'services.rajaongkir.api_key' => 'test-api-key',
        'services.rajaongkir.base_url' => 'https://rajaongkir.test/api/v1',
        'services.rajaongkir.couriers' => 'jne:jnt',
        'services.rajaongkir.connect_timeout' => 1,
        'services.rajaongkir.timeout' => 2,
        'services.rajaongkir.destination_cache_seconds' => 600,
        'services.rajaongkir.cost_cache_seconds' => 600,
    ]);
});

it('returns normalized destinations for a valid search', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/destination/domestic-destination*' => Http::response([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => [
                [
                    'id' => 501,
                    'label' => 'Kartoharjo, Kota Madiun, Jawa Timur 63117',
                    'province_name' => 'Jawa Timur',
                    'city_name' => 'Kota Madiun',
                    'district_name' => 'Kartoharjo',
                    'subdistrict_name' => 'Kartoharjo',
                    'zip_code' => '63117',
                ],
            ],
        ]),
    ]);

    $destinations = (new RajaOngkirService)->searchDomesticDestinations('Kartoharjo', 10, 0);

    expect($destinations)->toBe([
        [
            'id' => 501,
            'label' => 'Kartoharjo, Kota Madiun, Jawa Timur 63117',
            'province_name' => 'Jawa Timur',
            'city_name' => 'Kota Madiun',
            'district_name' => 'Kartoharjo',
            'subdistrict_name' => 'Kartoharjo',
            'zip_code' => '63117',
        ],
    ]);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && str_starts_with($request->url(), 'https://rajaongkir.test/api/v1/destination/domestic-destination?')
        && $request->hasHeader('key', 'test-api-key')
        && $request['search'] === 'Kartoharjo'
        && (int) $request['limit'] === 10
        && (int) $request['offset'] === 0);
});

it('caches repeated destination searches to protect the provider quota', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/destination/domestic-destination*' => Http::response([
            'data' => [
                [
                    'id' => 502,
                    'label' => 'Taman, Kota Madiun, Jawa Timur 63131',
                ],
            ],
        ]),
    ]);
    $service = new RajaOngkirService;

    $service->searchDomesticDestinations('Taman', 5, 0);
    $service->searchDomesticDestinations('Taman', 5, 0);

    Http::assertSentCount(1);
});

it('returns normalized domestic shipping costs', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => [
                [
                    'name' => 'Jalur Nugraha Ekakurir',
                    'code' => 'jne',
                    'service' => 'REG',
                    'description' => 'Layanan Reguler',
                    'cost' => 18000,
                    'etd' => '2-3 day',
                ],
            ],
        ]),
    ]);

    $costs = (new RajaOngkirService)->calculateDomesticCosts(501, 1200, 2500, 'jne:jnt');

    expect($costs)->toBe([
        [
            'courier_code' => 'jne',
            'courier_name' => 'Jalur Nugraha Ekakurir',
            'service' => 'REG',
            'description' => 'Layanan Reguler',
            'cost' => 18000,
            'etd' => '2-3 day',
        ],
    ]);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->url() === 'https://rajaongkir.test/api/v1/calculate/domestic-cost'
        && $request->hasHeader('key', 'test-api-key')
        && (int) $request['origin'] === 501
        && (int) $request['destination'] === 1200
        && (int) $request['weight'] === 2500
        && $request['courier'] === 'jne:jnt');
});

it('rejects couriers outside the configured allowlist without contacting the provider', function (): void {
    Http::preventStrayRequests();
    Http::fake([]);

    expect(fn () => (new RajaOngkirService)->calculateDomesticCosts(501, 1200, 2500, 'pos'))
        ->toThrow(InvalidArgumentException::class, 'Kurir pengiriman tidak valid.');
    Http::assertNothingSent();
});

it('returns normalized waybill tracking data', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/track/waybill*' => Http::response([
            'meta' => ['code' => 200, 'status' => 'success'],
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
                    'manifest_description' => 'Paket diterima oleh Siti Aminah',
                ]],
            ],
        ]),
    ]);

    $tracking = (new RajaOngkirService)->trackWaybill('JNE123456789', 'JNE', '56789');

    expect($tracking)->toBe([
        'delivered' => true,
        'status' => 'DELIVERED',
        'description' => 'Paket diterima oleh Siti Aminah',
        'receiver' => 'Siti Aminah',
        'delivered_at' => '2026-11-17 10:15:00',
    ]);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && str_starts_with($request->url(), 'https://rajaongkir.test/api/v1/track/waybill?')
        && $request->hasHeader('key', 'test-api-key')
        && str_contains($request->url(), 'awb=JNE123456789')
        && str_contains($request->url(), 'courier=jne')
        && str_contains($request->url(), 'last_phone_number=56789'));
});

it('does not contact RajaOngkir when the integration is disabled', function (): void {
    config()->set('services.rajaongkir.enabled', false);
    Http::preventStrayRequests();
    Http::fake([]);

    expect(fn () => (new RajaOngkirService)->searchDomesticDestinations('Madiun'))
        ->toThrow(RajaOngkirException::class, 'Integrasi ongkir belum diaktifkan');
    Http::assertNothingSent();
});

it('reports an unavailable service for an upstream error', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([
            'meta' => ['code' => 503, 'status' => 'error'],
        ], 503),
    ]);

    expect(fn () => (new RajaOngkirService)->calculateDomesticCosts(501, 1200, 1000, 'jne'))
        ->toThrow(RajaOngkirException::class, 'Layanan perhitungan ongkir sedang tidak dapat dihubungi.');
    Http::assertSentCount(1);
});

it('rejects a malformed provider response', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://rajaongkir.test/api/v1/destination/domestic-destination*' => Http::response([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => null,
        ]),
    ]);

    expect(fn () => (new RajaOngkirService)->searchDomesticDestinations('Manguharjo'))
        ->toThrow(RajaOngkirException::class, 'Layanan ongkir mengembalikan data yang tidak valid.');
    Http::assertSentCount(1);
});
