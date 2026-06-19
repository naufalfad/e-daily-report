<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bidangs = App\Models\Bidang::pluck('parent_id', 'id')->toArray();
$circular = [];
foreach ($bidangs as $id => $parent) {
    $visited = [];
    $curr = $id;
    while ($curr) {
        if (isset($visited[$curr])) {
            $circular[] = $id;
            break;
        }
        $visited[$curr] = true;
        $curr = $bidangs[$curr] ?? null;
    }
}
echo 'CIRCULAR_BIDANG: ' . json_encode($circular) . "\n";
