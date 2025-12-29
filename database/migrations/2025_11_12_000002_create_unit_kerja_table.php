<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit');
            
            // Opsi: Jika ada hierarki (misal: Dinas -> UPTD)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('unit_kerja')
                  ->onDelete('set null'); // Aman, tidak cascade
            
            $table->timestamps();
            
            // [BARU] Kolom ajaib untuk fitur "Tong Sampah"
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_kerja');
    }
};