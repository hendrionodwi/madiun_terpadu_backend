<?php

use App\Models\RetributionBill;
use App\Models\User;
use App\Models\WargaProfile;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    config()->set('services.midtrans.server_key', 'SB-Mid-server-RETRIBUTION-TEST');
    config()->set('services.midtrans.is_production', false);
    config()->set('services.midtrans.allow_mock', false);

    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'retribution-snap-token',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/retribution-snap-token',
        ], 201),
    ]);
});

test('warga can list their retribution bills and see overdue indicators (TRK-F-013)', function () {
    $warga = User::factory()->warga()->create();
    $otherWarga = User::factory()->warga()->create();

    $ownBill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'periode_bulan' => 10,
        'periode_tahun' => 2026,
        'nominal_tagihan' => 10000.0,
        'status' => 'belum_dibayar',
    ]);

    RetributionBill::factory()->create([
        'warga_id' => $otherWarga->id,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->getJson('/api/v1/retributions/bills');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.bills')
        ->assertJsonPath('data.bills.0.id', $ownBill->id)
        ->assertJsonPath('data.bills.0.nomor_tagihan', $ownBill->nomor_tagihan);
});

test('warga can pay retribution bill 100% using waste reward points', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 2000,
    ]);

    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000.0,
        'status' => 'belum_dibayar',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 1000, // 1000 poin * Rp10 = Rp10.000 (lunas)
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.payment_type', 'full_points')
        ->assertJsonPath('data.bill.status', 'lunas')
        ->assertJsonPath('data.sisa_saldo_poin', 1000);

    $bill->refresh();
    expect($bill->status)->toBe('lunas')
        ->and($bill->metode_pembayaran)->toBe('poin_sampah')
        ->and($bill->poin_digunakan)->toBe(1000)
        ->and($bill->nominal_dibayar)->toBe(0.0)
        ->and($bill->paid_at)->not->toBeNull();

    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'debit',
        'kategori' => 'bayar_retribusi',
        'jumlah_poin' => 1000,
    ]);
});

test('warga can pay retribution bill hybrid with partial points and remaining via midtrans', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 400,
    ]);

    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000.0,
        'status' => 'belum_dibayar',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 400, // 400 poin * Rp10 = Rp4.000, sisa Rp6.000 via Midtrans
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.payment_type', 'hybrid')
        ->assertJsonPath('data.nominal_harus_dibayar', 6000)
        ->assertJsonStructure(['data' => ['snap_token', 'snap_redirect_url']]);

    $bill->refresh();
    expect($bill->status)->toBe('menunggu_pembayaran')
        ->and($bill->metode_pembayaran)->toBe('hibrida')
        ->and($bill->poin_digunakan)->toBe(400)
        ->and($bill->nominal_dibayar)->toBe(6000.0)
        ->and($bill->snap_token)->not->toBeNull()
        ->and($warga->wargaProfile->fresh()->saldo_poin)->toBe(0);

    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'debit',
        'kategori' => 'bayar_retribusi',
        'jumlah_poin' => 400,
        'referensi_id' => $bill->id,
    ]);
});

test('signed midtrans webhook marks a pending retribution bill as paid without deducting reserved points twice', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 500,
    ]);

    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000.0,
        'status' => 'belum_dibayar',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);
    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 300,
    ])->assertOk();

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(200);

    $bill->refresh();
    $midtransOrderId = $bill->midtrans_order_id;
    $statusCode = '200';
    $grossAmount = '7000.00';
    $signature = hash('sha512', $midtransOrderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));

    $response = $this->postJson('/api/v1/retributions/midtrans/notification', [
        'order_id' => $midtransOrderId,
        'status_code' => $statusCode,
        'transaction_status' => 'settlement',
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $bill->refresh();
    expect($bill->status)->toBe('lunas')
        ->and($bill->paid_at)->not->toBeNull();

    $this->assertDatabaseCount('point_ledgers', 1);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'debit',
        'kategori' => 'bayar_retribusi',
        'jumlah_poin' => 300,
    ]);

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(200);
});

