<?php

use App\Models\Complaint;
use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PickupSchedule;
use App\Models\PointLedger;
use App\Models\RouteAssignment;
use App\Models\RouteSession;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteWeighing;
use Laravel\Sanctum\Sanctum;

test('petugas cannot start, finish, or renew a route assigned to another petugas', function () {
    $assignedPetugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();

    $route = RouteAssignment::factory()->create([
        'petugas_id' => $assignedPetugas->id,
        'route_code' => 'RUTE-ISOLASI-01',
        'is_active' => true,
    ]);

    Sanctum::actingAs($otherPetugas, ['role:petugas_lapangan']);

    $this->postJson("/api/v1/tracking/routes/{$route->route_code}/session/start")->assertForbidden();
    $this->postJson("/api/v1/tracking/routes/{$route->route_code}/session/finish")->assertForbidden();
    $this->postJson("/api/v1/tracking/routes/{$route->route_code}/renew-session")->assertForbidden();

    $this->assertDatabaseCount('route_sessions', 0);
});

test('route session enforces valid state transitions and idempotent active start', function () {
    $petugas = User::factory()->petugasLapangan()->create();

    $inactiveRoute = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-NONAKTIF-01',
        'is_active' => false,
    ]);

    $activeRoute = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-STATE-01',
        'is_active' => true,
    ]);

    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $activeRoute->route_code,
        'tanggal_penjemputan' => now()->toDateString(),
        'status' => 'selesai',
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    // Tidak bisa memulai rute non-aktif
    $this->postJson("/api/v1/tracking/routes/{$inactiveRoute->route_code}/session/start")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['route_code']);

    // Tidak bisa menyelesaikan rute sebelum dimulai
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/finish")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);

    // Mulai sesi rute
    $firstStart = $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/start", [
        'catatan' => 'Mulai armada dari depo',
    ]);
    $firstStart->assertOk()
        ->assertJsonPath('data.status', 'berlangsung');

    $startedSession = RouteSession::where('route_assignment_id', $activeRoute->id)->firstOrFail();

    // Panggil start ulang saat berlangsung tetap 200 idempoten tanpa membuat sesi ganda
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/start")
        ->assertOk()
        ->assertJsonPath('data.id', $startedSession->id)
        ->assertJsonPath('data.status', 'berlangsung')
        ->assertJsonPath('data.waktu_mulai', $startedSession->waktu_mulai);

    // Selesaikan sesi rute
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/finish", [
        'catatan' => 'Tiba di TPS3R',
    ])->assertOk()
        ->assertJsonPath('data.status', 'selesai');

    // Tidak bisa finish ulang atau start langsung dari selesai tanpa renew-session
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/finish")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/session/start")
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);

    // Setelah renew-session, jadwal kembali terjadwal
    $this->postJson("/api/v1/tracking/routes/{$activeRoute->route_code}/renew-session")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($schedule->fresh()->status)->toBe('terjadwal');
    expect(RouteSession::where('route_assignment_id', $activeRoute->id)->count())->toBe(1);
});

