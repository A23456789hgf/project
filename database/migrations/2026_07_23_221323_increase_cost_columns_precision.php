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
        // 1. executive_action_costs
        if (Schema::hasTable('executive_action_costs')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                if (Schema::hasColumn('executive_action_costs', 'amount')) {
                    DB::statement('ALTER TABLE `executive_action_costs` MODIFY COLUMN `amount` DECIMAL(24, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('executive_action_costs', 'total')) {
                    DB::statement('ALTER TABLE `executive_action_costs` MODIFY COLUMN `total` DECIMAL(24, 2) DEFAULT 0');
                }
            });
        }

        // 2. preliminary_costs
        if (Schema::hasTable('preliminary_costs')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                if (Schema::hasColumn('preliminary_costs', 'amount')) {
                    DB::statement('ALTER TABLE `preliminary_costs` MODIFY COLUMN `amount` DECIMAL(24, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('preliminary_costs', 'total')) {
                    DB::statement('ALTER TABLE `preliminary_costs` MODIFY COLUMN `total` DECIMAL(24, 2) DEFAULT 0');
                }
            });
        }

        // 3. executive_financial_summaries
        if (Schema::hasTable('executive_financial_summaries')) {
            Schema::table('executive_financial_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('executive_financial_summaries', 'amount')) {
                    DB::statement('ALTER TABLE `executive_financial_summaries` MODIFY COLUMN `amount` DECIMAL(24, 2) DEFAULT 0');
                }
            });
        }

        // 4. preliminary_financial_summaries
        if (Schema::hasTable('preliminary_financial_summaries')) {
            Schema::table('preliminary_financial_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('preliminary_financial_summaries', 'amount')) {
                    DB::statement('ALTER TABLE `preliminary_financial_summaries` MODIFY COLUMN `amount` DECIMAL(24, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('preliminary_financial_summaries', 'aggregated_total')) {
                    DB::statement('ALTER TABLE `preliminary_financial_summaries` MODIFY COLUMN `aggregated_total` DECIMAL(24, 2) DEFAULT 0');
                }
            });
        }

        // 5. project_costs
        if (Schema::hasTable('project_costs')) {
            Schema::table('project_costs', function (Blueprint $table) {
                if (Schema::hasColumn('project_costs', 'total_cost')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `total_cost` DECIMAL(24, 2)');
                }
                if (Schema::hasColumn('project_costs', 'spent_amount')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `spent_amount` DECIMAL(24, 2)');
                }
                if (Schema::hasColumn('project_costs', 'remaining_amount')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `remaining_amount` DECIMAL(24, 2)');
                }
            });
        }

        // 6. project_financings
        if (Schema::hasTable('project_financings')) {
            Schema::table('project_financings', function (Blueprint $table) {
                if (Schema::hasColumn('project_financings', 'financing_amount')) {
                    DB::statement('ALTER TABLE `project_financings` MODIFY COLUMN `financing_amount` DECIMAL(24, 2) DEFAULT 0');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. executive_action_costs
        if (Schema::hasTable('executive_action_costs')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                if (Schema::hasColumn('executive_action_costs', 'amount')) {
                    DB::statement('ALTER TABLE `executive_action_costs` MODIFY COLUMN `amount` DECIMAL(15, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('executive_action_costs', 'total')) {
                    DB::statement('ALTER TABLE `executive_action_costs` MODIFY COLUMN `total` DECIMAL(15, 2) DEFAULT 0');
                }
            });
        }

        // 2. preliminary_costs
        if (Schema::hasTable('preliminary_costs')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                if (Schema::hasColumn('preliminary_costs', 'amount')) {
                    DB::statement('ALTER TABLE `preliminary_costs` MODIFY COLUMN `amount` DECIMAL(15, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('preliminary_costs', 'total')) {
                    DB::statement('ALTER TABLE `preliminary_costs` MODIFY COLUMN `total` DECIMAL(15, 2) DEFAULT 0');
                }
            });
        }

        // 3. executive_financial_summaries
        if (Schema::hasTable('executive_financial_summaries')) {
            Schema::table('executive_financial_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('executive_financial_summaries', 'amount')) {
                    DB::statement('ALTER TABLE `executive_financial_summaries` MODIFY COLUMN `amount` DECIMAL(15, 2) DEFAULT 0');
                }
            });
        }

        // 4. preliminary_financial_summaries
        if (Schema::hasTable('preliminary_financial_summaries')) {
            Schema::table('preliminary_financial_summaries', function (Blueprint $table) {
                if (Schema::hasColumn('preliminary_financial_summaries', 'amount')) {
                    DB::statement('ALTER TABLE `preliminary_financial_summaries` MODIFY COLUMN `amount` DECIMAL(15, 2) DEFAULT 0');
                }
                if (Schema::hasColumn('preliminary_financial_summaries', 'aggregated_total')) {
                    DB::statement('ALTER TABLE `preliminary_financial_summaries` MODIFY COLUMN `aggregated_total` DECIMAL(15, 2) DEFAULT 0');
                }
            });
        }

        // 5. project_costs
        if (Schema::hasTable('project_costs')) {
            Schema::table('project_costs', function (Blueprint $table) {
                if (Schema::hasColumn('project_costs', 'total_cost')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `total_cost` DECIMAL(15, 2)');
                }
                if (Schema::hasColumn('project_costs', 'spent_amount')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `spent_amount` DECIMAL(15, 2)');
                }
                if (Schema::hasColumn('project_costs', 'remaining_amount')) {
                    DB::statement('ALTER TABLE `project_costs` MODIFY COLUMN `remaining_amount` DECIMAL(15, 2)');
                }
            });
        }

        // 6. project_financings
        if (Schema::hasTable('project_financings')) {
            Schema::table('project_financings', function (Blueprint $table) {
                if (Schema::hasColumn('project_financings', 'financing_amount')) {
                    DB::statement('ALTER TABLE `project_financings` MODIFY COLUMN `financing_amount` DECIMAL(15, 2) DEFAULT 0');
                }
            });
        }
    }
};
