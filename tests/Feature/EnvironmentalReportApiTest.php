<?php

use App\Models\EnvironmentalReport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('pemrakarsa can upload an environmental report through the API', function () {
    Storage::fake('public');
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);

    $response = $this->postJson('/api/v1/environmental-reports', [
        'judul_laporan' => 'Laporan Semester I 2026',
        'periode_semester' => 'Semester 1 (Jan - Jun)',
        'tahun' => 2026,
        'jenis_dokumen' => 'AMDAL',
        'timbulan_sampah_kg' => 1200.5,
        'metode_pengelolaan' => 'Pemilahan dan pengomposan mandiri.',
        'file_laporan' => UploadedFile::fake()->create('laporan.pdf', 1024, 'application/pdf'),
    ]);

    $response->assertCreated()->assertJsonPath('data.status', 'menunggu_verifikasi')->assertJsonPath('data.pemrakarsa_id', $pemrakarsa->id);
    $this->assertDatabaseHas('environmental_reports', ['pemrakarsa_id' => $pemrakarsa->id, 'judul_laporan' => 'Laporan Semester I 2026', 'status' => 'menunggu_verifikasi']);
});

test('pemrakarsa can only see their own environmental reports', function () {
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $otherPemrakarsa = User::factory()->pemrakarsa()->create();
    $ownReport = EnvironmentalReport::factory()->create(['pemrakarsa_id' => $pemrakarsa->id]);
    EnvironmentalReport::factory()->create(['pemrakarsa_id' => $otherPemrakarsa->id]);
    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);

    $this->getJson('/api/v1/environmental-reports')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $ownReport->id);
});

test('admin dlh can verify an environmental report through the API', function () {
    $admin = User::factory()->adminDlh()->create();
    $report = EnvironmentalReport::factory()->create(['status' => 'menunggu_verifikasi']);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->patchJson("/api/v1/environmental-reports/{$report->id}/verification", ['status' => 'disetujui', 'catatan_verifikasi' => 'Dokumen telah lengkap.'])
        ->assertOk()->assertJsonPath('data.status', 'disetujui');
    $this->assertDatabaseHas('environmental_reports', ['id' => $report->id, 'status' => 'disetujui']);
});

test('admin can only assign an active field officer through the environmental report API', function () {
    $admin = User::factory()->adminDlh()->create();
    $report = EnvironmentalReport::factory()->create(['status' => 'menunggu_verifikasi']);
    $invalidAssignees = [
        User::factory()->warga()->create(),
        User::factory()->petugasLapangan()->create(['is_active' => false]),
    ];
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    foreach ($invalidAssignees as $invalidAssignee) {
        $this->patchJson("/api/v1/environmental-reports/{$report->id}/verification", [
            'status' => 'perlu_cek_lapangan',
            'assigned_petugas_id' => $invalidAssignee->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['assigned_petugas_id']);
    }

    expect($report->fresh()->assigned_petugas_id)->toBeNull();
});

test('reassigning a field inspection clears the previous officer findings', function () {
    $admin = User::factory()->adminDlh()->create();
    $oldOfficer = User::factory()->petugasLapangan()->create();
    $newOfficer = User::factory()->petugasLapangan()->create();
    $report = EnvironmentalReport::factory()->create([
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $oldOfficer->id,
        'catatan_inspeksi_petugas' => 'Temuan petugas sebelumnya.',
        'hasil_inspeksi_lapangan' => 'perlu_perbaikan',
        'inspeksi_at' => now(),
    ]);
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $this->patchJson("/api/v1/environmental-reports/{$report->id}/verification", [
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $newOfficer->id,
        'instruksi_lapangan' => 'Lakukan pemeriksaan ulang dari awal.',
    ])->assertOk();

    $report->refresh();
    expect($report->assigned_petugas_id)->toBe($newOfficer->id)
        ->and($report->catatan_inspeksi_petugas)->toBeNull()
        ->and($report->hasil_inspeksi_lapangan)->toBeNull()
        ->and($report->inspeksi_at)->toBeNull();
});

test('warga cannot access environmental report endpoints', function () {
    Sanctum::actingAs(User::factory()->warga()->create(), ['role:warga']);
    $this->getJson('/api/v1/environmental-reports')->assertForbidden();
});

test('admin dlh can get macro monitoring summary via api', function () {
    $admin = User::factory()->adminDlh()->create();
    Sanctum::actingAs($admin, ['role:admin_dlh']);

    $response = $this->getJson('/api/v1/admin/monitoring');
    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_sampah_terkelola_kg',
                'total_aduan_aktif',
                'total_laporan_semester',
                'total_tps3r_aktif',
                'status_aduan',
                'status_laporan',
                'rekap_wilayah',
            ],
        ]);
});

