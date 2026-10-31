<?php

use App\Models\MasterData;
use App\Models\PengelolaTps3rProfile;
use App\Models\PickupSchedule;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

test('admin dlh can update and delete operational routes', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $newPetugas = User::factory()->petugasLapangan()->create();

    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-CRUD',
        'route_name' => 'Rute Lama',
        'district' => 'Kartoharjo',
        'schedule_day' => 'Senin',
        'start_time' => '07:00',
        'end_time' => '10:00',
    ]);

    // Admin updates the route
    $response = $this->actingAs($admin)->patch("/dashboard/admin/routes/{$route->id}", [
        'petugas_id' => $newPetugas->id,
        'route_code' => 'RUTE-TEST-CRUD',
        'route_name' => 'Rute Baru Diperbarui',
        'district' => 'Taman',
        'schedule_day' => 'Selasa',
        'start_time' => '08:00',
        'end_time' => '11:00',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('route_assignments', [
        'id' => $route->id,
        'route_name' => 'Rute Baru Diperbarui',
        'district' => 'Taman',
        'petugas_id' => $newPetugas->id,
    ]);

    // Admin deletes the route
    $deleteResponse = $this->actingAs($admin)->delete("/dashboard/admin/routes/{$route->id}");
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('route_assignments', ['id' => $route->id]);
});

test('admin dlh can update and delete master data category', function () {
    $admin = User::factory()->adminDlh()->create();

    $master = MasterData::create([
        'group' => 'waste_category',
        'code' => 'plastik_hdpe',
        'name' => 'Plastik HDPE Keras',
        'numeric_value' => 120,
        'unit' => 'Poin / Kg',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    // Update master data
    $updateResponse = $this->actingAs($admin)->patch("/dashboard/admin/master-data/{$master->id}", [
        'group' => 'waste_category',
        'code' => 'plastik_hdpe',
        'name' => 'Plastik HDPE Botol Tebal',
        'numeric_value' => 140,
        'unit' => 'Poin / Kg',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect();
    $this->assertDatabaseHas('master_data', [
        'id' => $master->id,
        'name' => 'Plastik HDPE Botol Tebal',
        'numeric_value' => 140,
    ]);

    // Delete master data
    $deleteResponse = $this->actingAs($admin)->delete("/dashboard/admin/master-data/{$master->id}");
    $deleteResponse->assertRedirect();
    $this->assertDatabaseMissing('master_data', ['id' => $master->id]);
});

test('admin dlh can view edit route page with waypoints and map controls', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-MAP-EDIT',
        'route_name' => 'Rute Map Editor Test',
        'district' => 'Kartoharjo',
        'waypoints' => [
            [-7.6180, 111.5300],
            [-7.6210, 111.5280],
        ],
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.routes.edit', $route->id));

    $response->assertOk()
        ->assertSee('Edit Penugasan Rute:')
        ->assertSee('RUTE-MAP-EDIT')
        ->assertSee('adminRouteEditMap')
        ->assertSee('MapLibre GL JS')
        ->assertDontSee('Leaflet');
});

test('admin dlh can update route with waypoints and sync operational schedules', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $newPetugas = User::factory()->petugasLapangan()->create();

    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-SYNC-OLD',
        'route_name' => 'Rute Sync Lama',
        'district' => 'Kartoharjo',
        'schedule_day' => 'Senin',
        'start_time' => '08:00',
        'end_time' => '12:00',
        'is_active' => true,
    ]);

    // Create existing schedule for this route
    PickupSchedule::create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => 'RUTE-SYNC-OLD',
        'nama_wilayah_rute' => 'Rute Sync Lama',
        'tanggal_penjemputan' => now()->toDateString(),
        'waktu_mulai' => '08:00',
        'waktu_selesai' => '09:30',
        'status' => 'terjadwal',
    ]);

    $newWaypoints = [
        [-7.6180, 111.5300],
        [-7.6220, 111.5270],
        [-7.6260, 111.5240],
    ];

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.routes.update', $route->id), [
        'petugas_id' => $newPetugas->id,
        'route_code' => 'RUTE-SYNC-NEW',
        'route_name' => 'Rute Sync Baru Diperbarui',
        'district' => 'Kartoharjo',
        'schedule_day' => 'Rabu',
        'start_time' => '08:30',
        'end_time' => '12:30',
        'is_active' => true,
        'latitude' => -7.6180,
        'longitude' => 111.5300,
        'waypoints' => $newWaypoints,
    ]);

    $response->assertRedirect(route('dashboard.admin.routes'));

    $this->assertDatabaseHas('route_assignments', [
        'id' => $route->id,
        'route_code' => 'RUTE-SYNC-NEW',
        'route_name' => 'Rute Sync Baru Diperbarui',
        'petugas_id' => $newPetugas->id,
    ]);

    // PickupSchedule should also be updated with new route code and name
    $this->assertDatabaseHas('pickup_schedules', [
        'id_wilayah_rute' => 'RUTE-SYNC-NEW',
        'nama_wilayah_rute' => 'Rute Sync Baru Diperbarui',
        'petugas_id' => $newPetugas->id,
    ]);
});

