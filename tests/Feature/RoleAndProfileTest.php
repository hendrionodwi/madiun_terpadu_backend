<?php

use App\Enums\UserRole;
use App\Models\AdminDlhProfile;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\User;
use App\Models\WargaProfile;

test('enum user role contains 5 expected roles', function () {
    expect(UserRole::values())->toEqualCanonicalizing([
        'warga',
        'petugas_lapangan',
        'pengelola_tps3r',
        'pemrakarsa',
        'admin_dlh',
    ]);
});

test('user has one-to-one relation with warga profile', function () {
    $user = User::factory()->warga()->create();
    $profile = WargaProfile::factory()->create([
        'user_id' => $user->id,
        'nik' => '3577012345678901',
        'latitude' => -7.6298,
        'longitude' => 111.5239,
    ]);

    expect($user->isWarga())->toBeTrue()
        ->and($user->wargaProfile)->not->toBeNull()
        ->and($user->wargaProfile->nik)->toBe('3577012345678901')
        ->and($user->profile)->toBeInstanceOf(WargaProfile::class);

    // Test cascade delete
    $user->delete();
    expect(WargaProfile::find($profile->id))->toBeNull();
});

test('user has one-to-one relation with petugas lapangan profile', function () {
    $user = User::factory()->petugasLapangan()->create();
    $profile = PetugasLapanganProfile::factory()->create([
        'user_id' => $user->id,
        'id_wilayah_rute' => 'RUTE-001',
    ]);

    expect($user->isPetugasLapangan())->toBeTrue()
        ->and($user->petugasLapanganProfile)->not->toBeNull()
        ->and($user->petugasLapanganProfile->id_wilayah_rute)->toBe('RUTE-001')
        ->and($user->profile)->toBeInstanceOf(PetugasLapanganProfile::class);

    $user->delete();
    expect(PetugasLapanganProfile::find($profile->id))->toBeNull();
});

test('user has one-to-one relation with pengelola tps3r profile', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $user->id,
        'nama_tps3r' => 'TPS3R Merak Madiun',
    ]);

    expect($user->isPengelolaTps3r())->toBeTrue()
        ->and($user->pengelolaTps3rProfile)->not->toBeNull()
        ->and($user->pengelolaTps3rProfile->nama_tps3r)->toBe('TPS3R Merak Madiun')
        ->and($user->profile)->toBeInstanceOf(PengelolaTps3rProfile::class);

    $user->delete();
    expect(PengelolaTps3rProfile::find($profile->id))->toBeNull();
});

test('user has one-to-one relation with pemrakarsa profile', function () {
    $user = User::factory()->pemrakarsa()->create();
    $profile = PemrakarsaProfile::factory()->create([
        'user_id' => $user->id,
        'nama_instansi' => 'Hotel Madiun Indah',
    ]);

    expect($user->isPemrakarsa())->toBeTrue()
        ->and($user->pemrakarsaProfile)->not->toBeNull()
        ->and($user->pemrakarsaProfile->nama_instansi)->toBe('Hotel Madiun Indah')
        ->and($user->profile)->toBeInstanceOf(PemrakarsaProfile::class);

    $user->delete();
    expect(PemrakarsaProfile::find($profile->id))->toBeNull();
});

test('user has one-to-one relation with admin dlh profile', function () {
    $user = User::factory()->adminDlh()->create();
    $profile = AdminDlhProfile::factory()->create([
        'user_id' => $user->id,
        'nip' => '198501012010011001',
    ]);

    expect($user->isAdminDlh())->toBeTrue()
        ->and($user->adminDlhProfile)->not->toBeNull()
        ->and($user->adminDlhProfile->nip)->toBe('198501012010011001')
        ->and($user->profile)->toBeInstanceOf(AdminDlhProfile::class);

    $user->delete();
    expect(AdminDlhProfile::find($profile->id))->toBeNull();
});
