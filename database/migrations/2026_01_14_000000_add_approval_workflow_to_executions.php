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
        // Add approval columns to project_executions (Executive Activities)
        if (Schema::hasTable('project_executions') && ! Schema::hasColumn('project_executions', 'approval_status')) {
            Schema::table('project_executions', function (Blueprint $table) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approval_status');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('rejection_reason')->nullable()->after('approved_at');
                $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('rejection_reason');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');

                // Index for efficient approval filtering
                $table->index('approval_status');
            });
        }

        // Add approval columns to preliminary_procedure_executions (Preliminary Activities)
        if (Schema::hasTable('preliminary_procedure_executions') && ! Schema::hasColumn('preliminary_procedure_executions', 'approval_status')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('notes');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('approval_status');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('rejection_reason')->nullable()->after('approved_at');
                $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('rejected_by');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');

                // Index for efficient approval filtering
                $table->index('approval_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('project_executions', 'approval_status')) {
            Schema::table('project_executions', function (Blueprint $table) {
                $table->dropIndex(['approval_status']);
                $table->dropForeign(['approved_by']);
                $table->dropForeign(['rejected_by']);
                $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'rejection_reason', 'rejected_by', 'rejected_at']);
            });
        }

        if (Schema::hasColumn('preliminary_procedure_executions', 'approval_status')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                $table->dropIndex(['approval_status']);
                $table->dropForeign(['approved_by']);
                $table->dropForeign(['rejected_by']);
                $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'rejection_reason', 'rejected_by', 'rejected_at']);
            });
        }
    }
};
