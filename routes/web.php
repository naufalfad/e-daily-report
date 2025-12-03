<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Core\PengumumanController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\RiwayatController;
use App\Http\Controllers\Core\SkoringController;
use App\Http\Controllers\Core\LkhController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTE
|--------------------------------------------------------------------------
*/

Route::view('/login', 'auth.login')->name('login');

Route::get('/', fn() => redirect()->route('login'));

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| GENERAL STATIC
|--------------------------------------------------------------------------
*/

Route::view('/tes-pohon-organisasi', 'organisasi');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('riwayat/export-pdf', [RiwayatController::class, 'exportPdf'])->name('riwayat.export.pdf');
    Route::get('/penilai/skoring/export-pdf', [App\Http\Controllers\Core\SkoringController::class, 'exportPdf']);

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
        Route::get('/skoring-kinerja', [SkpController::class, 'skoringKinerja'])->name('skoring-kinerja');
        Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
        Route::view('/riwayat', 'penilai.riwayat')->name('riwayat');
        Route::view('/pengumuman', 'penilai.pengumuman')->name('pengumuman');

        Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');

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
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('/manajemen-pegawai', 'admin.manajemen-pegawai')->name('manajemen-pegawai');
        Route::view('/akun-pengguna', 'admin.akun-pengguna')->name('akun-pengguna');
        Route::view('/pengaturan-sistem', 'admin.pengaturan-sistem')->name('pengaturan-sistem');

        Route::get('/akun-pengguna', fn() => view('admin.akun-pengguna'))->name('akun-pengguna');
        Route::get('/pengaturan-sistem', fn() => view('admin.pengaturan-sistem'))->name('pengaturan-sistem');
        Route::get('/log-aktivitas', fn() => view('admin.log-aktivitas'))->name('log-aktivitas');
    });

    /*
    |--------------------------------------------------------------------------
    | TEST ERROR PAGES
    |--------------------------------------------------------------------------
    */
    Route::get('/error', fn() => view('errors.error', ['message' => 'Contoh pesan error dari sistem.']));
    Route::get('/maintenance', fn() => view('errors.maintenance'));
    Route::get('/503', fn() => view('errors.503'));
});