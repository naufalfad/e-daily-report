<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_recipient')->constrained('users')->onDelete('cascade');
            $table->string('tipe_notifikasi', 50);
            $table->text('pesan');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID LKH atau Pengumuman terkait');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};