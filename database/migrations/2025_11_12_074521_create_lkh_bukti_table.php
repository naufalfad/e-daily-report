<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lkh_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporan_harian')->onDelete('cascade');
            $table->string('file_path')->comment('Object Key / Path di MinIO S3');
            $table->string('file_name_original')->nullable();
            $table->string('file_type', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lkh_bukti');
    }
};