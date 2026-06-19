<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::pluck('atasan_id', 'id')->toArray();
$circular = [];
foreach ($users as $id => $atasan) {
    $visited = [];
    $curr = $id;
    while ($curr) {
        if (isset($visited[$curr])) {
            $circular[] = $id;
            break;
        }
        $visited[$curr] = true;
        $curr = $users[$curr] ?? null;
    }
}
echo 'CIRCULAR: ' . json_encode($circular) . "\n";
