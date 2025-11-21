<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Core\ActivityLogController;

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
    Route::view('/input-skp', 'staf.input-skp')->name('input-skp');
    Route::view('/riwayat-lkh', 'staf.riwayat-lkh')->name('riwayat-lkh');
    Route::view('/peta-aktivitas', 'staf.peta-aktivitas')->name('peta-aktivitas');

    // Log Aktivitas Staf (pakai view)
    Route::view('/log-aktivitas', 'staf.log-aktivitas')->name('log-aktivitas');
});


/*
|--------------------------------------------------------------------------
| PENILAI ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('penilai')->name('penilai.')->group(function () {

    Route::view('/dashboard', 'penilai.dashboard')->name('dashboard');
    Route::view('/input-laporan', 'penilai.input-lkh')->name('input-laporan');
    Route::view('/input-skp', 'penilai.input-skp')->name('input-skp');
    Route::view('/validasi-laporan', 'penilai.validasi-laporan')->name('validasi-laporan');
    Route::view('/skoring-kinerja', 'penilai.skoring-kinerja')->name('skoring-kinerja');
    Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
    Route::view('/riwayat', 'penilai.riwayat')->name('riwayat');
    Route::view('/pengumuman', 'penilai.pengumuman')->name('pengumuman');

    // Log Aktivitas Penilai (pakai view)
    Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');
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

    // Log Aktivitas Admin (pakai view)
    Route::view('/log-aktivitas', 'admin.log-aktivitas')->name('log-aktivitas');
});

