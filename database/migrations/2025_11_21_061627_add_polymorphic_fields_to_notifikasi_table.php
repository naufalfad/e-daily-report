<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            // Menambahkan kolom 'related_type' setelah 'related_id'
            // Contoh isi: 'App\Models\LaporanHarian' atau 'App\Models\Pengumuman'
            $table->string('related_type')->nullable()->after('related_id');

            // Menambahkan Index untuk kecepatan lookup polymorphic
            $table->index(['related_type', 'related_id'], 'idx_notif_morph');
        });
    }

    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropIndex('idx_notif_morph');
            $table->dropColumn('related_type');
        });
    }
};