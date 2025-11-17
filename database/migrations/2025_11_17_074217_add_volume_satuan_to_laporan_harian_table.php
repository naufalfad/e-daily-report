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
            // Menambahkan kolom volume dan satuan setelah output_hasil_kerja agar rapi
            // Volume menggunakan integer (bilangan bulat), ubah ke float jika butuh desimal
            $table->integer('volume')->nullable()->after('output_hasil_kerja');
            
            // Satuan maksimal 50 karakter
            $table->string('satuan', 50)->nullable()->after('volume');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropColumn(['volume', 'satuan']);
        });
    }
};