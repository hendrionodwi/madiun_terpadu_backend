<?php

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('citizen can submit waste complaint with photo upload (PPL-F-001)', function () {
    Storage::fake('public');
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $file = UploadedFile::fake()->image('tumpukan_sampah.jpg', 640, 480);

    $payload = [
        'judul' => 'Tumpukan Sampah Liar di Pinggir Rel Kereta Kartoharjo',
        'deskripsi' => 'Terdapat pembuangan sampah liar yang sudah menumpuk dan menimbulkan bau tidak sedap.',
        'lokasi' => 'Jl. Sedap Malam dekat perlintasan kereta',
        'kelurahan' => 'Kartanegara',
        'kecamatan' => 'Kartoharjo',
        'latitude' => -7.6312,
        'longitude' => 111.5304,
        'foto' => $file,
    ];

    $response = $this->post('/api/v1/complaints', $payload, ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'judul' => 'Tumpukan Sampah Liar di Pinggir Rel Kereta Kartoharjo',
                'status' => 'diterima',
            ],
        ]);

    $complaint = Complaint::where('pelapor_id', $warga->id)->first();
    expect($complaint)->not->toBeNull();
    expect($complaint->foto)->not->toBeNull();
    Storage::disk('public')->assertExists($complaint->foto);

    $this->assertDatabaseHas('complaints', [
        'pelapor_id' => $warga->id,
        'kelurahan' => 'Kartanegara',
        'status' => 'diterima',
    ]);
});

test('citizen complaint requires photo for accountability', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $payload = [
        'judul' => 'Tumpukan Sampah Liar Tanpa Foto',
        'deskripsi' => 'Terdapat sampah tetapi pelapor tidak mengunggah bukti foto.',
        'lokasi' => 'Jl. Pahlawan Kota Madiun',
    ];

    $response = $this->postJson('/api/v1/complaints', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['foto']);
});

test('officer can submit field verification result (PPL-F-004)', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $complaint = Complaint::factory()->create([
        'status' => 'ditugaskan',
        'assigned_petugas_id' => $petugas->id,
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $payload = [
        'status_verifikasi' => 'selesai_dibersihkan',
        'catatan_petugas' => 'Lokasi telah dikunjungi dan tumpukan sampah liar telah diangkut ke dump truck.',
    ];

    $response = $this->postJson("/api/v1/complaints/{$complaint->id}/verify", $payload);

    $response->assertCreated()
        ->assertJson([
            'success' => true,
            'data' => [
                'status_pengaduan_terbaru' => 'selesai',
            ],
        ]);

    $this->assertDatabaseHas('complaint_verifications', [
        'complaint_id' => $complaint->id,
        'petugas_id' => $petugas->id,
        'status_verifikasi' => 'selesai_dibersihkan',
    ]);

    expect($complaint->fresh()->status)->toBe('selesai');
});

test('officer cannot view or verify complaints assigned to other officers', function () {
    $petugasA = User::factory()->petugasLapangan()->create(['name' => 'Petugas A']);
    $petugasB = User::factory()->petugasLapangan()->create(['name' => 'Petugas B']);

    $complaintA = Complaint::factory()->create([
        'assigned_petugas_id' => $petugasA->id,
        'status' => 'ditugaskan',
        'judul' => 'Tumpukan Sampah Wilayah Petugas A',
    ]);

    // Officer B should NOT see Petugas A's complaint in list
    Sanctum::actingAs($petugasB, ['role:petugas_lapangan']);

    $listResponse = $this->getJson('/api/v1/complaints');
    $listResponse->assertOk()
        ->assertJsonCount(0, 'data');

    // Officer B should NOT be able to view details of Petugas A's complaint
    $showResponse = $this->getJson("/api/v1/complaints/{$complaintA->id}");
    $showResponse->assertStatus(403)
        ->assertJsonPath('message', 'Akses ditolak. Pengaduan ini tidak ditugaskan kepada Anda.');

    // Officer B should NOT be able to verify Petugas A's complaint
    $verifyResponse = $this->postJson("/api/v1/complaints/{$complaintA->id}/verify", [
        'status_verifikasi' => 'selesai_dibersihkan',
        'catatan_petugas' => 'Petugas B mencoba memverifikasi tugas Petugas A',
    ]);
    $verifyResponse->assertStatus(403)
        ->assertJsonPath('message', 'Akses ditolak. Anda hanya dapat memverifikasi pengaduan yang ditugaskan kepada Anda.');

    // Officer A CAN see and verify their own complaint
    Sanctum::actingAs($petugasA, ['role:petugas_lapangan']);

    $listResponseA = $this->getJson('/api/v1/complaints');
    $listResponseA->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $complaintA->id);

    $showResponseA = $this->getJson("/api/v1/complaints/{$complaintA->id}");
    $showResponseA->assertOk()
        ->assertJsonPath('data.id', $complaintA->id);
});

test('citizen cannot verify complaint (forbidden role)', function () {
    $warga = User::factory()->warga()->create();
    $complaint = Complaint::factory()->create();

    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson("/api/v1/complaints/{$complaint->id}/verify", [
        'status_verifikasi' => 'selesai_dibersihkan',
        'catatan_petugas' => 'Catatan warga',
    ]);

    $response->assertStatus(403);
});

