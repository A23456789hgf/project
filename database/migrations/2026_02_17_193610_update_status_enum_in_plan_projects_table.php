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
        Schema::table('plan_projects', function (Blueprint $table) {
            // Modify enum to include 'terminated' and 'completed'
            // DB::statement("ALTER TABLE plan_projects MODIFY COLUMN status ENUM('new', 'in_progress', 'completed', 'terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new'");
            // Portable way if possible, or raw statement for MySQL
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE plan_projects MODIFY COLUMN status ENUM('new', 'in_progress', 'completed', 'terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_projects', function (Blueprint $table) {
            // Revert back to original
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // This might fail if there are 'terminated' or 'completed' values, so we should be careful or accept it might not rollback cleanly without data loss
            DB::statement("ALTER TABLE plan_projects MODIFY COLUMN status ENUM('new', 'in_progress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new'");
        }
    }
};
