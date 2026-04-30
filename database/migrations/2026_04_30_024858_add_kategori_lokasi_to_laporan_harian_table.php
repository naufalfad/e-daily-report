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
            $table->enum('kategori_lokasi', ['WFO', 'WFH', 'WFA', 'DL'])
                  ->default('WFO')
                  ->after('jenis_kegiatan')
                  ->comment('Kategori lokasi pelaporan kinerja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn('kategori_lokasi');
        });
    }
};