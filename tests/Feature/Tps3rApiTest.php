<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('pengelola tps3r can record incoming waste deposit (TPS-F-002)', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    Sanctum::actingAs($pengelola, ['role:pengelola_tps3r']);

    $payload = [
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 350.5,
        'tanggal_setor' => '2026-10-15',
        'catatan' => 'Sampah daun dan sisa makanan kelurahan Pandean',
    ];

    $response = $this->postJson('/api/v1/tps3r/deposits', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'sumber_sampah' => 'armada_kelurahan',
                'jenis_sampah' => 'organik',
                'berat_kg' => 350.5,
            ],
        ]);

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'pengelola_id' => $pengelola->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 350.5,
    ]);
});

test('pengelola tps3r can view waste volume recap (TPS-F-003)', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'kapasitas_harian_kg' => 2000.0,
    ]);

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 400.0,
        'tanggal_setor' => '2026-10-10',
    ]);

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'jenis_sampah' => 'anorganik_daur_ulang',
        'berat_kg' => 150.0,
        'tanggal_setor' => '2026-10-10',
    ]);

    Sanctum::actingAs($pengelola, ['role:pengelola_tps3r']);

    $response = $this->getJson('/api/v1/tps3r/volume-recap?start_date=2026-10-01&end_date=2026-10-31');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'ringkasan' => [
                    'total_volume_kg' => 550.0,
                    'total_transaksi_setoran' => 2,
                    'kapasitas_harian_tps3r_kg' => 2000.0,
                ],
            ],
        ]);
});

test('warga cannot record tps3r deposit (forbidden role)', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson('/api/v1/tps3r/deposits', [
        'sumber_sampah' => 'warga_mandiri',
        'jenis_sampah' => 'organik',
        'berat_kg' => 10.0,
        'tanggal_setor' => '2026-10-15',
    ]);

    $response->assertStatus(403);
});

test('petugas lapangan submits deposit with status menunggu_konfirmasi and pengelola verifies it', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profile->id,
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 250.0,
        'tanggal_setor' => '2026-10-15',
        'catatan' => 'Muatan truk dari rute Kartoharjo',
    ]);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'menunggu_konfirmasi',
                'berat_kg' => 250.0,
            ],
        ]);

    $depositId = $response->json('data.id');

    // Pengelola TPS3R verifies the deposit
    Sanctum::actingAs($pengelola, ['role:pengelola_tps3r']);

    $verifyResponse = $this->patchJson("/api/v1/tps3r/deposits/{$depositId}/verify", [
        'status' => 'diterima',
        'berat_konfirmasi_kg' => 248.5,
        'catatan_verifikasi' => 'Timbangan pos gerbang 248.5 kg, kondisi baik',
    ]);

    $verifyResponse->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'diterima',
                'berat_konfirmasi_kg' => 248.5,
                'catatan_verifikasi' => 'Timbangan pos gerbang 248.5 kg, kondisi baik',
            ],
        ]);

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'id' => $depositId,
        'status' => 'diterima',
        'berat_konfirmasi_kg' => 248.5,
    ]);
});

test('users can fetch active master data categories with pillar', function () {
    $response = $this->getJson('/api/v1/master-data?group=waste_category');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'group', 'code', 'name', 'pillar'],
            ],
        ]);
});

test('tps3r deposit accepts multi-category sorted weights', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $pengelola->id]);

    Sanctum::actingAs($pengelola, ['role:pengelola_tps3r']);

    $response = $this->postJson('/api/v1/tps3r/deposits', [
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'anorganik_daur_ulang',
        'berat_kg' => 70.0,
        'berat_organik_pilah_kg' => 35.0,
        'berat_anorganik_pilah_kg' => 25.0,
        'berat_residu_pilah_kg' => 10.0,
        'tanggal_setor' => '2026-10-17',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('tps3r_waste_deposits', [
        'berat_organik_pilah_kg' => 35.0,
        'berat_anorganik_pilah_kg' => 25.0,
        'berat_residu_pilah_kg' => 10.0,
    ]);
});
