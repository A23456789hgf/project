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
        // First, drop the foreign key constraint if it exists
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'project_supervising_authorities' AND COLUMN_NAME = 'authority_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (! empty($constraints)) {
                $constraintName = $constraints[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE project_supervising_authorities DROP FOREIGN KEY `{$constraintName}`");
            }
        });

        // Modify the column type from bigint to varchar
        DB::statement('ALTER TABLE project_supervising_authorities MODIFY COLUMN authority_id VARCHAR(191) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE project_supervising_authorities MODIFY COLUMN authority_id BIGINT(20) UNSIGNED NULL');
    }
};
