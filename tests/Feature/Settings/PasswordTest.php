<?php

use App\Livewire\Settings\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('guests are redirected to login from password settings', function () {
    $this->get(route('password.edit'))
        ->assertRedirect(route('login'));
});

test('authenticated password settings page uses current password verification', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('password.edit'))
        ->assertOk()
        ->assertSee('Ubah Kata Sandi')
        ->assertSee('Kata sandi saat ini')
        ->assertDontSee('Kirim Tautan Pemulihan');
});

test('authenticated user can update password with current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->set('current_password', 'password')
        ->set('password', 'new-secure-password')
        ->set('password_confirmation', 'new-secure-password')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-secure-password', $user->refresh()->password))->toBeTrue();
});

test('current password must be correct before password is changed', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-secure-password')
        ->set('password_confirmation', 'new-secure-password')
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

test('new password must be confirmed', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    Livewire::test(Password::class)
        ->set('current_password', 'password')
        ->set('password', 'new-secure-password')
        ->set('password_confirmation', 'different-password')
        ->call('updatePassword')
        ->assertHasErrors(['password']);

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});
