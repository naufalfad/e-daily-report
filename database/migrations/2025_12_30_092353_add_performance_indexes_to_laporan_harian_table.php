<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Index 1: Optimasi untuk Staf & Penilai (Mode: Riwayat Saya)
            // Pola Query: WHERE user_id = ? AND status IN (...) ORDER BY tanggal_laporan DESC
            $table->index(['user_id', 'status', 'tanggal_laporan'], 'idx_lkh_user_status_date');

            // Index 2: Optimasi untuk Penilai (Mode: Riwayat Bawahan)
            // Pola Query: WHERE atasan_id = ? AND status IN (...) ORDER BY tanggal_laporan DESC
            $table->index(['atasan_id', 'status', 'tanggal_laporan'], 'idx_lkh_atasan_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Hapus index saat rollback agar tidak meninggalkan sampah di DB
            $table->dropIndex('idx_lkh_user_status_date');
            $table->dropIndex('idx_lkh_atasan_status_date');
        });
    }
};