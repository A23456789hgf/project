<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Helper to safely add index if it doesn't already exist.
     */
    protected function addIndexSafely(string $table, array|string $columns, ?string $indexName = null): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $cols = (array) $columns;
        foreach ($cols as $col) {
            if (! Schema::hasColumn($table, $col)) {
                return;
            }
        }

        $name = $indexName ?? ($table.'_'.implode('_', $cols).'_index');

        try {
            Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                $t->index($cols, $name);
            });
        } catch (Throwable $e) {
            // Index already exists or cannot be created, ignore safely
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexSafely('project_details', 'project_id');
        $this->addIndexSafely('main_objectives', 'project_id');
        $this->addIndexSafely('special_objectives', 'project_id');
        $this->addIndexSafely('project_supervising_authorities', 'project_id');
        $this->addIndexSafely('implementing_entities', 'project_id');
        $this->addIndexSafely('preliminary_activities', 'project_id');
        $this->addIndexSafely('executive_activities', 'project_id');
        $this->addIndexSafely('project_costs', 'project_id');
        $this->addIndexSafely('project_financings', 'project_id');
        $this->addIndexSafely('project_approvals', ['project_id', 'is_completed', 'deleted_at'], 'idx_proj_appr_proj_comp');
        $this->addIndexSafely('projects', ['status', 'created_at']);
        $this->addIndexSafely('projects', 'form_number');
        $this->addIndexSafely('projects', 'creator_entity_id');
        $this->addIndexSafely('projects', 'internal_entity_id');
        $this->addIndexSafely('projects', 'created_by_user_id');
        $this->addIndexSafely('correspondences', ['sender_entity_id', 'recipient_entity_id']);
        $this->addIndexSafely('correspondences', 'parent_id');
        $this->addIndexSafely('correspondence_referrals', ['correspondence_id', 'referral_status']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for safety
    }
};
