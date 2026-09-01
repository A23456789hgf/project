<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_executions')) {
            Schema::table('project_executions', function (Blueprint $table) {
                if (Schema::hasIndex('project_executions', 'uq_proj_exec')) {
                    $table->dropUnique('uq_proj_exec');
                }
            });

            Schema::table('project_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('project_executions', 'sequence')) {
                    $table->unsignedInteger('sequence')->default(1)->after('executive_activity_action_id');
                }

                if (! Schema::hasIndex('project_executions', 'uq_proj_exec_seq')) {
                    $table->unique(['project_id', 'executive_activity_action_id', 'sequence'], 'uq_proj_exec_seq');
                }
            });
        }

        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec')) {
                    $table->dropUnique('uq_prelim_proc_exec');
                }
            });

            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('preliminary_procedure_executions', 'sequence')) {
                    $table->unsignedInteger('sequence')->default(1)->after('preliminary_procedure_id');
                }

                if (! Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec_seq')) {
                    $table->unique(['project_id', 'preliminary_procedure_id', 'sequence'], 'uq_prelim_proc_exec_seq');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_executions')) {
            Schema::table('project_executions', function (Blueprint $table) {
                if (Schema::hasIndex('project_executions', 'uq_proj_exec_seq')) {
                    $table->dropUnique('uq_proj_exec_seq');
                }
                if (Schema::hasColumn('project_executions', 'sequence')) {
                    $table->dropColumn('sequence');
                }
            });
        }

        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec_seq')) {
                    $table->dropUnique('uq_prelim_proc_exec_seq');
                }
                if (Schema::hasColumn('preliminary_procedure_executions', 'sequence')) {
                    $table->dropColumn('sequence');
                }
            });
        }
    }
};
