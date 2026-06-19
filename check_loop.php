<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = User::all();
foreach($users as $user) {
    $visited = [];
    $current = $user;
    while($current->atasan_id) {
        if(in_array($current->atasan_id, $visited)) {
            echo 'Circular reference found for user ID ' . $user->id . ' looping at ' . $current->atasan_id . "\n";
            // Fix it!
            $user->atasan_id = null;
            $user->save();
            echo "Fixed by setting atasan_id to null for user " . $user->id . "\n";
            break;
        }
        $visited[] = $current->id;
        $current = User::find($current->atasan_id);
        if (!$current) break;
    }
}
echo "Done check.\n";
