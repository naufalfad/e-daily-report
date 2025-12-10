<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('aktivitas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('deskripsi_aktivitas')->nullable();
            $table->text('output_hasil_kerja')->nullable();
            $table->string('jenis_kegiatan')->nullable();

            $table->date('tanggal_laporan')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();

            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->string('lokasi')->nullable(); // hasil reverse geocoding

            $table->enum('status', ['approved', 'rejected', 'waiting_review'])
                ->default('waiting_review');

            $table->boolean('is_luar_lokasi')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('aktivitas');
    }
};
