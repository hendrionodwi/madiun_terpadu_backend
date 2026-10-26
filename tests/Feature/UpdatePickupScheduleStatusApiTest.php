<?php

use App\Models\PickupSchedule;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('petugas can progress their pickup schedule from scheduled to in progress', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'status' => 'terjadwal',
    ]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", ['status' => 'berlangsung'])
        ->assertOk()
        ->assertJsonPath('data.status', 'berlangsung');

    $this->assertDatabaseHas('pickup_schedules', [
        'id' => $schedule->id,
        'status' => 'berlangsung',
    ]);
});

test('petugas cannot update another petugas pickup schedule', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();
    $schedule = PickupSchedule::factory()->create(['petugas_id' => $otherPetugas->id]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", ['status' => 'berlangsung'])
        ->assertForbidden();
});

test('pickup schedule status must follow the allowed transition', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'status' => 'terjadwal',
    ]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", ['status' => 'selesai'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});
