<?php

use App\Models\AdminDlhProfile;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\PengelolaTps3rProfile;
use App\Models\PetugasLapanganProfile;
use App\Models\PickupSchedule;
use App\Models\PointLedger;
use App\Models\RetributionBill;
use App\Models\RouteAssignment;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WargaProfile;
use App\Models\WasteProduct;
use App\Models\WasteWeighing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

test('warga and petugas summary apis return accurate kpis and enforce roles', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 350]);

    $petugas = User::factory()->petugasLapangan()->create();
    PetugasLapanganProfile::factory()->create(['user_id' => $petugas->id]);

    PickupSchedule::factory()->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'tanggal_penjemputan' => now()->toDateString(),
        'status' => 'terjadwal',
    ]);
    WasteWeighing::factory()->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'berat_kg' => 4.5,
        'poin_didapat' => 90,
    ]);
    RetributionBill::factory()->create([
        'warga_id' => $warga->id,
        'status' => 'menunggu_pembayaran',
        'jatuh_tempo' => now()->subDays(3)->toDateString(),
    ]);

    Sanctum::actingAs($warga, ['role:warga']);
    $this->getJson('/api/v1/warga/summary')
        ->assertOk()
        ->assertJsonPath('data.saldo_poin', 350)
        ->assertJsonPath('data.overdue_retribution.has_overdue', true);
    $this->getJson('/api/v1/petugas/summary')->assertForbidden();

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);
    $this->getJson('/api/v1/petugas/summary')
        ->assertOk()
        ->assertJsonPath('data.today_stats.pickup_total', 1);
    $this->getJson('/api/v1/warga/summary')->assertForbidden();
});

test('pickup schedules support generated kode_jadwal search show and weighing receipt', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id]);
    $petugas = User::factory()->petugasLapangan()->create();
    PetugasLapanganProfile::factory()->create(['user_id' => $petugas->id]);

    $schedule = PickupSchedule::factory()->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'nama_wilayah_rute' => 'Rute Taman Selatan',
        'estimasi_berat_kg' => 6.25,
        'status' => 'berlangsung',
    ]);

    expect($schedule->kode_jadwal)->toStartWith('JMP-');

    $weighing = WasteWeighing::factory()->create([
        'pickup_schedule_id' => $schedule->id,
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'berat_kg' => 5.0,
        'poin_didapat' => 100,
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $this->getJson('/api/v1/tracking/schedules?status=berjalan&search=Taman')
        ->assertOk()
        ->assertJsonFragment(['kode_jadwal' => $schedule->kode_jadwal]);

    $this->getJson("/api/v1/tracking/schedules/{$schedule->kode_jadwal}")
        ->assertOk()
        ->assertJsonPath('data.kode_jadwal', $schedule->kode_jadwal);

    $this->getJson("/api/v1/tracking/weighings/{$weighing->id}/receipt")
        ->assertOk()
        ->assertJsonPath('data.total_poin', 100);
});

test('offline weighing and tps3r deposit replay idempotently by client_reference_id', function () {
    $warga = User::factory()->warga()->create();
    WargaProfile::factory()->create(['user_id' => $warga->id, 'saldo_poin' => 0]);

    $petugas = User::factory()->petugasLapangan()->create();
    PetugasLapanganProfile::factory()->create(['user_id' => $petugas->id]);

    $tps3r = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3r->id]);

    Sanctum::actingAs($petugas, ['role:petugas_lapangan']);

    $weighingPayload = [
        'warga_id' => $warga->id,
        'client_reference_id' => 'OFFLINE-WGH-001',
        'jenis_sampah' => 'anorganik_plastik',
        'berat_kg' => 3.0,
    ];

    $this->postJson('/api/v1/tracking/weighings', $weighingPayload)->assertCreated();
    $this->postJson('/api/v1/tracking/weighings', $weighingPayload)
        ->assertOk()
        ->assertJsonPath('idempotent_replay', true);

    expect(WasteWeighing::where('client_reference_id', 'OFFLINE-WGH-001')->count())->toBe(1);
    expect(PointLedger::where('warga_id', $warga->id)->count())->toBe(1);

    Sanctum::actingAs($tps3r, ['role:pengelola_tps3r']);
    $depositPayload = [
        'petugas_id' => $petugas->id,
        'tps3r_profile_id' => $tps3rProfile->id,
        'client_reference_id' => 'OFFLINE-DEP-001',
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 45.5,
        'berat_bruto_kg' => 52.0,
        'berat_tara_kg' => 6.5,
        'tanggal_setor' => now()->toDateString(),
    ];

    $this->postJson('/api/v1/tps3r/deposits', $depositPayload)->assertCreated();
    $this->postJson('/api/v1/tps3r/deposits', $depositPayload)
        ->assertOk()
        ->assertJsonPath('idempotent_replay', true);

    expect(Tps3rWasteDeposit::where('client_reference_id', 'OFFLINE-DEP-001')->count())->toBe(1);
});

