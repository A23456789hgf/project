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
        // For any records where entity_id is NULL, try to populate it from authority_id
        // If authority_id is numeric, use it directly
        // If authority_id is text, try to find matching entity by name or use a default fallback

        $approvals = DB::table('project_approvals')
            ->whereNull('entity_id')
            ->where('authority_id', '!=', null)
            ->get();

        foreach ($approvals as $approval) {
            $entityId = null;

            // If authority_id is numeric, use it directly
            if (is_numeric($approval->authority_id)) {
                $entityId = (int) $approval->authority_id;
            } else {
                // Try to find entity by name (if authority_id is a name)
                $entity = DB::table('internal_entities')
                    ->where('name', $approval->authority_id)
                    ->first();
                $entityId = $entity ? $entity->id : null;
            }

            // Only update if we found a valid entity_id
            if ($entityId) {
                DB::table('project_approvals')
                    ->where('id', $approval->id)
                    ->update(['entity_id' => $entityId]);
            }
        }

        // Now remove authority_id column (it's no longer needed)
        Schema::table('project_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('project_approvals', 'authority_id')) {
                $table->dropColumn('authority_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            //
        });
    }
};
