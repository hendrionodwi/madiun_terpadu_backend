<?php

use App\Enums\OrderFulfillmentStatus;
use App\Models\Complaint;
use App\Models\EnvironmentalReport;
use App\Models\FundWithdrawalRequest;
use App\Models\Order;
use App\Models\OrderFulfillment;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WasteProduct;

test('admin dlh can access all 10 dedicated admin pages', function () {
    $admin = User::factory()->adminDlh()->create();

    $this->actingAs($admin)->get(route('dashboard.admin'))->assertOk();
    $this->actingAs($admin)->get(route('dashboard.admin.users'))->assertOk()->assertSee('Manajemen Pengguna');
    $this->actingAs($admin)->get(route('dashboard.admin.routes'))->assertOk()->assertSee('Penugasan Wilayah');
    $this->actingAs($admin)->get(route('dashboard.admin.complaints'))->assertOk()->assertSee('Kelola Pengaduan Masyarakat');
    $this->actingAs($admin)->get(route('dashboard.admin.reports'))->assertOk()->assertSee('Verifikasi Dokumen Lingkungan');
    $this->actingAs($admin)->get(route('dashboard.admin.entities'))->assertOk()->assertSee('Fasilitas TPS3R Kota');
    $this->actingAs($admin)->get(route('dashboard.admin.points'))->assertOk()->assertSee('Sirkulasi Poin');
    $this->actingAs($admin)->get(route('dashboard.admin.master-data'))->assertOk()->assertSee('Data Master');
    $this->actingAs($admin)->get(route('dashboard.admin.activity-logs'))->assertOk()->assertSee('Audit Trail');
    $this->actingAs($admin)->get(route('dashboard.admin.broadcasts'))->assertOk()->assertSee('Sistem Siaran Pengumuman');
});

test('admin dlh can toggle user active status', function () {
    $admin = User::factory()->adminDlh()->create();
    $targetUser = User::factory()->warga()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.users.toggle', $targetUser));

    $response->assertRedirect();
    expect($targetUser->fresh()->is_active)->toBeFalse();
});

test('admin dlh can store new route assignment', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();

    $response = $this->actingAs($admin)->post(route('dashboard.admin.routes.store'), [
        'petugas_id' => $petugas->id,
        'route_code' => 'RUTE-TEST-01',
        'route_name' => 'Jalur Uji Coba Taman',
        'district' => 'Taman',
        'subdistrict' => 'Pandean',
        'schedule_day' => 'Senin',
        'start_time' => '07:00',
        'end_time' => '11:00',
        'notes' => 'Catatan uji coba rute',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('route_assignments', [
        'route_code' => 'RUTE-TEST-01',
        'district' => 'Taman',
    ]);
});

test('admin dlh can finalize complaint status', function () {
    $admin = User::factory()->adminDlh()->create();
    $complaint = Complaint::factory()->create(['status' => 'dalam_proses']);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.complaints.finalize', $complaint->id), [
        'status' => 'selesai',
        'admin_note' => 'Sampah telah diangkut tuntas.',
    ]);

    $response->assertRedirect();
    expect($complaint->fresh()->status)->toBe('selesai');
});

test('admin dlh can verify environmental report', function () {
    $admin = User::factory()->adminDlh()->create();
    $report = EnvironmentalReport::factory()->create(['status' => 'menunggu_verifikasi']);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.reports.verify', $report->id), [
        'status' => 'disetujui',
        'catatan_verifikasi' => 'Telah memenuhi standar teknis DLH Madiun.',
    ]);

    $response->assertRedirect();
    expect($report->fresh()->status)->toBe('disetujui');
});

test('admin dlh can update fund withdrawal request status', function () {
    $admin = User::factory()->adminDlh()->create();
    $withdrawal = FundWithdrawalRequest::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'approved',
        'admin_note' => 'Transfer berhasil diproses via DANA.',
    ]);

    $response->assertRedirect();
    expect($withdrawal->fresh()->status)->toBe('approved');
});

test('admin dlh can reject fund withdrawal request even if user profile was missing', function () {
    $admin = User::factory()->adminDlh()->create();
    $user = User::factory()->warga()->create();
    $withdrawal = FundWithdrawalRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'source' => 'points',
        'points_used' => 500,
    ]);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.points.update', $withdrawal), [
        'status' => 'rejected',
        'admin_note' => 'Rekening tidak valid.',
    ]);

    $response->assertRedirect();
    expect($withdrawal->fresh()->status)->toBe('rejected');
});