test('tps3r supports perlu_perbaikan verification status and processing workflow api', function () {
    $tps3r = User::factory()->pengelolaTps3r()->create();
    $otherTps3r = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3r->id]);
    PengelolaTps3rProfile::factory()->create(['user_id' => $otherTps3r->id]);

    $deposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $tps3r->id,
        'tps3r_profile_id' => $profile->id,
        'status' => 'menunggu_verifikasi',
    ]);

    Sanctum::actingAs($tps3r, ['role:pengelola_tps3r']);

    $this->patchJson("/api/v1/tps3r/deposits/{$deposit->nomor_resi}/verify", [
        'status' => 'perlu_perbaikan',
        'catatan_verifikasi' => 'Plastik tercampur sisa makanan, harap pilah ulang.',
    ])->assertOk()
        ->assertJsonPath('data.status', 'perlu_perbaikan');

    $processingCreate = $this->postJson('/api/v1/tps3r/processing', [
        'tps3r_waste_deposit_id' => $deposit->id,
        'process_method' => 'composting',
        'waste_type' => 'organik',
        'input_weight_kg' => 25.0,
        'started_on' => now()->toDateString(),
    ])->assertCreated();

    $recordId = $processingCreate->json('data.id');

    $this->patchJson("/api/v1/tps3r/processing/{$recordId}", [
        'status' => 'processing',
    ])->assertOk()
        ->assertJsonPath('data.status', 'processing');

    Sanctum::actingAs($otherTps3r, ['role:pengelola_tps3r']);
    $this->patchJson("/api/v1/tps3r/processing/{$recordId}", [
        'status' => 'completed',
    ])->assertForbidden();
});

test('marketplace catalog filters work and seller can update or delete product with photo cleanup', function () {
    Storage::fake('public');

    $seller = User::factory()->pengelolaTps3r()->create();
    $otherSeller = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $seller->id]);
    PengelolaTps3rProfile::factory()->create(['user_id' => $otherSeller->id]);

    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Super Murah',
        'harga' => 10000,
        'is_featured' => true,
        'is_available' => true,
        'stok' => 5,
    ]);
    WasteProduct::factory()->create([
        'seller_id' => $seller->id,
        'nama_produk' => 'Kompos Paket Besar',
        'harga' => 55000,
        'is_featured' => false,
        'is_available' => true,
        'stok' => 5,
    ]);

    $this->getJson('/api/v1/marketplace/products?featured_only=1&harga_max=20000')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama_produk', 'Kompos Super Murah');

    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);

    $created = $this->postJson('/api/v1/marketplace/products', [
        'nama_produk' => 'Pot Daur Ulang',
        'kategori' => 'kerajinan_plastik',
        'harga' => 30000,
        'stok' => 12,
        'foto' => UploadedFile::fake()->image('pot-1.jpg', 300, 300),
    ])->assertCreated();

    $productPublicId = $created->json('data.public_id');
    $firstPhoto = WasteProduct::where('public_id', $productPublicId)->value('foto');
    Storage::disk('public')->assertExists($firstPhoto);

    Sanctum::actingAs($otherSeller, ['role:pengelola_tps3r']);
    $this->patchJson("/api/v1/marketplace/products/{$productPublicId}", [
        'harga' => 15000,
    ])->assertForbidden();

    Sanctum::actingAs($seller, ['role:pengelola_tps3r']);
    $this->patchJson("/api/v1/marketplace/products/{$productPublicId}", [
        'harga' => 32000,
        'foto' => UploadedFile::fake()->image('pot-2.png', 300, 300),
    ])->assertOk()
        ->assertJsonPath('data.harga', 32000);

    $secondPhoto = WasteProduct::where('public_id', $productPublicId)->value('foto');
    Storage::disk('public')->assertMissing($firstPhoto);
    Storage::disk('public')->assertExists($secondPhoto);

    $this->deleteJson("/api/v1/marketplace/products/{$productPublicId}")->assertOk();
    Storage::disk('public')->assertMissing($secondPhoto);
});

