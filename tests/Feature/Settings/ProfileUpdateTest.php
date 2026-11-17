<?php

use App\Livewire\Settings\Profile;
use App\Models\AdminDlhProfile;
use App\Models\EnvironmentalReport;
use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('admin dlh profile page renders civic cover, underline tabs, and saves official profile fields', function () {
    $admin = User::factory()->adminDlh()->create([
        'name' => 'Rina Admin DLH',
        'email' => 'rina@madiunkota.go.id',
    ]);
    AdminDlhProfile::factory()->create([
        'user_id' => $admin->id,
        'nip' => '198501012010012001',
        'jabatan' => 'Analis Lingkungan',
        'bidang' => 'Pengendalian',
    ]);

    $this->actingAs($admin)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Rina Admin DLH')
        ->assertSee('Informasi Pribadi')
        ->assertSee('Kata Sandi')
        ->assertSee('Keamanan & 2FA')
        ->assertSee('Tampilan')
        ->assertSee('ADUAN AKTIF')
        ->assertDontSee('Profil & Legalitas TPS3R');

    Livewire::test(Profile::class)
        ->set('name', 'Rina Diperbarui')
        ->set('email', 'rina@madiunkota.go.id')
        ->set('phone_number', '081234567890')
        ->set('nip', '198501012010012099')
        ->set('jabatan', 'Kepala Seksi Pengelolaan')
        ->set('bidang', 'Pengelolaan Sampah')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($admin->fresh()->phone_number)->toBe('081234567890');
    $this->assertDatabaseHas('admin_dlh_profiles', [
        'user_id' => $admin->id,
        'nip' => '198501012010012099',
        'jabatan' => 'Kepala Seksi Pengelolaan',
        'bidang' => 'Pengelolaan Sampah',
    ]);
});

test('user can upload and remove profile avatar on web settings page', function () {
    Storage::fake('public');

    $user = User::factory()->adminDlh()->create();
    $this->actingAs($user);

    $avatar = UploadedFile::fake()->image('profil.jpg', 240, 240);

    Livewire::test(Profile::class)
        ->set('name', $user->name)
        ->set('email', $user->email)
        ->set('avatar', $avatar)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $storedPath = $user->fresh()->avatar;
    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);

    Livewire::test(Profile::class)
        ->call('removeAvatar')
        ->assertHasNoErrors();

    expect($user->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($storedPath);
});

test('tps3r and pemrakarsa profile views render unified civic cover, tabs, and real metrics', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Kartoharjo Asri',
        'kapasitas_harian_kg' => 2500,
    ]);
    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $tps3rProfile->id,
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $pengelola->id,
    ]);

    $this->actingAs($pengelola)
        ->get(route('dashboard.tps3r.profile.edit'))
        ->assertOk()
        ->assertSee('TPS3R Kartoharjo Asri')
        ->assertSee('Profil & Legalitas TPS3R')
        ->assertSee('Informasi Pribadi')
        ->assertSee('SETORAN')
        ->assertSee('2.500 kg');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create([
        'user_id' => $pemrakarsa->id,
        'nama_instansi' => 'PT Mitra Hijau Madiun',
    ]);
    EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsa->id,
        'status' => 'disetujui',
    ]);

    $this->actingAs($pemrakarsa)
        ->get(route('dashboard.pemrakarsa.profile.edit'))
        ->assertOk()
        ->assertSee('PT Mitra Hijau Madiun')
        ->assertSee('Profil & Legalitas Usaha')
        ->assertSee('DISETUJUI');
});

test('tps3r account settings use one active profile and capacity navigation entry', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
    ]);

    $response = $this->actingAs($pengelola)->get(route('profile.edit'));

    $response
        ->assertOk()
        ->assertSee('Profil & Kapasitas')
        ->assertSee('Informasi Pribadi')
        ->assertSee('data-test="tps3r-profile-settings-navigation"', false)
        ->assertDontSee('data-test="account-settings-menu-item"', false);

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $profileNavigation = (new DOMXPath($document))
        ->query('//*[@data-test="tps3r-profile-settings-navigation"]')
        ->item(0);

    expect($profileNavigation)
        ->not->toBeNull()
        ->and($profileNavigation->hasAttribute('data-current'))->toBeTrue();
});

test('tps3r manager can publish facility information and cover photo', function () {
    Storage::fake('public');

    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'foto_fasilitas' => null,
    ]);

    $response = $this->actingAs($pengelola)->patch(route('dashboard.tps3r.profile.update'), [
        'nama_tps3r' => 'TPS3R Kartoharjo Berseri',
        'kode_tps3r' => 'TPS3R-KTH-01',
        'deskripsi' => 'Pusat pemilahan dan komposting untuk warga Kartoharjo.',
        'alamat' => 'Jl. Merpati No. 4',
        'kelurahan' => 'Oro-Oro Ombo',
        'kecamatan' => 'Kartoharjo',
        'kapasitas_harian_kg' => 2000,
        'jam_operasional' => 'Senin-Sabtu, 08.00-15.00 WIB',
        'fokus_pengolahan' => 'Komposting dan pemilahan anorganik',
        'cakupan_wilayah' => 'Kartoharjo dan kelurahan sekitar',
        'nama_koordinator' => 'Ahmad Fauzi',
        'kontak_publik' => '0351-400001',
        'layanan_tersedia_text' => "Penerimaan sampah pilah\nEdukasi lingkungan",
        'kategori_sampah_diterima_text' => "Sampah organik\nPlastik PET",
        'foto_fasilitas' => UploadedFile::fake()->image('fasilitas.webp', 1200, 800),
    ]);

    $response->assertRedirect()->assertSessionHasNoErrors();

    $profile->refresh();
    expect($profile->deskripsi)->toBe('Pusat pemilahan dan komposting untuk warga Kartoharjo.')
        ->and($profile->kontak_publik)->toBe('0351-400001')
        ->and($profile->layanan_tersedia)->toBe(['Penerimaan sampah pilah', 'Edukasi lingkungan'])
        ->and($profile->kategori_sampah_diterima)->toBe(['Sampah organik', 'Plastik PET'])
        ->and($profile->foto_fasilitas)->not->toBeNull();

    Storage::disk('public')->assertExists($profile->foto_fasilitas);
});