test('admin dlh can view create route page with map editor controls', function () {
    $admin = User::factory()->adminDlh()->create();
    User::factory()->petugasLapangan()->create(['name' => 'Budi Petugas']);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.routes.create'));

    $response->assertOk()
        ->assertSee('Tambah Penugasan Rute Baru')
        ->assertSee('adminRouteCreateMap')
        ->assertSee('MapLibre GL JS')
        ->assertDontSee('Leaflet')
        ->assertSee('Budi Petugas');
});

test('non-admin cannot access route create page', function () {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)
        ->get(route('dashboard.admin.routes.create'))
        ->assertForbidden();
});

test('non-admin cannot access route edit page', function () {
    $warga = User::factory()->warga()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create(['petugas_id' => $petugas->id]);

    $this->actingAs($warga)
        ->get(route('dashboard.admin.routes.edit', $route->id))
        ->assertForbidden();
});

test('admin dlh routes map filters out past completed schedules but keeps uncompleted and today completed', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-FILTER-MAP',
        'is_active' => true,
    ]);

    $yesterdayCompleted = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'tanggal_penjemputan' => now()->subDay()->toDateString(),
        'status' => 'selesai',
        'keterangan' => 'Selesai Kemarin Tidak Boleh Muncul',
    ]);

    $todayCompleted = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'tanggal_penjemputan' => now()->toDateString(),
        'status' => 'selesai',
        'keterangan' => 'Selesai Hari Ini Harus Muncul',
    ]);

    $pastUncompleted = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'tanggal_penjemputan' => now()->subDays(2)->toDateString(),
        'status' => 'terjadwal',
        'keterangan' => 'Belum Diambil Hari Apapun Harus Muncul',
    ]);

    $todayOngoing = PickupSchedule::factory()->create([
        'petugas_id' => $petugas->id,
        'id_wilayah_rute' => $route->route_code,
        'tanggal_penjemputan' => now()->toDateString(),
        'status' => 'berlangsung',
        'keterangan' => 'Sedang Berlangsung Harus Muncul',
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard.admin.routes'));

    $response->assertOk();

    /** @var Collection $viewSchedules */
    $viewSchedules = $response->viewData('schedules');
    $scheduleIds = $viewSchedules->pluck('id')->all();

    expect($scheduleIds)
        ->not->toContain($yesterdayCompleted->id)
        ->toContain($todayCompleted->id)
        ->toContain($pastUncompleted->id)
        ->toContain($todayOngoing->id);
});

test('admin dlh can view create and edit route pages with searchable end facilities including tps3r and tpa winongo', function () {
    $admin = User::factory()->adminDlh()->create();
    $tpsUser = User::factory()->pengelolaTps3r()->create(['name' => 'Pengelola TPS3R Sukun']);
    PengelolaTps3rProfile::updateOrCreate(
        ['user_id' => $tpsUser->id],
        [
            'nama_tps3r' => 'TPS3R Sukun Makmur',
            'kode_tps3r' => 'TPS3R-SKN-01',
            'kecamatan' => 'Kartoharjo',
            'kelurahan' => 'Sukun',
        ]
    );

    // Test create page
    $createResponse = $this->actingAs($admin)->get(route('dashboard.admin.routes.create'));
    $createResponse->assertOk()
        ->assertSee('TPS3R Sukun Makmur')
        ->assertSee('TPA Winongo (Residu Kota Madiun)');

    $petugas = User::factory()->petugasLapangan()->create();
    $route = RouteAssignment::factory()->create([
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-FACILITY',
        'route_name' => 'Rute Fasilitas Test',
        'district' => 'Kartoharjo',
        'end_facility' => 'TPS3R Sukun Makmur',
    ]);

    // Test edit page
    $editResponse = $this->actingAs($admin)->get(route('dashboard.admin.routes.edit', $route->id));
    $editResponse->assertOk()
        ->assertSee('TPS3R Sukun Makmur')
        ->assertSee('TPA Winongo (Residu Kota Madiun)');
});

test('admin dlh can create route with selected tps3r or custom facility name', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    $response = $this->actingAs($admin)->post(route('dashboard.admin.routes.store'), [
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-KTH-TPS',
        'route_name' => 'Jalur Kartoharjo Barat',
        'start_location' => 'Depo Truk Sukun',
        'end_facility' => 'TPS3R Bersih Berseri Kartoharjo',
        'district' => 'Kartoharjo',
        'subdistrict' => 'Oro-Oro Ombo',
        'schedule_day' => 'Senin',
        'start_time' => '06:00',
        'end_time' => '10:00',
        'latitude' => -7.6180,
        'longitude' => 111.5300,
    ]);

    $response->assertRedirect(route('dashboard.admin.routes'));
    $this->assertDatabaseHas('route_assignments', [
        'route_code' => 'RUTE-KTH-TPS',
        'end_facility' => 'TPS3R Bersih Berseri Kartoharjo',
    ]);
});
