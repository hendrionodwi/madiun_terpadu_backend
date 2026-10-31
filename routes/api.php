<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ComplaintController;
use App\Http\Controllers\Api\V1\EducationController;
use App\Http\Controllers\Api\V1\EnvironmentalReportController;
use App\Http\Controllers\Api\V1\FundWithdrawalController;
use App\Http\Controllers\Api\V1\MarketplaceController;
use App\Http\Controllers\Api\V1\MasterDataController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\OrderFulfillmentController;
use App\Http\Controllers\Api\V1\PointLedgerController;
use App\Http\Controllers\Api\V1\RetributionController;
use App\Http\Controllers\Api\V1\Tps3rController;
use App\Http\Controllers\Api\V1\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aplikasi Pengelolaan Sampah dan Lingkungan Terpadu Kota Madiun
*/

Route::prefix('v1')->group(function () {
    // ==========================================
    // 1. Modul Autentikasi (AUT-F-002)
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });
    });

    // ==========================================
    // Public / Protected Master Data & Catalog
    // ==========================================
    Route::get('/master-data', [MasterDataController::class, 'index']);
    Route::get('/marketplace/stores', [MarketplaceController::class, 'indexStores']);
    Route::get('/marketplace/stores/{store}', [MarketplaceController::class, 'showStore']);
    Route::get('/marketplace/products', [MarketplaceController::class, 'indexProducts']);
    Route::get('/marketplace/products/{product}', [MarketplaceController::class, 'showProduct']);
    Route::post('/marketplace/midtrans/notification', [MarketplaceController::class, 'handleMidtransNotification']);
    Route::post('/retributions/midtrans/notification', [RetributionController::class, 'handleMidtransNotification']);
    Route::get('/education/waste-sorting', [EducationController::class, 'index']);
    Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*');

    // ==========================================
    // Authenticated API Routes
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {

        // ==========================================
        // Modul A: Tracking (TRK-F-001, TRK-F-006, TRK-F-009)
        // ==========================================
        Route::prefix('tracking')->group(function () {
            Route::get('/warga', [TrackingController::class, 'indexWarga'])
                ->middleware('role:petugas_lapangan,admin_dlh');

            // TRK-F-001: Jadwal Penjemputan & Rute
            Route::get('/routes', [TrackingController::class, 'indexRoutes']);
            Route::get('/routes/{routeCode}/session', [TrackingController::class, 'showRouteSession']);
            Route::post('/routes/{routeCode}/session/start', [TrackingController::class, 'startRouteSession'])
                ->middleware('role:petugas_lapangan,admin_dlh');
            Route::post('/routes/{routeCode}/session/finish', [TrackingController::class, 'finishRouteSession'])
                ->middleware('role:petugas_lapangan,admin_dlh');
            Route::post('/routes/{routeCode}/renew-session', [TrackingController::class, 'renewRouteSession'])
                ->middleware('role:petugas_lapangan,admin_dlh');
            Route::get('/schedules', [TrackingController::class, 'indexSchedules']);
            Route::post('/schedules', [TrackingController::class, 'storeSchedule'])
                ->middleware('role:petugas_lapangan,admin_dlh,warga');
            Route::patch('/schedules/{pickupSchedule}', [TrackingController::class, 'updateSchedule'])
                ->middleware('role:petugas_lapangan,admin_dlh,warga');
            Route::delete('/schedules/{pickupSchedule}', [TrackingController::class, 'destroySchedule'])
                ->middleware('role:admin_dlh,warga');
            Route::patch('/schedules/{pickupSchedule}/status', [TrackingController::class, 'updateScheduleStatus'])
                ->middleware('role:petugas_lapangan,admin_dlh');

            // TRK-F-006 & TRK-F-009: Input Hasil Timbangan & Auto Tambah Poin Warga
            Route::post('/weighings', [TrackingController::class, 'storeWeighing'])
                ->middleware('role:petugas_lapangan,admin_dlh');
            Route::get('/weighings', [TrackingController::class, 'indexWeighings']);
        });

        // ==========================================
        // Modul B: TPS3R (TPS-F-002, TPS-F-003)
        // ==========================================
        Route::prefix('tps3r')->group(function () {
            Route::get('/facilities', [Tps3rController::class, 'facilities'])
                ->middleware('role:petugas_lapangan,admin_dlh');
            // TPS-F-002: Penerimaan Setoran Sampah
            Route::get('/deposits', [Tps3rController::class, 'indexDeposits'])
                ->middleware('role:pengelola_tps3r,admin_dlh,petugas_lapangan');
            Route::get('/deposits/{deposit}', [Tps3rController::class, 'showDeposit'])
                ->middleware('role:pengelola_tps3r,admin_dlh,petugas_lapangan');
            Route::post('/deposits', [Tps3rController::class, 'storeDeposit'])
                ->middleware('role:pengelola_tps3r,admin_dlh,petugas_lapangan');
            Route::patch('/deposits/{deposit}/verify', [Tps3rController::class, 'verifyDeposit'])
                ->middleware('role:pengelola_tps3r,admin_dlh');

            // TPS-F-003: Rekapitulasi Volume Sampah
            Route::get('/volume-recap', [Tps3rController::class, 'volumeRecap'])
                ->middleware('role:pengelola_tps3r,admin_dlh');
        });

        // ==========================================
        // Modul C: Marketplace (MKT-F-001, MKT-F-003)
        // ==========================================
        Route::prefix('marketplace')->group(function () {
            // MKT-F-001: Tambah Produk Daur Ulang ke Katalog
            Route::post('/products', [MarketplaceController::class, 'storeProduct'])
                ->middleware('role:pengelola_tps3r,pemrakarsa,admin_dlh');

            // MKT-F-003: Checkout Keranjang Belanja
            Route::post('/checkout', [MarketplaceController::class, 'checkout']);
            Route::get('/orders', [MarketplaceController::class, 'indexOrders']);
            Route::get('/orders/{order}', [MarketplaceController::class, 'showOrder']);
            Route::patch('/orders/{order}/cancel', [MarketplaceController::class, 'cancelOrder']);
            Route::patch('/orders/{order}/confirm-received', [MarketplaceController::class, 'confirmReceived']);
            Route::get('/fulfillments', [OrderFulfillmentController::class, 'index'])
                ->middleware('role:pengelola_tps3r');
            Route::get('/fulfillments/{orderFulfillment}', [OrderFulfillmentController::class, 'show'])
                ->middleware('role:pengelola_tps3r');
            Route::patch('/fulfillments/{orderFulfillment}', [OrderFulfillmentController::class, 'update'])
                ->middleware('role:pengelola_tps3r');
        });

        // ==========================================
        // Modul D: Pengaduan (PPL-F-001, PPL-F-004)
        // ==========================================
        Route::prefix('complaints')->group(function () {
            // PPL-F-001: Form Pengaduan Masyarakat
            Route::get('/', [ComplaintController::class, 'index'])
                ->middleware('role:warga,petugas_lapangan,admin_dlh');
            Route::get('/{complaint}', [ComplaintController::class, 'show'])
                ->middleware('role:warga,petugas_lapangan,admin_dlh');
            Route::post('/', [ComplaintController::class, 'store'])
                ->middleware('role:warga');
            Route::get('/{complaint}/notifications', [ComplaintController::class, 'notifications'])
                ->middleware('role:warga,petugas_lapangan,admin_dlh');

            // PPL-F-004: Verifikasi Lapangan oleh Petugas
            Route::post('/{complaint}/verify', [ComplaintController::class, 'verify'])
                ->middleware('role:petugas_lapangan,admin_dlh');
        });

        Route::prefix('environmental-reports')->group(function () {
            Route::get('/', [EnvironmentalReportController::class, 'index'])
                ->middleware('role:pemrakarsa,admin_dlh,petugas_lapangan');
            Route::get('/{environmentalReport}', [EnvironmentalReportController::class, 'show'])
                ->middleware('role:pemrakarsa,admin_dlh,petugas_lapangan');
            Route::post('/', [EnvironmentalReportController::class, 'store'])
                ->middleware('role:pemrakarsa');
            Route::patch('/{environmentalReport}/verification', [EnvironmentalReportController::class, 'verify'])
                ->middleware('role:admin_dlh');
            Route::post('/{environmentalReport}/field-check', [EnvironmentalReportController::class, 'fieldCheck'])
                ->middleware('role:petugas_lapangan,admin_dlh');
        });

        Route::get('/admin/monitoring', [EnvironmentalReportController::class, 'monitoringSummary'])
            ->middleware('role:admin_dlh');

        Route::get('/fund-withdrawals', [FundWithdrawalController::class, 'index'])
            ->middleware('role:warga,pengelola_tps3r');
        Route::post('/fund-withdrawals', [FundWithdrawalController::class, 'store'])
            ->middleware('role:warga,pengelola_tps3r');
        Route::get('/announcements', [AnnouncementController::class, 'index']);

        // TRK-F-010: Riwayat Mutasi Buku Kas Poin Warga
        Route::get('/points/ledger', [PointLedgerController::class, 'index'])
            ->middleware('role:warga,admin_dlh');

        // TRK-F-013: Sistem Tagihan & Pembayaran Retribusi Sampah
        Route::prefix('retributions')->group(function () {
            Route::get('/bills', [RetributionController::class, 'index'])
                ->middleware('role:warga,admin_dlh');
            Route::get('/bills/{retributionBill}', [RetributionController::class, 'show'])
                ->middleware('role:warga,admin_dlh');
            Route::post('/bills/{retributionBill}/pay', [RetributionController::class, 'pay'])
                ->middleware('role:warga');
        });

        // Ping checks
        Route::get('/admin/ping', fn () => response()->json(['success' => true, 'message' => 'Admin DLH authorized.']))
            ->middleware('role:admin_dlh');
        Route::get('/petugas/ping', fn () => response()->json(['success' => true, 'message' => 'Petugas Lapangan authorized.']))
            ->middleware('role:petugas_lapangan');
        Route::get('/tps3r/ping', fn () => response()->json(['success' => true, 'message' => 'Pengelola TPS3R authorized.']))
            ->middleware('role:pengelola_tps3r');
        Route::get('/pemrakarsa/ping', fn () => response()->json(['success' => true, 'message' => 'Pemrakarsa authorized.']))
            ->middleware('role:pemrakarsa');
        Route::get('/warga/ping', fn () => response()->json(['success' => true, 'message' => 'Warga authorized.']))
            ->middleware('role:warga');
    });
});

// Shorthand route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
