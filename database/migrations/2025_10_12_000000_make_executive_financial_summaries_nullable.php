<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        Schema::table('executive_financial_summaries', function (Blueprint $table) {
            // Make these columns nullable since we're grouping by financial_item_id only
            $table->foreignId('executive_activity_id')->nullable()->change();
            $table->foreignId('executive_activity_action_id')->nullable()->change();
            $table->foreignId('executive_action_cost_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('executive_financial_summaries', function (Blueprint $table) {
            // Revert to NOT NULL
            $table->foreignId('executive_activity_id')->nullable(false)->change();
            $table->foreignId('executive_activity_action_id')->nullable(false)->change();
            $table->foreignId('executive_action_cost_id')->nullable(false)->change();
        });
    }
};
