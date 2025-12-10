<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;

use App\Http\Controllers\Core\ActivityLogController;
use App\Http\Controllers\Core\PengumumanController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\RiwayatController;
use App\Http\Controllers\Core\PetaAktivitasController;
use App\Http\Controllers\Core\SkoringController;
use App\Http\Controllers\Core\LkhController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTE (Global Access)
|--------------------------------------------------------------------------
*/

// Login Page
Route::view('/login', 'auth.login')->name('login');
Route::get('/', fn() => redirect()->route('login'));

// Login Process
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
| GENERAL STATIC TEST / DEMO & ERROR PAGES (Guest Access)
|--------------------------------------------------------------------------
*/

Route::view('/tes-pohon-organisasi', 'organisasi');

// Error Pages
Route::get('/error', fn() =>
    view('errors.error', ['message' => 'Contoh pesan error dari sistem.'])
);

Route::get('/maintenance', fn() =>
    view('errors.maintenance')
)->name('maintenance');

Route::get('/503', fn() => view('errors.503'));


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (WAJIB AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Export PDF (Global)
    Route::get('riwayat/export-pdf', [RiwayatController::class, 'exportPdf'])
        ->name('riwayat.export.pdf');

    Route::get('/penilai/skoring/export-pdf', [SkoringController::class, 'exportPdf']);

    Route::get('/skp/export/pdf', [SkpController::class, 'exportPdf'])
        ->name('skp.export.pdf');
    // GLOBAL EXPORT (bisa untuk staf, kadis, penilai)
    Route::post('/export-map', [PetaAktivitasController::class, 'exportMap'])
        ->middleware('auth');

    Route::get('/preview-map-pdf', [PetaAktivitasController::class, 'previewMapPdf'])
        ->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | PROFIL ROUTES
    |--------------------------------------------------------------------------
    */

    Route::prefix('profil')->name('profil.')->group(function () {
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
        Route::view('/pengumuman', 'staf.pengumuman')->name('pengumuman');
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

        Route::post('/validasi-laporan/{id}', 
            [App\Http\Controllers\Core\ValidatorController::class, 'validateLkh']
        )->name('validasi.store');

        Route::view('/skoring-kinerja', 'penilai.skoring-kinerja')->name('skoring-kinerja');
        Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
        Route::view('/riwayat', 'penilai.riwayat')->name('riwayat');
        Route::view('/pengumuman', 'penilai.pengumuman')->name('pengumuman');
        Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');

        // CRUD Pengumuman (Penilai)
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

        // FIX: Mengganti Route::view dengan Closure untuk passing variabel $role
        Route::get('/dashboard', function () {
            $role = 'kadis';
            return view('kadis.dashboard', compact('role'));
        })->name('dashboard');

        Route::view('/validasi-laporan', 'kadis.validasi-laporan')->name('validasi-laporan');

        // FIX: Mengganti Route::view dengan Closure untuk passing variabel $role (TAHAP 4.1)
        Route::get('/skoring-bidang', function () {
            $role = 'kadis';
            return view('kadis.skoring-bidang', compact('role'));
        })->name('skoring-bidang');

        Route::view('/log-aktivitas', 'kadis.log-aktivitas')->name('log-aktivitas');

        // Pengumuman Kadis
        Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
            Route::view('/', 'kadis.pengumuman')->name('index');
        });

        Route::view('/peta-aktivitas', 'kadis.peta-aktivitas')->name('peta-aktivitas');

        Route::get('/skoring-bidang/export-pdf', 
            [App\Http\Controllers\Core\BidangSkoringController::class, 'exportPdf']
        )->name('skoring-bidang.export.pdf');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
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
