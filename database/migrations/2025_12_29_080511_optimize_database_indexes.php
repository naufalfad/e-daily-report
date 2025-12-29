<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. GIN INDEX untuk PENGUMUMAN (Full Text Search)
        // Kita gabungkan judul dan isi agar bisa dicari sekaligus
        DB::statement("
            CREATE INDEX idx_pengumuman_fulltext 
            ON pengumuman 
            USING GIN (to_tsvector('indonesian', judul || ' ' || COALESCE(isi_pengumuman, '')));
        ");

        // 2. GIN INDEX untuk LAPORAN HARIAN (Pencarian Deskripsi)
        DB::statement("
            CREATE INDEX idx_lkh_deskripsi_fulltext 
            ON laporan_harian 
            USING GIN (to_tsvector('indonesian', deskripsi_aktivitas));
        ");

        // 3. COMPOSITE INDEX untuk RIWAYAT (Filter Cepat)
        // Mempercepat query: where user_id = ? order by tanggal_laporan desc
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->index(['user_id', 'tanggal_laporan'], 'idx_lkh_user_tanggal');
            $table->index(['atasan_id', 'status'], 'idx_lkh_approval'); // Mempercepat halaman validasi atasan
        });
    }

    public function down(): void
    {
        // Hapus index jika rollback
        DB::statement("DROP INDEX IF EXISTS idx_pengumuman_fulltext");
        DB::statement("DROP INDEX IF EXISTS idx_lkh_deskripsi_fulltext");
        
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropIndex('idx_lkh_user_tanggal');
            $table->dropIndex('idx_lkh_approval');
        });
    }
};
