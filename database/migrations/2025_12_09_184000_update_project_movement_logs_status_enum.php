<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE project_movement_logs MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'requires_action', 'initiated', 'auto_progressed', 'returned_for_revision')");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE project_movement_logs MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'requires_action')");
        }
    }
};
