<?php

use App\Models\RouteAssignment;
use App\Models\RouteSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('petugas can view route session status for today', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-01',
        'is_active' => true,
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->getJson('/api/v1/tracking/routes/'.$route->route_code.'/session');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'route_code' => 'RUTE-TEST-01',
                'status' => 'terjadwal',
            ],
        ]);
});

test('petugas can start route session from depo', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-02',
        'start_location' => 'Depo DLH Madiun',
        'end_facility' => 'TPS3R Sukosari',
        'is_active' => true,
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->postJson('/api/v1/tracking/routes/'.$route->route_code.'/session/start', [
        'catatan' => 'Armada berangkat tepat waktu',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'route_code' => 'RUTE-TEST-02',
                'status' => 'berlangsung',
                'start_location' => 'Depo DLH Madiun',
            ],
        ]);

    $this->assertDatabaseHas('route_sessions', [
        'route_code' => 'RUTE-TEST-02',
        'petugas_id' => $petugas->id,
        'status' => 'berlangsung',
        'catatan' => 'Armada berangkat tepat waktu',
    ]);
});

test('petugas can finish route session at TPS3R', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-03',
        'is_active' => true,
    ]);

    // Start session first
    $session = RouteSession::create([
        'route_assignment_id' => $route->id,
        'petugas_id' => $petugas->id,
        'route_code' => $route->route_code,
        'tanggal' => now()->toDateString(),
        'status' => 'berlangsung',
        'waktu_mulai' => now()->subHours(2)->toTimeString(),
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->postJson('/api/v1/tracking/routes/'.$route->route_code.'/session/finish', [
        'catatan' => 'Tiba di TPS3R dengan selamat',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'route_code' => 'RUTE-TEST-03',
                'status' => 'selesai',
            ],
        ]);

    $this->assertDatabaseHas('route_sessions', [
        'id' => $session->id,
        'status' => 'selesai',
    ]);
});

test('warga cannot start or finish route session', function () {
    $warga = User::factory()->warga()->create();
    $route = RouteAssignment::factory()->create([
        'route_code' => 'RUTE-TEST-04',
        'is_active' => true,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $responseStart = $this->postJson('/api/v1/tracking/routes/'.$route->route_code.'/session/start');
    $responseStart->assertForbidden();

    $responseFinish = $this->postJson('/api/v1/tracking/routes/'.$route->route_code.'/session/finish');
    $responseFinish->assertForbidden();
});

test('index routes includes today_session relationship', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-05',
        'is_active' => true,
    ]);

    RouteSession::create([
        'route_assignment_id' => $route->id,
        'petugas_id' => $petugas->id,
        'route_code' => $route->route_code,
        'tanggal' => now()->toDateString(),
        'status' => 'berlangsung',
        'waktu_mulai' => '08:00:00',
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->getJson('/api/v1/tracking/routes');

    $response->assertOk();
    $routeItem = collect($response->json('data'))->firstWhere('route_code', 'RUTE-TEST-05');
    expect($routeItem)->not->toBeNull();
    expect($routeItem['today_session'])->not->toBeNull();
    expect($routeItem['today_session']['status'])->toBe('berlangsung');
});
