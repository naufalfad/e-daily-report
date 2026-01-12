<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            // Menambahkan kolom level, nullable agar aman untuk data lama
            $table->string('level')->nullable()->after('parent_id');
            // Atau jika ingin enum: 
            // $table->enum('level', ['bidang', 'sub_bidang', 'seksi'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};