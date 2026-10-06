<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aplikasi Pengelolaan Sampah dan Lingkungan Terpadu Kota Madiun
*/

Route::prefix('v1')->group(function () {
    // Authentication Routes (AUT-F-002)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        });
    });

    // Role-Protected Route Groups (RBAC Verification)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware('role:admin_dlh')->prefix('admin')->group(function () {
            Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'Admin DLH authorized.']));
        });

        Route::middleware('role:petugas_lapangan')->prefix('petugas')->group(function () {
            Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'Petugas Lapangan authorized.']));
        });

        Route::middleware('role:pengelola_tps3r')->prefix('tps3r')->group(function () {
            Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'Pengelola TPS3R authorized.']));
        });

        Route::middleware('role:pemrakarsa')->prefix('pemrakarsa')->group(function () {
            Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'Pemrakarsa authorized.']));
        });

        Route::middleware('role:warga')->prefix('warga')->group(function () {
            Route::get('/ping', fn () => response()->json(['success' => true, 'message' => 'Warga authorized.']));
        });
    });
});

// Fallback legacy / shorthand route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
