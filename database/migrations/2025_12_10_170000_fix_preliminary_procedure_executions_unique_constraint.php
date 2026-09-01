<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec')) {
                    $table->dropUnique('uq_prelim_proc_exec');
                }
            });

            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('preliminary_procedure_executions', 'sequence')) {
                    $table->integer('sequence')->default(1)->after('preliminary_procedure_id');
                }
                if (! Schema::hasColumn('preliminary_procedure_executions', 'completion_percentage')) {
                    $table->decimal('completion_percentage', 5, 2)->default(0)->after('status');
                }
                if (! Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec')) {
                    $table->unique(['project_id', 'preliminary_procedure_id', 'sequence'], 'uq_prelim_proc_exec');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (Schema::hasIndex('preliminary_procedure_executions', 'uq_prelim_proc_exec')) {
                    $table->dropUnique('uq_prelim_proc_exec');
                }
                $table->unique(['project_id', 'preliminary_procedure_id'], 'uq_prelim_proc_exec');
            });
        }
    }
};