test('petugas lapangan can view assigned environmental reports and submit field inspection', function () {
    $petugas = User::factory()->petugasLapangan()->create();
    $report = EnvironmentalReport::factory()->create([
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $petugas->id,
        'instruksi_lapangan' => 'Cek bak sedimentasi limbah cair dan filter cerobong.',
        'assigned_at' => now(),
    ]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    // List assigned reports
    $listResponse = $this->getJson('/api/v1/environmental-reports?assigned_to_me=1');
    $listResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $report->id)
        ->assertJsonPath('data.0.instruksi_lapangan', 'Cek bak sedimentasi limbah cair dan filter cerobong.')
        ->assertJsonPath('data.0.assigned_petugas.id', $petugas->id);

    // View detail
    $detailResponse = $this->getJson("/api/v1/environmental-reports/{$report->id}");
    $detailResponse->assertOk()
        ->assertJsonPath('data.id', $report->id)
        ->assertJsonPath('data.instruksi_lapangan', 'Cek bak sedimentasi limbah cair dan filter cerobong.');

    // Submit field check finding
    $fieldCheckResponse = $this->postJson("/api/v1/environmental-reports/{$report->id}/field-check", [
        'catatan_inspeksi' => 'Filter cerobong dalam kondisi baik, bak sedimentasi telah dibersihkan oleh pengelola.',
        'hasil_rekomendasi' => 'sesuai',
    ]);

    $fieldCheckResponse->assertOk()
        ->assertJsonPath('data.catatan_inspeksi_petugas', 'Filter cerobong dalam kondisi baik, bak sedimentasi telah dibersihkan oleh pengelola.')
        ->assertJsonPath('data.hasil_inspeksi_lapangan', 'sesuai');

    $report->refresh();
    expect($report->catatan_inspeksi_petugas)->toBe('Filter cerobong dalam kondisi baik, bak sedimentasi telah dibersihkan oleh pengelola.')
        ->and($report->hasil_inspeksi_lapangan)->toBe('sesuai')
        ->and($report->inspeksi_at)->not->toBeNull();
});

test('officer cannot submit field check for report assigned to another officer', function () {
    $officer1 = User::factory()->petugasLapangan()->create();
    $officer2 = User::factory()->petugasLapangan()->create();

    $report = EnvironmentalReport::factory()->create([
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $officer1->id,
    ]);

    Sanctum::actingAs($officer2, ['role:petugas_lapangan']);

    $response = $this->postJson("/api/v1/environmental-reports/{$report->id}/field-check", [
        'catatan_inspeksi' => 'Catatan ilegal',
        'hasil_rekomendasi' => 'sesuai',
    ]);

    $response->assertForbidden();
});

test('officer cannot overwrite field findings after admin finalizes the report', function () {
    $officer = User::factory()->petugasLapangan()->create();
    $report = EnvironmentalReport::factory()->create([
        'status' => 'disetujui',
        'assigned_petugas_id' => $officer->id,
        'catatan_inspeksi_petugas' => 'Temuan final yang sudah ditinjau admin.',
        'hasil_inspeksi_lapangan' => 'sesuai',
        'inspeksi_at' => now()->subHour(),
    ]);
    Sanctum::actingAs($officer, ['role:petugas_lapangan']);

    $this->postJson("/api/v1/environmental-reports/{$report->id}/field-check", [
        'catatan_inspeksi' => 'Upaya mengubah hasil setelah finalisasi.',
        'hasil_rekomendasi' => 'tidak_sesuai',
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'Hasil inspeksi hanya dapat diperbarui saat laporan berstatus perlu cek lapangan.');

    $report->refresh();
    expect($report->catatan_inspeksi_petugas)->toBe('Temuan final yang sudah ditinjau admin.')
        ->and($report->hasil_inspeksi_lapangan)->toBe('sesuai');
});

test('officer cannot list or view environmental reports assigned to other officers', function () {
    $officer1 = User::factory()->petugasLapangan()->create();
    $officer2 = User::factory()->petugasLapangan()->create();

    $report1 = EnvironmentalReport::factory()->create([
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $officer1->id,
    ]);

    $report2 = EnvironmentalReport::factory()->create([
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $officer2->id,
    ]);

    Sanctum::actingAs($officer1, ['role:petugas_lapangan']);

    // List only returns officer1's report
    $listResponse = $this->getJson('/api/v1/environmental-reports');
    $listResponse->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $report1->id);

    // Viewing report of officer2 should be 403 Forbidden
    $this->getJson("/api/v1/environmental-reports/{$report2->id}")->assertForbidden();
});
