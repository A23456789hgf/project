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
        // 7. transactions
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'amount_change')) {
                    DB::statement('ALTER TABLE `transactions` MODIFY COLUMN `amount_change` DECIMAL(24, 2)');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'amount_change')) {
                    DB::statement('ALTER TABLE `transactions` MODIFY COLUMN `amount_change` DECIMAL(15, 2)');
                }
            });
        }
    }
};
