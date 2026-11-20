<?php

use App\Models\Complaint;
use App\Models\User;
use App\Models\WasteWeighing;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('warga can visit their dashboard with points and waste history', function () {
    $warga = User::factory()->warga()->create();
    $this->actingAs($warga);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Saldo Poin Reward')
        ->assertSee('Riwayat Penimbangan Sampah');
});

test('warga dashboard shows the total weight of all their weighings', function () {
    $warga = User::factory()->warga()->create();
    $otherWarga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    WasteWeighing::factory()->count(6)->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'pickup_schedule_id' => null,
        'berat_kg' => 2,
    ]);
    WasteWeighing::factory()->create([
        'warga_id' => $otherWarga->id,
        'petugas_id' => $petugas->id,
        'pickup_schedule_id' => null,
        'berat_kg' => 30,
    ]);

    $response = $this->actingAs($warga)->get(route('dashboard'));

    $response->assertViewHas('wargaData', fn (array $data) => $data['totalSampahKg'] === 12.0)
        ->assertSeeInOrder(['Sampah Disetor', '12,0']);
});

test('petugas lapangan can visit their dashboard with schedule and weighings', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $this->actingAs($petugas);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Wilayah Rute Tugas')
        ->assertSee('Timbangan Hari Ini');
});

test('warga with complaints can visit their dashboard without error', function () {
    $warga = User::factory()->warga()->create();
    Complaint::factory()->create([
        'pelapor_id' => $warga->id,
        'judul' => 'Sampah Menumpuk di Jl Pahlawan',
    ]);
    $this->actingAs($warga);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Sampah Menumpuk di Jl Pahlawan');
});
