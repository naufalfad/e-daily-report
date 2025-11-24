<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Core\ActivityLogController;
// [BARU] Import Controller Pengumuman agar bisa dipanggil di route
use App\Http\Controllers\Core\PengumumanController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTE
|--------------------------------------------------------------------------
*/

// Login (GET)
Route::view('/login', 'auth.login')->name('login');

// Redirect root → login
Route::get('/', fn () => redirect()->route('login'));

// Login (POST)
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');


/*
|--------------------------------------------------------------------------
| GENERAL STATIC TEST / DEMO
|--------------------------------------------------------------------------
*/

Route::view('/tes-pohon-organisasi', 'organisasi');


/*
|--------------------------------------------------------------------------
| STAF ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('staf')->name('staf.')->group(function () {

    Route::view('/dashboard', 'staf.dashboard')->name('dashboard');
    Route::view('/input-lkh', 'staf.input-lkh')->name('input-lkh');
    Route::get('/input-lkh/{id?}', function ($id = null) {
        return view('staf.input-lkh', ['id' => $id]);
    })->name('input-lkh');
    Route::view('/input-skp', 'staf.input-skp')->name('input-skp');
    Route::view('/riwayat-lkh', 'staf.riwayat-lkh')->name('riwayat-lkh');
    Route::view('/peta-aktivitas', 'staf.peta-aktivitas')->name('peta-aktivitas');

    // Log Aktivitas Staf
    Route::view('/log-aktivitas', 'staf.log-aktivitas')->name('log-aktivitas');

    // [PERBAIKAN 1] Route Pengumuman untuk Staf
    // ---------------------------------------------------------
    // View untuk halaman (pastikan nanti file view staf.pengumuman dibuat/dicopy)
    Route::view('/pengumuman', 'staf.pengumuman')->name('pengumuman');
    // API untuk mengambil data list pengumuman (AJAX)
    Route::get('/pengumuman/list', [PengumumanController::class, 'index'])->name('pengumuman.list');
});


/*
|--------------------------------------------------------------------------
| PENILAI ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('penilai')->name('penilai.')->group(function () {

    Route::get('/input-laporan/{id?}', function ($id = null) {
        return view('penilai.input-lkh', ['id' => $id]);
    })->name('input-laporan');
    Route::view('/dashboard', 'penilai.dashboard')->name('dashboard');
    Route::view('/input-laporan', 'penilai.input-lkh')->name('input-laporan');
    Route::view('/input-skp', 'penilai.input-skp')->name('input-skp');
    Route::view('/validasi-laporan', 'penilai.validasi-laporan')->name('validasi-laporan');
    Route::view('/skoring-kinerja', 'penilai.skoring-kinerja')->name('skoring-kinerja');
    Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
    Route::view('/riwayat', 'penilai.riwayat')->name('riwayat');
    
    // Log Aktivitas Penilai
    Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');

    // [PERBAIKAN 2] Route Pengumuman Lengkap untuk Penilai (CRUD)
    // ---------------------------------------------------------
    Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
        // Halaman Utama (View)
        Route::view('/', 'penilai.pengumuman')->name('index');
        
        // API endpoints (dipanggil via fetch/axios di JS)
        Route::get('/list', [PengumumanController::class, 'index'])->name('list');   // Ambil Data
        Route::post('/store', [PengumumanController::class, 'store'])->name('store'); // Simpan Baru
        Route::delete('/{id}', [PengumumanController::class, 'destroy'])->name('destroy'); // Hapus
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
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('/manajemen-pegawai', 'admin.manajemen-pegawai')->name('manajemen-pegawai');
    Route::view('/akun-pengguna', 'admin.akun-pengguna')->name('akun-pengguna');
    Route::view('/pengaturan-sistem', 'admin.pengaturan-sistem')->name('pengaturan-sistem');

    // Log Aktivitas Admin
    Route::view('/log-aktivitas', 'admin.log-aktivitas')->name('log-aktivitas');
});