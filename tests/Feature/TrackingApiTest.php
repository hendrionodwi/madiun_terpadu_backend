<?php

use App\Models\MasterData;
use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\WargaProfile;
use Laravel\Sanctum\Sanctum;

test('petugas can create pickup schedule (TRK-F-001)', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $payload = [
        'id_wilayah_rute' => 'RUTE-KART-01',
        'nama_wilayah_rute' => 'Rute Kartoharjo Timur',
        'tanggal_penjemputan' => '2026-10-15',
        'waktu_mulai' => '07:30',
        'waktu_selesai' => '10:30',
        'keterangan' => 'Penjemputan botol plastik dan kardus',
    ];

    $response = $this->postJson('/api/v1/tracking/schedules', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'id_wilayah_rute' => 'RUTE-KART-01',
                'status' => 'terjadwal',
            ],
        ]);

    $this->assertDatabaseHas('pickup_schedules', [
        'id_wilayah_rute' => 'RUTE-KART-01',
        'petugas_id' => $petugas->id,
    ]);
});

test('warga can list their own pickup schedules (TRK-F-001)', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    $otherWarga = User::factory()->warga()->create();

    $ownSchedules = PickupSchedule::factory()->count(3)->create([
        'petugas_id' => $petugas->id,
        'warga_id' => $warga->id,
    ]);
    PickupSchedule::factory()->count(2)->create([
        'petugas_id' => $petugas->id,
        'warga_id' => $otherWarga->id,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->getJson('/api/v1/tracking/schedules');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta' => ['current_page', 'total'],
        ]);

    expect(count($response->json('data')))->toBe(3);
});

test('petugas only sees their own pickup schedules', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();
    $ownSchedule = PickupSchedule::factory()->create(['petugas_id' => $petugas->id]);
    PickupSchedule::factory()->create(['petugas_id' => $otherPetugas->id]);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->getJson('/api/v1/tracking/schedules')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownSchedule->id);
});

test('petugas can input waste weighing and citizen points are automatically added (TRK-F-006 & TRK-F-009)', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    $wargaProfile = WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 200,
    ]);

    $schedule = PickupSchedule::factory()->create(['petugas_id' => $petugas->id]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $payload = [
        'warga_id' => $warga->id,
        'pickup_schedule_id' => $schedule->id,
        'jenis_sampah' => 'anorganik_plastik',
        'berat_kg' => 4.5,
        'poin_per_kg' => 100,
        'catatan' => 'Botol PET bersih dan kering',
    ];

    $response = $this->postJson('/api/v1/tracking/weighings', $payload);

    // 4.5 kg * 100 poin/kg = 450 poin. Saldo awal 200 + 450 = 650 poin.
    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'poin_didapat' => 450,
                'total_saldo_poin_warga' => 650,
            ],
        ]);

    $this->assertDatabaseHas('waste_weighings', [
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'berat_kg' => 4.5,
        'poin_didapat' => 450,
    ]);

    expect($wargaProfile->fresh()->saldo_poin)->toBe(650);
    expect($schedule->fresh()->status)->toBe('selesai');
});

test('petugas can input multi-item waste weighing with custom categories from master data', function () {
    MasterData::create([
        'group' => 'waste_category',
        'code' => 'kardus_bekas',
        'name' => 'Kardus Bekas',
        'numeric_value' => 150,
        'unit' => 'Poin / Kg',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 0,
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $payload = [
        'warga_id' => $warga->id,
        'items' => [
            [
                'jenis_sampah' => 'kardus_bekas',
                'berat_kg' => 10,
                'catatan' => 'Kardus tebal bersih',
            ],
        ],
    ];

    $response = $this->postJson('/api/v1/tracking/weighings', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'poin_didapat' => 1500,
                'total_saldo_poin_warga' => 1500,
            ],
        ]);

    $this->assertDatabaseHas('waste_weighings', [
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'jenis_sampah' => 'kardus_bekas',
        'berat_kg' => 10,
        'poin_didapat' => 1500,
    ]);
});

test('warga cannot record waste weighing (forbidden role)', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $warga->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 2.0,
    ]);

    $response->assertStatus(403);
});

test('warga can schedule a waste pickup by selecting available route and time slot', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'alamat' => 'Jl. Salak No. 12, Pandean',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $payload = [
        'id_wilayah_rute' => 'RUTE-TAMAN-01',
        'nama_wilayah_rute' => 'Rute Taman Indah - Pandean',
        'tanggal_penjemputan' => now()->addDay()->toDateString(),
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '10:00',
        'jenis_sampah' => 'anorganik_plastik',
        'alamat_penjemputan' => 'Jl. Salak No. 12, depan pagar',
        'keterangan' => 'Ada 2 karung botol plastik siap jemput',
    ];

    $response = $this->postJson('/api/v1/tracking/schedules', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'id_wilayah_rute' => 'RUTE-TAMAN-01',
                'warga_id' => $warga->id,
                'jenis_sampah' => 'anorganik_plastik',
                'status' => 'terjadwal',
            ],
        ]);

    $this->assertDatabaseHas('pickup_schedules', [
        'warga_id' => $warga->id,
        'id_wilayah_rute' => 'RUTE-TAMAN-01',
        'jenis_sampah' => 'anorganik_plastik',
    ]);
});

test('authenticated users can list operational routes and schedules', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->getJson('/api/v1/tracking/routes');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
});

test('petugas can filter routes to only their assigned routes via my_routes or petugas_id', function () {
    $petugasA = User::factory()->petugasLapangan()->create();
    $petugasB = User::factory()->petugasLapangan()->create();

    $routeA = RouteAssignment::factory()->create([
        'petugas_id' => $petugasA->id,
        'route_code' => 'RUTE-TEST-01',
        'is_active' => true,
    ]);
    $routeB = RouteAssignment::factory()->create([
        'petugas_id' => $petugasB->id,
        'route_code' => 'RUTE-TEST-02',
        'is_active' => true,
    ]);

    Sanctum::actingAs($petugasA, ['role:petugas_lapangan']);

    // With my_routes=true
    $responseMy = $this->getJson('/api/v1/tracking/routes?my_routes=true');
    $responseMy->assertOk();
    $dataMy = $responseMy->json('data');
    expect(collect($dataMy)->pluck('route_code'))->toContain('RUTE-TEST-01')
        ->and(collect($dataMy)->pluck('route_code'))->not->toContain('RUTE-TEST-02');

    // With explicit petugas_id
    $responseFiltered = $this->getJson('/api/v1/tracking/routes?petugas_id='.$petugasB->id);
    $responseFiltered->assertOk();
    $dataFiltered = $responseFiltered->json('data');
    expect(collect($dataFiltered)->pluck('route_code'))->toContain('RUTE-TEST-02')
        ->and(collect($dataFiltered)->pluck('route_code'))->not->toContain('RUTE-TEST-01');
});

test('petugas lapangan can renew route session and reset pickup points back to terjadwal', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $schedules = PickupSchedule::factory()->count(3)->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => 'RUTE-KART-01',
        'tanggal_penjemputan' => now()->toDateString(),
        'status' => 'selesai',
    ]);

    $response = $this->postJson('/api/v1/tracking/routes/RUTE-KART-01/renew-session');

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $schedules->each(function ($sched) {
        expect($sched->fresh()->status)->toBe('terjadwal');
    });
});
