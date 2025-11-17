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
        Schema::table('skp', function (Blueprint $table) {
            $table->dropColumn('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('skp', function (Blueprint $table) {
            $table->string('satuan', 50)->nullable(); // Kembalikan jika rollback
        });
    }
};
