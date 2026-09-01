<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE project_approvals MODIFY `drop` ENUM('assembly', 'union', 'committee', 'implementation') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE project_approvals MODIFY `drop` ENUM('assembly', 'union', 'committee') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL");
    }
};
