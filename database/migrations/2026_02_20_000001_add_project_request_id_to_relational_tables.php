<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add project_request_id to all relational project tables so data
     * can be stored against a ProjectRequest before it becomes an official Project.
     */
    public function up(): void
    {
        $tables = [
            'project_details',
            'project_locations',
            'main_objectives',
            'special_objectives',
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
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'project_request_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('project_request_id')->nullable()->after('project_id');
                    $table->index('project_request_id');
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
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'project_request_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropIndex(['project_request_id']);
                    $table->dropColumn('project_request_id');
                });
            }
        }
    }
};
