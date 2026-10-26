<?php

use App\Models\PointLedger;
use App\Models\User;
use App\Models\WargaProfile;
use Laravel\Sanctum\Sanctum;

test('warga can view their unified chronological point ledger (TRK-F-010)', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 500,
    ]);

    PointLedger::factory()->create([
        'warga_id' => $warga->id,
        'tipe' => 'kredit',
        'kategori' => 'setor_sampah',
        'jumlah_poin' => 600,
        'saldo_sebelumnya' => 0,
        'saldo_setelahnya' => 600,
        'deskripsi' => 'Setor botol plastik 3 kg',
    ]);

    PointLedger::factory()->create([
        'warga_id' => $warga->id,
        'tipe' => 'debit',
        'kategori' => 'bayar_retribusi',
        'jumlah_poin' => 100,
        'saldo_sebelumnya' => 600,
        'saldo_setelahnya' => 500,
        'deskripsi' => 'Bayar iuran retribusi Oktober 2026',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->getJson('/api/v1/points/ledger');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.saldo_saat_ini', 500)
        ->assertJsonPath('data.total_kredit', 600)
        ->assertJsonPath('data.total_debit', 100)
        ->assertJsonCount(2, 'data.mutations');
});

test('waste weighing automatically creates credit ledger entry (TRK-F-009 & TRK-F-010)', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 0]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $response = $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $warga->id,
        'jenis_sampah' => 'anorganik_plastik',
        'berat_kg' => 5.0,
        'poin_per_kg' => 100,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'kredit',
        'kategori' => 'setor_sampah',
        'jumlah_poin' => 500,
    ]);

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(500);
});

test('admin city-wide ledger summary matches the unfiltered mutation list', function () {
    $admin = User::factory()->adminDlh()->create();
    $firstWarga = User::factory()->warga()->create();
    $secondWarga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $firstWarga->id, 'saldo_poin' => 100]);
    WargaProfile::factory()->create(['user_id' => $secondWarga->id, 'saldo_poin' => 200]);
    PointLedger::factory()->create([
        'warga_id' => $firstWarga->id,
        'tipe' => 'kredit',
        'jumlah_poin' => 100,
    ]);
    PointLedger::factory()->create([
        'warga_id' => $secondWarga->id,
        'tipe' => 'kredit',
        'jumlah_poin' => 250,
    ]);
    PointLedger::factory()->create([
        'warga_id' => $secondWarga->id,
        'tipe' => 'debit',
        'jumlah_poin' => 50,
    ]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->getJson('/api/v1/points/ledger')
        ->assertOk()
        ->assertJsonPath('data.saldo_saat_ini', null)
        ->assertJsonPath('data.total_kredit', 350)
        ->assertJsonPath('data.total_debit', 50)
        ->assertJsonCount(3, 'data.mutations');
});
