<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            // 1. Tambah kolom parent_id
            // Tipe data harus sama persis dengan kolom 'id' (unsignedBigInteger)
            // Wajib 'nullable' karena Bidang Induk (Level Root) tidak punya parent.
            // Kita taruh after('id') supaya rapi saat di-inspect di database tools.
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');

            // 2. Tambah Foreign Key Constraint (Self-Join)
            // Menghubungkan parent_id ke id di tabel yang sama (bidangs)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('bidang')
                  ->onDelete('set null'); 
                  // Logika: Jika Induk dihapus, Anak jangan dihapus, tapi parent_id-nya diset NULL.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            // Urutan rollback: Hapus constraint dulu, baru kolomnya.
            // Format default nama index laravel: nama_tabel_nama_kolom_foreign
            $table->dropForeign(['parent_id']); 
            $table->dropColumn('parent_id');
        });
    }
};
