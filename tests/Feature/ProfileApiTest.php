<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('warga can update their mobile profile', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga);

    $this->patchJson('/api/v1/auth/profile', [
        'name' => 'Siti Aminah',
        'phone_number' => '081234567890',
        'alamat' => 'Jl. Pahlawan No. 1',
        'rt' => '01',
        'rw' => '02',
        'kelurahan' => 'Pandean',
        'kecamatan' => 'Taman',
    ])->assertOk()->assertJsonPath('data.name', 'Siti Aminah');

    $this->assertDatabaseHas('warga_profiles', ['user_id' => $warga->id, 'kecamatan' => 'Taman']);
});
