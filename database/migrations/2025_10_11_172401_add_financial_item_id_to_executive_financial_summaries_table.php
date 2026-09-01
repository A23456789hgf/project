<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('executive_financial_summaries', 'financial_item_id')) {
            Schema::table('executive_financial_summaries', function (Blueprint $table) {
                // Add the financial_item_id foreign key column
                $table->foreignId('financial_item_id')
                    ->after('executive_action_cost_id')
                    ->nullable()
                    ->constrained('financial_items')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('executive_financial_summaries', 'financial_item_id')) {
            Schema::table('executive_financial_summaries', function (Blueprint $table) {
                $table->dropForeign(['financial_item_id']);
                $table->dropColumn('financial_item_id');
            });
        }
    }
};
