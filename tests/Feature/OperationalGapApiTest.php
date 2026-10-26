<?php

use App\Models\BroadcastAnnouncement;
use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use Laravel\Sanctum\Sanctum;

test('waste sorting education exposes active master categories', function () {
    MasterData::factory()->create([
        'group' => 'waste_category',
        'code' => 'organik',
        'name' => 'Sampah Organik',
        'description' => 'Sisa makanan diolah menjadi kompos.',
        'is_active' => true,
    ]);

    $this->getJson('/api/v1/education/waste-sorting')
        ->assertOk()
        ->assertJsonPath('data.0.code', 'organik')
        ->assertJsonPath('data.0.name', 'Sampah Organik');
});

test('warga can request point redemption and rejected points are restored', function () {
    MasterData::factory()->create([
        'group' => 'point_conversion',
        'code' => 'idr',
        'name' => 'Konversi Rupiah',
        'numeric_value' => 10,
        'is_active' => true,
    ]);
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 2000]);
    Sanctum::actingAs($warga);

    $this->postJson('/api/v1/fund-withdrawals', [
        'source' => 'points',
        'points_used' => 1000,
        'destination_type' => 'e_wallet',
        'destination_account' => '081234567890',
    ])->assertCreated()->assertJsonPath('data.amount', 10000);

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(1000);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'debit',
        'kategori' => 'penukaran_poin',
        'jumlah_poin' => 1000,
    ]);
    $withdrawal = FundWithdrawalRequest::query()->firstOrFail();
    $admin = User::factory()->adminDlh()->create();
    $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Nomor akun belum valid.',
    ])->assertRedirect();

    expect($warga->fresh()->wargaProfile?->saldo_poin)->toBe(2000);
    $this->assertDatabaseHas('point_ledgers', [
        'warga_id' => $warga->id,
        'tipe' => 'kredit',
        'kategori' => 'refund_penukaran_poin',
        'jumlah_poin' => 1000,
    ]);
});

test('petugas can submit a deposit only to a selected tps3r facility', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $manager = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);
    Sanctum::actingAs($petugas);

    $this->getJson('/api/v1/tps3r/facilities')->assertOk()->assertJsonPath('data.0.id', $profile->id);
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profile->id,
        'petugas_id' => $manager->id,
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 125.5,
        'tanggal_setor' => '2026-10-10',
    ])->assertCreated();

    $deposit = Tps3rWasteDeposit::query()->firstOrFail();
    expect($deposit->pengelola_id)->toBe($manager->id);
    expect($deposit->petugas_id)->toBe($petugas->id);
});

test('announcements are limited to the authenticated user role', function () {
    $admin = User::factory()->adminDlh()->create();
    BroadcastAnnouncement::factory()->create(['created_by' => $admin->id, 'title' => 'Untuk warga', 'audience_role' => 'warga', 'published_at' => now()->subMinute()]);
    BroadcastAnnouncement::factory()->create(['created_by' => $admin->id, 'title' => 'Untuk petugas', 'audience_role' => 'petugas_lapangan', 'published_at' => now()->subMinute()]);
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga);

    $this->getJson('/api/v1/announcements')
        ->assertOk()
        ->assertJsonFragment(['title' => 'Untuk warga'])
        ->assertJsonMissing(['title' => 'Untuk petugas']);
});

test('warga cannot submit store revenue withdrawal', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 1000]);
    Sanctum::actingAs($warga);

    $this->postJson('/api/v1/fund-withdrawals', [
        'source' => 'store',
        'amount' => 10000,
        'destination_type' => 'bank_transfer',
        'destination_account' => '1234567890',
    ])->assertUnprocessable();
});
