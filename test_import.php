<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\UserCsvImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

$import = new UserCsvImport();
Excel::import($import, 'Absen Perbidang 2026.xlsx');

if (count($import->errors) > 0) {
    echo "Import completed with errors:\n";
    print_r($import->errors);
} else {
    echo "Import completed successfully! No errors.\n";
}

$userCount = \App\Models\User::count();
echo "Total users in DB: " . $userCount . "\n";