test('citizen can append additional info to open complaint while others and closed complaints fail', function () {
    Storage::fake('public');

    $warga = User::factory()->warga()->create();
    $otherWarga = User::factory()->warga()->create();
    $openComplaint = Complaint::factory()->create([
        'pelapor_id' => $warga->id,
        'status' => 'menunggu_verifikasi',
    ]);
    $closedComplaint = Complaint::factory()->create([
        'pelapor_id' => $warga->id,
        'status' => 'selesai',
    ]);

    Sanctum::actingAs($warga, ['role:warga']);

    $this->postJson("/api/v1/complaints/{$openComplaint->nomor_tiket}/additional-info", [
        'pesan' => 'Titik tumpukan berada tepat di sebelah pos kamling RT 04.',
        'foto' => UploadedFile::fake()->image('bukti-tambahan.jpg', 300, 300),
    ])->assertCreated()
        ->assertJsonPath('success', true);

    expect($openComplaint->statusLogs()->where('judul_notifikasi', 'Informasi Tambahan Pelapor')->exists())->toBeTrue();

    $this->postJson("/api/v1/complaints/{$closedComplaint->nomor_tiket}/additional-info", [
        'pesan' => 'Menambahkan keterangan setelah selesai.',
    ])->assertUnprocessable();

    Sanctum::actingAs($otherWarga, ['role:warga']);
    $this->postJson("/api/v1/complaints/{$openComplaint->nomor_tiket}/additional-info", [
        'pesan' => 'Akses tidak sah.',
    ])->assertForbidden();
});

test('pemrakarsa environmental report supports draft save edit submit delete and auto bap on approval', function () {
    Storage::fake('public');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);

    $draftResponse = $this->postJson('/api/v1/environmental-reports', [
        'judul_laporan' => 'Draft RKL-RPL Semester 1',
        'periode_semester' => 'Semester 1 (Jan - Jun)',
        'tahun' => 2026,
        'jenis_dokumen' => 'UKL-UPL',
        'is_draft' => true,
        'monitoring_data' => ['b3_kg' => 12.5, 'domestik_kg' => 450],
    ])->assertCreated()
        ->assertJsonPath('data.status', 'draft');

    $publicId = $draftResponse->json('data.public_id');

    $this->patchJson("/api/v1/environmental-reports/{$publicId}", [
        'status' => 'menunggu_verifikasi',
        'file_laporan' => UploadedFile::fake()->create('laporan-resmi.pdf', 120, 'application/pdf'),
    ])->assertOk()
        ->assertJsonPath('data.status', 'menunggu_verifikasi');

    $this->deleteJson("/api/v1/environmental-reports/{$publicId}")->assertUnprocessable();

    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $verifyResponse = $this->patchJson("/api/v1/environmental-reports/{$publicId}/verification", [
        'status' => 'disetujui',
        'catatan_verifikasi' => 'Dokumen lengkap sesuai ketentuan DLH.',
    ])->assertOk()
        ->assertJsonPath('data.status', 'disetujui');

    expect($verifyResponse->json('data.nomor_bap'))->toStartWith('BAP-');

    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);
    $deletableDraft = $this->postJson('/api/v1/environmental-reports', [
        'judul_laporan' => 'Draft Untuk Dihapus',
        'periode_semester' => 'Semester 2 (Jul - Des)',
        'tahun' => 2026,
        'jenis_dokumen' => 'SPPL',
        'status' => 'draft',
        'file_laporan' => UploadedFile::fake()->create('draft.pdf', 50, 'application/pdf'),
    ])->assertCreated();

    $deletableId = $deletableDraft->json('data.public_id');
    $deletablePath = EnvironmentalReport::where('public_id', $deletableId)->value('file_path');
    Storage::disk('public')->assertExists($deletablePath);

    $this->deleteJson("/api/v1/environmental-reports/{$deletableId}")->assertOk();
    Storage::disk('public')->assertMissing($deletablePath);
    expect(EnvironmentalReport::where('public_id', $deletableId)->exists())->toBeFalse();
});

