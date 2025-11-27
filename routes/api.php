<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController; 
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SystemSettingController; 
use App\Http\Controllers\GIS\WilayahController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\LkhController;
use App\Http\Controllers\Core\ValidatorController;
use App\Http\Controllers\Core\DashboardController;
use App\Http\Controllers\Core\ExportController;
use App\Http\Controllers\Core\PengumumanController;
use App\Http\Controllers\Core\NotifikasiController;
use App\Http\Controllers\Core\ActivityLogController; 
use App\Http\Controllers\Core\OrganisasiController; 

/*
|--------------------------------------------------------------------------
| API Routes - e-Daily Report Bapenda Mimika
|--------------------------------------------------------------------------
*/

// ======================================================
// 1. PUBLIC ROUTES (Tanpa Login)
// ======================================================
Route::post('/login', [AuthController::class, 'login']);


// ======================================================
// 2. PROTECTED ROUTES (Wajib Login / Sanctum)
// ======================================================
Route::middleware('auth:sanctum')->group(function () {
     // Laporan yang harus divalidasi oleh KADIS (dari KABID)
    Route::get('/lkh', [\App\Http\Controllers\Core\KadisValidatorController::class, 'index']);
    Route::get('/lkh/{id}', [\App\Http\Controllers\Core\KadisValidatorController::class, 'show']);
    Route::post('/lkh/{id}/validate', [\App\Http\Controllers\Core\KadisValidatorController::class, 'validateLkh']);

    // Monitoring laporan staf (yang sudah approved oleh Kabid)
    Route::get('/monitoring/staf', [\App\Http\Controllers\Core\KadisValidatorController::class, 'monitoringStaf']);

    // --- A. AUTH & PROFILE ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    // --- B. DASHBOARD ---
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/kadis', [DashboardController::class, 'getStatsKadis']);

    // --- C. ADMIN MODULE ---
    Route::prefix('admin')->group(function () {

        // User Management
        Route::apiResource('users', UserManagementController::class);

        // Master Data
        Route::prefix('master')->group(function() {
            Route::get('unit-kerja', [MasterDataController::class, 'indexUnitKerja']);
            Route::post('unit-kerja', [MasterDataController::class, 'storeUnitKerja']);

            Route::get('bidang', [MasterDataController::class, 'indexBidang']);
            Route::post('bidang', [MasterDataController::class, 'storeBidang']);
            Route::put('bidang/{id}', [MasterDataController::class, 'updateBidang']);
            Route::delete('bidang/{id}', [MasterDataController::class, 'destroyBidang']);

            Route::get('tupoksi', [MasterDataController::class, 'indexTupoksi']);
            Route::post('tupoksi', [MasterDataController::class, 'storeTupoksi']);
            Route::put('tupoksi/{id}', [MasterDataController::class, 'updateTupoksi']);
            Route::delete('tupoksi/{id}', [MasterDataController::class, 'destroyTupoksi']);
        });

        // Dropdown Getters
        Route::prefix('master-dropdown')->group(function() {
            Route::get('roles', [MasterDataController::class, 'getRoles']);
            Route::get('jabatan', [MasterDataController::class, 'getJabatan']);
            Route::get('unit-kerja', [MasterDataController::class, 'getUnitKerja']);
            Route::get('calon-atasan', [MasterDataController::class, 'getCalonAtasan']);
            Route::get('bidang-by-unit-kerja/{unitKerjaId}', [MasterDataController::class, 'getBidangByUnitKerja']);
        });

        // System Settings
        Route::get('settings', [SystemSettingController::class, 'index']);
        Route::post('settings', [SystemSettingController::class, 'update']);
    });

    // --- D. GIS MODULE ---
    Route::get('/provinsi', [WilayahController::class, 'provinsi']);
    Route::get('/kabupaten', [WilayahController::class, 'kabupaten']);
    Route::get('/kecamatan', [WilayahController::class, 'kecamatan']);
    Route::get('/kelurahan', [WilayahController::class, 'kelurahan']);

    // --- E. CORE MODULE ---
    Route::apiResource('skp', SkpController::class);

    // LKH
    Route::prefix('lkh')->group(function () {
        // 1. Spesifik / Utility Routes (Ditaruh di atas)
        Route::get('riwayat', [LkhController::class, 'getRiwayat']);
        Route::get('referensi', [LkhController::class, 'getReferensi']);
                
        // 2. Resource Routes (API Resource diganti manual)
        Route::get('/', [LkhController::class, 'index']); // GET /lkh -> List
        Route::post('/', [LkhController::class, 'store']); // POST /lkh -> Create
        Route::post('/update/{id}', [LkhController::class, 'update']); // PUT /lkh -> Create
        
        // Rute yang menggunakan {id} (Harus DITARUH PALING BAWAH)
        Route::get('/{id}', [LkhController::class, 'show']); // GET /lkh/{id} -> Show
        Route::delete('/{id}', [LkhController::class, 'destroy']); // DELETE /lkh/{id} -> Delete
    });

    // Validator
    Route::prefix('validator')->group(function () {
        Route::get('lkh', [ValidatorController::class, 'index']);
        Route::get('lkh/{id}', [ValidatorController::class, 'show']);
        Route::post('lkh/{id}/validate', [ValidatorController::class, 'validateLkh']);
    });

    // Export
    Route::get('export/excel', [ExportController::class, 'exportExcel']);
    Route::get('export/pdf', [ExportController::class, 'exportPdf']);

    // Pengumuman
    Route::get('pengumuman', [PengumumanController::class, 'index']);
    Route::post('pengumuman', [PengumumanController::class, 'store']);
    Route::delete('pengumuman/{id}', [PengumumanController::class, 'destroy']);

    // Notifikasi
    Route::get('notifikasi', [NotifikasiController::class, 'index']);
    Route::post('notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::post('notifikasi/read-all', [NotifikasiController::class, 'markAllRead']);

    // Activity Log
    Route::get('log-aktivitas', [ActivityLogController::class, 'index'])->name('api.log.aktivitas');

    // Organisasi
    Route::get('organisasi/tree', [OrganisasiController::class, 'getTree']);
});