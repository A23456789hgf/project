<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_executions') && ! Schema::hasColumn('project_executions', 'completion_percentage')) {
            Schema::table('project_executions', function (Blueprint $table) {
                $table->decimal('completion_percentage', 5, 2)->default(0)->after('status');
            });
        }

        if (Schema::hasTable('preliminary_procedure_executions') && ! Schema::hasColumn('preliminary_procedure_executions', 'completion_percentage')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                $table->decimal('completion_percentage', 5, 2)->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_executions') && Schema::hasColumn('project_executions', 'completion_percentage')) {
            Schema::table('project_executions', function (Blueprint $table) {
                $table->dropColumn('completion_percentage');
            });
        }

        if (Schema::hasTable('preliminary_procedure_executions') && Schema::hasColumn('preliminary_procedure_executions', 'completion_percentage')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                $table->dropColumn('completion_percentage');
            });
        }
    }
};
