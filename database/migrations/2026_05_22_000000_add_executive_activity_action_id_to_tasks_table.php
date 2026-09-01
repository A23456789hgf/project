<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks') || ! Schema::hasTable('executive_activity_actions')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'executive_activity_action_id')) {
                $table->foreignId('executive_activity_action_id')
                    ->nullable()
                    ->after('project_entities_id')
                    ->constrained('executive_activity_actions')
                    ->nullOnDelete();
            }
        });

        $this->backfillExecutiveActionLinks();
    }

    public function down(): void
    {
        if (! Schema::hasTable('tasks') || ! Schema::hasColumn('tasks', 'executive_activity_action_id')) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['executive_activity_action_id']);
            $table->dropColumn('executive_activity_action_id');
        });
    }

    private function backfillExecutiveActionLinks(): void
    {
        $actions = DB::table('executive_activity_actions')
            ->select('id', 'project_id', 'action')
            ->whereNotNull('action')
            ->get()
            ->groupBy(fn ($action) => $action->project_id.'|'.$action->action)
            ->filter(fn ($group) => $group->count() === 1)
            ->map(fn ($group) => $group->first()->id);

        DB::table('tasks')
            ->select('id', 'project_id', 'title')
            ->whereNull('executive_activity_action_id')
            ->whereNotNull('title')
            ->orderBy('id')
            ->chunkById(200, function ($tasks) use ($actions) {
                foreach ($tasks as $task) {
                    $actionId = $actions->get($task->project_id.'|'.$task->title);

                    if ($actionId) {
                        DB::table('tasks')
                            ->where('id', $task->id)
                            ->update(['executive_activity_action_id' => $actionId]);
                    }
                }
            });
    }
};
