<?php

use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\PointLedger;
use App\Models\RewardFundAllocation;
use App\Models\User;
use App\Models\WargaProfile;
use App\Services\PointConversionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

function createPointWithdrawalFixture(int $saldoPoin = 20000): array
{
    $admin = User::factory()->adminDlh()->create();
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => $saldoPoin,
    ]);
    $allocation = RewardFundAllocation::factory()->create([
        'created_by' => $admin->id,
        'amount' => 100000,
    ]);
    MasterData::factory()->create([
        'group' => 'point_conversion',
        'code' => 'idr',
        'numeric_value' => 1,
        'is_active' => true,
    ]);

    return compact('admin', 'warga', 'allocation');
}

function submitPointWithdrawal(User $warga): FundWithdrawalRequest
{
    Sanctum::actingAs($warga);

    test()->postJson('/api/v1/fund-withdrawals', [
        'source' => 'points',
        'points_used' => 10000,
        'destination_type' => 'e_wallet',
        'destination_provider' => 'DANA',
        'destination_account' => '081234567890',
        'destination_account_name' => 'Siti Aminah',
    ])->assertCreated()
        ->assertJsonPath('data.amount', 10000)
        ->assertJsonPath('data.status', 'pending');

    return FundWithdrawalRequest::query()->latest('id')->firstOrFail();
}

function submitCashPointWithdrawal(User $warga): FundWithdrawalRequest
{
    Sanctum::actingAs($warga);

    test()->postJson('/api/v1/fund-withdrawals', [
        'source' => 'points',
        'points_used' => 10000,
        'destination_type' => 'cash',
        'cash_pickup_location' => 'Lokasi dari klien tidak boleh dipercaya',
    ])->assertCreated()
        ->assertJsonPath('data.amount', 10000)
        ->assertJsonPath('data.status', 'pending');

    return FundWithdrawalRequest::query()->latest('id')->firstOrFail();
}

test('point withdrawal is rejected when reward funding has not been allocated', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 20000]);
    Sanctum::actingAs($warga);

    $this->postJson('/api/v1/fund-withdrawals', [
        'source' => 'points',
        'points_used' => 10000,
        'destination_type' => 'e_wallet',
        'destination_provider' => 'DANA',
        'destination_account' => '081234567890',
        'destination_account_name' => 'Siti Aminah',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('points_used');

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(20000)
        ->and(FundWithdrawalRequest::count())->toBe(0)
        ->and(PointLedger::count())->toBe(0);
});

test('approval only reserves funding and payment becomes final after proof is recorded', function () {
    Storage::fake('local');
    ['admin' => $admin, 'warga' => $warga, 'allocation' => $allocation] = createPointWithdrawalFixture();
    $withdrawal = submitPointWithdrawal($warga);

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(10000)
        ->and($withdrawal->cash_claim_code)->toBeNull();

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'reward_fund_allocation_id' => $allocation->id,
        'admin_note' => 'Identitas dan tujuan pembayaran valid.',
    ])->assertRedirect();

    $withdrawal->refresh();
    expect($withdrawal->status)->toBe('approved')
        ->and($withdrawal->paid_at)->toBeNull()
        ->and($withdrawal->payout_reference)->toBeNull();

    $this->actingAs($admin)->patch(route('dashboard.admin.points.payout', $withdrawal), [
        'status' => 'processing',
        'payout_note' => 'Masuk antrean bendahara DLH.',
    ])->assertRedirect();

    $this->actingAs($admin)->patch(route('dashboard.admin.points.payout', $withdrawal), [
        'status' => 'paid',
        'payout_reference' => 'DANA-20261114-001',
        'payout_proof' => UploadedFile::fake()->image('bukti-transfer.jpg'),
        'payout_note' => 'Pembayaran berhasil dikirim.',
    ])->assertRedirect();

    $withdrawal->refresh();
    expect($withdrawal->status)->toBe('paid')
        ->and($withdrawal->paid_at)->not->toBeNull()
        ->and($withdrawal->processed_by)->toBe($admin->id)
        ->and($withdrawal->payout_reference)->toBe('DANA-20261114-001')
        ->and($withdrawal->reward_fund_allocation_id)->toBe($allocation->id);
    Storage::disk('local')->assertExists($withdrawal->payout_proof_path);
    expect(PointLedger::where('kategori', 'refund_penukaran_poin')->count())->toBe(0);
});

