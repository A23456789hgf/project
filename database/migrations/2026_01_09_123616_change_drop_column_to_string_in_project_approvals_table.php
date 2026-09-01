<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('project_approvals', 'drop')) {
            DB::statement('ALTER TABLE project_approvals MODIFY `drop` VARCHAR(50) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('project_approvals', 'drop')) {
            DB::statement("ALTER TABLE project_approvals MODIFY `drop` ENUM('assembly', 'union', 'committee', 'implementation') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
        }
    }
};