test('pickup schedule status transition blocks skipping state or modifying foreign or closed schedules', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();

    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'status' => 'terjadwal',
    ]);

    // Petugas lain dilarang mengubah status jadwal
    Sanctum::actingAs($otherPetugas, ['role:petugas_lapangan']);
    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'berlangsung',
    ])->assertForbidden();

    // Tidak boleh melompati terjadwal -> selesai secara langsung
    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);
    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'selesai',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);

    // Transisi valid: terjadwal -> berlangsung -> selesai
    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'berlangsung',
    ])->assertOk()->assertJsonPath('data.status', 'berlangsung');

    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'selesai',
    ])->assertOk()->assertJsonPath('data.status', 'selesai');

    // Jadwal yang sudah final (selesai) tidak dapat diubah kembali
    $this->patchJson("/api/v1/tracking/schedules/{$schedule->id}/status", [
        'status' => 'batal',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

test('petugas cannot verify already finalized or unassigned complaints', function () {
    $petugas = User::factory()->petugasLapangan()->create();

    $unassignedComplaint = Complaint::factory()->create([
        'status' => 'ditugaskan',
        'assigned_petugas_id' => null,
    ]);

    $openComplaint = Complaint::factory()->create([
        'status' => 'ditugaskan',
        'assigned_petugas_id' => $petugas->id,
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $this->postJson("/api/v1/complaints/{$unassignedComplaint->nomor_tiket}/verify", [
        'status_verifikasi' => 'valid',
        'catatan_petugas' => 'Mencoba verifikasi aduan tanpa penugasan',
    ])->assertForbidden();

    $this->postJson("/api/v1/complaints/{$openComplaint->nomor_tiket}/verify", [
        'status_verifikasi' => 'selesai_dibersihkan',
        'catatan_petugas' => 'Lokasi sudah dibersihkan tuntas.',
    ])->assertCreated();

    // Pengaduan yang sudah selesai tidak dapat diverifikasi ulang
    $this->postJson("/api/v1/complaints/{$openComplaint->nomor_tiket}/verify", [
        'status_verifikasi' => 'tidak_valid',
        'catatan_petugas' => 'Upaya menimpa hasil final.',
    ])->assertUnprocessable();

    expect($openComplaint->fresh()->status)->toBe('selesai');
});

test('petugas cannot weigh another officer schedule, closed schedule, mismatched warga, or non-warga user', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();
    $wargaA = User::factory()->warga()->create();
    $wargaB = User::factory()->warga()->create();

    $foreignSchedule = PickupSchedule::factory()->create([
        'petugas_id' => $otherPetugas->id,
        'warga_id' => $wargaA->id,
        'status' => 'terjadwal',
    ]);

    $cancelledSchedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'warga_id' => $wargaA->id,
        'status' => 'batal',
    ]);

    $activeOwnSchedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'warga_id' => $wargaA->id,
        'status' => 'berlangsung',
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    // Jadwal milik petugas lain -> 403
    $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $wargaA->id,
        'pickup_schedule_id' => $foreignSchedule->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 3.5,
    ])->assertForbidden();

    // Jadwal sudah batal -> 422
    $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $wargaA->id,
        'pickup_schedule_id' => $cancelledSchedule->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 3.5,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['pickup_schedule_id']);

    // Warga tidak cocok dengan warga pada jadwal -> 422
    $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $wargaB->id,
        'pickup_schedule_id' => $activeOwnSchedule->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 3.5,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['warga_id']);

    // Target user bukan role warga -> 422
    $this->postJson('/api/v1/tracking/weighings', [
        'warga_id' => $otherPetugas->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 2.0,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['warga_id']);

    $this->assertDatabaseCount('waste_weighings', 0);
    $this->assertDatabaseCount('point_ledgers', 0);
    expect($foreignSchedule->fresh()->status)->toBe('terjadwal');
    expect($activeOwnSchedule->fresh()->status)->toBe('berlangsung');
});

test('multi-item weighing replays idempotently by client_reference_id without double points and preserves flutter contract', function () {
    MasterData::updateOrCreate(
        ['group' => 'waste_category', 'code' => 'organik'],
        ['name' => 'Sampah Organik', 'numeric_value' => 50, 'pillar' => 'organik', 'is_active' => true]
    );
    MasterData::updateOrCreate(
        ['group' => 'waste_category', 'code' => 'anorganik_plastik'],
        ['name' => 'Plastik Daur Ulang', 'numeric_value' => 150, 'pillar' => 'anorganik', 'is_active' => true]
    );

    $petugas = User::factory()->petugasLapangan()->create();
    $warga = User::factory()->warga()->create();
    $wargaProfile = WargaProfile::factory()->create([
        'user_id' => $warga->id,
        'saldo_poin' => 100,
    ]);

    $schedule = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'warga_id' => $warga->id,
        'status' => 'berlangsung',
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $payload = [
        'warga_id' => $warga->id,
        'pickup_schedule_id' => $schedule->id,
        'client_reference_id' => 'OFFLINE-MULTI-WGH-01',
        'items' => [
            ['jenis_sampah' => 'organik', 'berat_kg' => 2.0, 'catatan' => 'Sisa dapur'],
            ['jenis_sampah' => 'anorganik_plastik', 'berat_kg' => 3.0, 'catatan' => 'Botol PET'],
        ],
    ];

    $created = $this->postJson('/api/v1/tracking/weighings', $payload);
    $created->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.poin_didapat', 550)
        ->assertJsonPath('data.total_saldo_poin_warga', 650)
        ->assertJsonCount(2, 'data.items')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'timbangan' => ['id', 'warga_id', 'petugas_id', 'jenis_sampah', 'berat_kg', 'poin_didapat'],
                'items',
                'poin_didapat',
                'total_saldo_poin_warga',
            ],
        ]);

    expect($schedule->fresh()->status)->toBe('selesai');

    // Retry sinkronisasi offline meskipun jadwal kini berstatus selesai tetap replay idempoten
    $replay = $this->postJson('/api/v1/tracking/weighings', $payload);
    $replay->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('idempotent_replay', true)
        ->assertJsonPath('data.poin_didapat', 550)
        ->assertJsonPath('data.total_saldo_poin_warga', 650)
        ->assertJsonCount(2, 'data.items');

    expect(WasteWeighing::where('petugas_id', $petugas->id)->count())->toBe(2);
    expect(PointLedger::where('warga_id', $warga->id)->count())->toBe(1);
    expect($wargaProfile->fresh()->saldo_poin)->toBe(650);
});

