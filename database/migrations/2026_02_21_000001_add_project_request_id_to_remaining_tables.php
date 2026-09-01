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
            'transactions',
            'project_approvals',
            'project_approval_requests',
            'project_movement_logs',
            'project_documents',
            'project_quality_records',
            'project_activity_history',
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
            'transactions',
            'project_approvals',
            'project_approval_requests',
            'project_movement_logs',
            'project_documents',
            'project_quality_records',
            'project_activity_history',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableObj) use ($table) {
                    if (Schema::hasColumn($table, 'project_id')) {
                        $tableObj->unsignedBigInteger('project_id')->nullable(false)->change();
                    }
                    if (Schema::hasColumn($table, 'project_request_id')) {
                        $tableObj->dropIndex(['project_request_id']);
                        $tableObj->dropColumn('project_request_id');
                    }
                });
            }
        }
    }
};
