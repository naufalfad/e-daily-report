<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            // Tambah kolom jenis_kegiatan setelah output_hasil_kerja
            $table->string('jenis_kegiatan', 100)->nullable()->after('output_hasil_kerja');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn('jenis_kegiatan');
        });
    }
};