test('cash point withdrawal issues a unique claim code without marking it paid', function () {
    ['warga' => $warga] = createPointWithdrawalFixture();

    $firstWithdrawal = submitCashPointWithdrawal($warga);

    expect($firstWithdrawal->cash_claim_code)
        ->toMatch('/^TUN-[A-Z0-9]{5}-[A-Z0-9]{5}$/')
        ->and($firstWithdrawal->status)->toBe('pending')
        ->and($firstWithdrawal->cash_pickup_location)->toBe('Loket Admin DLH Kota Madiun')
        ->and($firstWithdrawal->paid_at)->toBeNull()
        ->and($firstWithdrawal->payout_reference)->toBeNull();
});

test('approved cash claim can be disbursed once by admin dlh with receipt proof', function () {
    Storage::fake('local');
    ['admin' => $admin, 'warga' => $warga, 'allocation' => $allocation] = createPointWithdrawalFixture();
    $withdrawal = submitCashPointWithdrawal($warga);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'reward_fund_allocation_id' => $allocation->id,
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('dashboard.admin.points.cash-disbursements.store'), [
        'claim_code' => strtolower((string) $withdrawal->cash_claim_code),
        'identity_verified' => '1',
        'payout_reference' => 'KWT-DLH-2026-0001',
        'payout_proof' => UploadedFile::fake()->image('bukti-serah-terima.jpg'),
        'payout_note' => 'Diterima langsung oleh warga.',
    ])->assertRedirect()
        ->assertSessionHas('status');

    $withdrawal->refresh();
    expect($withdrawal->status)->toBe('paid')
        ->and($withdrawal->payout_reference)->toBe('KWT-DLH-2026-0001')
        ->and($withdrawal->cash_claimed_by)->toBe($admin->id)
        ->and($withdrawal->cash_claimed_at)->not->toBeNull()
        ->and($withdrawal->paid_at)->not->toBeNull()
        ->and($withdrawal->payout_note)->toContain('Diterima langsung oleh warga.')
        ->and(PointLedger::where('kategori', 'refund_penukaran_poin')->count())->toBe(0);
    Storage::disk('local')->assertExists($withdrawal->payout_proof_path);

    $this->actingAs($admin)->post(route('dashboard.admin.points.cash-disbursements.store'), [
        'claim_code' => $withdrawal->cash_claim_code,
        'identity_verified' => '1',
        'payout_reference' => 'KWT-DLH-2026-0002',
        'payout_proof' => UploadedFile::fake()->image('bukti-duplikat.jpg'),
    ])->assertSessionHasErrors('claim_code');

    expect($withdrawal->fresh()->cash_claimed_at?->equalTo($withdrawal->cash_claimed_at))->toBeTrue();
});

test('cash claim cannot be redeemed before admin approval', function () {
    Storage::fake('local');
    ['admin' => $admin, 'warga' => $warga] = createPointWithdrawalFixture();
    $withdrawal = submitCashPointWithdrawal($warga);

    $this->actingAs($admin)->post(route('dashboard.admin.points.cash-disbursements.store'), [
        'claim_code' => $withdrawal->cash_claim_code,
        'identity_verified' => '1',
        'payout_reference' => 'KWT-DLH-2026-0003',
        'payout_proof' => UploadedFile::fake()->image('bukti-pending.jpg'),
    ])->assertSessionHasErrors('claim_code');

    expect($withdrawal->fresh()->status)->toBe('pending')
        ->and($withdrawal->cash_claimed_at)->toBeNull();
});

test('cash disbursement requires identity verification and handover proof', function () {
    ['admin' => $admin, 'warga' => $warga, 'allocation' => $allocation] = createPointWithdrawalFixture();
    $withdrawal = submitCashPointWithdrawal($warga);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'reward_fund_allocation_id' => $allocation->id,
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('dashboard.admin.points.cash-disbursements.store'), [
        'claim_code' => $withdrawal->cash_claim_code,
        'payout_reference' => 'KWT-DLH-2026-0005',
    ])->assertSessionHasErrors(['identity_verified', 'payout_proof']);

    expect($withdrawal->fresh()->status)->toBe('approved')
        ->and($withdrawal->cash_claimed_at)->toBeNull();
});

