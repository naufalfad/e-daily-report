<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_creator')->constrained('users')->onDelete('cascade');
            $table->string('judul');
            $table->text('isi_pengumuman')->nullable();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};