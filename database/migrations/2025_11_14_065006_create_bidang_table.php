<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidang', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Unit Kerja
            // [AMAN] Kita hapus 'cascade' agar database tidak sembarangan menghapus data anak
            $table->foreignId('unit_kerja_id')
                  ->constrained('unit_kerja'); 
            
            $table->string('nama_bidang'); 
            $table->timestamps();
            
            // [WAJIB] Fitur Tong Sampah
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang');
    }
};