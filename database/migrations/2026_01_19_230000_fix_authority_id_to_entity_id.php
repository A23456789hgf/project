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
        // Step 1: Add entity_id column if it doesn't exist (nullable first)
        if (! Schema::hasColumn('project_approvals', 'entity_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('project_id');
            });
        }

        // Step 2: Migrate data from authority_id to entity_id if authority_id exists
        if (Schema::hasColumn('project_approvals', 'authority_id')) {
            // Copy authority_id values to entity_id where entity_id is null
            DB::statement('UPDATE project_approvals SET entity_id = authority_id WHERE entity_id IS NULL');

            // Step 3: Drop the foreign key constraint on authority_id
            try {
                Schema::table('project_approvals', function (Blueprint $table) {
                    $table->dropForeign(['authority_id']);
                });
            } catch (Throwable $e) {
            }

            // Step 4: Drop the authority_id column
            try {
                Schema::table('project_approvals', function (Blueprint $table) {
                    $table->dropColumn('authority_id');
                });
            } catch (Throwable $e) {
            }
        }

        // Step 5: Make entity_id NOT NULL and add foreign key
        Schema::table('project_approvals', function (Blueprint $table) {
            // First make it NOT NULL
            DB::statement('ALTER TABLE project_approvals MODIFY entity_id BIGINT UNSIGNED NOT NULL');

            // Add foreign key constraint to internal_entities
            $table->foreign('entity_id')->references('id')->on('internal_entities')->onDelete('cascade');
        });

        // Step 6: Drop old index if it exists and create new one
        try {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->dropIndex(['project_id', 'authority_id']);
            });
        } catch (Exception $e) {
            // Index might not exist, that's okay
        }

        Schema::table('project_approvals', function (Blueprint $table) {
            $table->index(['project_id', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add authority_id back if not present
        if (! Schema::hasColumn('project_approvals', 'authority_id')) {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->unsignedBigInteger('authority_id')->nullable()->after('project_id');
            });
        }

        // Copy entity_id back to authority_id
        DB::statement('UPDATE project_approvals SET authority_id = entity_id WHERE authority_id IS NULL');

        // Drop entity_id foreign key
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropForeign(['entity_id']);
            $table->dropColumn('entity_id');
        });

        // Make authority_id NOT NULL and add foreign key
        DB::statement('ALTER TABLE project_approvals MODIFY authority_id BIGINT UNSIGNED NOT NULL');

        Schema::table('project_approvals', function (Blueprint $table) {
            $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');
        });

        // Restore old index
        try {
            Schema::table('project_approvals', function (Blueprint $table) {
                $table->dropIndex(['project_id', 'entity_id']);
            });
        } catch (Exception $e) {
            // Index might not exist
        }

        Schema::table('project_approvals', function (Blueprint $table) {
            $table->index(['project_id', 'authority_id']);
        });
    }
};
