<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('petugas can search active warga by name or phone number', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $matchingWarga = User::factory()->warga()->create([
        'name' => 'Razen Madiun',
        'phone_number' => '081234567890',
    ]);
    User::factory()->warga()->create(['name' => 'Warga Lain']);
    User::factory()->petugasLapangan()->create(['name' => 'Razen Petugas']);
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->getJson('/api/v1/tracking/warga?search=Razen')
        ->assertOk()
        ->assertJsonPath('data.0.id', $matchingWarga->id)
        ->assertJsonPath('data.0.name', 'Razen Madiun')
        ->assertJsonMissing(['name' => 'Razen Petugas']);
});

test('petugas must provide at least two search characters', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->getJson('/api/v1/tracking/warga?search=R')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['search']);
});

test('warga cannot search the warga directory', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $this->getJson('/api/v1/tracking/warga?search=Ra')
        ->assertForbidden();
});
