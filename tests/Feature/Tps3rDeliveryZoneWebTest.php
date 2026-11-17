<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rDeliveryZone;
use App\Models\User;

it('shows only the signed in tps3r delivery zones', function (): void {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($user)->create([
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
    ]);
    $ownedZone = Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Oro-Oro Ombo',
    ]);
    Tps3rDeliveryZone::factory()->create(['kelurahan' => 'Kelurahan TPS Lain']);

    $this->actingAs($user)
        ->get(route('dashboard.tps3r.delivery-zones.index'))
        ->assertOk()
        ->assertSee('Wilayah Pengiriman')
        ->assertSee($ownedZone->kelurahan)
        ->assertDontSee('Kelurahan TPS Lain');
});

it('lets a tps3r manager submit a delivery zone from the web', function (): void {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('dashboard.tps3r.delivery-zones.store'), [
            'kelurahan' => '  oro-oro ombo ',
            'kecamatan' => 'kartoharjo',
            'delivery_fee' => 7000,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('tps3r_delivery_zones', [
        'pengelola_tps3r_profile_id' => $profile->id,
        'kelurahan' => 'Oro-Oro Ombo',
        'kecamatan' => 'Kartoharjo',
        'delivery_fee' => 7000,
        'status' => Tps3rDeliveryZone::STATUS_PENDING,
    ]);
});

it('returns an edited approved zone to admin review', function (): void {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($user)->create();
    $deliveryZone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create([
        'delivery_fee' => 5000,
    ]);

    $this->actingAs($user)
        ->patch(route('dashboard.tps3r.delivery-zones.update', $deliveryZone), [
            'kelurahan' => $deliveryZone->kelurahan,
            'kecamatan' => $deliveryZone->kecamatan,
            'delivery_fee' => 8000,
            'is_active' => true,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $deliveryZone->refresh();

    expect($deliveryZone->delivery_fee)->toBe(8000.0)
        ->and($deliveryZone->status)->toBe(Tps3rDeliveryZone::STATUS_PENDING)
        ->and($deliveryZone->approved_by)->toBeNull()
        ->and($deliveryZone->approved_at)->toBeNull();
});

it('lets a tps3r manager pause an approved zone without another review', function (): void {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($user)->create();
    $deliveryZone = Tps3rDeliveryZone::factory()->approved()->for($profile, 'tps3rProfile')->create();

    $this->actingAs($user)
        ->patch(route('dashboard.tps3r.delivery-zones.update', $deliveryZone), [
            'is_active' => false,
        ])
        ->assertRedirect();

    $deliveryZone->refresh();

    expect($deliveryZone->status)->toBe(Tps3rDeliveryZone::STATUS_APPROVED)
        ->and($deliveryZone->is_active)->toBeFalse();
});

it('prevents a tps3r manager from editing another managers zone', function (): void {
    $owner = User::factory()->pengelolaTps3r()->create();
    $ownerProfile = PengelolaTps3rProfile::factory()->for($owner)->create();
    $otherManager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->for($otherManager)->create();
    $deliveryZone = Tps3rDeliveryZone::factory()->for($ownerProfile, 'tps3rProfile')->create();

    $this->actingAs($otherManager)
        ->patch(route('dashboard.tps3r.delivery-zones.update', $deliveryZone), [
            'delivery_fee' => 1000,
        ])
        ->assertForbidden();
});

it('lets an admin approve a delivery zone from the web', function (): void {
    $admin = User::factory()->adminDlh()->create();
    $deliveryZone = Tps3rDeliveryZone::factory()->create([
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.tps3r-delivery-zones.review', $deliveryZone), [
            'status' => Tps3rDeliveryZone::STATUS_APPROVED,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $deliveryZone->refresh();

    expect($deliveryZone->status)->toBe(Tps3rDeliveryZone::STATUS_APPROVED)
        ->and($deliveryZone->is_active)->toBeTrue()
        ->and($deliveryZone->approved_by)->toBe($admin->id)
        ->and($deliveryZone->approved_at)->not->toBeNull();
});

it('shows delivery zone submissions on the admin verification page', function (): void {
    $admin = User::factory()->adminDlh()->create();
    $manager = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->for($manager)->create([
        'nama_tps3r' => 'TPS3R Winongo Lestari',
    ]);
    Tps3rDeliveryZone::factory()->for($profile, 'tps3rProfile')->create([
        'kelurahan' => 'Nambangan Lor',
        'kecamatan' => 'Manguharjo',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard.admin.tps3r-delivery-zones.index', ['status' => 'pending']))
        ->assertOk()
        ->assertSee('Verifikasi Wilayah Pengiriman')
        ->assertSee('TPS3R Winongo Lestari')
        ->assertSee('Nambangan Lor');
});

it('requires an admin rejection reason on the web', function (): void {
    $admin = User::factory()->adminDlh()->create();
    $deliveryZone = Tps3rDeliveryZone::factory()->create();

    $this->actingAs($admin)
        ->from(route('dashboard.admin.tps3r-delivery-zones.index'))
        ->patch(route('dashboard.admin.tps3r-delivery-zones.review', $deliveryZone), [
            'status' => Tps3rDeliveryZone::STATUS_REJECTED,
        ])
        ->assertRedirect(route('dashboard.admin.tps3r-delivery-zones.index'))
        ->assertSessionHasErrors('rejection_reason');

    expect($deliveryZone->fresh()->status)->toBe(Tps3rDeliveryZone::STATUS_PENDING);
});

it('restricts web delivery zone management by role', function (): void {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)
        ->get(route('dashboard.tps3r.delivery-zones.index'))
        ->assertForbidden();
    $this->actingAs($warga)
        ->get(route('dashboard.admin.tps3r-delivery-zones.index'))
        ->assertForbidden();
});
