<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE users CASCADE;');
    Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE bidang CASCADE;');
    Illuminate\Support\Facades\DB::statement('TRUNCATE TABLE jabatan CASCADE;');
    echo "Tables truncated via Postgres CASCADE!\n";
} catch (\Exception $e) {
    echo "Error truncating tables: " . $e->getMessage() . "\n";
}
