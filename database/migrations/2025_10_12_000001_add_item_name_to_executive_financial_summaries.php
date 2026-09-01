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
            // Add item_name column if it doesn't exist
            if (! Schema::hasColumn('executive_financial_summaries', 'item_name')) {
                $table->string('item_name')->nullable()->after('financial_item_id');
            }
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('executive_financial_summaries', function (Blueprint $table) {
            // Drop the item_name column
            if (Schema::hasColumn('executive_financial_summaries', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }
};
