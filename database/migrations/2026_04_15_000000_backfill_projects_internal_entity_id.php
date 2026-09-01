<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill projects.internal_entity_id from the creator user's entity_id.
 *
 * This is required for DomainScope to work correctly — it JOIN-s projects
 * with internal_entities on internal_entity_id. Any project with NULL
 * internal_entity_id will be invisible to entity-scoped users.
 *
 * Logic:
 *  1. For projects where internal_entity_id IS NULL and created_by_user_id IS NOT NULL:
 *     → set internal_entity_id = users.entity_id (where users.id = projects.created_by_user_id)
 *
 *  2. Fallback for projects that have creator_entity_id (separate FK):
 *     → set internal_entity_id = creator_entity_id
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Step 1: Backfill from creator user's entity_id (MySQL JOIN UPDATE)
            DB::statement('
                UPDATE projects p
                INNER JOIN users u ON u.id = p.created_by_user_id
                SET p.internal_entity_id = u.entity_id
                WHERE p.internal_entity_id IS NULL
                  AND u.entity_id IS NOT NULL
            ');

            // Step 2: Backfill from creator_entity_id as fallback
            DB::statement('
                UPDATE projects p
                SET p.internal_entity_id = p.creator_entity_id
                WHERE p.internal_entity_id IS NULL
                  AND p.creator_entity_id IS NOT NULL
            ');
        } else {
            // SQLite / other: use correlated subquery instead of JOIN UPDATE
            DB::statement('
                UPDATE projects
                SET internal_entity_id = (
                    SELECT entity_id FROM users WHERE users.id = projects.created_by_user_id LIMIT 1
                )
                WHERE internal_entity_id IS NULL
                  AND created_by_user_id IS NOT NULL
                  AND (SELECT entity_id FROM users WHERE users.id = projects.created_by_user_id LIMIT 1) IS NOT NULL
            ');

            DB::statement('
                UPDATE projects
                SET internal_entity_id = creator_entity_id
                WHERE internal_entity_id IS NULL
                  AND creator_entity_id IS NOT NULL
            ');
        }
    }

    public function down(): void
    {
        // Cannot safely reverse a data backfill — leave data as-is
    }
};
