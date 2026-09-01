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
        // This migration cleans up the redundancy in project_approvals foreign keys
        // which was causing errno: 121 "Duplicate key on write or update"

        try {
            Schema::table('project_approvals', function (Blueprint $table) {
                // Drop the foreign key if it exists
                // We use an array to specify the column name
                $table->dropForeign(['entity_id']);
            });
        } catch (Exception $e) {
            // Foreign key might not exist, ignore
        }

        try {
            Schema::table('project_approvals', function (Blueprint $table) {
                // Re-add it correctly
                $table->foreign('entity_id')
                    ->references('id')
                    ->on('internal_entities')
                    ->onDelete('cascade');
            });
        } catch (Exception $e) {
            // If it still fails, it might be because it was already there (another name?)
            // But usually this succeeds after a drop
            Log::info('Cleanup migration handled project_approvals_entity_id_foreign');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse cleanup
    }
};
