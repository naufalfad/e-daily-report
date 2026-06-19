<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/api/admin/akun', 'GET');
$req->headers->set('Accept', 'application/json');
$req->headers->set('X-Requested-With', 'XMLHttpRequest');
$user = App\Models\User::find(1);
auth()->login($user);
$response = app()->handle($req);

echo 'STATUS: ' . $response->getStatusCode() . "\n";
echo 'CONTENT: ' . substr($response->getContent(), 0, 500) . "\n";
