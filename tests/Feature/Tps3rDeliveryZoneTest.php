<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\WasteProduct;

it('stores approved local delivery zones under a tps3r profile', function (): void {
    $profile = PengelolaTps3rProfile::factory()->create([
        'rajaongkir_origin_id' => 6311,
    ]);
    $zone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Klegen',
        'kecamatan' => 'Kartoharjo',
        'delivery_fee' => 7000,
    ]);

    expect($zone->isApproved())->toBeTrue()
        ->and($zone->getRouteKeyName())->toBe('public_id')
        ->and($zone->delivery_fee)->toBe(7000.0)
        ->and($profile->deliveryZones()->first()?->is($zone))->toBeTrue()
        ->and($profile->rajaongkir_origin_id)->toBe(6311);
});

it('only returns active approved delivery zones', function (): void {
    $profile = PengelolaTps3rProfile::factory()->create();
    $approved = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Klegen',
        'kecamatan' => 'Kartoharjo',
    ]);
    Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Pandean',
        'kecamatan' => 'Taman',
    ]);
    Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Nambangan Lor',
        'kecamatan' => 'Manguharjo',
        'is_active' => false,
    ]);

    $zones = Tps3rDeliveryZone::query()->approved()->get();

    expect($zones)->toHaveCount(1)
        ->and($zones->first()?->is($approved))->toBeTrue();
});

it('stores product shipping weight in grams', function (): void {
    $product = WasteProduct::factory()->create(['weight_grams' => 5000]);

    expect($product->weight_grams)->toBe(5000);
});
