<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();

            // [BARU] Relasi ke Unit Kerja
            // Logic: Jabatan 'Kepala Dinas' di Dinas A beda ID dengan di Dinas B
            $table->foreignId('unit_kerja_id')
                  ->constrained('unit_kerja'); // Hapus cascade agar aman

            $table->string('nama_jabatan'); // Contoh: 'Sekretaris', 'Kasubag Umum'
            $table->timestamps();
            
            // [WAJIB] Fitur Tong Sampah
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan');
    }
};