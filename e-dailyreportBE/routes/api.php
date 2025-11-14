<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import All Controllers
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\GIS\WilayahController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\LkhController;
use App\Http\Controllers\Core\ValidatorController;
use App\Http\Controllers\Core\DashboardController;
use App\Http\Controllers\Core\ExportController;
use App\Http\Controllers\Core\PengumumanController; // <-- Baru
use App\Http\Controllers\Core\NotifikasiController; // <-- Baru

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
// 2. PROTECTED ROUTES (Wajib Login / Bearer Token)
// ======================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- A. AUTH MODULE ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // --- B. ADMIN MODULE (Manajemen User) ---
    Route::prefix('admin')->group(function () {
        Route::get('/master/roles', [MasterDataController::class, 'getRoles']);
        Route::get('/master/jabatan', [MasterDataController::class, 'getJabatan']);
        Route::get('/master/unit-kerja', [MasterDataController::class, 'getUnitKerja']);
        Route::get('/master/calon-atasan', [MasterDataController::class, 'getCalonAtasan']);
        Route::apiResource('users', UserManagementController::class);
    });

    // --- C. GIS MODULE ---
    Route::get('/provinsi', [WilayahController::class, 'provinsi']);
    Route::get('/kabupaten', [WilayahController::class, 'kabupaten']);
    Route::get('/kecamatan', [WilayahController::class, 'kecamatan']);
    Route::get('/kelurahan', [WilayahController::class, 'kelurahan']);

    // --- D. CORE: SKP ---
    Route::apiResource('skp', SkpController::class);

    // --- E. CORE: LKH ---
    Route::apiResource('lkh', LkhController::class);

    // --- F. CORE: VALIDATOR ---
    Route::prefix('validator')->group(function () {
        Route::get('/lkh', [ValidatorController::class, 'index']); 
        Route::get('/lkh/{id}', [ValidatorController::class, 'show']); 
        Route::post('/lkh/{id}/validate', [ValidatorController::class, 'validateLkh']); 
    });

    // --- G. CORE: DASHBOARD ---
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

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

    // === 1. MODUL PROFIL (Self Service) ===
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/password', [ProfileController::class, 'updatePassword']);

    // === 2. MODUL LOG AKTIVITAS ===
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    // === 3. MODUL SYSTEM SETTINGS (Admin Only) ===
    // Tambahkan middleware role admin jika perlu
    Route::prefix('admin')->group(function () {
        Route::get('/settings', [SystemSettingController::class, 'index']);
        Route::post('/settings', [SystemSettingController::class, 'update']);
    });

});