test('web dashboards support perlu_perbaikan deposit status and pemrakarsa draft report flow', function () {
    Storage::fake('public');

    $tps3r = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3r->id]);
    $deposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $tps3r->id,
        'tps3r_profile_id' => $profile->id,
        'status' => 'menunggu_konfirmasi',
    ]);

    $this->actingAs($tps3r)
        ->patch(route('dashboard.tps3r.verify-deposit', $deposit->nomor_resi), [
            'status' => 'perlu_perbaikan',
            'catatan_verifikasi' => 'Residu terlalu tinggi pada karung A.',
        ])->assertRedirect();

    expect($deposit->fresh()->status)->toBe('perlu_perbaikan');

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    $this->actingAs($pemrakarsa)
        ->post(route('dashboard.pemrakarsa.store-laporan'), [
            'judul_laporan' => 'Web Draft Laporan Semester 1',
            'periode_semester' => 'Semester 1 (Jan - Jun)',
            'tahun' => 2026,
            'jenis_dokumen' => 'SPPL',
            'is_draft' => true,
        ])->assertRedirect();

    $report = EnvironmentalReport::where('judul_laporan', 'Web Draft Laporan Semester 1')->firstOrFail();
    expect($report->status)->toBe('draft');

    $this->actingAs($pemrakarsa)
        ->patch(route('dashboard.pemrakarsa.reports.update', $report->public_id), [
            'submit' => true,
            'file_laporan' => UploadedFile::fake()->create('web-laporan.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

    expect($report->fresh()->status)->toBe('menunggu_verifikasi');

    $this->actingAs($admin)
        ->patch(route('dashboard.admin.reports.verify', $report->public_id), [
            'status' => 'disetujui',
            'catatan_verifikasi' => 'Disetujui DLH melalui dashboard web.',
        ])->assertRedirect();

    expect($report->fresh()->status)->toBe('disetujui');
    expect($report->fresh()->nomor_bap)->toStartWith('BAP-');
});

test('tps3r deposit validation and web contracts support bruto tara status filter and featured products', function () {
    $tps3r = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create(['user_id' => $tps3r->id]);
    $petugas = User::factory()->petugasLapangan()->create();

    Sanctum::actingAs($tps3r, ['role:pengelola_tps3r']);

    $this->postJson('/api/v1/tps3r/deposits', [
        'petugas_id' => $petugas->id,
        'sumber_sampah' => 'armada_kelurahan',
        'jenis_sampah' => 'organik',
        'berat_kg' => 20,
        'berat_bruto_kg' => 10,
        'berat_tara_kg' => 15,
        'tanggal_setor' => now()->toDateString(),
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['berat_bruto_kg']);

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $tps3r->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-FILTER-OK',
        'status' => 'diterima',
        'tanggal_setor' => now()->toDateString(),
    ]);
    $needsFixDeposit = Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $tps3r->id,
        'tps3r_profile_id' => $profile->id,
        'nomor_resi' => 'DEP-FILTER-FIX',
        'status' => 'menunggu_konfirmasi',
        'tanggal_setor' => now()->toDateString(),
    ]);

    $this->patchJson("/api/v1/tps3r/deposits/{$needsFixDeposit->nomor_resi}/verify", [
        'status' => 'perlu_perbaikan',
        'berat_organik_pilah_kg' => 12.5,
        'berat_anorganik_pilah_kg' => 4.5,
        'berat_residu_pilah_kg' => 2.0,
        'catatan_verifikasi' => 'Residu perlu dipisahkan ulang.',
    ])->assertOk()
        ->assertJsonPath('data.status', 'perlu_perbaikan')
        ->assertJsonPath('data.berat_organik_pilah_kg', 12.5);

    $this->getJson('/api/v1/tps3r/deposits?status=perlu_perbaikan')
        ->assertOk()
        ->assertJsonFragment(['nomor_resi' => 'DEP-FILTER-FIX'])
        ->assertJsonMissing(['nomor_resi' => 'DEP-FILTER-OK']);

    $this->actingAs($tps3r)
        ->post(route('dashboard.tps3r.store-deposit'), [
            'sumber_sampah' => 'warga_mandiri',
            'jenis_sampah' => 'organik',
            'route_code' => 'RUTE-KTH-01',
            'berat_kg' => 18.5,
            'berat_bruto_kg' => 25.0,
            'berat_tara_kg' => 6.5,
            'tanggal_setor' => now()->toDateString(),
        ])->assertRedirect();

    $storedDeposit = Tps3rWasteDeposit::where('route_code', 'RUTE-KTH-01')->firstOrFail();
    expect($storedDeposit->berat_bruto_kg)->toBe(25.0);
    expect($storedDeposit->berat_tara_kg)->toBe(6.5);

    $this->actingAs($tps3r)
        ->get(route('dashboard.tps3r.deposits', ['status' => 'perlu_perbaikan']))
        ->assertOk()
        ->assertViewHas('statusCounts', fn (array $counts): bool => ($counts['perlu_perbaikan'] ?? 0) === 1)
        ->assertSee('DEP-FILTER-FIX')
        ->assertDontSee('DEP-FILTER-OK');

    $this->actingAs($tps3r)
        ->post(route('dashboard.tps3r.store-product'), [
            'nama_produk' => 'Kompos Super Unggulan',
            'kategori' => 'pupuk_kompos',
            'harga' => 30000,
            'stok' => 12,
            'is_available' => true,
            'is_featured' => true,
        ])->assertRedirect();

    $product = WasteProduct::where('nama_produk', 'Kompos Super Unggulan')->firstOrFail();
    expect($product->is_featured)->toBeTrue();

    $this->actingAs($tps3r)
        ->patch(route('dashboard.tps3r.products.update', $product), [
            'nama_produk' => 'Kompos Super Unggulan v2',
            'kategori' => 'pupuk_kompos',
            'harga' => 32000,
            'stok' => 10,
            'is_available' => true,
        ])->assertRedirect();

    expect($product->fresh()->is_featured)->toBeTrue();

    $this->actingAs($tps3r)
        ->patch(route('dashboard.tps3r.products.update', $product), [
            'nama_produk' => 'Kompos Super Unggulan v2',
            'kategori' => 'pupuk_kompos',
            'harga' => 32000,
            'stok' => 10,
            'is_available' => true,
            'is_featured' => false,
        ])->assertRedirect();

    expect($product->fresh()->is_featured)->toBeFalse();
});

