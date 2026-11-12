<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

test('mobile multipart post uploads avatar and patch remove_avatar clears it', function () {
    Storage::fake('public');

    $user = User::factory()->warga()->create();
    Sanctum::actingAs($user);

    $uploadResponse = $this->post('/api/v1/auth/profile', [
        'name' => 'Warga Foto Mobile',
        'avatar' => UploadedFile::fake()->image('mobile-avatar.jpg', 240, 240),
    ], ['Accept' => 'application/json']);

    $uploadResponse->assertOk()
        ->assertJsonPath('data.name', 'Warga Foto Mobile');

    $storedPath = $user->fresh()->avatar;
    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);

    $removeResponse = $this->patchJson('/api/v1/auth/profile', [
        'remove_avatar' => true,
    ]);

    $removeResponse->assertOk()
        ->assertJsonPath('data.avatar', null)
        ->assertJsonPath('data.avatar_url', null);

    expect($user->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($storedPath);
});
