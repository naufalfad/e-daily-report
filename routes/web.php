<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Core\ActivityLogController;
use App\Http\Controllers\Core\PengumumanController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\RiwayatController;
use App\Http\Controllers\Core\SkoringController;
use App\Http\Controllers\Core\LkhController;
use App\Http\Controllers\Auth\ProfileController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTE (Global Access)
|--------------------------------------------------------------------------
*/

Route::view('/login', 'auth.login')->name('login');

Route::get('/', fn() => redirect()->route('login'));

// Login (POST) - API Route (Diobrolin di API.php)
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| GENERAL STATIC TEST / DEMO & ERROR PAGES (Guest Access)
|--------------------------------------------------------------------------
*/

Route::view('/tes-pohon-organisasi', 'organisasi');

// === ROUTE HALAMAN ERROR & MAINTENANCE (WAJIB DILUAR MIDDLEWARE 'AUTH') ===
// Blok ini harus berada di sini agar bisa diakses user yang belum login.

Route::get('/error', function () {
    return view('errors.error', ['message' => 'Contoh pesan error dari sistem.']);
});

Route::get('/maintenance', function () {
    return view('errors.maintenance');
})->name('maintenance'); // <--- INI ADALAH FIX YANG DIBUTUHKAN

Route::get('/503', function () {
    return view('errors.503');
});


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (WAJIB AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('riwayat/export-pdf', [RiwayatController::class, 'exportPdf'])->name('riwayat.export.pdf');
    Route::get('/penilai/skoring/export-pdf', [App\Http\Controllers\Core\SkoringController::class, 'exportPdf']);

    /*
    |--------------------------------------------------------------------------
    | PROFIL ROUTES (Manajemen Akun Mandiri)
    |--------------------------------------------------------------------------
    */
    Route::prefix('profil')->name('profil.')->group(function () {
        // Halaman Edit Profil
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        
        // Proses Update Data
        Route::put('/update-biodata', [ProfileController::class, 'updateBiodata'])->name('update-biodata');
        Route::put('/update-account', [ProfileController::class, 'updateAccount'])->name('update-account');
    });

    /*
    |--------------------------------------------------------------------------
    | STAF ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('staf')->name('staf.')->group(function () {

        Route::view('/dashboard', 'staf.dashboard')->name('dashboard');
        Route::get('/input-lkh/{id?}', function ($id = null) {
            return view('staf.input-lkh', ['id' => $id]);
        })->name('input-lkh');
        
        Route::view('/input-skp', 'staf.input-skp')->name('input-skp');
        Route::view('/riwayat-lkh', 'staf.riwayat-lkh')->name('riwayat-lkh');
        Route::view('/peta-aktivitas', 'staf.peta-aktivitas')->name('peta-aktivitas');
        Route::view('/log-aktivitas', 'staf.log-aktivitas')->name('log-aktivitas');
    });

    /*
    |--------------------------------------------------------------------------
    | PENILAI ROUTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('penilai')->name('penilai.')->group(function () {

        Route::view('/dashboard', 'penilai.dashboard')->name('dashboard');
        
        Route::get('/input-laporan/{id?}', function ($id = null) {
            return view('penilai.input-lkh', ['id' => $id]);
        })->name('input-laporan');
        
        Route::view('/input-skp', 'penilai.input-skp')->name('input-skp');
        Route::view('/validasi-laporan', 'penilai.validasi-laporan')->name('validasi-laporan');
        Route::view('/skoring-kinerja', 'penilai.skoring-kinerja')->name('skoring-kinerja');
        Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
        Route::view('/riwayat', 'penilai.riwayat')->name('riwayat');
        Route::view('/pengumuman', 'penilai.pengumuman')->name('pengumuman');

        Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');

        // Route Pengumuman Lengkap untuk Penilai (CRUD)
        Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
            Route::view('/', 'penilai.pengumuman')->name('index');
            Route::get('/list', [PengumumanController::class, 'index'])->name('list');   
            Route::post('/store', [PengumumanController::class, 'store'])->name('store'); 
            Route::delete('/{id}', [PengumumanController::class, 'destroy'])->name('destroy'); 
        });
    });

    /*
    |--------------------------------------------------------------------------
    | KADIS ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('kadis')->name('kadis.')->group(function () {
        Route::view('/dashboard', 'kadis.dashboard')->name('dashboard');
        Route::view('/validasi-laporan', 'kadis.validasi-laporan')->name('validasi-laporan');
        Route::view('/log-aktivitas', 'kadis.log-aktivitas')->name('log-aktivitas');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (MODUL ROLE ADMIN - CLEAN VERSION)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        
        // 1. View HR: Manajemen Pegawai (Profile & Struktur)
        Route::view('/manajemen-pegawai', 'admin.manajemen-pegawai')->name('manajemen-pegawai');
        
        // 2. View IT: Akun Pengguna (Password & Akses)
        Route::view('/akun-pengguna', 'admin.akun-pengguna')->name('akun-pengguna');
        
        Route::view('/pengaturan-sistem', 'admin.pengaturan-sistem')->name('pengaturan-sistem');
        Route::view('/log-aktivitas', 'admin.log-aktivitas')->name('log-aktivitas');
    });
});