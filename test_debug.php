<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$entity = App\Models\InternalEntity::firstOrCreate(['name' => 'Test Entity 123']);
$assignedUser = App\Models\User::factory()->create(['entity_id' => $entity->id]);
$project = App\Models\Project::create(['project_name' => 'Test Project', 'status' => 'pending_approval']);
$approval = App\Models\ProjectApproval::create(['project_id' => $project->id, 'entity_id' => $entity->id, 'assigned_user_id' => $assignedUser->id, 'status' => 'pending', 'is_active' => true, 'step_order' => 1, 'approval_type' => 'approval']);
Auth::login($assignedUser);
$q = App\Models\ProjectApproval::query()->where('is_active', true)->where(function ($q) use ($assignedUser, $entity) {
    $q->where('status', 'pending')->whereHas('project', function($pq) {
        $pq->where('status', 'pending_approval');
    });
    $q->whereIn('entity_id', [$entity->id]);
    $q->where(function ($userQ) use ($assignedUser) {
        $userQ->where('technical_reviewer_id', $assignedUser->id)
              ->orWhere('financial_reviewer_id', $assignedUser->id)
              ->orWhere('assigned_user_id', $assignedUser->id);
    });
});
echo "Count: " . $q->count() . "\n";
