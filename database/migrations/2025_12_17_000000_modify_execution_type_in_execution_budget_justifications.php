<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid Doctrine/DBAL's enum issue
        // We also check if table exists to be safe
        if (Schema::hasTable('execution_budget_justifications')) {
            DB::statement('ALTER TABLE execution_budget_justifications MODIFY execution_type VARCHAR(191) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('execution_budget_justifications')) {
            // Revert back if needed, but be careful with data loss if values aren't in enum
            DB::statement("ALTER TABLE execution_budget_justifications MODIFY execution_type ENUM('preliminary', 'executive') NOT NULL");
        }
    }
};
