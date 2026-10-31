<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminRetributionController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FundWithdrawalController;
use App\Http\Controllers\Web\OrderFulfillmentController;
use App\Http\Controllers\Web\PemrakarsaProfileController;
use App\Http\Controllers\Web\ReportExportController;
use App\Http\Controllers\Web\Tps3rPaymentSettingsController;
use App\Http\Controllers\Web\Tps3rProfileController;
use App\Http\Controllers\Web\WasteProcessingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Primary dynamic dashboard router
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================================
    // DSH-F-004: Portofolio Lengkap Admin DLH (10 Dedicated Pages)
    // ==========================================================
    Route::middleware('role:admin_dlh')->group(function () {
        // 1. Dashboard Utama (Rekapitulasi Lintas Wilayah Kota Madiun)
        Route::get('dashboard/admin', [AdminDashboardController::class, 'index'])->name('dashboard.admin');

        // 2. Kelola Hak Akses (RBAC) & Akun Pengguna
        Route::get('dashboard/admin/users', [AdminDashboardController::class, 'users'])->name('dashboard.admin.users');
        Route::post('dashboard/admin/users', [AdminDashboardController::class, 'storeUser'])->name('dashboard.admin.users.store');
        Route::patch('dashboard/admin/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('dashboard.admin.users.update');
        Route::patch('dashboard/admin/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('dashboard.admin.users.toggle');

        // 3. Penugasan Wilayah & Rute Tetap Petugas Lapangan
        Route::get('dashboard/admin/routes', [AdminDashboardController::class, 'routes'])->name('dashboard.admin.routes');
        Route::get('dashboard/admin/routes/create', [AdminDashboardController::class, 'createRoute'])->name('dashboard.admin.routes.create');
        Route::get('dashboard/admin/routes/{route}/edit', [AdminDashboardController::class, 'editRoute'])->name('dashboard.admin.routes.edit');
        Route::post('dashboard/admin/routes', [AdminDashboardController::class, 'storeRoute'])->name('dashboard.admin.routes.store');
        Route::patch('dashboard/admin/routes/{route}', [AdminDashboardController::class, 'updateRoute'])->name('dashboard.admin.routes.update');
        Route::delete('dashboard/admin/routes/{route}', [AdminDashboardController::class, 'destroyRoute'])->name('dashboard.admin.routes.destroy');

        // 4. Kelola Pengaduan Masyarakat & Peninjauan Hasil
        Route::get('dashboard/admin/complaints', [AdminDashboardController::class, 'complaints'])->name('dashboard.admin.complaints');
        Route::patch('dashboard/admin/complaints/{complaint}/assign', [AdminDashboardController::class, 'assignComplaint'])->name('dashboard.admin.complaints.assign');
        Route::patch('dashboard/admin/complaints/{complaint}/finalize', [AdminDashboardController::class, 'finalizeComplaint'])->name('dashboard.admin.complaints.finalize');

        // 5. Verifikasi Laporan Dokumen Lingkungan Pemrakarsa
        Route::get('dashboard/admin/reports', [AdminDashboardController::class, 'reports'])->name('dashboard.admin.reports');
        Route::patch('dashboard/admin/reports/{id}/verify', [AdminDashboardController::class, 'verifyReport'])->name('dashboard.admin.reports.verify');
        Route::patch('dashboard/admin/laporan/{id}/verify', [AdminDashboardController::class, 'verifyReport'])->name('dashboard.admin.verify-laporan');

        // 6. Validasi & Manajemen Entitas Pemrakarsa serta Pengelola TPS3R
        Route::get('dashboard/admin/entities', [AdminDashboardController::class, 'entities'])->name('dashboard.admin.entities');
        Route::patch('dashboard/admin/entities/{entityVerification}', [AdminDashboardController::class, 'reviewEntity'])->name('dashboard.admin.entities.update');
        Route::get('dashboard/admin/entities/{entityVerification}/document', [AdminDashboardController::class, 'downloadEntityDocument'])->name('dashboard.admin.entities.download');

        // 7. Manajemen Persetujuan Pencairan Dana / Poin Warga
        Route::get('dashboard/admin/points', [AdminDashboardController::class, 'points'])->name('dashboard.admin.points');
        Route::patch('dashboard/admin/points/{fundWithdrawalRequest}/update', [AdminDashboardController::class, 'updateWithdrawalStatus'])->name('dashboard.admin.points.update');
        Route::post('dashboard/admin/points/allocations', [AdminDashboardController::class, 'storeRewardAllocation'])->name('dashboard.admin.points.allocations.store');

        // 8. Kelola Data Master
        Route::get('dashboard/admin/master-data', [AdminDashboardController::class, 'masterData'])->name('dashboard.admin.master-data');
        Route::post('dashboard/admin/master-data', [AdminDashboardController::class, 'storeMasterData'])->name('dashboard.admin.master-data.store');
        Route::patch('dashboard/admin/master-data/{masterData}', [AdminDashboardController::class, 'updateMasterData'])->name('dashboard.admin.master-data.update');
        Route::delete('dashboard/admin/master-data/{masterData}', [AdminDashboardController::class, 'destroyMasterData'])->name('dashboard.admin.master-data.destroy');

        // 9. Pemantauan Log Aktivitas Pengguna (Audit Trail)
        Route::get('dashboard/admin/activity-logs', [AdminDashboardController::class, 'activityLogs'])->name('dashboard.admin.activity-logs');

        // 10. Sistem Siaran Notifikasi Pengumuman / Broadcast
        Route::get('dashboard/admin/broadcasts', [AdminDashboardController::class, 'broadcasts'])->name('dashboard.admin.broadcasts');
        Route::post('dashboard/admin/broadcasts', [AdminDashboardController::class, 'storeBroadcast'])->name('dashboard.admin.broadcasts.store');

        // 11. Kelola Retribusi Pelayanan Persampahan Warga (TRK-F-013)
        Route::get('dashboard/admin/retributions', [AdminRetributionController::class, 'index'])->name('dashboard.admin.retributions');
        Route::post('dashboard/admin/retributions/generate', [AdminRetributionController::class, 'generateBills'])->name('dashboard.admin.retributions.generate');
        Route::patch('dashboard/admin/retributions/{retributionBill}/confirm-manual', [AdminRetributionController::class, 'confirmManualPayment'])->name('dashboard.admin.retributions.confirm-manual');
        Route::post('dashboard/admin/retributions/settings', [AdminRetributionController::class, 'updateSettings'])->name('dashboard.admin.retributions.settings');
        Route::post('dashboard/admin/retributions/send-reminders', [AdminRetributionController::class, 'sendReminders'])->name('dashboard.admin.retributions.send-reminders');

        // 12. Export & Cetak Laporan Resmi Rekapitulasi Sampah DLH
        Route::get('dashboard/admin/reports/export', [ReportExportController::class, 'exportAdminWasteRecap'])->name('dashboard.admin.reports.export');
    });

    // DSH-F-003: Dashboard Pengelola TPS3R
    Route::get('dashboard/tps3r', [DashboardController::class, 'tps3r'])
        ->name('dashboard.tps3r')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::get('dashboard/tps3r/deposits', [DashboardController::class, 'tps3rDeposits'])
        ->name('dashboard.tps3r.deposits')
        ->middleware('role:pengelola_tps3r');
    Route::post('dashboard/tps3r/deposits', [DashboardController::class, 'storeTps3rDeposit'])
        ->name('dashboard.tps3r.store-deposit')
        ->middleware('role:pengelola_tps3r');
    Route::patch('dashboard/tps3r/deposits/{deposit}/verify', [DashboardController::class, 'verifyTps3rDeposit'])
        ->name('dashboard.tps3r.verify-deposit')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/deposits/{deposit}', [DashboardController::class, 'tps3rDepositDetail'])
        ->name('dashboard.tps3r.deposits.show')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::get('dashboard/tps3r/volume-recap', [DashboardController::class, 'tps3rVolumeRecap'])
        ->name('dashboard.tps3r.volume-recap')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::get('dashboard/tps3r/volume-recap/export', [ReportExportController::class, 'exportTps3rVolumeRecap'])
        ->name('dashboard.tps3r.volume-recap.export')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::get('dashboard/tps3r/products', [DashboardController::class, 'tps3rProducts'])
        ->name('dashboard.tps3r.products')
        ->middleware('role:pengelola_tps3r');
    Route::post('dashboard/tps3r/products', [DashboardController::class, 'storeMarketplaceProduct'])
        ->name('dashboard.tps3r.store-product')
        ->middleware('role:pengelola_tps3r');
    Route::patch('dashboard/tps3r/products/{wasteProduct}', [DashboardController::class, 'updateMarketplaceProduct'])
        ->name('dashboard.tps3r.products.update')
        ->middleware('role:pengelola_tps3r');
    Route::delete('dashboard/tps3r/products/{wasteProduct}', [DashboardController::class, 'destroyMarketplaceProduct'])
        ->name('dashboard.tps3r.products.destroy')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/orders', [DashboardController::class, 'tps3rOrders'])
        ->name('dashboard.tps3r.orders')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::get('dashboard/tps3r/orders/{order}', [DashboardController::class, 'tps3rOrder'])
        ->name('dashboard.tps3r.orders.show')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::patch('dashboard/tps3r/fulfillments/{orderFulfillment}', [OrderFulfillmentController::class, 'update'])
        ->name('dashboard.tps3r.fulfillments.update')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/profile', [Tps3rProfileController::class, 'edit'])
        ->name('dashboard.tps3r.profile.edit')
        ->middleware('role:pengelola_tps3r');
    Route::patch('dashboard/tps3r/profile', [Tps3rProfileController::class, 'update'])
        ->name('dashboard.tps3r.profile.update')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/payment-settings', [Tps3rPaymentSettingsController::class, 'index'])
        ->name('dashboard.tps3r.payment-settings')
        ->middleware('role:pengelola_tps3r,admin_dlh');
    Route::put('dashboard/tps3r/payment-settings', [Tps3rPaymentSettingsController::class, 'update'])
        ->name('dashboard.tps3r.payment-settings.update')
        ->middleware('role:pengelola_tps3r');
    Route::post('dashboard/tps3r/payment-settings/test', [Tps3rPaymentSettingsController::class, 'test'])
        ->name('dashboard.tps3r.payment-settings.test')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/processing', [WasteProcessingController::class, 'index'])
        ->name('dashboard.tps3r.processing.index')
        ->middleware('role:pengelola_tps3r');
    Route::post('dashboard/tps3r/processing', [WasteProcessingController::class, 'store'])
        ->name('dashboard.tps3r.processing.store')
        ->middleware('role:pengelola_tps3r');
    Route::patch('dashboard/tps3r/processing/{wasteProcessingRecord}', [WasteProcessingController::class, 'update'])
        ->name('dashboard.tps3r.processing.update')
        ->middleware('role:pengelola_tps3r');
    Route::get('dashboard/tps3r/withdrawals', [FundWithdrawalController::class, 'index'])
        ->name('dashboard.tps3r.withdrawals.index')
        ->middleware('role:pengelola_tps3r');
    Route::post('dashboard/tps3r/withdrawals', [FundWithdrawalController::class, 'store'])
        ->name('dashboard.tps3r.withdrawals.store')
        ->middleware('role:pengelola_tps3r');

    // PPL-F-008: Dashboard & Form Unggah Laporan Semester Pemrakarsa Kegiatan
    Route::get('dashboard/pemrakarsa', [DashboardController::class, 'pemrakarsa'])
        ->name('dashboard.pemrakarsa')
        ->middleware('role:pemrakarsa,admin_dlh');
    Route::get('dashboard/pemrakarsa/reports', [DashboardController::class, 'pemrakarsaReports'])
        ->name('dashboard.pemrakarsa.reports')
        ->middleware('role:pemrakarsa,admin_dlh');
    Route::get('dashboard/pemrakarsa/reports/create', [DashboardController::class, 'pemrakarsaCreateReport'])
        ->name('dashboard.pemrakarsa.reports.create')
        ->middleware('role:pemrakarsa,admin_dlh');
    Route::post('dashboard/pemrakarsa/laporan', [DashboardController::class, 'storeLaporanPemrakarsa'])
        ->name('dashboard.pemrakarsa.store-laporan')
        ->middleware('role:pemrakarsa,admin_dlh');
    Route::get('dashboard/pemrakarsa/profile', [PemrakarsaProfileController::class, 'edit'])
        ->name('dashboard.pemrakarsa.profile.edit')
        ->middleware('role:pemrakarsa');
    Route::patch('dashboard/pemrakarsa/profile', [PemrakarsaProfileController::class, 'update'])
        ->name('dashboard.pemrakarsa.profile.update')
        ->middleware('role:pemrakarsa');
});

require __DIR__.'/settings.php';