test('tps3r deposit enforces facility activity, weighing ownership, single-link integrity, and null-petugas idempotency', function () {
    $petugasA = User::factory()->petugasLapangan()->create();
    $petugasB = User::factory()->petugasLapangan()->create();
    $pemrakarsa = User::factory()->pemrakarsa()->create();

    $pengelolaA = User::factory()->pengelolaTps3r()->create();
    $profileA = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelolaA->id]);

    $pengelolaB = User::factory()->pengelolaTps3r()->create();
    $profileB = PengelolaTps3rProfile::factory()->create(['user_id' => $pengelolaB->id]);

    $inactiveManager = User::factory()->pengelolaTps3r()->create(['is_active' => false]);
    $inactiveProfile = PengelolaTps3rProfile::factory()->create(['user_id' => $inactiveManager->id]);

    $ownWeighing = WasteWeighing::factory()->create([
        'petugas_id' => $petugasA->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 12.5,
    ]);
    $foreignWeighing = WasteWeighing::factory()->create([
        'petugas_id' => $petugasB->id,
        'jenis_sampah' => 'anorganik_plastik',
        'berat_kg' => 8.0,
    ]);

    // Pemrakarsa tidak diizinkan menyetor
    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profileA->id,
        'sumber_sampah' => 'pemrakarsa_komersial',
        'jenis_sampah' => 'organik',
        'berat_kg' => 15.0,
        'tanggal_setor' => now()->toDateString(),
    ])->assertForbidden();

    // Pengelola TPS3R tidak boleh mencatat setoran ke fasilitas pengelola lain
    Sanctum::actingAs($pengelolaA, ['role:pengelola_tps3r']);
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profileB->id,
        'sumber_sampah' => 'warga_mandiri',
        'jenis_sampah' => 'organik',
        'berat_kg' => 10.0,
        'tanggal_setor' => now()->toDateString(),
    ])->assertForbidden();

    // Petugas A tidak boleh menyetor ke fasilitas TPS3R non-aktif
    Sanctum::actingAs($petugasA, ['role:petugas_lapangan']);
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $inactiveProfile->id,
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 25.0,
        'tanggal_setor' => now()->toDateString(),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['tps3r_profile_id']);

    // Petugas A tidak boleh mengklaim timbangan milik Petugas B
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profileA->id,
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 20.0,
        'tanggal_setor' => now()->toDateString(),
        'weighing_ids' => [$foreignWeighing->id],
    ])->assertForbidden();

    // Petugas A mencatat setoran dengan weighing miliknya dan client_reference_id
    $depositPayload = [
        'tps3r_profile_id' => $profileA->id,
        'client_reference_id' => 'OFFLINE-PETUGAS-DEP-01',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 12.5,
        'tanggal_setor' => now()->toDateString(),
        'weighing_ids' => [$ownWeighing->id],
    ];

    $createdDeposit = $this->postJson('/api/v1/tps3r/deposits', $depositPayload);
    $createdDeposit->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'menunggu_konfirmasi')
        ->assertJsonPath('data.berat_kg', 12.5)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'nomor_resi',
                'status',
                'berat_kg',
                'tps3r_profile',
                'weighings',
            ],
        ]);

    // Replay offline dengan client_reference_id yang sama -> 200 idempotent_replay: true
    $this->postJson('/api/v1/tps3r/deposits', $depositPayload)
        ->assertOk()
        ->assertJsonPath('idempotent_replay', true)
        ->assertJsonPath('data.id', $createdDeposit->json('data.id'));

    // Setoran baru yang mencoba menghubungkan weighing_ids yang sudah terpakai -> 422
    $this->postJson('/api/v1/tps3r/deposits', [
        'tps3r_profile_id' => $profileA->id,
        'client_reference_id' => 'OFFLINE-PETUGAS-DEP-02',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 12.5,
        'tanggal_setor' => now()->toDateString(),
        'weighing_ids' => [$ownWeighing->id],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['weighing_ids']);

    // Pengelola TPS3R tanpa petugas_id tetap idempoten berdasarkan client_reference_id
    Sanctum::actingAs($pengelolaA, ['role:pengelola_tps3r']);
    $selfPayload = [
        'client_reference_id' => 'OFFLINE-PENGELOLA-SELF-01',
        'sumber_sampah' => 'warga_mandiri',
        'jenis_sampah' => 'organik',
        'berat_kg' => 9.0,
        'tanggal_setor' => now()->toDateString(),
    ];

    $firstSelf = $this->postJson('/api/v1/tps3r/deposits', $selfPayload);
    $firstSelf->assertCreated();

    $this->postJson('/api/v1/tps3r/deposits', $selfPayload)
        ->assertOk()
        ->assertJsonPath('idempotent_replay', true)
        ->assertJsonPath('data.id', $firstSelf->json('data.id'));

    expect(Tps3rWasteDeposit::where('client_reference_id', 'OFFLINE-PETUGAS-DEP-01')->count())->toBe(1);
    expect(Tps3rWasteDeposit::where('client_reference_id', 'OFFLINE-PENGELOLA-SELF-01')->count())->toBe(1);
});
