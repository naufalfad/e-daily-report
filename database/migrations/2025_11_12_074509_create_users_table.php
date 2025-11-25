<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip', 50)->unique()->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto_profil')->nullable();
            $table->string('no_telp', 50)->nullable();
            $table->string('alamat', 100)->nullable();
            
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->onDelete('set null');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->onDelete('set null');
            $table->foreignId('atasan_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};