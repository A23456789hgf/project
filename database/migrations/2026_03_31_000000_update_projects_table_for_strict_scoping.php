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
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'internal_entity_id')) {
                $table->unsignedBigInteger('internal_entity_id')->nullable()->after('created_by_user_id');
                $table->index('internal_entity_id');
            }
            if (! Schema::hasColumn('projects', 'authority_id')) {
                $table->unsignedBigInteger('authority_id')->nullable()->after('internal_entity_id');
                $table->index('authority_id');
            }
            if (! Schema::hasColumn('projects', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('authority_id');
                $table->index('created_by');
            }
        });

        // Optional: Sync existing data from created_by_user_id if possible
        DB::statement('UPDATE projects SET created_by = created_by_user_id WHERE created_by IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['internal_entity_id', 'authority_id', 'created_by']);
        });
    }
};
