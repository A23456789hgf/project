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
        // Change status column from ENUM to VARCHAR to support all approval statuses
        DB::statement("ALTER TABLE `project_approvals` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (only if needed for rollback)
        DB::statement("ALTER TABLE `project_approvals` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'needs_revision', 'on_hold') NOT NULL DEFAULT 'pending'");
    }
};
