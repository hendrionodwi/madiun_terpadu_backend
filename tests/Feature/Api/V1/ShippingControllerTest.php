<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Cache::flush();

    config()->set([
        'services.rajaongkir.enabled' => true,
        'services.rajaongkir.api_key' => 'test-api-key',
        'services.rajaongkir.base_url' => 'https://rajaongkir.test/api/v1',
        'services.rajaongkir.couriers' => 'jne:jnt',
        'services.rajaongkir.destination_cache_seconds' => 600,
        'services.rajaongkir.cost_cache_seconds' => 600,
    ]);
});

it('requires authentication to access shipping endpoints', function (): void {
    $this->getJson('/api/v1/shipping/destinations?search=Madiun')
        ->assertUnauthorized();
});

it('allows warga to search normalized destinations', function (): void {
    Http::fake([
        'https://rajaongkir.test/api/v1/destination/domestic-destination*' => Http::response([
            'data' => [[
                'id' => 6311,
                'label' => 'Kartoharjo, Kota Madiun, Jawa Timur, 63111',
                'province_name' => 'Jawa Timur',
                'city_name' => 'Kota Madiun',
                'district_name' => 'Kartoharjo',
                'subdistrict_name' => 'Klegen',
                'zip_code' => '63111',
            ]],
        ]),
    ]);
    Http::preventStrayRequests();
    Sanctum::actingAs(User::factory()->warga()->create());

    $this->getJson('/api/v1/shipping/destinations?search=Kartoharjo&limit=5')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.id', 6311)
        ->assertJsonPath('data.0.city_name', 'Kota Madiun');

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('key', 'test-api-key')
        && $request['search'] === 'Kartoharjo'
        && (int) $request['limit'] === 5);
});

it('validates destination search before calling the provider', function (): void {
    Http::preventStrayRequests();
    Sanctum::actingAs(User::factory()->warga()->create());

    $this->getJson('/api/v1/shipping/destinations?search=Ma')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('search');

    Http::assertNothingSent();
});

it('allows warga to request domestic shipping quotes', function (): void {
    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([
            'data' => [[
                'code' => 'jne',
                'name' => 'Jalur Nugraha Ekakurir',
                'service' => 'REG',
                'description' => 'Layanan Reguler',
                'cost' => 12000,
                'etd' => '2-3 day',
            ]],
        ]),
    ]);
    Http::preventStrayRequests();
    Sanctum::actingAs(User::factory()->warga()->create());

    $this->postJson('/api/v1/shipping/quotes', [
        'origin_id' => 6311,
        'destination_id' => 501,
        'weight_grams' => 2500,
        'couriers' => 'jne:jnt',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.courier_code', 'jne')
        ->assertJsonPath('data.0.cost', 12000);

    Http::assertSent(fn (Request $request): bool => $request->isForm()
        && (int) $request['origin'] === 6311
        && (int) $request['destination'] === 501
        && (int) $request['weight'] === 2500
        && $request['courier'] === 'jne:jnt');
});

it('returns a controlled response when the provider is unavailable', function (): void {
    Http::fake([
        'https://rajaongkir.test/api/v1/calculate/domestic-cost' => Http::response([], 503),
    ]);
    Http::preventStrayRequests();
    Sanctum::actingAs(User::factory()->warga()->create());

    $this->postJson('/api/v1/shipping/quotes', [
        'origin_id' => 6311,
        'destination_id' => 501,
        'weight_grams' => 1000,
    ])->assertServiceUnavailable()
        ->assertJson([
            'success' => false,
            'message' => 'Layanan perhitungan ongkir sedang tidak dapat dihubungi.',
        ]);
});
