<?php

use App\Models\AdminDlhProfile;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\FundWithdrawalRequest;
use App\Models\Order;
use App\Models\PengelolaTps3rProfile;
use App\Models\PickupSchedule;
use App\Models\RetributionBill;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('forgot password and reset password recover account access and revoke old api tokens', function () {
    Notification::fake();

    $user = User::factory()->warga()->create([
        'email' => 'warga.recovery@madiunkota.go.id',
        'password' => Hash::make('OldPassword123'),
    ]);
    $user->createToken('old-device');

    $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $forgotResponse->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo($user, ResetPassword::class);

    $token = Password::broker()->createToken($user);

    $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $resetResponse->assertOk()
        ->assertJsonPath('success', true);

    $user->refresh();
    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue();
    expect($user->tokens()->count())->toBe(0);
});

test('reset password rejects invalid recovery token with 422', function () {
    $user = User::factory()->warga()->create();

    $this->postJson('/api/v1/auth/reset-password', [
        'token' => 'token-tidak-valid',
        'email' => $user->email,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can update password while preserving current token and revoking other tokens', function () {
    $user = User::factory()->warga()->create([
        'password' => Hash::make('CurrentPass123'),
    ]);

    $oldToken = $user->createToken('other-phone')->accessToken;
    $currentToken = $user->createToken('active-phone')->plainTextToken;

    $this->withToken($currentToken)
        ->patchJson('/api/v1/auth/password', [
            'current_password' => 'WrongPass123',
            'password' => 'UpdatedPass123!',
            'password_confirmation' => 'UpdatedPass123!',
        ])->assertUnprocessable()
        ->assertJsonValidationErrors(['current_password']);

    $this->withToken($currentToken)
        ->patchJson('/api/v1/auth/password', [
            'current_password' => 'CurrentPass123',
            'password' => 'UpdatedPass123!',
            'password_confirmation' => 'UpdatedPass123!',
        ])->assertOk()
        ->assertJsonPath('success', true);

    $user->refresh();
    expect(Hash::check('UpdatedPass123!', $user->password))->toBeTrue();
    expect($user->tokens()->where('id', $oldToken->id)->exists())->toBeFalse();
    expect($user->tokens()->count())->toBe(1);
});

test('profile update stores avatar and cleans up previously stored avatar', function () {
    Storage::fake('public');

    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id]);
    Sanctum::actingAs($warga, ['role:warga']);

    $firstAvatar = UploadedFile::fake()->image('avatar-1.jpg', 200, 200);
    $firstResponse = $this->patchJson('/api/v1/auth/profile', [
        'name' => 'Warga Diperbarui',
        'avatar' => $firstAvatar,
    ]);

    $firstResponse->assertOk()
        ->assertJsonPath('data.name', 'Warga Diperbarui');

    $warga->refresh();
    $firstPath = $warga->avatar;
    expect($firstPath)->not->toBeNull();
    Storage::disk('public')->assertExists($firstPath);

    $secondAvatar = UploadedFile::fake()->image('avatar-2.png', 200, 200);
    $this->patchJson('/api/v1/auth/profile', [
        'avatar' => $secondAvatar,
    ])->assertOk();

    $warga->refresh();
    expect($warga->avatar)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($warga->avatar);
});

test('inactive user is blocked from api endpoints and admin toggle revokes api tokens', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    $target = User::factory()->warga()->create(['is_active' => true]);
    WargaProfile::factory()->create(['user_id' => $target->id]);
    $plainToken = $target->createToken('mobile-session')->plainTextToken;

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.users.toggle', $target))
        ->assertRedirect();

    $target->refresh();
    expect($target->is_active)->toBeFalse();
    expect($target->tokens()->count())->toBe(0);

    Sanctum::actingAs($target, ['role:warga']);
    $this->getJson('/api/v1/warga/ping')->assertForbidden();
});

test('policies enforce strict ownership and role authorization across core resources', function () {
    $admin = User::factory()->adminDlh()->create();
    $wargaA = User::factory()->warga()->create();
    $wargaB = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $tps3rA = User::factory()->pengelolaTps3r()->create();
    $tps3rB = User::factory()->pengelolaTps3r()->create();
    $pemrakarsaA = User::factory()->pemrakarsa()->create();
    $pemrakarsaB = User::factory()->pemrakarsa()->create();

    $profileTps3rA = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3rA->id]);
    PengelolaTps3rProfile::factory()->create(['user_id' => $tps3rB->id]);

    $complaint = Complaint::factory()->create(['pelapor_id' => $wargaA->id]);
    $report = EnvironmentalReport::factory()->create(['pemrakarsa_id' => $pemrakarsaA->id]);
    $schedule = PickupSchedule::factory()->create(['petugas_id' => $petugas->id, 'warga_id' => $wargaA->id]);
    $order = Order::factory()->create(['buyer_id' => $wargaA->id]);
    $deposit = Tps3rWasteDeposit::factory()->create(['pengelola_id' => $tps3rA->id, 'tps3r_profile_id' => $profileTps3rA->id, 'petugas_id' => $petugas->id]);
    $product = WasteProduct::factory()->create(['seller_id' => $tps3rA->id]);
    $bill = RetributionBill::factory()->create(['warga_id' => $wargaA->id]);
    $withdrawal = FundWithdrawalRequest::factory()->create(['user_id' => $wargaA->id, 'source' => 'points']);

    expect(Gate::forUser($wargaA)->allows('view', $complaint))->toBeTrue();
    expect(Gate::forUser($wargaB)->allows('view', $complaint))->toBeFalse();
    expect(Gate::forUser($wargaB)->allows('appendInfo', $complaint))->toBeFalse();
    expect(Gate::forUser($admin)->allows('assign', $complaint))->toBeTrue();

    expect(Gate::forUser($pemrakarsaA)->allows('update', $report))->toBeTrue();
    expect(Gate::forUser($pemrakarsaB)->allows('update', $report))->toBeFalse();
    expect(Gate::forUser($admin)->allows('verify', $report))->toBeTrue();

    expect(Gate::forUser($petugas)->allows('updateStatus', $schedule))->toBeTrue();
    expect(Gate::forUser($wargaB)->allows('view', $schedule))->toBeFalse();

    expect(Gate::forUser($wargaA)->allows('view', $order))->toBeTrue();
    expect(Gate::forUser($wargaB)->allows('view', $order))->toBeFalse();

    expect(Gate::forUser($tps3rA)->allows('verify', $deposit))->toBeTrue();
    expect(Gate::forUser($tps3rB)->allows('verify', $deposit))->toBeFalse();

    expect(Gate::forUser($tps3rA)->allows('update', $product))->toBeTrue();
    expect(Gate::forUser($tps3rB)->allows('update', $product))->toBeFalse();

    expect(Gate::forUser($wargaA)->allows('pay', $bill))->toBeTrue();
    expect(Gate::forUser($wargaB)->allows('pay', $bill))->toBeFalse();

    expect(Gate::forUser($wargaA)->allows('view', $withdrawal))->toBeTrue();
    expect(Gate::forUser($wargaB)->allows('view', $withdrawal))->toBeFalse();
    expect(Gate::forUser($admin)->allows('review', $withdrawal))->toBeTrue();
});
