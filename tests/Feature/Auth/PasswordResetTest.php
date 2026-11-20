<?php

use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response
        ->assertOk()
        ->assertSee('Kirim Tautan Pemulihan')
        ->assertSee('Anda tidak perlu memasukkan kata sandi lama')
        ->assertDontSee('Kata sandi saat ini');
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetNotification::class);
});

test('password reset request does not reveal whether an email is registered', function () {
    Notification::fake();

    $user = User::factory()->create();
    $genericMessage = 'Jika email terdaftar dalam sistem, tautan atur ulang kata sandi telah dikirim.';

    $this->post(route('password.request'), ['email' => $user->email])
        ->assertSessionHas('status', $genericMessage);

    $this->post(route('password.request'), ['email' => 'tidak-terdaftar@example.test'])
        ->assertSessionHas('status', $genericMessage);

    Notification::assertSentTo($user, PasswordResetNotification::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) use ($user) {
        $response = $this->get(route('password.reset', [
            'token' => $notification->token,
            'email' => $user->email,
        ]));

        $response
            ->assertOk()
            ->assertSee('Buat Kata Sandi Baru')
            ->assertSee('Buka di Aplikasi Android')
            ->assertSee('madiunterpadu://auth/reset-password', false)
            ->assertDontSee('Kata sandi saat ini');

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();
    config()->set('session.driver', 'database');

    $user = User::factory()->create([
        'remember_token' => 'remember-token-lama',
    ]);
    $user->createToken('perangkat-lama');
    DB::table('sessions')->insert([
        'id' => 'sesi-perangkat-lama',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->post(route('password.request'), ['email' => $user->email]);

    Notification::assertSentTo($user, PasswordResetNotification::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $user->refresh();
        expect(Hash::check('PasswordBaru123!', $user->password))->toBeTrue()
            ->and($user->remember_token)->not->toBe('remember-token-lama')
            ->and($user->tokens()->count())->toBe(0)
            ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();

        return true;
    });
});
