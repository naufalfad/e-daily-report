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
            // [LOGIC] Default 'geofence' untuk backward compatibility data lama
            $table->string('mode_lokasi')
                  ->default('geofence')
                  ->after('lokasi')
                  ->comment('Pilihan: geofence, geocoding');

            // [LOGIC] Nullable karena tidak wajib jika mode = geofence
            $table->text('lokasi_teks')
                  ->nullable()
                  ->after('mode_lokasi')
                  ->comment('Menyimpan nama tempat hasil pencarian (Google Maps/POI)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn(['mode_lokasi', 'lokasi_teks']);
        });
    }
};