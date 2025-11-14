<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tupoksi', function (Blueprint $table) {
            $table->id();
            // Relasi ke Bidang (Tupoksi ini milik bidang apa?)
            $table->foreignId('bidang_id')
                  ->constrained('bidang')
                  ->onDelete('cascade');
            
            // Uraian tugas (Contoh: 'Menyiapkan bahan koordinasi...')
            $table->text('uraian_tugas'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tupoksi');
    }
};