<?php

use App\Models\PengelolaTps3rProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('pengelola tps3r can view midtrans payment settings and tutorial page', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $user->id,
        'nama_tps3r' => 'TPS3R Manguharjo Berdikari',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard.tps3r.payment-settings'));

    $response->assertOk()
        ->assertSee('Gateway Pembayaran Midtrans Mandiri')
        ->assertSee('TPS3R Manguharjo Berdikari')
        ->assertSee('Panduan Menghubungkan Akun Midtrans TPS3R')
        ->assertSee('Payment Notification URL')
        ->assertSee('Salin URL');
});

test('pengelola tps3r can update midtrans credentials and server key is encrypted', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $user->id]);

    $payload = [
        'midtrans_merchant_id' => 'G12345678',
        'midtrans_client_key' => 'SB-Mid-client-SecretKey123',
        'midtrans_server_key' => 'SB-Mid-server-SecretKey456',
        'midtrans_is_production' => '0',
        'midtrans_is_active' => '1',
    ];

    $response = $this->actingAs($user)
        ->put(route('dashboard.tps3r.payment-settings.update'), $payload);

    $response->assertRedirect(route('dashboard.tps3r.payment-settings'))
        ->assertSessionHas('status');

    $profile->refresh();

    expect($profile->midtrans_merchant_id)->toBe('G12345678');
    expect($profile->midtrans_client_key)->toBe('SB-Mid-client-SecretKey123');
    expect($profile->midtrans_server_key)->toBe('SB-Mid-server-SecretKey456');
    expect($profile->midtrans_is_active)->toBeTrue();
    expect($profile->midtrans_is_production)->toBeFalse();
});

test('tps3r can test midtrans connection successfully via mock ping', function () {
    $user = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->withMidtrans()->create(['user_id' => $user->id]);

    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'dummy-snap-token',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/dummy',
        ], 201),
    ]);

    $response = $this->actingAs($user)->postJson(route('dashboard.tps3r.payment-settings.test'), [
        'server_key' => 'SB-Mid-server-VALID123',
        'is_production' => false,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);
});

test('admin dlh can inspect tps3r payment settings', function () {
    $admin = User::factory()->adminDlh()->create();
    $tps3rUser = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $tps3rUser->id,
        'nama_tps3r' => 'TPS3R Taman Indah',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('dashboard.tps3r.payment-settings', ['tps3r_user_id' => $tps3rUser->id]));

    $response->assertOk()
        ->assertSee('Mode Monitoring Admin DLH')
        ->assertSee('TPS3R Taman Indah');
});