test('retribution webhook rejects an invalid signature without changing the bill', function () {
    $warga = User::factory()->warga()->create();
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'menunggu_pembayaran',
        'nominal_dibayar' => 10000,
    ]);

    $this->postJson('/api/v1/retributions/midtrans/notification', [
        'order_id' => $bill->nomor_tagihan,
        'status_code' => '200',
        'transaction_status' => 'settlement',
        'gross_amount' => '10000.00',
        'signature_key' => 'invalid-signature',
    ])->assertForbidden();

    expect($bill->fresh()->status)->toBe('menunggu_pembayaran');
});

test('retribution webhook rejects a signed payment with a mismatched amount', function () {
    $warga = User::factory()->warga()->create();
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'menunggu_pembayaran',
        'nominal_dibayar' => 10000,
    ]);
    $statusCode = '200';
    $grossAmount = '1000.00';
    $signature = hash('sha512', $bill->nomor_tagihan.$statusCode.$grossAmount.config('services.midtrans.server_key'));

    $this->postJson('/api/v1/retributions/midtrans/notification', [
        'order_id' => $bill->nomor_tagihan,
        'status_code' => $statusCode,
        'transaction_status' => 'settlement',
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
    ])->assertUnprocessable();

    expect($bill->fresh()->status)->toBe('menunggu_pembayaran');
});

test('expired hybrid payment returns reserved points exactly once', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 500]);
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000,
        'status' => 'belum_dibayar',
    ]);
    Sanctum::actingAs($warga, ['role:warga']);

    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 300,
    ])->assertOk();

    $bill->refresh();
    $firstMidtransOrderId = $bill->midtrans_order_id;
    $statusCode = '200';
    $grossAmount = '7000.00';
    $signature = hash('sha512', $firstMidtransOrderId.$statusCode.$grossAmount.config('services.midtrans.server_key'));
    $payload = [
        'order_id' => $firstMidtransOrderId,
        'status_code' => $statusCode,
        'transaction_status' => 'expire',
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
    ];

    $this->postJson('/api/v1/retributions/midtrans/notification', $payload)->assertOk();
    $this->postJson('/api/v1/retributions/midtrans/notification', $payload)->assertOk();

    expect($bill->fresh()->status)->toBe('belum_dibayar')
        ->and($bill->fresh()->poin_digunakan)->toBe(0)
        ->and($warga->wargaProfile->fresh()->saldo_poin)->toBe(500);
    $this->assertDatabaseCount('point_ledgers', 2);

    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 200,
    ])->assertOk();
    $bill->refresh();
    $retryMidtransOrderId = $bill->midtrans_order_id;
    expect($retryMidtransOrderId)->not->toBe($firstMidtransOrderId);
    $retryGrossAmount = '8000.00';
    $retryPayload = [
        'order_id' => $retryMidtransOrderId,
        'status_code' => $statusCode,
        'transaction_status' => 'expire',
        'gross_amount' => $retryGrossAmount,
        'signature_key' => hash('sha512', $retryMidtransOrderId.$statusCode.$retryGrossAmount.config('services.midtrans.server_key')),
    ];

    $this->postJson('/api/v1/retributions/midtrans/notification', $retryPayload)->assertOk();

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(500);
    $this->assertDatabaseCount('point_ledgers', 4);
});

test('failed snap creation restores the reserved points and resets payment state', function () {
    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('createRetributionSnapTransaction')
            ->once()
            ->andThrow(new RuntimeException('Gateway unavailable'));
    });
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 500]);
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000,
        'status' => 'belum_dibayar',
    ]);
    Sanctum::actingAs($warga, ['role:warga']);

    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", [
        'use_points' => true,
        'points_to_use' => 300,
    ])->assertInternalServerError()
        ->assertJsonMissing(['message' => 'Gateway unavailable']);

    expect($bill->fresh()->status)->toBe('belum_dibayar')
        ->and($bill->fresh()->poin_digunakan)->toBe(0)
        ->and($warga->wargaProfile->fresh()->saldo_poin)->toBe(500);
});

