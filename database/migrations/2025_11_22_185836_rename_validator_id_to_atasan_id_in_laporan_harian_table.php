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
        // Perubahan skema ke atas (Roll forward)
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Cek dulu apakah kolom validator_id ada sebelum diubah
            if (Schema::hasColumn('laporan_harian', 'validator_id')) {
                $table->renameColumn('validator_id', 'atasan_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perubahan skema ke bawah (Roll back)
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Cek dulu apakah kolom atasan_id ada sebelum dikembalikan
            if (Schema::hasColumn('laporan_harian', 'atasan_id')) {
                $table->renameColumn('atasan_id', 'validator_id');
            }
        });
    }
};
