<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;

// Core Controllers
use App\Http\Controllers\Core\ActivityLogController;
use App\Http\Controllers\Core\PengumumanController;
use App\Http\Controllers\Core\SkpController;
use App\Http\Controllers\Core\RiwayatController;
use App\Http\Controllers\Core\PetaAktivitasController;
use App\Http\Controllers\Core\SkoringController;
use App\Http\Controllers\Core\LkhController;
use App\Http\Controllers\Core\KadisValidatorController;
use App\Http\Controllers\Core\BidangSkoringController;

// Admin Controllers
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserAccountController;   
use App\Http\Controllers\Admin\Master\UnitKerjaController;
use App\Http\Controllers\Admin\Master\BidangController;
use App\Http\Controllers\Admin\Master\JabatanController;
use App\Http\Controllers\Admin\Master\TupoksiController;
use App\Http\Controllers\GIS\WilayahController;


/*
|--------------------------------------------------------------------------
| AUTH ROUTE (Global Access)
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
| GENERAL STATIC TEST / DEMO & ERROR PAGES
|--------------------------------------------------------------------------
*/
Route::view('/tes-pohon-organisasi', 'organisasi');
Route::get('/error', fn() => view('errors.error', ['message' => 'Contoh pesan error dari sistem.']));
Route::get('/maintenance', fn() => view('errors.maintenance'))->name('maintenance');
Route::get('/503', fn() => view('errors.503'));

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (WAJIB AUTH)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Export PDF (Global)
    Route::get('riwayat/export-pdf', [RiwayatController::class, 'exportPdf'])->name('riwayat.export.pdf');
    Route::get('/penilai/skoring/export-pdf', [SkoringController::class, 'exportPdf']);
    Route::get('/skp/export/pdf', [SkpController::class, 'exportPdf'])->name('skp.export.pdf');
    Route::post('/export-map', [PetaAktivitasController::class, 'exportMap']);
    Route::get('/preview-map-pdf', [PetaAktivitasController::class, 'previewMapPdf']);

    /*
    |--------------------------------------------------------------------------
    | UNIVERSAL PENGUMUMAN API (Accessible by All Roles)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/pengumuman')->name('pengumuman.api.')->group(function () {
        Route::get('/list', [PengumumanController::class, 'index'])->name('list');
        Route::post('/store', [PengumumanController::class, 'store'])->name('store');
        Route::delete('/{id}', [PengumumanController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | PROFIL ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
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
        Route::get('/riwayat-lkh', [RiwayatController::class, 'indexStaf'])->name('riwayat-lkh');
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
        Route::post('/validasi-laporan/{id}', [App\Http\Controllers\Core\ValidatorController::class, 'validateLkh'])->name('validasi.store');
        Route::view('/skoring-kinerja', 'penilai.skoring-kinerja')->name('skoring-kinerja');
        Route::view('/peta-aktivitas', 'penilai.peta-aktivitas')->name('peta-aktivitas');
        Route::get('/riwayat', [RiwayatController::class, 'indexPenilai'])->name('riwayat');
        Route::view('/log-aktivitas', 'penilai.log-aktivitas')->name('log-aktivitas');
        Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
            Route::view('/', 'penilai.pengumuman')->name('index'); 
        });
    });


    /*
    |--------------------------------------------------------------------------
    | KADIS ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('kadis')->name('kadis.')->group(function () {
        Route::get('/dashboard', function () {
            $role = 'kadis';
            return view('kadis.dashboard', compact('role'));
        })->name('dashboard');
        Route::view('/validasi-laporan', 'kadis.validasi-laporan')->name('validasi-laporan');
        Route::get('/validasi-laporan/{id}', function ($id) {
            return view('kadis.validasi-laporan', ['id' => $id]);
        })->where('id', '[0-9]+')->name('validasi-laporan.detail');
        Route::post('/validasi-laporan/{id}', [KadisValidatorController::class, 'validateLkh'])->name('validasi-laporan.store');
        Route::get('/skoring-bidang', function () {
            $role = 'kadis';
            return view('kadis.skoring-bidang', compact('role'));
        })->name('skoring-bidang');
        Route::view('/log-aktivitas', 'kadis.log-aktivitas')->name('log-aktivitas');
        Route::view('/peta-aktivitas', 'kadis.peta-aktivitas')->name('peta-aktivitas');
        Route::get('/skoring-bidang/export-pdf', [BidangSkoringController::class, 'exportPdf'])->name('skoring-bidang.export.pdf');
        
        Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
            Route::get('/', function () {
                $bidangs = \App\Models\Bidang::orderBy('nama_bidang', 'asc')->get();
                return view('kadis.pengumuman', compact('bidangs'));
            })->name('index');
        });
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES (MODIFIED FOR PAGINATION & HIERARCHY)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        
        // [HR] Manajemen Pegawai
        Route::get('/manajemen-pegawai', [UserManagementController::class, 'index'])->name('manajemen-pegawai');
        
        // [IT] Akun Pengguna
        Route::get('/akun-pengguna', [UserAccountController::class, 'index'])->name('akun-pengguna');
        
        Route::view('/pengaturan-sistem', 'admin.pengaturan-sistem')->name('pengaturan-sistem');
        Route::view('/log-aktivitas', 'admin.log-aktivitas')->name('log-aktivitas');

        Route::prefix('master')->name('master.')->group(function () {
            // 1. Unit Kerja
            Route::get('unit-kerja', [UnitKerjaController::class, 'index'])->name('unit-kerja.index');
            // TODO: Tambahkan route CRUD Unit Kerja (store, update, destroy) jika diperlukan

            // 2. Bidang (Updated for Hierarchy & CRUD)
            // Route API internal untuk mengambil data induk bidang (AJAX)
            Route::get('bidang/get-parents', [BidangController::class, 'getParents'])->name('bidang.get-parents');
            Route::get('bidang', [BidangController::class, 'index'])->name('bidang.index');
            Route::post('bidang', [BidangController::class, 'store'])->name('bidang.store');
            Route::put('bidang/{id}', [BidangController::class, 'update'])->name('bidang.update');
            Route::delete('bidang/{id}', [BidangController::class, 'destroy'])->name('bidang.destroy');

            // 3. Jabatan
            Route::get('jabatan', [JabatanController::class, 'index'])->name('jabatan.index');
            // TODO: Tambahkan route CRUD Jabatan (store, update, destroy) jika diperlukan

            // 4. Tupoksi
            Route::resource('tupoksi', TupoksiController::class)->only(['index', 'store', 'update', 'destroy']);
        });
    });

    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        // Dropdown Provinsi & Kabupaten (jika diperlukan perluasan area nanti)
        Route::get('/provinsi', [WilayahController::class, 'provinsi'])->name('provinsi');
        Route::get('/kabupaten', [WilayahController::class, 'kabupaten'])->name('kabupaten');

        // [CORE FEATURE] Dropdown untuk pencarian lokasi LKH
        // API 1: Mengambil list kecamatan berdasarkan kabupaten (Timika)
        Route::get('/kecamatan', [WilayahController::class, 'kecamatan'])->name('kecamatan'); 
        
        // API 2: Mengambil kelurahan BESERTA koorinat (Lat/Long) untuk flyTo di peta
        Route::get('/kelurahan', [WilayahController::class, 'kelurahan'])->name('kelurahan'); 
    });
});