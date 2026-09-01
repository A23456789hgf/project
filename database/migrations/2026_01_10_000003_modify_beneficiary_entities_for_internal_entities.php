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
        Schema::table('beneficiary_entities', function (Blueprint $table) {
            // First, drop the foreign key constraint by name
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'beneficiary_entities' AND COLUMN_NAME = 'authority_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (! empty($constraints)) {
                $constraintName = $constraints[0]->CONSTRAINT_NAME;
                $table->dropForeign($constraintName);
            }
        });

        // Modify the column type from integer to string
        Schema::table('beneficiary_entities', function (Blueprint $table) {
            $table->string('authority_id', 191)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_entities', function (Blueprint $table) {
            $table->bigInteger('authority_id')->unsigned()->change();
        });
    }
};
