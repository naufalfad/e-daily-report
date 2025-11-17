<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/tes-pohon-organisasi', function () {
    return view('organisasi');
});

Route::view('/login', 'auth.login')->name('login');
Route::get('/', fn() => redirect()->route('login'));

// Rute Staf
Route::view('/staf/dashboard', 'staf.dashboard')
    ->name('staf.dashboard');
Route::get('/staf/input-lkh', function () {
    return view('staf.input-lkh');
})->name('staf.input-lkh');

// [BARU] Menambahkan rute untuk Input SKP
Route::get('/staf/input-skp', function () {
    return view('staf.input-skp');
})->name('staf.input-skp');

// [BARU] Menambahkan rute untuk Riwayat LKH
Route::get('/staf/riwayat-lkh', function () {
    // Data dummy dipindahkan ke sini dari file Blade
    $dummyDataList = [
        [
            'id' => 1,
            'tanggal_kirim' => '07 Nov 2025 | 12:10',
            'nama_kegiatan' => 'Rapat Koordinasi Internal',
            'tanggal_verifikasi' => '08 Nov 2025 | 13:20',
            'penilai' => 'Ahmad Saeful Muridan, S.E., M.H.',
            'status' => 'Diterima', // 'Diterima', 'Ditolak', 'Menunggu'
            'uraian' => 'Melaksanakan rapat koordinasi internal untuk evaluasi kinerja bulanan.',
            'output' => 'Notulensi Rapat',
            'volume' => 1,
            'satuan' => 'Dokumen',
            'kategori' => 'SKP',
            'jam_mulai' => '13:00',
            'jam_selesai' => '16:00',
            'lokasi' => 'Kantor Bapenda',
            'catatan' => 'Good Job! Tingkatkan lagi kinerjamu!',
        ],
        [
            'id' => 2,
            'tanggal_kirim' => '08 Nov 2025 | 14:10',
            'nama_kegiatan' => 'Rapat Koordinasi Pendapatan',
            'tanggal_verifikasi' => '09 Nov 2025 | 14:30',
            'penilai' => 'Ahmad Saeful Muridan, S.E., M.H.',
            'status' => 'Ditolak',
            'uraian' => 'Melakukan kunjungan lapangan untuk proyek jalan.',
            'output' => 'Hasil Kunjungan',
            'volume' => 3,
            'satuan' => 'Jam',
            'kategori' => 'Non - SKP',
            'jam_mulai' => '13:00',
            'jam_selesai' => '16:00',
            'lokasi' => 'Jalan Mimika',
            'catatan' => 'Laporan belum sesuai, mohon perbaiki lagi!',
        ],
        [
            'id' => 3,
            'tanggal_kirim' => '10 Nov 2025 | 10:10',
            'nama_kegiatan' => 'Perjalanan Dinas',
            'tanggal_verifikasi' => '12 Nov 2025 | 15:00',
            'penilai' => 'Ahmad Saeful Muridan, S.E., M.H.',
            'status' => 'Ditolak',
            'uraian' => 'Perjalanan dinas ke kantor pusat terkait audit.',
            'output' => 'Laporan Perjadin',
            'volume' => 1,
            'satuan' => 'Laporan',
            'kategori' => 'Non - SKP',
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:00',
            'lokasi' => 'Kantor Pusat',
            'catatan' => 'Bukti perjalanan (tiket/boarding pass) tidak dilampirkan.',
        ]
    ];

    return view('staf.riwayat-lkh', compact('dummyDataList'));
})->name('staf.riwayat-lkh');

// [BARU] Menambahkan rute untuk Peta Aktivitas
Route::get('/staf/peta-aktivitas', function () {
    return view('staf.peta-aktivitas');
})->name('staf.peta-aktivitas');

Route::get('/staf/log-aktivitas', function () {
    return view('staf.log-aktivitas');
})->name('staf.log-aktivitas');

Route::prefix('penilai')->name('penilai.')->group(function () {
    Route::get('/dashboard', fn () => view('penilai.dashboard'))->name('dashboard');
    Route::get('/input-laporan', fn () => view('penilai.input-lkh'))->name('input-laporan');
    Route::get('/validasi-laporan', fn () => view('penilai.validasi-laporan'))->name('validasi-laporan');
    Route::get('/skoring-kinerja', fn () => view('penilai.skoring-kinerja'))->name('skoring-kinerja');
    Route::get('/peta-aktivitas', fn () => view('penilai.peta-aktivitas'))->name('peta-aktivitas');
    Route::get('/riwayat', fn () => view('penilai.riwayat'))->name('riwayat');
    Route::get('/log-aktivitas', fn () => view('penilai.log-aktivitas'))->name('log-aktivitas');
    Route::get('/pengumuman', fn () => view('penilai.pengumuman'))->name('pengumuman');
});