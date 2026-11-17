<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function createTps3rDeliveryZoneOwner(): array
{
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($user)->create();

    return [$user, $profile];
}

it('allows a tps3r manager to submit a delivery zone for review', function (): void {
    [$user, $profile] = createTps3rDeliveryZoneOwner();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/tps3r/delivery-zones', [
        'kelurahan' => '  oro-oro ombo ',
        'kecamatan' => 'kartoharjo',
        'delivery_fee' => 7000,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.kelurahan', 'Oro-Oro Ombo')
        ->assertJsonPath('data.kecamatan', 'Kartoharjo')
        ->assertJsonPath('data.delivery_fee', 7000)
        ->assertJsonPath('data.status', Tps3rDeliveryZone::STATUS_PENDING);

    $this->assertDatabaseHas('tps3r_delivery_zones', [
        'pengelola_tps3r_profile_id' => $profile->id,
        'kelurahan' => 'Oro-Oro Ombo',
        'kecamatan' => 'Kartoharjo',
        'status' => Tps3rDeliveryZone::STATUS_PENDING,
    ]);
});

it('rejects duplicate zones for the same tps3r', function (): void {
    [$user, $profile] = createTps3rDeliveryZoneOwner();
    Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Klegen',
        'kecamatan' => 'Kartoharjo',
    ]);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/tps3r/delivery-zones', [
        'kelurahan' => 'Klegen',
        'kecamatan' => 'Kartoharjo',
        'delivery_fee' => 5000,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('kelurahan');
});

it('prevents another tps3r manager from changing or deleting a zone', function (): void {
    [, $ownerProfile] = createTps3rDeliveryZoneOwner();
    [$otherUser] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->for($ownerProfile, 'tps3rProfile')->create();
    Sanctum::actingAs($otherUser);

    $this->patchJson("/api/v1/tps3r/delivery-zones/{$deliveryZone->public_id}", [
        'delivery_fee' => 9000,
    ])->assertForbidden();

    $this->deleteJson("/api/v1/tps3r/delivery-zones/{$deliveryZone->public_id}")
        ->assertForbidden();
});

it('requires another review when an approved zone or fee changes', function (): void {
    [$user, $profile] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'delivery_fee' => 5000,
    ]);
    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/tps3r/delivery-zones/{$deliveryZone->public_id}", [
        'delivery_fee' => 7500,
    ])->assertOk()
        ->assertJsonPath('data.delivery_fee', 7500)
        ->assertJsonPath('data.status', Tps3rDeliveryZone::STATUS_PENDING);

    $deliveryZone->refresh();

    expect($deliveryZone->status)->toBe(Tps3rDeliveryZone::STATUS_PENDING)
        ->and($deliveryZone->approved_by)->toBeNull()
        ->and($deliveryZone->approved_at)->toBeNull();
});

it('allows an approved zone to be deactivated without another review', function (): void {
    [$user, $profile] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create();
    Sanctum::actingAs($user);

    $this->patchJson("/api/v1/tps3r/delivery-zones/{$deliveryZone->public_id}", [
        'is_active' => false,
    ])->assertOk()
        ->assertJsonPath('data.status', Tps3rDeliveryZone::STATUS_APPROVED)
        ->assertJsonPath('data.is_active', false);
});

it('lets an admin approve a zone and exposes it through the public catalog', function (): void {
    [, $profile] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'is_active' => false,
    ]);
    $admin = User::factory()->adminDlh()->create();
    Sanctum::actingAs($admin);

    $this->patchJson("/api/v1/admin/tps3r-delivery-zones/{$deliveryZone->public_id}/review", [
        'status' => Tps3rDeliveryZone::STATUS_APPROVED,
    ])->assertOk()
        ->assertJsonPath('data.status', Tps3rDeliveryZone::STATUS_APPROVED)
        ->assertJsonPath('data.is_active', true);

    $deliveryZone->refresh();

    expect($deliveryZone->approved_by)->toBe($admin->id)
        ->and($deliveryZone->approved_at)->not->toBeNull();

    $this->getJson("/api/v1/marketplace/stores/{$profile->public_id}/delivery-zones")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $deliveryZone->public_id);
});

it('does not expose pending rejected or inactive zones publicly', function (): void {
    [, $profile] = createTps3rDeliveryZoneOwner();
    Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Klegen',
        'kecamatan' => 'Kartoharjo',
    ]);
    Tps3rDeliveryZone::factory()->rejected()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Pandean',
        'kecamatan' => 'Taman',
    ]);
    Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Nambangan Lor',
        'kecamatan' => 'Manguharjo',
        'is_active' => false,
    ]);

    $this->getJson("/api/v1/marketplace/stores/{$profile->public_id}/delivery-zones")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('hides internal review data from public visitors', function (): void {
    [, $profile] = createTps3rDeliveryZoneOwner();
    Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create();

    $this->getJson("/api/v1/marketplace/stores/{$profile->public_id}/delivery-zones")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissingPath('data.0.status')
        ->assertJsonMissingPath('data.0.rejection_reason');
});

it('requires a rejection reason from the admin', function (): void {
    [, $profile] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create();
    Sanctum::actingAs(User::factory()->adminDlh()->create());

    $this->patchJson("/api/v1/admin/tps3r-delivery-zones/{$deliveryZone->public_id}/review", [
        'status' => Tps3rDeliveryZone::STATUS_REJECTED,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('rejection_reason');
});

it('keeps approved zones as auditable records instead of deleting them', function (): void {
    [$user, $profile] = createTps3rDeliveryZoneOwner();
    $deliveryZone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/tps3r/delivery-zones/{$deliveryZone->public_id}")
        ->assertConflict();

    $this->assertModelExists($deliveryZone);
});

it('restricts delivery zone management to its intended roles', function (): void {
    Sanctum::actingAs(User::factory()->warga()->create());

    $this->getJson('/api/v1/tps3r/delivery-zones')->assertForbidden();
    $this->getJson('/api/v1/admin/tps3r-delivery-zones')->assertForbidden();
});
