<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// AUTH
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;

// ADMIN
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SystemSettingController;

// GIS
use App\Http\Controllers\GIS\WilayahController;

// CORE
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
use App\Http\Controllers\Core\BidangSkoringController;

/*
|--------------------------------------------------------------------------
| API Routes - e-Daily Report Bapenda Mimika
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Tanpa Login)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Wajib Login / Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | A. KADIS VALIDASI LKH
    |--------------------------------------------------------------------------
    */
    Route::get('/lkh', [KadisValidatorController::class, 'index']);
    Route::get('/lkh/{id}', [KadisValidatorController::class, 'show']);
    Route::post('/lkh/{id}/validate', [KadisValidatorController::class, 'validateLkh']);

    // Monitoring staf
    Route::get('/monitoring/staf', [KadisValidatorController::class, 'monitoringStaf']);


    /*
    |--------------------------------------------------------------------------
    | B. AUTH & PROFILE
    |--------------------------------------------------------------------------
    */
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);


    /*
    |--------------------------------------------------------------------------
    | C. DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/kadis', [DashboardController::class, 'getStatsKadis']);


    /*
    |--------------------------------------------------------------------------
    | D. ADMIN MODULE
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        /*
        |----------------------------------------------------------------------
        | 1. HR DOMAIN — MANAJEMEN PEGAWAI
        |----------------------------------------------------------------------
        */
        Route::apiResource('pegawai', UserManagementController::class);


        /*
        |----------------------------------------------------------------------
        | 2. IT DOMAIN — AKUN PENGGUNA (Keamanan)
        |----------------------------------------------------------------------
        */
        Route::prefix('akun')->group(function () {

            Route::get('/', [UserAccountController::class, 'index']);
            Route::patch('/{id}/credentials', [UserAccountController::class, 'updateCredentials']);
            Route::patch('/{id}/role', [UserAccountController::class, 'updateRole']);
            Route::patch('/{id}/status', [UserAccountController::class, 'updateStatus']);

        });


        /*
        |----------------------------------------------------------------------
        | 3. MASTER DATA & SYSTEM SETTINGS
        |----------------------------------------------------------------------
        */
        Route::prefix('master')->group(function () {

            // Unit Kerja
            Route::get('unit-kerja', [MasterDataController::class, 'indexUnitKerja']);
            Route::post('unit-kerja', [MasterDataController::class, 'storeUnitKerja']);

            // Bidang
            Route::get('bidang', [MasterDataController::class, 'indexBidang']);
            Route::post('bidang', [MasterDataController::class, 'storeBidang']);
            Route::put('bidang/{id}', [MasterDataController::class, 'updateBidang']);
            Route::delete('bidang/{id}', [MasterDataController::class, 'destroyBidang']);

            // Tupoksi
            Route::get('tupoksi', [MasterDataController::class, 'indexTupoksi']);
            Route::post('tupoksi', [MasterDataController::class, 'storeTupoksi']);
            Route::put('tupoksi/{id}', [MasterDataController::class, 'updateTupoksi']);
            Route::delete('tupoksi/{id}', [MasterDataController::class, 'destroyTupoksi']);

        });

        /*
        |----------------------------------------------------------------------
        | Dropdown Getters
        |----------------------------------------------------------------------
        */
        Route::prefix('master-dropdown')->group(function () {
            Route::get('roles', [MasterDataController::class, 'getRoles']);
            Route::get('jabatan', [MasterDataController::class, 'getJabatan']);
            Route::get('unit-kerja', [MasterDataController::class, 'getUnitKerja']);
            Route::get('calon-atasan', [MasterDataController::class, 'getCalonAtasan']);
            Route::get('bidang-by-unit-kerja/{unitKerjaId}', [MasterDataController::class, 'getBidangByUnitKerja']);
        });

        /*
        |----------------------------------------------------------------------
        | System Settings
        |----------------------------------------------------------------------
        */
        Route::get('settings', [SystemSettingController::class, 'index']);
        Route::post('settings', [SystemSettingController::class, 'update']);

    });


    /*
    |--------------------------------------------------------------------------
    | E. GIS MODULE
    |--------------------------------------------------------------------------
    */
    Route::get('/provinsi', [WilayahController::class, 'provinsi']);
    Route::get('/kabupaten', [WilayahController::class, 'kabupaten']);
    Route::get('/kecamatan', [WilayahController::class, 'kecamatan']);
    Route::get('/kelurahan', [WilayahController::class, 'kelurahan']);


    /*
    |--------------------------------------------------------------------------
    | F. CORE MODULE
    |--------------------------------------------------------------------------
    */

    // SKP
    Route::apiResource('skp', SkpController::class);

    // LKH
    Route::prefix('lkh')->group(function () {

        Route::get('riwayat', [LkhController::class, 'getRiwayat']);
        Route::get('referensi', [LkhController::class, 'getReferensi']);

        Route::get('/', [LkhController::class, 'index']);
        Route::post('/', [LkhController::class, 'store']);

        Route::post('/update/{id}', [LkhController::class, 'update'])
            ->where('id', '[0-9]+');

        Route::get('/{id}', [LkhController::class, 'show'])
            ->where('id', '[0-9]+');

        Route::delete('/{id}', [LkhController::class, 'destroy'])
            ->where('id', '[0-9]+');

    });

    // Validator
    Route::prefix('validator')->group(function () {

        Route::get('kadis/lkh', [ValidatorController::class, 'index']);
        Route::post('kadis/lkh/{id}/validate', [ValidatorController::class, 'validateLkh']);

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
    Route::get('log-aktivitas', [ActivityLogController::class, 'index'])
        ->name('api.log.aktivitas');


    /*
    |--------------------------------------------------------------------------
    | G. KADIS API
    |--------------------------------------------------------------------------
    */
    Route::prefix('kadis')->group(function () {

        // Skoring Per Bidang
        Route::get('/skoring-bidang', [BidangSkoringController::class, 'index']);

        // Pengumuman Kadis
        Route::prefix('pengumuman')->group(function () {
            Route::get('/list', [PengumumanController::class, 'index']);
            Route::post('/store', [PengumumanController::class, 'store']);
            Route::delete('/{id}', [PengumumanController::class, 'destroy']);
        });

    });


    /*
    |--------------------------------------------------------------------------
    | H. ORGANISASI
    |--------------------------------------------------------------------------
    */
    Route::get('organisasi/tree', [OrganisasiController::class, 'getTree']);


    /*
    |--------------------------------------------------------------------------
    | I. SKORING KINERJA (BAWAHAN)
    |--------------------------------------------------------------------------
    */
    Route::get('/skoring-kinerja', [SkpController::class, 'getSkoringData']);


    /*
    |--------------------------------------------------------------------------
    | J. PETA AKTIVITAS
    |--------------------------------------------------------------------------
    */
    Route::get('peta-aktivitas', [PetaAktivitasController::class, 'getPetaAktivitas']);
    Route::get('staf-aktivitas', [PetaAktivitasController::class, 'getStafAktivitas']);
    Route::get('all-aktivitas', [PetaAktivitasController::class, 'getAllAktivitas']);
});
