<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function ($table) {
        $table->string('email')->nullable()->change();
    });
}

public function down()
{
    // Isi nilai default untuk email yang NULL agar tidak error saat rollback (Not Null Violation)
    \Illuminate\Support\Facades\DB::table('users')->whereNull('email')->update([
        'email' => \Illuminate\Support\Facades\DB::raw("username || '@example.com'")
    ]);

    Schema::table('users', function ($table) {
        $table->string('email')->nullable(false)->change();
    });
}

};
