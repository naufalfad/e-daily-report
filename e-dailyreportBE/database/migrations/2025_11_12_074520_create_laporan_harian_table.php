<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('skp_id')->nullable()->constrained('skp')->onDelete('set null');
            
            $table->date('tanggal_laporan');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->text('deskripsi_aktivitas');
            $table->text('output_hasil_kerja');
            $table->string('status', 20)->default('draft'); 

            // --- OPSI LOKASI (Sintaks laravel-spatial) ---
            $table->point('lokasi')->nullable(); // Ini akan menjadi kolom PostGIS
            // ---------------------------------------------
            
            $table->string('lokasi_manual_text')->nullable();
            $table->string('master_kelurahan_id', 15)->nullable();
            $table->boolean('is_luar_lokasi')->default(false);
            
            // Data Validasi
            $table->foreignId('validator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('waktu_validasi')->nullable();
            $table->text('komentar_validasi')->nullable();
            $table->timestamps();

            // FK untuk master wilayah
            $table->foreign('master_kelurahan_id')
                  ->references('id')
                  ->on('master_kelurahan')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};