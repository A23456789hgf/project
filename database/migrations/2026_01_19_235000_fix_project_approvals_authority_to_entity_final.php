<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if authority_id column exists - if so, we need to handle it
        if (Schema::hasColumn('project_approvals', 'authority_id')) {

            // Step 1: Add entity_id column if it doesn't exist
            if (! Schema::hasColumn('project_approvals', 'entity_id')) {
                Schema::table('project_approvals', function (Blueprint $table) {
                    $table->unsignedBigInteger('entity_id')->nullable()->after('project_id');
                });
            }

            // Step 2: Migrate data from authority_id to entity_id
            // For numeric authority_id values, use them as entity_id
            DB::statement('UPDATE project_approvals SET entity_id = CAST(authority_id AS UNSIGNED) WHERE entity_id IS NULL AND authority_id REGEXP "^[0-9]+$"');

            // Step 3: For any remaining NULL entity_id values, try to find matching entity
            $approvalsWithNullEntity = DB::table('project_approvals')
                ->whereNull('entity_id')
                ->get();

            foreach ($approvalsWithNullEntity as $approval) {
                // Try to find entity by name
                $entity = DB::table('internal_entities')
                    ->where('name', 'like', '%'.$approval->authority_id.'%')
                    ->first();

                if ($entity) {
                    DB::table('project_approvals')
                        ->where('id', $approval->id)
                        ->update(['entity_id' => $entity->id]);
                }
            }

            // Step 4: For any STILL NULL entity_id, use a default entity (fallback)
            // Get the first internal entity as fallback
            $defaultEntity = DB::table('internal_entities')->first();
            if ($defaultEntity) {
                DB::table('project_approvals')
                    ->whereNull('entity_id')
                    ->update(['entity_id' => $defaultEntity->id]);
            }

            // Step 5: Drop the foreign key constraint on authority_id
            try {
                Schema::table('project_approvals', function (Blueprint $table) {
                    $table->dropForeign(['authority_id']);
                });
            } catch (Exception $e) {
                // Constraint might not exist
            }

            // Step 6: Drop the authority_id column
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->dropColumn('authority_id');
            });
        }

        // Step 7: Ensure entity_id is NOT NULL and add proper foreign key
        Schema::table('project_approvals', function (Blueprint $table) {
            // Check if entity_id already has a foreign key, if not add it
            // First ensure the column is not null
            if (Schema::hasColumn('project_approvals', 'entity_id')) {
                DB::statement('ALTER TABLE project_approvals MODIFY entity_id BIGINT UNSIGNED NOT NULL');
            }
        });

        // Step 8: Add foreign key constraint
        try {
            // First check if a foreign key already exists (optional, but try-catch is usually enough)
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->foreign('entity_id')
                    ->references('id')
                    ->on('internal_entities')
                    ->onDelete('cascade');
            });
        } catch (Exception $e) {
            // Foreign key might already exist, which is fine
            // We'll log it as info instead of warning to avoid cluttering logs
            Log::info('Migration note: Foreign key on entity_id already exists or was handled elsewhere.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback is not practical for this migration
        // as we're removing a column permanently
    }
};