test('a second payment request cannot reserve points again while the first is pending', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 700]);
    $bill = RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000,
        'status' => 'belum_dibayar',
    ]);
    Sanctum::actingAs($warga, ['role:warga']);
    $payload = ['use_points' => true, 'points_to_use' => 300];

    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", $payload)->assertOk();
    $this->postJson("/api/v1/retributions/bills/{$bill->nomor_tagihan}/pay", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('payment');

    expect($warga->wargaProfile->fresh()->saldo_poin)->toBe(400);
    $this->assertDatabaseCount('point_ledgers', 1);
});

test('citizen with overdue unpaid retribution bill is blocked from scheduling waste pickup', function () {
    $warga = User::factory()->warga()->create();

    // Create overdue bill
    RetributionBill::factory()->overdue()->create([
        'warga_id' => $warga->id,
        'nominal_tagihan' => 10000.0,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson('/api/v1/tracking/schedules', [
        'id_wilayah_rute' => 'RUTE-KART-01',
        'tanggal_penjemputan' => now()->addDays(2)->toDateString(),
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '10:00',
        'jenis_sampah' => 'anorganik_plastik',
        'alamat_penjemputan' => 'Jl. Pahlawan No. 5, Kartoharjo',
    ]);

    $response->assertForbidden()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error_code', 'RETRIBUTION_OVERDUE_LOCKED')
        ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'Layanan penjemputan sampah ditangguhkan'));
});

test('citizen with an overdue payment still pending at midtrans remains blocked from scheduling pickup', function () {
    $warga = User::factory()->warga()->create();
    RetributionBill::factory()->overdue()->create([
        'warga_id' => $warga->id,
        'status' => 'menunggu_pembayaran',
    ]);
    Sanctum::actingAs($warga, ['role:warga']);

    $this->postJson('/api/v1/tracking/schedules', [
        'id_wilayah_rute' => 'RUTE-KART-01',
        'tanggal_penjemputan' => now()->addDays(2)->toDateString(),
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '10:00',
        'jenis_sampah' => 'anorganik_plastik',
        'alamat_penjemputan' => 'Jl. Pahlawan No. 5, Kartoharjo',
    ])->assertForbidden()
        ->assertJsonPath('error_code', 'RETRIBUTION_OVERDUE_LOCKED');
});

test('citizen who has paid their retribution bill can schedule waste pickup smoothly', function () {
    User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();

    // Create paid bill
    RetributionBill::factory()->paid()->create([
        'warga_id' => $warga->id,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson('/api/v1/tracking/schedules', [
        'id_wilayah_rute' => 'RUTE-KART-01',
        'tanggal_penjemputan' => now()->addDays(2)->toDateString(),
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '10:00',
        'jenis_sampah' => 'anorganik_plastik',
        'alamat_penjemputan' => 'Jl. Pahlawan No. 5, Kartoharjo',
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.warga_id', $warga->id);
});

test('admin dlh can generate monthly retribution bills via artisan command', function () {
    $warga1 = User::factory()->warga()->create();
    $warga2 = User::factory()->warga()->create();

    $this->artisan('retribution:generate-bills', [
        '--month' => 11,
        '--year' => 2026,
        '--nominal' => 10000,
    ])->assertSuccessful();

    $this->artisan('retribution:generate-bills', [
        '--month' => 11,
        '--year' => 2026,
        '--nominal' => 10000,
    ])->assertSuccessful();

    $this->assertDatabaseHas('retribution_bills', [
        'warga_id' => $warga1->id,
        'periode_bulan' => 11,
        'periode_tahun' => 2026,
        'nominal_tagihan' => 10000.0,
        'status' => 'belum_dibayar',
    ]);

    $this->assertDatabaseHas('retribution_bills', [
        'warga_id' => $warga2->id,
        'periode_bulan' => 11,
        'periode_tahun' => 2026,
    ]);
    $this->assertDatabaseCount('retribution_bills', 2);
});