test('admin dlh can store new reward fund allocation and view on points page', function () {
    $admin = User::factory()->adminDlh()->create();

    $response = $this->actingAs($admin)->post(route('dashboard.admin.points.allocations.store'), [
        'source_type' => 'apbd',
        'amount' => 25000000,
        'fiscal_year' => 2026,
        'allocated_at' => '2026-10-17',
        'reference_number' => 'DPA-DLH/2026/TEST',
        'notes' => 'Alokasi tambahan anggaran insentif',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('reward_fund_allocations', [
        'reference_number' => 'DPA-DLH/2026/TEST',
        'amount' => 25000000,
        'source_type' => 'apbd',
    ]);

    $pageResponse = $this->actingAs($admin)->get(route('dashboard.admin.points'));
    $pageResponse->assertOk()
        ->assertSee('Saldo Kas Tersedia')
        ->assertSee('DPA-DLH/2026/TEST');
});

test('admin dlh can store master data and broadcast announcement', function () {
    $admin = User::factory()->adminDlh()->create();

    // Store master data
    $responseMaster = $this->actingAs($admin)->post(route('dashboard.admin.master-data.store'), [
        'group' => 'waste_categories',
        'code' => 'TEST_KACA',
        'name' => 'Limbah Kaca Terpilah',
        'numeric_value' => 250,
        'unit' => 'Poin / Kg',
        'description' => 'Botol dan kaca tebal',
    ]);
    $responseMaster->assertRedirect();
    $this->assertDatabaseHas('master_data', ['code' => 'TEST_KACA']);

    // Store broadcast announcement
    $responseBroadcast = $this->actingAs($admin)->post(route('dashboard.admin.broadcasts.store'), [
        'title' => 'Pengumuman Kerja Bakti Massal',
        'content' => 'Kerja bakti serentak Minggu depan di seluruh kelurahan.',
        'audience_role' => 'all',
        'priority' => 'high',
    ]);
    $responseBroadcast->assertRedirect();
    $this->assertDatabaseHas('broadcast_announcements', ['title' => 'Pengumuman Kerja Bakti Massal']);
});

test('non-admin user is forbidden from accessing admin pages', function () {
    $warga = User::factory()->warga()->create();

    $this->actingAs($warga)->get(route('dashboard.admin.users'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.routes'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.complaints'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.reports'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.entities'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.points'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.master-data'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.activity-logs'))->assertForbidden();
    $this->actingAs($warga)->get(route('dashboard.admin.broadcasts'))->assertForbidden();
});

test('admin dlh can assign petugas lapangan to complaint with instructions', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $complaint = Complaint::factory()->create([
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.complaints.assign', $complaint), [
        'petugas_id' => $petugas->id,
        'instruksi_admin' => 'Harap cek tumpukan sampah liar di lokasi secepatnya.',
    ]);

    $response->assertRedirect();
    $complaint->refresh();
    expect($complaint->assigned_petugas_id)->toBe($petugas->id)
        ->and($complaint->status)->toBe('ditugaskan')
        ->and($complaint->instruksi_admin)->toBe('Harap cek tumpukan sampah liar di lokasi secepatnya.')
        ->and($complaint->assigned_at)->not->toBeNull();
});

test('admin dlh can assign field check for environmental report', function () {
    $admin = User::factory()->adminDlh()->create();
    $petugas = User::factory()->petugasLapangan()->create();
    $report = EnvironmentalReport::factory()->create([
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->patch(route('dashboard.admin.reports.verify', $report->id), [
        'status' => 'perlu_cek_lapangan',
        'assigned_petugas_id' => $petugas->id,
        'instruksi_lapangan' => 'Periksa langsung cerobong asap dan bak penampungan limbah.',
    ]);

    $response->assertRedirect();
    $report->refresh();
    expect($report->status)->toBe('perlu_cek_lapangan')
        ->and($report->assigned_petugas_id)->toBe($petugas->id)
        ->and($report->instruksi_lapangan)->toBe('Periksa langsung cerobong asap dan bak penampungan limbah.')
        ->and($report->assigned_at)->not->toBeNull();
});

test('admin dlh cannot assign inactive field officers to operational work', function () {
    $admin = User::factory()->adminDlh()->create();
    $inactiveOfficer = User::factory()->petugasLapangan()->create(['is_active' => false]);
    $complaint = Complaint::factory()->create(['status' => 'menunggu_verifikasi']);
    $report = EnvironmentalReport::factory()->create(['status' => 'menunggu_verifikasi']);

    $this->actingAs($admin)
        ->from(route('dashboard.admin.complaints'))
        ->patch(route('dashboard.admin.complaints.assign', $complaint), [
            'petugas_id' => $inactiveOfficer->id,
        ])
        ->assertRedirect(route('dashboard.admin.complaints'))
        ->assertSessionHasErrors(['petugas_id']);

    $this->actingAs($admin)
        ->from(route('dashboard.admin.reports'))
        ->patch(route('dashboard.admin.reports.verify', $report->id), [
            'status' => 'perlu_cek_lapangan',
            'assigned_petugas_id' => $inactiveOfficer->id,
        ])
        ->assertRedirect(route('dashboard.admin.reports'))
        ->assertSessionHasErrors(['assigned_petugas_id']);

    $this->actingAs($admin)
        ->from(route('dashboard.admin.routes'))
        ->post(route('dashboard.admin.routes.store'), [
            'petugas_id' => $inactiveOfficer->id,
            'route_code' => 'RUTE-INACTIVE-TEST',
            'route_name' => 'Rute Petugas Nonaktif',
            'district' => 'Taman',
            'schedule_day' => 'Senin',
            'start_time' => '07:00',
            'end_time' => '10:00',
        ])
        ->assertRedirect(route('dashboard.admin.routes'))
        ->assertSessionHasErrors(['petugas_id']);

    expect($complaint->fresh()->assigned_petugas_id)->toBeNull()
        ->and($report->fresh()->assigned_petugas_id)->toBeNull();
    $this->assertDatabaseMissing('route_assignments', ['route_code' => 'RUTE-INACTIVE-TEST']);
});

test('admin dlh can monitor each seller fulfillment without counting unpaid orders as revenue', function () {
    $admin = User::factory()->adminDlh()->create();
    $buyer = User::factory()->warga()->create();
    $sellerA = User::factory()->pengelolaTps3r()->create(['name' => 'Pengelola TPS3R Kartoharjo']);
    $sellerB = User::factory()->pengelolaTps3r()->create(['name' => 'Pengelola TPS3R Taman']);

    $productA = WasteProduct::factory()->create([
        'seller_id' => $sellerA->id,
        'nama_produk' => 'Kompos Organik Super Madiun',
        'harga' => 25000,
        'stok' => 50,
        'is_available' => true,
    ]);
    $productB = WasteProduct::factory()->create([
        'seller_id' => $sellerB->id,
        'nama_produk' => 'Tas Daur Ulang Taman',
        'harga' => 20000,
        'stok' => 20,
        'is_available' => true,
    ]);

    $order = Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 70000,
        'status' => 'dibayar',
    ]);
    Order::factory()->create([
        'buyer_id' => $buyer->id,
        'total_harga' => 125000,
        'status' => 'pending',
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productA->id,
        'jumlah' => 2,
        'harga_satuan' => 25000,
        'subtotal' => 50000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $productB->id,
        'jumlah' => 1,
        'harga_satuan' => 20000,
        'subtotal' => 20000,
    ]);

    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $sellerA->id,
        'status' => OrderFulfillmentStatus::Menunggu,
    ]);
    OrderFulfillment::factory()->create([
        'order_id' => $order->id,
        'seller_id' => $sellerB->id,
        'status' => OrderFulfillmentStatus::Diproses,
    ]);

    $adminDashboard = $this->actingAs($admin)->get(route('dashboard.admin'));
    $adminDashboard->assertOk()
        ->assertSee('Marketplace se-Kota')
        ->assertSee('Aktivitas Transaksi Marketplace TPS3R se-Kota')
        ->assertSee($order->nomor_pesanan);
    expect($adminDashboard->viewData('macro')['total_omset_marketplace'])->toBe(70000.0);

    $tps3rOrders = $this->actingAs($admin)->get(route('dashboard.tps3r.orders'));
    $tps3rOrders->assertOk()
        ->assertSee($order->nomor_pesanan)
        ->assertSee($sellerA->name)
        ->assertSee($sellerB->name)
        ->assertSee('Kompos Organik Super Madiun')
        ->assertSee('Tas Daur Ulang Taman');
    expect(substr_count($tps3rOrders->getContent(), $order->nomor_pesanan))->toBe(2);

    $orderDetail = $this->actingAs($admin)->get(route('dashboard.tps3r.orders.show', $order));
    $orderDetail->assertOk()
        ->assertSee($order->nomor_pesanan)
        ->assertSee('Kompos Organik Super Madiun')
        ->assertSee('Tas Daur Ulang Taman')
        ->assertSee('Menunggu diproses')
        ->assertSee('Sedang diproses')
        ->assertDontSee('Perbarui Status Pemrosesan');
});
