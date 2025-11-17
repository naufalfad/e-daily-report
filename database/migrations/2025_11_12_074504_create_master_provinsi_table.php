<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_provinsi', function (Blueprint $table) {
            $table->string('id', 15)->primary(); // PK adalah String dari API
            $table->string('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_provinsi');
    }
};