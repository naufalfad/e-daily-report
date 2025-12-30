<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kapabilitas scope pengumuman berbasis Bidang (Divisi).
     */
    public function up(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            // Menambahkan foreignId bidang_id setelah unit_kerja_id
            // Nullable karena jika NULL berarti pengumuman bersifat UMUM (Global)
            $table->foreignId('bidang_id')
                ->after('unit_kerja_id')
                ->nullable()
                ->constrained('bidang')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengumuman', function (Blueprint $table) {
            // Menghapus constraint foreign key terlebih dahulu sebelum drop kolom
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });
    }
};