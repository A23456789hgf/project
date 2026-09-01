<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill creator_entity_id from created_by_entity text field.
 * Matches the text value of created_by_entity to internal_entities.name.
 */
return new class extends Migration
{
    public function up(): void
    {
        $projects = DB::table('projects')
            ->whereNull('creator_entity_id')
            ->whereNotNull('created_by_entity')
            ->where('created_by_entity', '!=', '')
            ->get(['id', 'created_by_entity']);

        foreach ($projects as $project) {
            $name = trim($project->created_by_entity);
            if (empty($name)) {
                continue;
            }

            $entity = DB::table('internal_entities')
                ->where('name', $name)
                ->first(['id']);

            if ($entity) {
                DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['creator_entity_id' => $entity->id]);
            }
        }
    }

    public function down(): void
    {
        // Cannot safely reverse a data backfill
    }
};
