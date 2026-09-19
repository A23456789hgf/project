<?php

use App\Http\Controllers\Project\Services\ProjectService;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$user = User::whereHas('role', function ($q) {
    $q->where('name', '!=', 'admin')
        ->where('name', '!=', 'Admin')
        ->where('name', '!=', 'مدير النظام');
})->first();

if (! $user) {
    echo 'No non-admin user found.';
    exit;
}
$user->administrative_scope_id = 5;
$user->save();
Auth::login($user);

$service = app(ProjectService::class);
$projects = $service->getProjects(request(), [], 'projects.index', [], true); // true for query only

echo $projects->toSql();
echo "\nBindings:\n";
echo json_encode($projects->getBindings());
