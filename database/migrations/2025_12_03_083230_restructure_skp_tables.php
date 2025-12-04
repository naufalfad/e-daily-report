<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. BERSIHKAN RELASI LAMA DI LAPORAN HARIAN
        // Kita harus menghapus FK 'skp_id' dulu sebelum bisa menghapus tabel 'skp'
        if (Schema::hasColumn('laporan_harian', 'skp_id')) {
            Schema::table('laporan_harian', function (Blueprint $table) {
                // Drop Foreign Key & Column
                // Menggunakan array sintaks agar Laravel otomatis mencari nama index-nya
                $table->dropForeign(['skp_id']); 
                $table->dropColumn('skp_id');
            });
        }

        // 2. HAPUS TABEL SKP LAMA (FLAT)
        Schema::dropIfExists('skp');

        // 3. BUAT TABEL HEADER: SKP_RENCANA
        // Menyimpan "Apa yang mau dikerjakan" (Manual Text)
        Schema::create('skp_rencana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->date('periode_awal');  // Filter Periode
            $table->date('periode_akhir'); 

            // Input Manual (String/Text)
            $table->text('rhk_intervensi')->nullable(); // RHK Atasan
            $table->text('rencana_hasil_kerja');        // RHK Pegawai (Judul Utama)
            
            $table->timestamps();
        });

        // 4. BUAT TABEL DETAIL: SKP_TARGET
        // Menyimpan "Target Angka per Aspek"
        Schema::create('skp_target', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Header
            $table->foreignId('skp_rencana_id')->constrained('skp_rencana')->onDelete('cascade');
            
            // Jenis Aspek (Dinamis: Bisa Kuantitas, Kualitas, Waktu, Biaya)
            $table->enum('jenis_aspek', ['Kuantitas', 'Kualitas', 'Waktu', 'Biaya']);
            
            $table->string('indikator');        // Contoh: "Jumlah Dokumen"
            $table->integer('target');          // Contoh: 10
            $table->string('satuan', 50);       // Contoh: "Dokumen"
            
            $table->timestamps();
        });

        // 5. UPDATE LAPORAN HARIAN (LINK KE STRUKTUR BARU)
        // Laporan harian sekarang menginduk ke 'skp_rencana'
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->foreignId('skp_rencana_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('skp_rencana')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Rollback: Hapus struktur baru
        Schema::table('laporan_harian', function (Blueprint $table) {
             $table->dropForeign(['skp_rencana_id']);
             $table->dropColumn('skp_rencana_id');
        });

        Schema::dropIfExists('skp_target');
        Schema::dropIfExists('skp_rencana');
        
        // Note: Tabel 'skp' lama tidak dikembalikan otomatis karena datanya sudah didrop.
        // Jika ingin rollback sempurna, harus define create 'skp' lagi di sini.
    }
};