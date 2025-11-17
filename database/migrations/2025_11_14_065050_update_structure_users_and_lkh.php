<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Users: Tambah kolom bidang_id
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('bidang_id')
                  ->nullable()
                  ->after('unit_kerja_id') // Diletakkan setelah unit_kerja
                  ->constrained('bidang')
                  ->onDelete('set null');
        });

        // 2. Update Laporan Harian: Tambah tupoksi_id & pastikan jenis_kegiatan ada
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Link ke Tupoksi (Referensi Tugas)
            $table->foreignId('tupoksi_id')
                  ->nullable()
                  ->after('skp_id')
                  ->constrained('tupoksi')
                  ->onDelete('set null');

            // Kolom jenis_kegiatan (Rapat, Pelayanan Publik, dll)
            // Kita gunakan string agar fleksibel menyimpan value dari dropdown
            if (!Schema::hasColumn('laporan_harian', 'jenis_kegiatan')) {
                $table->string('jenis_kegiatan')->after('waktu_selesai')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });

        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropForeign(['tupoksi_id']);
            $table->dropColumn('tupoksi_id');
            // Jangan drop jenis_kegiatan jika sebelumnya sudah ada dari migrasi lain, 
            // tapi aman jika kita drop disini kalau ini migrasi penambahan murni.
            // $table->dropColumn('jenis_kegiatan'); 
        });
    }
};