test('tps3r operator cannot disburse citizen cash claims', function () {
    ['warga' => $warga] = createPointWithdrawalFixture();
    $withdrawal = submitCashPointWithdrawal($warga);
    $operator = User::factory()->pengelolaTps3r()->create();

    $this->actingAs($operator)->post(route('dashboard.admin.points.cash-disbursements.store'), [
        'claim_code' => $withdrawal->cash_claim_code,
        'identity_verified' => '1',
        'payout_reference' => 'KWT-DLH-2026-0004',
        'payout_proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertForbidden();

    expect($withdrawal->fresh()->status)->toBe('pending')
        ->and($withdrawal->cash_claimed_at)->toBeNull();
});

test('approved cash claim cannot be completed through the transfer payout workflow', function () {
    ['admin' => $admin, 'warga' => $warga, 'allocation' => $allocation] = createPointWithdrawalFixture();
    $withdrawal = submitCashPointWithdrawal($warga);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'reward_fund_allocation_id' => $allocation->id,
    ])->assertRedirect();

    $this->actingAs($admin)->patch(route('dashboard.admin.points.payout', $withdrawal), [
        'status' => 'processing',
    ])->assertSessionHasErrors('status');

    expect($withdrawal->fresh()->status)->toBe('approved')
        ->and($withdrawal->processed_at)->toBeNull();
});

test('rejected point withdrawal restores reserved points exactly once', function () {
    ['admin' => $admin, 'warga' => $warga] = createPointWithdrawalFixture();
    $withdrawal = submitPointWithdrawal($warga);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Nama pemilik akun tidak sesuai.',
    ])->assertRedirect();

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(20000)
        ->and(PointLedger::where('kategori', 'refund_penukaran_poin')->count())->toBe(1);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Pengulangan permintaan.',
    ])->assertSessionHasErrors('status');

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(20000)
        ->and(PointLedger::where('kategori', 'refund_penukaran_poin')->count())->toBe(1);
});

test('failed payout restores reserved citizen points', function () {
    ['admin' => $admin, 'warga' => $warga, 'allocation' => $allocation] = createPointWithdrawalFixture();
    $withdrawal = submitPointWithdrawal($warga);

    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'reward_fund_allocation_id' => $allocation->id,
    ])->assertRedirect();

    $this->actingAs($admin)->patch(route('dashboard.admin.points.payout', $withdrawal), [
        'status' => 'failed',
        'failure_note' => 'Nomor tujuan ditolak penyedia pembayaran.',
    ])->assertRedirect();

    expect($withdrawal->fresh()->status)->toBe('failed')
        ->and($warga->fresh()->wargaProfile?->saldo_poin)->toBe(20000)
        ->and(PointLedger::where('kategori', 'refund_penukaran_poin')->count())->toBe(1);
});

test('unsafe point conversion configuration falls back to the canonical rate', function () {
    MasterData::factory()->create([
        'group' => 'point_conversion',
        'code' => 'idr',
        'numeric_value' => 150,
        'is_active' => true,
    ]);

    expect(app(PointConversionService::class)->currentRate())->toBe(1.0)
        ->and(app(PointConversionService::class)->convert(10000))->toBe(10000.0);
});

test('admin cannot store an unsafe point conversion rate', function () {
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)->post(route('dashboard.admin.master-data.store'), [
        'group' => 'point_conversion',
        'code' => 'idr',
        'name' => 'Nilai Tukar Poin ke Rupiah',
        'numeric_value' => 150,
        'unit' => 'IDR/Poin',
    ])->assertSessionHasErrors('numeric_value');

    $this->assertDatabaseMissing('master_data', [
        'group' => 'point_conversion',
        'code' => 'idr',
        'numeric_value' => 150,
    ]);
});

test('citizen cannot record payout execution', function () {
    ['warga' => $warga] = createPointWithdrawalFixture();
    $withdrawal = FundWithdrawalRequest::factory()->create([
        'user_id' => $warga->id,
        'status' => 'approved',
    ]);

    $this->actingAs($warga)->patch(route('dashboard.admin.points.payout', $withdrawal), [
        'status' => 'processing',
    ])->assertForbidden();
});
