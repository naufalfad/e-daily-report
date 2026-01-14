<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_pangkat_to_users.php
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pangkat')->nullable()->after('nip'); // e.g. "Pembina (IV/a)"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        });
    }
};
