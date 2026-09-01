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
            'governorates',
            'directorates',
            'project_movement_logs',
            'project_activity_history',
            'plan_projects',
            'plan_project_activities',
            'plan_project_activity_actions',
            'executive_activities',
            'executive_activity_actions',
            'audit_logs',
            'plan_project_activity',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (! Schema::hasColumn($tableName, 'geographic_scope_id')) {
                        $table->unsignedBigInteger('geographic_scope_id')->nullable()->index();
                    }
                    if (! Schema::hasColumn($tableName, 'administrative_scope_id')) {
                        $table->unsignedBigInteger('administrative_scope_id')->nullable()->index();
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
            'governorates',
            'directorates',
            'project_movement_logs',
            'project_activity_history',
            'plan_projects',
            'plan_project_activities',
            'plan_project_activity_actions',
            'executive_activities',
            'executive_activity_actions',
            'audit_logs',
            'plan_project_activity',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $cols = [];
                    if (Schema::hasColumn($tableName, 'geographic_scope_id')) {
                        $cols[] = 'geographic_scope_id';
                    }
                    if (Schema::hasColumn($tableName, 'administrative_scope_id')) {
                        $cols[] = 'administrative_scope_id';
                    }

                    if (! empty($cols)) {
                        $table->dropColumn($cols);
                    }
                });
            }
        }
    }
};
