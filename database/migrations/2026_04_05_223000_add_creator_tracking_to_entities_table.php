<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tables to add creator tracking to.
     */
    protected $tables = [
        'projects',
        'programs',
        'domains',
        'subdomains',
        'interventions',
        'correspondences',
        'plans',
        'project_requests',
        'empowerment_projects',
        'internal_entities',
        'authorities',
        'plan_projects',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'creator_username')) {
                    $table->string('creator_username')->nullable()->after('created_at');
                }

                if (! Schema::hasColumn($tableName, 'creator_entity_id')) {
                    $table->unsignedBigInteger('creator_entity_id')->nullable()->after('creator_username');
                    $table->foreign('creator_entity_id')->references('id')->on('internal_entities')->onDelete('set null');
                }
            });

            // Attempt to backfill data from existing user tracking columns
            $this->backfillData($tableName);
        }
    }

    /**
     * Attempt to backfill data from existing user tracking columns.
     */
    protected function backfillData($tableName): void
    {
        // Identify potential user ID columns
        $userIdColumn = null;
        if (Schema::hasColumn($tableName, 'created_by_user_id')) {
            $userIdColumn = 'created_by_user_id';
        } elseif (Schema::hasColumn($tableName, 'created_by')) {
            // Check if 'created_by' is numeric (user ID) or name
            $userIdColumn = 'created_by';
        }

        if ($userIdColumn) {
            DB::table($tableName)
                ->join('users', "{$tableName}.{$userIdColumn}", '=', 'users.id')
                ->whereNull("{$tableName}.creator_username")
                ->update([
                    "{$tableName}.creator_username" => DB::raw('users.user_id'),
                    "{$tableName}.creator_entity_id" => DB::raw('users.entity_id'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'creator_entity_id')) {
                    $table->dropForeign(['creator_entity_id']);
                    $table->dropColumn('creator_entity_id');
                }

                if (Schema::hasColumn($tableName, 'creator_username')) {
                    // Only drop if we added it (some tables like 'plans' might have had it)
                    // But we checked for existence in up(), so dropping might be risky.
                    // For safety, we only drop if it's not a 'legacy' column we want to keep.
                    $table->dropColumn('creator_username');
                }
            });
        }
    }
};
