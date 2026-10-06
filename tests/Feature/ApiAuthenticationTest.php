<?php

use App\Enums\UserRole;
use App\Models\AdminDlhProfile;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

test('all 5 roles can successfully login via API (AUT-F-002)', function (UserRole $role, string $profileModel, string $relation) {
    $user = User::factory()->state(['role' => $role, 'password' => Hash::make('password123')])->create();
    $profileModel::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'role_label',
                    'profile',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'role' => $role->value,
                ],
            ],
        ]);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    expect($response->json('data.user.profile'))->not->toBeNull();
})->with([
    'warga' => [UserRole::Warga, WargaProfile::class, 'wargaProfile'],
    'petugas_lapangan' => [UserRole::PetugasLapangan, PetugasLapanganProfile::class, 'petugasLapanganProfile'],
    'pengelola_tps3r' => [UserRole::PengelolaTps3r, PengelolaTps3rProfile::class, 'pengelolaTps3rProfile'],
    'pemrakarsa' => [UserRole::Pemrakarsa, PemrakarsaProfile::class, 'pemrakarsaProfile'],
    'admin_dlh' => [UserRole::AdminDlh, AdminDlhProfile::class, 'adminDlhProfile'],
]);

test('login succeeds when requested role matches user role', function () {
    $user = User::factory()->petugasLapangan()->create(['password' => Hash::make('password123')]);
    PetugasLapanganProfile::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
        'role' => 'petugas_lapangan',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'role' => 'petugas_lapangan',
                ],
            ],
        ]);
});

test('login fails with 403 when requested role does not match user role', function () {
    $user = User::factory()->warga()->create(['password' => Hash::make('password123')]);
    WargaProfile::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
        'role' => 'admin_dlh',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Akun Anda tidak memiliki hak akses sebagai admin_dlh.',
        ]);
});

test('login fails with 401 when given incorrect password', function () {
    $user = User::factory()->create(['password' => Hash::make('correct_password')]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong_password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
        ]);
});

test('login fails with 403 when user account is deactivated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
        ]);
});

test('citizen can register through API and creates profile', function () {
    $payload = [
        'name' => 'Warga Madiun Baru',
        'email' => 'warga.baru@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone_number' => '08987654321',
        'nik' => '3577019999990001',
        'alamat' => 'Jl. Pahlawan Gg. 2',
        'rt' => '001',
        'rw' => '002',
        'kelurahan' => 'Kartanegara',
        'kecamatan' => 'Kartoharjo',
        'latitude' => -7.6291,
        'longitude' => 111.5235,
    ];

    $response = $this->postJson('/api/v1/auth/register', $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Pendaftaran akun warga berhasil.',
            'data' => [
                'user' => [
                    'email' => 'warga.baru@example.com',
                    'role' => 'warga',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'warga.baru@example.com',
        'role' => 'warga',
    ]);

    $this->assertDatabaseHas('warga_profiles', [
        'nik' => '3577019999990001',
        'kelurahan' => 'Kartanegara',
    ]);
});

test('authenticated user can fetch me endpoint with profile', function () {
    $user = User::factory()->petugasLapangan()->create();
    PetugasLapanganProfile::factory()->create([
        'user_id' => $user->id,
        'id_wilayah_rute' => 'RUTE-KART-02',
    ]);

    Sanctum::actingAs($user, ['role:petugas_lapangan']);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'role' => 'petugas_lapangan',
                'profile' => [
                    'id_wilayah_rute' => 'RUTE-KART-02',
                ],
            ],
        ]);
});

test('authenticated user can logout and revoke current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    expect($user->tokens()->count())->toBe(0);
});

test('rbac middleware protects endpoints according to user role', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    $petugas = User::factory()->petugasLapangan()->create();
    PetugasLapanganProfile::factory()->create(['user_id' => $petugas->id]);

    // Admin accessing admin endpoint -> OK
    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $this->getJson('/api/v1/admin/ping')->assertOk();

    // Admin accessing petugas endpoint -> 403 Forbidden
    $this->getJson('/api/v1/petugas/ping')->assertStatus(403);

    // Petugas accessing petugas endpoint -> OK
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);
    $this->getJson('/api/v1/petugas/ping')->assertOk();

    // Petugas accessing admin endpoint -> 403 Forbidden
    $this->getJson('/api/v1/admin/ping')->assertStatus(403);
});
