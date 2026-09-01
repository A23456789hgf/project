<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'project_supervising_authorities' => 'authority_type',
            'implementing_entities' => 'entity_type',
            'participating_entities' => 'entity_type',
            'beneficiary_entities' => 'entity_type',
        ];

        foreach ($tables as $tableName => $typeCol) {
            Log::info("Starting migration refinement for table: $tableName");

            // 1. Drop ALL existing Foreign Keys on these columns (Raw SQL - FIRST)
            try {
                $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$tableName' AND COLUMN_NAME IN ('authority_id', 'internal_entity_id') AND REFERENCED_TABLE_NAME IS NOT NULL");
                foreach ($constraints as $constraint) {
                    DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                }
            } catch (Exception $e) {
            }

            // 2. TIMESTAMPS SANITIZATION (Fixes PDOException 22007: Invalid datetime)
            try {
                DB::statement("UPDATE `$tableName` SET `created_at` = NOW() WHERE `created_at` = '0000-00-00 00:00:00' OR `created_at` IS NULL OR `created_at` < '1970-01-01 00:00:01'");
                DB::statement("UPDATE `$tableName` SET `updated_at` = NOW() WHERE `updated_at` = '0000-00-00 00:00:00' OR `updated_at` IS NULL OR `updated_at` < '1970-01-01 00:00:01'");
            } catch (Exception $e) {
            }

            // 3. Precise Data Sanitization for IDs
            try {
                // Nullify non-numeric authority_id
                DB::statement("UPDATE `$tableName` SET `authority_id` = NULL WHERE `authority_id` IS NOT NULL AND `authority_id` NOT REGEXP '^[0-9]+$'");

                // Nullify authority_id if it's internal
                DB::statement("UPDATE `$tableName` SET `authority_id` = NULL WHERE `$typeCol` = 'internal'");

                // Nullify authority_id if it doesn't exist in authorities table
                DB::statement("UPDATE `$tableName` SET `authority_id` = NULL WHERE `authority_id` IS NOT NULL AND `authority_id` NOT IN (SELECT CAST(`id` AS CHAR) FROM `authorities`)");

                // Nullify internal_entity_id if it doesn't exist in internal_entities table
                if (Schema::hasColumn($tableName, 'internal_entity_id')) {
                    DB::statement("UPDATE `$tableName` SET `internal_entity_id` = NULL WHERE `internal_entity_id` IS NOT NULL AND `internal_entity_id` NOT IN (SELECT `id` FROM `internal_entities`)");
                }
            } catch (Exception $e) {
            }

            // 4. Modify column types (Raw SQL - bypasses change() issues)
            try {
                DB::statement("ALTER TABLE `$tableName` MODIFY `authority_id` BIGINT UNSIGNED NULL");
                if (Schema::hasColumn($tableName, 'internal_entity_id')) {
                    DB::statement("ALTER TABLE `$tableName` MODIFY `internal_entity_id` BIGINT UNSIGNED NULL");
                }
            } catch (Exception $e) {
            }

            // 5. Add Constraints with fresh names
            try {
                DB::statement("ALTER TABLE `$tableName` ADD CONSTRAINT `{$tableName}_auth_id_fk_fix` FOREIGN KEY (`authority_id`) REFERENCES `authorities` (`id`) ON DELETE CASCADE");
                if (Schema::hasColumn($tableName, 'internal_entity_id')) {
                    DB::statement("ALTER TABLE `$tableName` ADD CONSTRAINT `{$tableName}_int_ent_id_fk_fix` FOREIGN KEY (`internal_entity_id`) REFERENCES `internal_entities` (`id`) ON DELETE CASCADE");
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'project_supervising_authorities',
            'implementing_entities',
            'participating_entities',
            'beneficiary_entities',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                try {
                    $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$tableName' AND COLUMN_NAME IN ('authority_id', 'internal_entity_id') AND REFERENCED_TABLE_NAME IS NOT NULL");
                    foreach ($constraints as $constraint) {
                        DB::statement("ALTER TABLE `$tableName` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                    }
                } catch (Exception $e) {
                }

                $table->string('authority_id')->nullable()->change();
            });
        }
    }
};
