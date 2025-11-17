<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kelurahan', function (Blueprint $table) {
            $table->string('id', 15)->primary();
            $table->string('kecamatan_id', 15);
            $table->string('nama');
            
            $table->foreign('kecamatan_id')->references('id')->on('master_kecamatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kelurahan');
    }
};