test('admin dlh queues hide pemrakarsa private drafts and receive complaint sla routes and logs', function () {
    $pemrakarsa = User::factory()->pemrakarsa()->create();
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);
    $petugas = User::factory()->petugasLapangan()->create();

    RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'assigned_by' => $admin->id,
        'route_code' => 'RUTE-SLA-01',
        'is_active' => true,
    ]);

    EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsa->id,
        'judul_laporan' => 'Draft Rahasia Pemrakarsa',
        'status' => 'draft',
    ]);
    EnvironmentalReport::factory()->create([
        'pemrakarsa_id' => $pemrakarsa->id,
        'judul_laporan' => 'Laporan Resmi Diajukan',
        'status' => 'menunggu_verifikasi',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard.admin.reports'))
        ->assertOk()
        ->assertSee('Laporan Resmi Diajukan')
        ->assertDontSee('Draft Rahasia Pemrakarsa');

    Sanctum::actingAs($admin, ['role:admin_dlh']);
    $this->getJson('/api/v1/environmental-reports')
        ->assertOk()
        ->assertJsonFragment(['judul_laporan' => 'Laporan Resmi Diajukan'])
        ->assertJsonMissing(['judul_laporan' => 'Draft Rahasia Pemrakarsa']);

    Sanctum::actingAs($pemrakarsa, ['role:pemrakarsa']);
    $this->getJson('/api/v1/environmental-reports')
        ->assertOk()
        ->assertJsonFragment(['judul_laporan' => 'Draft Rahasia Pemrakarsa']);

    $complaint = Complaint::factory()->create(['status' => 'menunggu_verifikasi']);

    $this->actingAs($admin)
        ->get(route('dashboard.admin.complaints'))
        ->assertOk()
        ->assertViewHas('activeRoutes', fn ($routes): bool => $routes->contains('route_code', 'RUTE-SLA-01'))
        ->assertViewHas('complaints', fn ($paginator): bool => $paginator->getCollection()->firstWhere('id', $complaint->id)?->relationLoaded('statusLogs') === true);
});
