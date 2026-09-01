<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to project_requests table needed for the full wizard.
     */
    public function up(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            // Ensure request_number uses the correct QPRO format (rename if old format exists)
            // Add additional basic info fields needed by the wizard
            if (! Schema::hasColumn('project_requests', 'main_directives')) {
                $table->text('main_directives')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'subdirectives')) {
                $table->text('subdirectives')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'priority_id')) {
                $table->unsignedBigInteger('priority_id')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'target_category_id')) {
                $table->unsignedBigInteger('target_category_id')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable();
            }
            if (! Schema::hasColumn('project_requests', 'rejected_by_user_id')) {
                $table->unsignedBigInteger('rejected_by_user_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requests', function (Blueprint $table) {
            $columns = ['main_directives', 'subdirectives', 'priority_id', 'target_category_id', 'rejection_notes', 'rejected_by_user_id'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('project_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
