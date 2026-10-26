<?php

use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteWeighing;
use Illuminate\Support\Carbon;

test('warga can create pickup schedule with multiple categories', function () {
    $warga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $tomorrow = now()->addDays(2)->toDateString();

    $response = $this->actingAs($warga)->postJson('/api/v1/tracking/schedules', [
        'id_wilayah_rute' => 'RUTE-TEST-01',
        'nama_wilayah_rute' => 'Rute Kartoharjo',
        'kategori_sampah' => ['organik', 'anorganik_plastik', 'anorganik_kertas'],
        'tanggal_penjemputan' => $tomorrow,
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '10:00',
        'keterangan' => 'Campuran 3 karung terpilah',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'warga_id' => $warga->id,
                'jenis_sampah' => 'organik, anorganik_plastik, anorganik_kertas',
                'status' => 'terjadwal',
            ],
        ]);
});

test('warga booking today past cut-off time is rejected', function () {
    $warga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $today = now()->toDateString();

    RouteAssignment::factory()->create([
        'route_code' => 'RUTE-EARLY-01',
        'petugas_id' => $petugas->id,
        'start_time' => '07:00',
        'end_time' => '11:00',
    ]);

    // Set now to 06:30 (past the 06:00 cut-off for a 07:00 route)
    Carbon::setTestNow(Carbon::parse($today.' 06:30:00'));

    $response = $this->actingAs($warga)->postJson('/api/v1/tracking/schedules', [
        'id_wilayah_rute' => 'RUTE-EARLY-01',
        'nama_wilayah_rute' => 'Rute Pagi Buta',
        'tanggal_penjemputan' => $today,
        'waktu_mulai' => '07:00',
        'waktu_selesai' => '09:00',
        'jenis_sampah' => 'organik',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tanggal_penjemputan']);

    Carbon::setTestNow(); // reset
});

test('warga can update and cancel their own scheduled pickup', function () {
    $warga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $futureDate = now()->addDays(3)->toDateString();

    $schedule = PickupSchedule::factory()->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => 'RUTE-TEST-02',
        'tanggal_penjemputan' => $futureDate,
        'status' => 'terjadwal',
        'jenis_sampah' => 'organik',
    ]);

    // Update schedule
    $patchResponse = $this->actingAs($warga)->patchJson("/api/v1/tracking/schedules/{$schedule->id}", [
        'kategori_sampah' => ['organik', 'anorganik_plastik'],
        'keterangan' => 'Tambahan kardus dan plastik',
    ]);

    $patchResponse->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
                'jenis_sampah' => 'organik, anorganik_plastik',
                'keterangan' => 'Tambahan kardus dan plastik',
            ],
        ]);

    // Delete schedule
    $deleteResponse = $this->actingAs($warga)->deleteJson("/api/v1/tracking/schedules/{$schedule->id}");
    $deleteResponse->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('pickup_schedules', ['id' => $schedule->id]);
});

test('petugas can record multi-item waste weighing for citizen', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();

    MasterData::updateOrCreate(
        ['group' => 'waste_category', 'code' => 'organik'],
        ['name' => 'Organik', 'numeric_value' => 50, 'is_active' => true]
    );
    MasterData::updateOrCreate(
        ['group' => 'waste_category', 'code' => 'anorganik_plastik'],
        ['name' => 'Plastik', 'numeric_value' => 150, 'is_active' => true]
    );

    $response = $this->actingAs($petugas)->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $warga->id,
        'items' => [
            [
                'jenis_sampah' => 'organik',
                'berat_kg' => 2.0, // 2 * 50 = 100 poin
                'foto_bukti' => 'evidence_organik.jpg',
            ],
            [
                'jenis_sampah' => 'anorganik_plastik',
                'berat_kg' => 4.0, // 4 * 150 = 600 poin
                'foto_bukti' => 'evidence_plastik.jpg',
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'poin_didapat' => 700,
                'total_saldo_poin_warga' => 700,
            ],
        ]);

    $this->assertDatabaseCount('waste_weighings', 2);
});

test('tps3r can inspect waste deposit details including citizen weighings and photos', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();

    $deposit = Tps3rWasteDeposit::create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'petugas_id' => $petugas->id,
        'nomor_resi' => 'DEP-TEST-001',
        'route_code' => 'RUTE-TEST-01',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 25.5,
        'status' => 'menunggu_konfirmasi',
        'tanggal_setor' => now()->toDateString(),
    ]);

    $weighing = WasteWeighing::create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 12.0,
        'poin_per_kg' => 50,
        'poin_didapat' => 600,
        'foto_bukti' => 'https://example.com/photo1.jpg',
        'tps3r_waste_deposit_id' => $deposit->id,
    ]);

    $response = $this->actingAs($pengelola)->getJson("/api/v1/tps3r/deposits/{$deposit->id}");

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $deposit->id,
                'nomor_resi' => 'DEP-TEST-001',
                'weighings' => [
                    [
                        'id' => $weighing->id,
                        'berat_kg' => '12.00',
                        'foto_bukti' => 'https://example.com/photo1.jpg',
                    ],
                ],
            ],
        ]);
});
