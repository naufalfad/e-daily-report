<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_skp');
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->text('rencana_aksi')->nullable();
            $table->text('indikator')->nullable();
            $table->integer('target')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skp');
    }
};