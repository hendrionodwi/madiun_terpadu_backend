<?php

use App\Models\AdminDlhProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\Tps3rWasteDeposit;
use App\Models\User;
use App\Models\WasteWeighing;

test('admin dlh can view printable official waste recap report (format=print)', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    $warga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    WasteWeighing::factory()->create([
        'warga_id' => $warga->id,
        'petugas_id' => $petugas->id,
        'jenis_sampah' => 'organik',
        'berat_kg' => 25.5,
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.reports.export', [
        'format' => 'print',
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertSee('Pemerintah Kota Madiun')
        ->assertSee('Dinas Lingkungan Hidup')
        ->assertSee('Laporan Rekapitulasi Timbulan & Neraca Pengelolaan Sampah Kota Madiun', false)
        ->assertSee('Kecamatan Kartoharjo')
        ->assertSee('Kecamatan Manguharjo')
        ->assertSee('Kecamatan Taman');
});

test('admin dlh can download csv export stream for waste recap (format=csv)', function () {
    $admin = User::factory()->adminDlh()->create();
    AdminDlhProfile::factory()->create(['user_id' => $admin->id]);

    $tps3rUser = User::factory()->pengelolaTps3r()->create();
    $tps3rProfile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $tps3rUser->id,
        'nama_tps3r' => 'TPS3R Sukun Bersih',
    ]);

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $tps3rUser->id,
        'tps3r_profile_id' => $tps3rProfile->id,
        'berat_kg' => 150.0,
        'tanggal_setor' => now()->toDateString(),
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.reports.export', [
        'format' => 'csv',
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('rekap-timbulan-sampah-dlh-kota-madiun-');

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('PEMERINTAH KOTA MADIUN - DINAS LINGKUNGAN HIDUP');
    expect($csvContent)->toContain('RINGKASAN EKSEKUTIF KOTA MADIUN');
    expect($csvContent)->toContain('REKAPITULASI PER WILAYAH KECAMATAN');
});

test('pengelola tps3r can view printable official volume recap report (format=print)', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Winongo Asri',
        'kode_tps3r' => 'TPS3R-WNG-01',
    ]);

    $petugas = User::factory()->petugasLapangan()->create();

    Tps3rWasteDeposit::factory()->create([
        'pengelola_id' => $pengelola->id,
        'tps3r_profile_id' => $profile->id,
        'petugas_id' => $petugas->id,
        'berat_kg' => 200.0,
        'berat_organik_pilah_kg' => 120.0,
        'berat_anorganik_pilah_kg' => 50.0,
        'berat_residu_pilah_kg' => 30.0,
        'tanggal_setor' => now()->toDateString(),
    ]);

    $response = $this->actingAs($pengelola)->get(route('dashboard.tps3r.volume-recap.export', [
        'format' => 'print',
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk()
        ->assertSee('Pemerintah Kota Madiun')
        ->assertSee('Dinas Lingkungan Hidup')
        ->assertSee('Unit Pengolahan Sampah Terpadu 3R')
        ->assertSee('TPS3R WINONGO ASRI')
        ->assertSee('TPS3R-WNG-01')
        ->assertSee('Berita Acara Rekapitulasi Timbangan & Neraca Sampah Masuk', false)
        ->assertSee('Buku Ekspedisi Setoran Masuk ke TPS 3R');
});

test('pengelola tps3r can download csv export stream for volume recap (format=csv)', function () {
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Winongo Asri',
        'kode_tps3r' => 'TPS3R-WNG-01',
    ]);

    $response = $this->actingAs($pengelola)->get(route('dashboard.tps3r.volume-recap.export', [
        'format' => 'csv',
        'start_date' => now()->startOfMonth()->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('rekap-volume-sampah-TPS3R-WNG-01-');

    ob_start();
    $response->sendContent();
    $csvContent = ob_get_clean();

    expect($csvContent)->toContain('UNIT PENGOLAHAN SAMPAH 3R: TPS3R Winongo Asri');
    expect($csvContent)->toContain('NERACA OPERASIONAL & KAPASITAS FASILITAS');
    expect($csvContent)->toContain('BUKU EKSPEDISI SETORAN TRUK ARMADA & WARGA');
});

test('admin dlh can also export tps3r volume recap for any facility', function () {
    $admin = User::factory()->adminDlh()->create();
    $pengelola = User::factory()->pengelolaTps3r()->create();
    $profile = PengelolaTps3rProfile::factory()->create([
        'user_id' => $pengelola->id,
        'nama_tps3r' => 'TPS3R Rejomulyo Maju',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.tps3r.volume-recap.export', [
        'format' => 'print',
        'tps3r_profile_id' => $profile->id,
    ]));

    $response->assertOk()
        ->assertSee('TPS3R REJOMULYO MAJU');
});

test('unauthorized users with other roles are forbidden from dlh export routes', function () {
    $warga = User::factory()->warga()->create();

    // Warga forbidden from admin export
    $this->actingAs($warga)->get(route('dashboard.admin.reports.export'))
        ->assertForbidden();

    // Warga forbidden from tps3r export
    $this->actingAs($warga)->get(route('dashboard.tps3r.volume-recap.export'))
        ->assertForbidden();
});

test('unauthenticated guests are redirected to login for export routes', function () {
    $this->get(route('dashboard.admin.reports.export'))
        ->assertRedirect(route('login'));

    $this->get(route('dashboard.tps3r.volume-recap.export'))
        ->assertRedirect(route('login'));
});
