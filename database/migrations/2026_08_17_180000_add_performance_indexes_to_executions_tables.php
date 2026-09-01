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
        Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
            $table->index('project_id', 'idx_prelim_exec_project_id');
            $table->index(['project_id', 'approval_status'], 'idx_prelim_exec_proj_appr');
        });

        Schema::table('project_executions', function (Blueprint $table) {
            $table->index('project_id', 'idx_proj_exec_project_id');
            $table->index(['project_id', 'approval_status'], 'idx_proj_exec_proj_appr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
            $table->dropIndex('idx_prelim_exec_project_id');
            $table->dropIndex('idx_prelim_exec_proj_appr');
        });

        Schema::table('project_executions', function (Blueprint $table) {
            $table->dropIndex('idx_proj_exec_project_id');
            $table->dropIndex('idx_proj_exec_proj_appr');
        });
    }
};
