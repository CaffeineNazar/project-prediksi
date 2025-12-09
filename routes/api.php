<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    PrediksiController,
    MahasiswaController,
    FakultasController,
    ProgramStudiController,
    IntervensiController,
    DashboardController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (jika diperlukan)
Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]);
    });

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [DashboardController::class, 'statistics']);
            Route::get('/recent', [DashboardController::class, 'recentActivities']);
        });

        // Fakultas
        Route::prefix('fakultas')->group(function () {
            Route::get('/', [FakultasController::class, 'index']);
            Route::get('/{id}/prodi', [FakultasController::class, 'withProdi']);
        });

        // Program Studi
        Route::get('/program-studi', [ProgramStudiController::class, 'index']);

        // Mahasiswa
        Route::prefix('mahasiswa')->group(function () {
            Route::get('/', [MahasiswaController::class, 'index']);
            Route::get('/berisiko', [MahasiswaController::class, 'berisiko']);
            Route::get('/{id}', [MahasiswaController::class, 'show']);
        });

        // Prediksi
        Route::prefix('prediksi')->group(function () {
            Route::post('/individual', [PrediksiController::class, 'individual']);
            Route::post('/batch', [PrediksiController::class, 'batch']);
            Route::get('/batch/{id}', [PrediksiController::class, 'batchStatus']);
            Route::get('/mahasiswa/{mahasiswaId}', [PrediksiController::class, 'history']);
            Route::get('/statistics', [PrediksiController::class, 'statistics']);
            Route::get('/health', [PrediksiController::class, 'health']);
        });

        // Intervensi
        Route::prefix('intervensi')->group(function () {
            Route::get('/', [IntervensiController::class, 'index']);
            Route::post('/', [IntervensiController::class, 'store']);
            Route::patch('/{id}/status', [IntervensiController::class, 'updateStatus']);
        });
    });
});