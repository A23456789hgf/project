<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'project_supervising_authorities',
            'implementing_entities',
            'participating_entities',
            'beneficiary_entities',
        ];

        foreach ($tables as $tableName) {
            try {
                // Find and drop the foreign key on parent_id
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = '$tableName' 
                    AND COLUMN_NAME = 'parent_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL");

                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                }
            } catch (Exception $e) {
                // Probably no foreign key to drop
            }

            // Ensure parent_id is nullable BIGINT
            try {
                DB::statement("ALTER TABLE `$tableName` MODIFY `parent_id` BIGINT UNSIGNED NULL");
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
