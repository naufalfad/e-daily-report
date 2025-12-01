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
        // 1. Optimasi Tabel Notifikasi (Prioritas Utama)
        Schema::table('notifikasi', function (Blueprint $table) {
            // COMPOUND INDEX 1: Untuk fitur "Badge Count" (Jumlah belum dibaca)
            // Query: where('user_id_recipient', $id)->where('is_read', false)
            // Logika: Database langsung melompat ke bucket user tersebut dan hanya menghitung yang flag-nya false.
            $table->index(['user_id_recipient', 'is_read'], 'idx_notif_user_read');

            // COMPOUND INDEX 2: Untuk fitur "Inbox List" (Urutan waktu)
            // Query: where('user_id_recipient', $id)->latest()
            // Logika: Database sudah menyimpan data user dalam urutan waktu, jadi tidak perlu sorting manual (CPU intensive) saat select.
            $table->index(['user_id_recipient', 'created_at'], 'idx_notif_user_time');
        });

        // 2. Optimasi Tabel Laporan Harian (Logika Scheduler)
        Schema::table('laporan_harian', function (Blueprint $table) {
            // INDEX: Untuk mempercepat Scheduler pengecekan LKH harian
            // Query: whereDate('tanggal_laporan', $today)
            $table->index('tanggal_laporan', 'idx_lkh_date');

            // INDEX: Untuk User saat melihat riwayat kinerja mereka sendiri
            // Query: where('user_id', $id)->whereMonth(...)
            $table->index(['user_id', 'tanggal_laporan'], 'idx_lkh_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropIndex('idx_notif_user_read');
            $table->dropIndex('idx_notif_user_time');
        });

        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropIndex('idx_lkh_date');
            $table->dropIndex('idx_lkh_user_date');
        });
    }
};