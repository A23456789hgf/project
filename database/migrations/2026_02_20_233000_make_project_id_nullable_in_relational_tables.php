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
        $tables = [
            'project_details',
            'project_locations',
            'main_objectives',
            'special_objectives',
            'objective_results',
            'result_outputs',
            'project_costs',
            'project_financings',
            'project_risks',
            'project_supervising_authorities',
            'implementing_entities',
            'participating_entities',
            'beneficiary_entities',
            'preliminary_activities',
            'preliminary_procedures',
            'preliminary_costs',
            'preliminary_financial_summaries',
            'executive_activities',
            'executive_activity_actions',
            'executive_action_assigneds',
            'executive_action_costs',
            'executive_financial_summaries',
            'beneficiary_group_project',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableObj) use ($table) {
                    // 1. Make project_id nullable
                    if (Schema::hasColumn($table, 'project_id')) {
                        $tableObj->unsignedBigInteger('project_id')->nullable()->change();
                    }

                    // 2. Add project_request_id if it doesn't exist
                    if (! Schema::hasColumn($table, 'project_request_id')) {
                        $afterColumn = Schema::hasColumn($table, 'project_id') ? 'project_id' : 'id';
                        $tableObj->unsignedBigInteger('project_request_id')->nullable()->after($afterColumn);
                        $tableObj->index('project_request_id');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'project_details',
            'project_locations',
            'main_objectives',
            'special_objectives',
            'objective_results',
            'result_outputs',
            'project_costs',
            'project_financings',
            'project_risks',
            'project_supervising_authorities',
            'implementing_entities',
            'participating_entities',
            'beneficiary_entities',
            'preliminary_activities',
            'preliminary_procedures',
            'preliminary_costs',
            'preliminary_financial_summaries',
            'executive_activities',
            'executive_activity_actions',
            'executive_action_assigneds',
            'executive_action_costs',
            'executive_financial_summaries',
            'beneficiary_group_project',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableObj) use ($table) {
                    if (Schema::hasColumn($table, 'project_id')) {
                        $tableObj->unsignedBigInteger('project_id')->nullable(false)->change();
                    }
                    // We don't necessarily remove project_request_id in down unless we want to be destructive
                    // But for consistency with usual behavior, we leave it if added by previous migrations too.
                });
            }
        }
    }
};
