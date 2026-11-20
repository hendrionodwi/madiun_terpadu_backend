<?php

use App\Models\MasterData;
use App\Models\User;
use App\Services\RajaOngkirService;

test('admin dlh can view global shipping settings', function () {
    config(['services.rajaongkir.couriers' => 'jne:jnt']);
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)
        ->get(route('dashboard.admin.shipping-settings.index'));

    $response
        ->assertOk()
        ->assertSee('Pengaturan Pengiriman')
        ->assertSee('JNE')
        ->assertSee('J&T Express')
        ->assertSee('Fallback .env');
});

test('admin dlh can update the global courier allowlist', function () {
    config(['services.rajaongkir.couriers' => 'pos']);
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->patch(route('dashboard.admin.shipping-settings.update'), [
            'couriers' => ['jne', 'sicepat'],
        ]);

    $response
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHas('status', 'Daftar kurir global berhasil diperbarui.');
    $this->assertDatabaseHas('master_data', [
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'jne',
        'is_active' => true,
    ]);
    $this->assertDatabaseHas('master_data', [
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'jnt',
        'is_active' => false,
    ]);
    expect(app(RajaOngkirService::class)->allowedCourierCodes())
        ->toBe(['jne', 'sicepat']);
});

test('admin dlh can add a courier to the global catalog', function () {
    config(['services.rajaongkir.couriers' => 'jne:jnt']);
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->post(route('dashboard.admin.shipping-settings.store'), [
            'code' => 'TIKI',
            'name' => 'TIKI',
            'description' => 'Layanan reguler TIKI melalui RajaOngkir.',
        ]);

    $response
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHas('status', 'Kurir baru berhasil ditambahkan dan diaktifkan.');
    $this->assertDatabaseHas('master_data', [
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'tiki',
        'name' => 'TIKI',
        'is_active' => true,
    ]);
    expect(app(RajaOngkirService::class)->allowedCourierCodes())
        ->toContain('jne', 'jnt', 'tiki');
});

test('admin dlh can remove a courier from the global catalog', function () {
    config(['services.rajaongkir.couriers' => 'jne:jnt:pos']);
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->delete(route('dashboard.admin.shipping-settings.destroy', 'pos'));

    $response
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHas('status', 'Kurir berhasil dihapus dari katalog.');
    $this->assertDatabaseMissing('master_data', [
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'pos',
    ]);
    expect(app(RajaOngkirService::class)->allowedCourierCodes())
        ->not->toContain('pos');
});

test('admin dlh cannot remove the last active courier', function () {
    $admin = User::factory()->adminDlh()->create();
    MasterData::factory()->create([
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'jne',
        'name' => 'JNE',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->delete(route('dashboard.admin.shipping-settings.destroy', 'jne'));

    $response
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHasErrors('couriers');
    $this->assertDatabaseHas('master_data', [
        'group' => RajaOngkirService::COURIER_SETTINGS_GROUP,
        'code' => 'jne',
        'is_active' => true,
    ]);
});

test('global shipping settings reject duplicate and malformed courier codes', function () {
    config(['services.rajaongkir.couriers' => 'jne:jnt']);
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->post(route('dashboard.admin.shipping-settings.store'), [
            'code' => 'JNE',
            'name' => 'JNE Duplikat',
        ])
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHasErrors('code');

    $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->post(route('dashboard.admin.shipping-settings.store'), [
            'code' => 'jne:evil',
            'name' => 'Kode Tidak Valid',
        ])
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHasErrors('code');
});

test('global shipping settings reject an empty or unsupported courier selection', function () {
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->patch(route('dashboard.admin.shipping-settings.update'), [
            'couriers' => [],
        ])
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHasErrors('couriers');

    $this->actingAs($admin)
        ->from(route('dashboard.admin.shipping-settings.index'))
        ->patch(route('dashboard.admin.shipping-settings.update'), [
            'couriers' => ['jne', 'unsupported'],
        ])
        ->assertRedirect(route('dashboard.admin.shipping-settings.index'))
        ->assertSessionHasErrors('couriers.1');

    expect(MasterData::query()
        ->where('group', RajaOngkirService::COURIER_SETTINGS_GROUP)
        ->exists())->toBeFalse();
});

test('non admin user is forbidden from global shipping settings', function () {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)
        ->get(route('dashboard.admin.shipping-settings.index'))
        ->assertForbidden();

    $this->actingAs($warga)
        ->patch(route('dashboard.admin.shipping-settings.update'), [
            'couriers' => ['jne'],
        ])
        ->assertForbidden();

    $this->actingAs($warga)
        ->post(route('dashboard.admin.shipping-settings.store'), [
            'code' => 'tiki',
            'name' => 'TIKI',
        ])
        ->assertForbidden();

    $this->actingAs($warga)
        ->delete(route('dashboard.admin.shipping-settings.destroy', 'jne'))
        ->assertForbidden();
});
