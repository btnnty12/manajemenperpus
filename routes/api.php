<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RekomendasiController;
use Illuminate\Support\Facades\Route;

// =========================================
// PUBLIC ROUTES (tanpa token)
// =========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// =========================================
// PROTECTED ROUTES (wajib Token Sanctum)
// =========================================
Route::middleware('auth:sanctum')->group(function () {

    // LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    // ADMIN-ONLY ROUTE
    Route::middleware('role:admin')->get('/admin-only', function () {
        return response()->json(['message' => 'Halo Admin']);
    });

    // =====================================
    // REKOMENDASI ROUTES
    // =====================================
    Route::prefix('rekomendasi')->group(function () {

        // Batch (HARUS DI ATAS)
        Route::post('/batch', [RekomendasiController::class, 'simpanBatch']);

        // List semua
        Route::get('/', [RekomendasiController::class, 'index']);

        // Tambah single
        Route::post('/', [RekomendasiController::class, 'store']);

        // Berdasarkan pengguna
        Route::get('/pengguna/{id}', [RekomendasiController::class, 'getByUser']);

        // Detail, Update, Delete berdasarkan ID
        Route::get('/{id}', [RekomendasiController::class, 'show']);
        Route::put('/{id}', [RekomendasiController::class, 'update']);
        Route::delete('/{id}', [RekomendasiController::class, 'destroy']);
    });

});
