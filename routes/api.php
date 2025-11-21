<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import All Controllers
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
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);


// ======================================================
// 2. PROTECTED ROUTES (Wajib Login / Bearer Token)
// ======================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- G. CORE: DASHBOARD ---
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    // --- A. AUTH & PROFIL MODULE ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    // --- B. ADMIN MODULE (Manajemen User & Master) ---
    Route::prefix('admin')->group(function () {
        
        // B.1. Manajemen User
        Route::apiResource('users', UserManagementController::class);

        // B.2. Manajemen Master Data (CRUD oleh Admin)
        Route::prefix('master')->group(function() {
            // CRUD Unit Kerja
            Route::get('/unit-kerja', [MasterDataController::class, 'indexUnitKerja']);
            Route::post('/unit-kerja', [MasterDataController::class, 'storeUnitKerja']);
            
            // CRUD Bidang
            Route::get('/bidang', [MasterDataController::class, 'indexBidang']);
            Route::post('/bidang', [MasterDataController::class, 'storeBidang']);
            Route::put('/bidang/{id}', [MasterDataController::class, 'updateBidang']);
            Route::delete('/bidang/{id}', [MasterDataController::class, 'destroyBidang']);

            // CRUD Tupoksi
            Route::get('/tupoksi', [MasterDataController::class, 'indexTupoksi']); // Wajib kirim ?bidang_id=...
            Route::post('/tupoksi', [MasterDataController::class, 'storeTupoksi']);
            Route::put('/tupoksi/{id}', [MasterDataController::class, 'updateTupoksi']);
            Route::delete('/tupoksi/{id}', [MasterDataController::class, 'destroyTupoksi']);
        });

        // B.3. Dropdown Getters (Untuk Form)
        Route::prefix('master-dropdown')->group(function() {
            Route::get('/roles', [MasterDataController::class, 'getRoles']);
            Route::get('/jabatan', [MasterDataController::class, 'getJabatan']);
            Route::get('/unit-kerja', [MasterDataController::class, 'getUnitKerja']);
            Route::get('/calon-atasan', [MasterDataController::class, 'getCalonAtasan']);
            // [BARU] Cascading dropdown
            Route::get('/bidang-by-unit-kerja/{unitKerjaId}', [MasterDataController::class, 'getBidangByUnitKerja']);
        });

        // B.4. System Settings
        Route::get('/settings', [SystemSettingController::class, 'index']);
        Route::post('/settings', [SystemSettingController::class, 'update']);
    });

    // --- C. GIS MODULE ---
    Route::get('/provinsi', [WilayahController::class, 'provinsi']);
    Route::get('/kabupaten', [WilayahController::class, 'kabupaten']);
    Route::get('/kecamatan', [WilayahController::class, 'kecamatan']);
    Route::get('/kelurahan', [WilayahController::class, 'kelurahan']);

    // --- D. CORE: SKP ---
    Route::apiResource('skp', SkpController::class);

    // --- E. CORE: LKH (FIXED ROUTING ORDER) ---
    Route::prefix('lkh')->group(function () {
        
        // 1. Spesifik / Utility Routes (Ditaruh di atas)
        Route::get('/history/riwayat', [LkhController::class, 'getRiwayat']); // FIX: Rute Riwayat
        Route::get('/referensi', [LkhController::class, 'getReferensi']); // FIX: Rute Referensi
        
        // 2. Resource Routes (API Resource diganti manual)
        Route::get('/', [LkhController::class, 'index']); // GET /lkh -> List
        Route::post('/', [LkhController::class, 'store']); // POST /lkh -> Create
        
        // Rute yang menggunakan {id} (Harus DITARUH PALING BAWAH)
        Route::get('/{id}', [LkhController::class, 'show']); // GET /lkh/{id} -> Show
        Route::put('/{id}', [LkhController::class, 'update']); // PUT /lkh/{id} -> Update
        Route::delete('/{id}', [LkhController::class, 'destroy']); // DELETE /lkh/{id} -> Delete
    });
    // Route::get('/lkh/riwayat', [LkhController::class, 'getRiwayat']); // HAPUS RUTE INI

    // --- F. CORE: VALIDATOR ---
    Route::prefix('validator')->group(function () {
        Route::get('/lkh', [ValidatorController::class, 'index']); 
        Route::get('/lkh/{id}', [ValidatorController::class, 'show']); 
        Route::post('/lkh/{id}/validate', [ValidatorController::class, 'validateLkh']); 
    });

    // --- H. CORE: EXPORT ---
    Route::get('/export/excel', [ExportController::class, 'exportExcel']);
    Route::get('/export/pdf', [ExportController::class, 'exportPdf']);

    // --- I. CORE: PENGUMUMAN ---
    Route::get('/pengumuman', [PengumumanController::class, 'index']);
    Route::post('/pengumuman', [PengumumanController::class, 'store']);
    Route::delete('/pengumuman/{id}', [PengumumanController::class, 'destroy']);

    // --- J. CORE: NOTIFIKASI ---
    Route::get('/notifikasi', [NotifikasiController::class, 'index']);
    Route::post('/notifikasi/{id}/read', [NotifikasiController::class, 'markAsRead']);
    Route::post('/notifikasi/read-all', [NotifikasiController::class, 'markAllRead']);
    
    // --- K. MODUL LOG AKTIVITAS ---
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    // --- L. [BARU] MODUL STRUKTUR ORGANISASI ---
    Route::get('/organisasi/tree', [OrganisasiController::class, 'getTree']);

});