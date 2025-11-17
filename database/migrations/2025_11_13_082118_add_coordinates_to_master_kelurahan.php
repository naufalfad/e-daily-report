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
        Schema::table('master_kelurahan', function (Blueprint $table) {
            // Tambah koordinat pusat pemerintahan desa/kelurahan
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_kelurahan', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
