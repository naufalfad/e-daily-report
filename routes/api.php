<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController; 
use App\Http\Controllers\Admin\UserManagementController;
// [BARU] Controller khusus untuk manajemen akun (IT/Security)
use App\Http\Controllers\Admin\UserAccountController; 
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
use App\Http\Controllers\Core\KadisValidatorController;
use App\Http\Controllers\Core\PetaAktivitasController;

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
    
    // Laporan yang harus divalidasi oleh KADIS
    Route::get('/lkh', [KadisValidatorController::class, 'index']);
    Route::get('/lkh/{id}', [KadisValidatorController::class, 'show']);
    Route::post('/lkh/{id}/validate', [KadisValidatorController::class, 'validateLkh']);

    // Monitoring laporan staf 
    Route::get('/monitoring/staf', [KadisValidatorController::class, 'monitoringStaf']);

    // --- A. AUTH & PROFILE ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    // --- B. DASHBOARD ---
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/kadis', [DashboardController::class, 'getStatsKadis']);

    // --- C. ADMIN MODULE (PEMISAHAN LOGIKA HR vs IT) ---
    Route::prefix('admin')->group(function () {

        // 1. DOMAIN HR: MANAJEMEN PEGAWAI
        // Fokus: Create Pegawai Baru, Edit Profil, Jabatan, Unit Kerja.
        // Catatan: Tidak melayani ganti password/username.
        Route::apiResource('pegawai', UserManagementController::class);

        // 2. DOMAIN IT: AKUN PENGGUNA (BARU)
        // Fokus: Security, Password Reset, Username, Role, Suspend.
        Route::prefix('akun')->group(function() {
            Route::get('/', [UserAccountController::class, 'index']); // List user tabel akun
            Route::patch('/{id}/credentials', [UserAccountController::class, 'updateCredentials']); // Username & Password
            Route::patch('/{id}/role', [UserAccountController::class, 'updateRole']); // Role Access
            Route::patch('/{id}/status', [UserAccountController::class, 'updateStatus']); // Suspend/Active
        });

        // 3. MASTER DATA & SETTINGS
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
        Route::get('riwayat', [LkhController::class, 'getRiwayat']);
        Route::get('referensi', [LkhController::class, 'getReferensi']);
        Route::get('/', [LkhController::class, 'index']); 
        Route::post('/', [LkhController::class, 'store']); 
        
        Route::post('/update/{id}', [LkhController::class, 'update'])->where('id', '[0-9]+'); 
        Route::get('/{id}', [LkhController::class, 'show'])->where('id', '[0-9]+'); 
        Route::delete('/{id}', [LkhController::class, 'destroy'])->where('id', '[0-9]+'); 
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
    Route::post('/lkh/export-pdf', [LkhController::class, 'exportPdfDirect']);

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

    // API untuk mengambil data skoring bawahan
    Route::get('/skoring-kinerja', [\App\Http\Controllers\Core\SkpController::class, 'getSkoringData']);

    //API mengambil lokasi peta aktivitas
    Route::get('peta-aktivitas', [PetaAktivitasController::class, 'getPetaAktivitas']);
    Route::get('staf-aktivitas', [PetaAktivitasController::class, 'getStafAktivitas']);
});