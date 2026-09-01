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
        Schema::table('projects', function (Blueprint $table) {
            // Add erpnext related columns if they don't exist
            if (! Schema::hasColumn('projects', 'erpnext_project_id')) {
                $table->string('erpnext_project_id')->nullable()->after('frappe_project_id');
            }
            if (! Schema::hasColumn('projects', 'sync_status')) {
                $table->string('sync_status')->nullable()->after('erpnext_project_id');
            }
            if (! Schema::hasColumn('projects', 'synced_to_erpnext_at')) {
                $table->dateTime('synced_to_erpnext_at')->nullable()->after('sync_status');
            }
            if (! Schema::hasColumn('projects', 'frappe_project_name')) {
                $table->string('frappe_project_name')->nullable()->after('synced_to_erpnext_at');
            }
            if (! Schema::hasColumn('projects', 'frappe_sync_status')) {
                $table->string('frappe_sync_status')->nullable()->after('frappe_project_name');
            }
            if (! Schema::hasColumn('projects', 'frappe_synced_at')) {
                // This might exist from previous migration but let's be safe
                if (! Schema::hasColumn('projects', 'frappe_synced_at')) {
                    $table->dateTime('frappe_synced_at')->nullable()->after('frappe_sync_status');
                }
            }
            if (! Schema::hasColumn('projects', 'sync_error')) {
                $table->text('sync_error')->nullable()->after('frappe_synced_at');
            }
            if (! Schema::hasColumn('projects', 'execution_started_at')) {
                $table->dateTime('execution_started_at')->nullable()->after('sync_error');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'erpnext_project_id',
                'sync_status',
                'synced_to_erpnext_at',
                'frappe_project_name',
                'frappe_sync_status',
                'frappe_synced_at',
                'sync_error',
                'execution_started_at',
            ]);
        });
    }
};
