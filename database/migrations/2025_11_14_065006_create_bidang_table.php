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
            // Relasi ke Unit Kerja (Contoh: Bapenda)
            $table->foreignId('unit_kerja_id')
                  ->constrained('unit_kerja')
                  ->onDelete('cascade'); 
            
            $table->string('nama_bidang'); // Contoh: 'Sekretariat', 'Bidang Pajak'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang');
    }
};