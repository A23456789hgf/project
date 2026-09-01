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
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            // First, drop the foreign key constraint by name
            // Get the constraint name dynamically
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'project_supervising_authorities' AND COLUMN_NAME = 'authority_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (! empty($constraints)) {
                $constraintName = $constraints[0]->CONSTRAINT_NAME;
                $table->dropForeign($constraintName);
            }
        });

        // Modify the column type from integer to string
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            $table->string('authority_id', 191)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            // This won't work perfectly in reverse since we need to ensure data is still valid
            // For safety, just change back the column type
            $table->bigInteger('authority_id')->unsigned()->change();
        });
    }
};
