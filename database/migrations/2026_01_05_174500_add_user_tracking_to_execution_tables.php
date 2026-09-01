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
        // Add user tracking to preliminary_procedure_executions
        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('preliminary_procedure_executions', 'created_by_user_id')) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('id');
                    $table->foreign('created_by_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                    $table->index('created_by_user_id');
                }

                if (! Schema::hasColumn('preliminary_procedure_executions', 'entity')) {
                    $table->string('entity')->nullable()->after('created_by_user_id');
                }
            });
        }

        // Add user tracking to executive_action_executions if it exists
        if (Schema::hasTable('executive_action_executions')) {
            Schema::table('executive_action_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('executive_action_executions', 'created_by_user_id')) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('id');
                    $table->foreign('created_by_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                    $table->index('created_by_user_id');
                }

                if (! Schema::hasColumn('executive_action_executions', 'entity')) {
                    $table->string('entity')->nullable()->after('created_by_user_id');
                }
            });
        }

        // Add user tracking to project_executions if it exists
        if (Schema::hasTable('project_executions')) {
            Schema::table('project_executions', function (Blueprint $table) {
                if (! Schema::hasColumn('project_executions', 'created_by_user_id')) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('id');
                    $table->foreign('created_by_user_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                    $table->index('created_by_user_id');
                }

                if (! Schema::hasColumn('project_executions', 'entity')) {
                    $table->string('entity')->nullable()->after('created_by_user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('preliminary_procedure_executions')) {
            Schema::table('preliminary_procedure_executions', function (Blueprint $table) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn(['created_by_user_id', 'entity']);
            });
        }

        if (Schema::hasTable('executive_action_executions')) {
            Schema::table('executive_action_executions', function (Blueprint $table) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn(['created_by_user_id', 'entity']);
            });
        }

        if (Schema::hasTable('project_executions')) {
            Schema::table('project_executions', function (Blueprint $table) {
                $table->dropForeign(['created_by_user_id']);
                $table->dropColumn(['created_by_user_id', 'entity']);
            });
        }
    }
};