test('unrelated roles cannot access citizen complaint endpoints', function () {
    $complaint = Complaint::factory()->create();
    $unrelatedUsers = [
        User::factory()->pengelolaTps3r()->create(),
        User::factory()->pemrakarsa()->create(),
    ];

    foreach ($unrelatedUsers as $user) {
        Sanctum::actingAs($user, ['role:'.$user->role->value]);

        $this->getJson('/api/v1/complaints')->assertForbidden();
        $this->getJson("/api/v1/complaints/{$complaint->id}")->assertForbidden();
        $this->getJson("/api/v1/complaints/{$complaint->id}/notifications")->assertForbidden();
        $this->postJson('/api/v1/complaints', [])->assertForbidden();
    }
});

test('officer can filter complaints assigned to them and view admin instructions', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $otherPetugas = User::factory()->petugasLapangan()->create();

    $assignedComplaint = Complaint::factory()->create([
        'assigned_petugas_id' => $petugas->id,
        'instruksi_admin' => 'Periksa langsung tumpukan sampah di taman.',
        'status' => 'dalam_proses',
    ]);

    $otherComplaint = Complaint::factory()->create([
        'assigned_petugas_id' => $otherPetugas->id,
        'instruksi_admin' => 'Periksa wilayah lain.',
        'status' => 'dalam_proses',
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    // Filter assigned_to_me=1
    $response = $this->getJson('/api/v1/complaints?assigned_to_me=1');
    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $assignedComplaint->id)
        ->assertJsonPath('data.0.instruksi_admin', 'Periksa langsung tumpukan sampah di taman.')
        ->assertJsonPath('data.0.assigned_petugas.id', $petugas->id);

    // Detail show
    $detailResponse = $this->getJson("/api/v1/complaints/{$assignedComplaint->id}");
    $detailResponse->assertOk()
        ->assertJsonPath('data.instruksi_admin', 'Periksa langsung tumpukan sampah di taman.')
        ->assertJsonPath('data.assigned_petugas.name', $petugas->name);
});

test('citizen can submit complaint anonymously (PPL-F-007)', function () {
    $warga = User::factory()->warga()->create(['name' => 'Warga Rahasia']);
    Sanctum::actingAs($warga, ['role:warga']);

    $response = $this->postJson('/api/v1/complaints', [
        'judul' => 'Pembuangan Sampah Liar oleh Oknum',
        'kategori_masalah' => 'pembakaran_sampah',
        'deskripsi' => 'Terjadi pembakaran sampah plastik sembarangan di belakang ruko.',
        'lokasi' => 'Jl. Trunojoyo',
        'foto' => 'bukti_pembakaran.jpg',
        'is_anonim' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_anonim', true)
        ->assertJsonPath('data.kategori_masalah', 'pembakaran_sampah')
        ->assertJsonPath('data.display_pelapor_name', 'Warga Anonim (Identitas Dirahasiakan)');

    $this->assertDatabaseHas('complaints', [
        'pelapor_id' => $warga->id,
        'is_anonim' => true,
        'kategori_masalah' => 'pembakaran_sampah',
    ]);
});

test('field officer cannot see the identity of an anonymous reporter', function () {
    $officer = User::factory()->petugasLapangan()->create();
    $anonymousComplaint = Complaint::factory()->create([
        'assigned_petugas_id' => $officer->id,
        'is_anonim' => true,
        'status' => 'ditugaskan',
    ]);
    Sanctum::actingAs($officer, ['role:petugas_lapangan']);

    $listResponse = $this->getJson('/api/v1/complaints');
    $listResponse->assertOk()
        ->assertJsonPath('data.0.display_pelapor_name', 'Warga Anonim (Identitas Dirahasiakan)');
    expect($listResponse->json('data.0'))->not->toHaveKeys(['pelapor_id', 'pelapor']);

    $detailResponse = $this->getJson("/api/v1/complaints/{$anonymousComplaint->id}");
    $detailResponse->assertOk()
        ->assertJsonPath('data.display_pelapor_name', 'Warga Anonim (Identitas Dirahasiakan)');
    expect($detailResponse->json('data'))->not->toHaveKeys(['pelapor_id', 'pelapor']);
});

test('citizen can fetch complaint status tracking notifications (PPL-F-002 & PPL-F-006)', function () {
    $warga = User::factory()->warga()->create();
    Sanctum::actingAs($warga, ['role:warga']);

    $complaintResponse = $this->postJson('/api/v1/complaints', [
        'judul' => 'Saluran Air Tersumbat Sampah Pasar',
        'kategori_masalah' => 'saluran_tersumbat',
        'deskripsi' => 'Saluran air tersumbat sampah sayur dan plastik sehingga air meluap.',
        'lokasi' => 'Pasar Besar Madiun',
        'foto' => 'saluran_tersumbat.jpg',
    ]);

    $complaintId = $complaintResponse->json('data.id');

    // Fetch notifications
    $notifResponse = $this->getJson("/api/v1/complaints/{$complaintId}/notifications");
    $notifResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'diterima')
        ->assertJsonPath('data.0.judul_notifikasi', 'Pengaduan Diterima Sistem');
});
