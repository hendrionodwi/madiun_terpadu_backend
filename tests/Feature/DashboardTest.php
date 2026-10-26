<?php

use App\Models\Complaint;
use App\Models\User;

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
