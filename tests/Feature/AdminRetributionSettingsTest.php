<?php

use App\Models\PointLedger;
use App\Models\RetributionBill;
use App\Models\User;
use App\Models\WargaProfile;

test('admin dlh can view retribution dashboard with stats, config, and filter tabs (TRK-F-013)', function () {
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create(['name' => 'Budi Santoso']);

    RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'periode_bulan' => 10,
        'periode_tahun' => 2026,
        'nominal_tagihan' => 10000,
        'status' => 'belum_dibayar',
        'jatuh_tempo' => now()->addDays(5)->toDateString(),
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.retributions'));

    $response->assertOk()
        ->assertSee('Retribusi Pelayanan Persampahan (TRK-F-013)')
        ->assertSee('Pengaturan Tarif')
        ->assertSee('Kirim Pengingat Massal')
        ->assertSee('Budi Santoso');
});

test('admin dlh can update retribution tariff policies, grace period, and notification template', function () {
    $admin = User::factory()->adminDlh()->create();

    $payload = [
        'monthly_tariff' => 12500,
        'standard_due_day' => 25,
        'max_point_discount_percent' => 80,
        'grace_period_days' => 3,
        'point_conversion_rate' => 15,
        'reminder_template' => 'Yth. {nama}, tagihan {periode} sebesar Rp {nominal} jatuh tempo pada {jatuh_tempo}. Status: {status_info}.',
    ];

    $response = $this->actingAs($admin)->post(route('dashboard.admin.retributions.settings'), $payload);

    $response->assertRedirect(route('dashboard.admin.retributions'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('master_data', [
        'group' => 'retribution_config',
        'code' => 'monthly_tariff',
        'numeric_value' => 12500,
    ]);

    $this->assertDatabaseHas('master_data', [
        'group' => 'retribution_config',
        'code' => 'grace_period_days',
        'numeric_value' => 3,
    ]);

    $config = RetributionBill::getAllConfig();
    expect($config['monthly_tariff'])->toBe(12500.0)
        ->and($config['standard_due_day'])->toBe(25)
        ->and($config['max_point_discount_percent'])->toBe(80.0)
        ->and($config['grace_period_days'])->toBe(3)
        ->and($config['point_conversion_rate'])->toBe(15.0);
});

test('admin dlh can broadcast bulk retribution reminders to warga', function () {
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create();

    RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'belum_dibayar',
        'jatuh_tempo' => now()->subDays(2)->toDateString(),
    ]);

    $payload = [
        'target_filter' => 'overdue_only',
        'title' => 'Peringatan Jatuh Tempo Retribusi',
        'content' => 'Mohon segera selesaikan pelunasan retribusi kebersihan Anda agar layanan jemput sampah tidak ditangguhkan.',
        'priority' => 'urgent',
    ];

    $response = $this->actingAs($admin)->post(route('dashboard.admin.retributions.send-reminders'), $payload);

    $response->assertRedirect(route('dashboard.admin.retributions'));
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('broadcast_announcements', [
        'created_by' => $admin->id,
        'title' => 'Peringatan Jatuh Tempo Retribusi',
        'audience_role' => 'warga',
        'priority' => 'urgent',
        'is_active' => true,
    ]);
});

test('retribution bills can be filtered by status tabs including overdue and unpaid', function () {
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create();

    $overdueBill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nomor_tagihan' => 'RET-OVERDUE-001',
        'status' => 'belum_dibayar',
        'jatuh_tempo' => now()->subDays(5)->toDateString(),
    ]);

    $activeBill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nomor_tagihan' => 'RET-ACTIVE-002',
        'periode_bulan' => 9,
        'status' => 'belum_dibayar',
        'jatuh_tempo' => now()->addDays(5)->toDateString(),
    ]);

    RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nomor_tagihan' => 'RET-PENDING-003',
        'periode_bulan' => 8,
        'status' => 'menunggu_pembayaran',
        'jatuh_tempo' => now()->subDays(5)->toDateString(),
    ]);

    // Test overdue tab
    $overdueResponse = $this->actingAs($admin)->get(route('dashboard.admin.retributions', ['status' => 'overdue']));
    $overdueResponse->assertOk()
        ->assertSee('RET-OVERDUE-001')
        ->assertSee('RET-PENDING-003')
        ->assertDontSee('RET-ACTIVE-002');

    // Test belum_dibayar tab
    $activeResponse = $this->actingAs($admin)->get(route('dashboard.admin.retributions', ['status' => 'belum_dibayar']));
    $activeResponse->assertOk()
        ->assertSee('RET-ACTIVE-002')
        ->assertDontSee('RET-OVERDUE-001');
});

test('manual payment returns reserved hybrid points before recording full cash settlement', function () {
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 500]);
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'belum_dibayar',
        'nominal_tagihan' => 10000,
    ]);
    PointLedger::record(
        $warga->id,
        'debit',
        'bayar_retribusi',
        300,
        'Reservasi poin pembayaran hibrida',
        $bill
    );
    $bill->update([
        'status' => 'menunggu_pembayaran',
        'metode_pembayaran' => 'hibrida',
        'poin_digunakan' => 300,
        'nominal_potongan_poin' => 3000,
        'nominal_dibayar' => 7000,
    ]);

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.retributions.confirm-manual', $bill))
        ->assertRedirect(route('dashboard.admin.retributions'));

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(500)
        ->and($bill->fresh()->status)->toBe('lunas')
        ->and($bill->fresh()->metode_pembayaran)->toBe('manual_dlh')
        ->and($bill->fresh()->poin_digunakan)->toBe(0);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'kredit',
        'kategori' => 'refund_retribusi',
        'jumlah_poin' => 300,
    ]);
});

test('console command retribution:send-reminders runs successfully with broadcast', function () {
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create(['phone_number' => '08123456789']);

    RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'belum_dibayar',
        'jatuh_tempo' => now()->subDays(3)->toDateString(),
    ]);

    $this->artisan('retribution:send-reminders --overdue-only --broadcast')
        ->assertSuccessful();

    $this->assertDatabaseHas('broadcast_announcements', [
        'audience_role' => 'warga',
        'priority' => 'urgent',
    ]);
});

test('non-admin user cannot access retribution settings or send reminders', function () {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)
        ->post(route('dashboard.admin.retributions.settings'), [
            'monthly_tariff' => 20000,
            'standard_due_day' => 15,
            'max_point_discount_percent' => 50,
            'grace_period_days' => 1,
            'point_conversion_rate' => 10,
            'reminder_template' => 'Test',
        ])
        ->assertForbidden();

    $this->actingAs($warga)
        ->post(route('dashboard.admin.retributions.send-reminders'), [
            'target_filter' => 'all_unpaid',
            'title' => 'Hack',
            'content' => 'Hack',
            'priority' => 'normal',
        ])
        ->assertForbidden();
});
