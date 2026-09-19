<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\EntityApprovalStage;
use App\Models\InternalEntity;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

// Look for Ministry
$ministry = InternalEntity::where('name', 'وزارة الزراعة')->first();
if (! $ministry) {
    echo "No ministry\n";
}

$stages = EntityApprovalStage::where('entity_id', $ministry->id)->where('is_active', true)->with('responsibleUser')->get();
echo 'Ministry stages count: '.$stages->count()."\n";
foreach ($stages as $stage) {
    echo "Stage: {$stage->stage}, Valid User: ".($stage->hasValidResponsibleUser() ? 'Yes' : 'No')."\n";
}

$user = User::where('username', 'min_tech')->first();
if ($user) {
    echo "User status: {$user->status}, entity_id: {$user->entity_id}, type: ".gettype($user->entity_id)."\n";
}
