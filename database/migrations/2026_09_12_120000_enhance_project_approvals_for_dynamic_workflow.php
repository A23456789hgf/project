<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            if (! Schema::hasColumn('project_approvals', 'drop')) {
                $table->string('drop', 50)->nullable()->after('entity_id')->comment('Legacy stage identifier (e.g. assembly, union, entity_1)');
            }

            if (! Schema::hasColumn('project_approvals', 'phase')) {
                $table->string('phase', 50)->nullable()->after('drop')->comment('Sub-stage phase: technical_review, financial_review, stage_approval');
            }

            if (! Schema::hasColumn('project_approvals', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('status')->comment('Whether this step is currently the single active actionable step');
            }

            if (! Schema::hasColumn('project_approvals', 'return_target')) {
                $table->string('return_target', 50)->nullable()->after('returned_from_stage')->comment('Target of rollback: creator_entity or previous_step');
            }

            if (! Schema::hasColumn('project_approvals', 'returned_to_step_order')) {
                $table->unsignedInteger('returned_to_step_order')->nullable()->after('return_target')->comment('Step order to which the project was returned');
            }

            // Index additions for query efficiency
            $table->index(['project_id', 'step_order'], 'idx_proj_appr_project_step');
            $table->index(['project_id', 'is_active'], 'idx_proj_appr_project_active');
            $table->index(['project_id', 'phase'], 'idx_proj_appr_project_phase');
            $table->index(['project_id', 'entity_id'], 'idx_proj_appr_project_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropIndex('idx_proj_appr_project_step');
            $table->dropIndex('idx_proj_appr_project_active');
            $table->dropIndex('idx_proj_appr_project_phase');
            $table->dropIndex('idx_proj_appr_project_entity');

            $columnsToDrop = [];
            if (Schema::hasColumn('project_approvals', 'phase')) {
                $columnsToDrop[] = 'phase';
            }
            if (Schema::hasColumn('project_approvals', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (Schema::hasColumn('project_approvals', 'return_target')) {
                $columnsToDrop[] = 'return_target';
            }
            if (Schema::hasColumn('project_approvals', 'returned_to_step_order')) {
                $columnsToDrop[] = 'returned_to_step_order';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
