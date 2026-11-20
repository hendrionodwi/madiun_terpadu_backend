<?php

use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\RouteSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('petugas can progress their pickup schedule from scheduled to in progress', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-STATUS-API-01',
    ]);
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'status' => 'terjadwal',
    ]);
    RouteSession::create([
        'route_assignment_id' => $route->id,
        'petugas_id' => $petugas->id,
        'route_code' => $route->route_code,
        'tanggal' => now()->toDateString(),
        'status' => 'berlangsung',
        'waktu_mulai' => now()->format('H:i'),
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

test('petugas cannot reject a pickup schedule before starting route from depot', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'status' => 'terjadwal',
    ]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'ditolak',
        'keterangan' => 'Akses jalan sempit',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_session']);
});

test('petugas can reject a pickup schedule with reason after starting route session', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TOLAK-01',
    ]);
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'status' => 'terjadwal',
    ]);
    RouteSession::create([
        'route_assignment_id' => $route->id,
        'petugas_id' => $petugas->id,
        'route_code' => $route->route_code,
        'tanggal' => now()->toDateString(),
        'status' => 'berlangsung',
        'waktu_mulai' => now()->format('H:i'),
    ]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'ditolak',
        'keterangan' => 'Akses jalan sempit / terhalang material proyek warga',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', 'ditolak')
        ->assertJsonPath('data.keterangan', 'Akses jalan sempit / terhalang material proyek warga')
        ->assertJsonPath('message', 'Jadwal penjemputan berhasil ditolak.');

    $this->assertDatabaseHas('pickup_schedules', [
        'id' => $schedule->id,
        'status' => 'ditolak',
        'keterangan' => 'Akses jalan sempit / terhalang material proyek warga',
    ]);
});

test('updating schedule to its existing status returns successful idempotent response', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'status' => 'ditolak',
    ]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'ditolak',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', 'ditolak')
        ->assertJsonPath('message', 'Jadwal penjemputan sudah berstatus ditolak.');
});
