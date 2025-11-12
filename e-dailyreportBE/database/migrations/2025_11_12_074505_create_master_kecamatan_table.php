<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kecamatan', function (Blueprint $table) {
            $table->string('id', 15)->primary();
            $table->string('kabupaten_id', 15);
            $table->string('nama');
            
            $table->foreign('kabupaten_id')->references('id')->on('master_kabupaten');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kecamatan');
    }
};