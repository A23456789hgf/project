<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The additional tables to add creator tracking to.
     */
    protected $tables = [
        'users',
        'roles',
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

            // Backfill
            $userIdColumn = Schema::hasColumn($tableName, 'created_by') ? 'created_by' : null;
            if ($userIdColumn) {
                DB::table($tableName)
                    ->join('users as creators', "{$tableName}.{$userIdColumn}", '=', 'creators.id')
                    ->whereNull("{$tableName}.creator_username")
                    ->update([
                        "{$tableName}.creator_username" => DB::raw('creators.user_id'),
                        "{$tableName}.creator_entity_id" => DB::raw('creators.entity_id'),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'creator_entity_id')) {
                    $table->dropForeign(['creator_entity_id']);
                    $table->dropColumn(['creator_username', 'creator_entity_id']);
                }
            });
        }
    }
};
