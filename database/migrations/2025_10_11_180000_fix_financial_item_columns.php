<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix preliminary_costs table
        if (Schema::hasColumn('preliminary_costs', 'financial_item')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                // Drop the old string column
                $table->dropColumn('financial_item');
            });
        }

        if (! Schema::hasColumn('preliminary_costs', 'financial_item_id')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                // Add the new foreign key column
                $table->foreignId('financial_item_id')
                    ->after('procedure_id')
                    ->nullable()
                    ->constrained('financial_items')
                    ->onDelete('cascade');
            });
        }

        // Fix executive_action_costs table
        if (Schema::hasColumn('executive_action_costs', 'financial_item')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                // Drop the old string column
                $table->dropColumn('financial_item');
            });
        }

        if (! Schema::hasColumn('executive_action_costs', 'financial_item_id')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                // Add the new foreign key column
                $table->foreignId('financial_item_id')
                    ->after('executive_activity_action_id')
                    ->nullable()
                    ->constrained('financial_items')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        // Revert preliminary_costs table
        if (Schema::hasColumn('preliminary_costs', 'financial_item_id')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                $table->dropForeign(['financial_item_id']);
                $table->dropColumn('financial_item_id');
            });
        }

        if (! Schema::hasColumn('preliminary_costs', 'financial_item')) {
            Schema::table('preliminary_costs', function (Blueprint $table) {
                $table->string('financial_item')->after('procedure_id');
            });
        }

        // Revert executive_action_costs table
        if (Schema::hasColumn('executive_action_costs', 'financial_item_id')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                $table->dropForeign(['financial_item_id']);
                $table->dropColumn('financial_item_id');
            });
        }

        if (! Schema::hasColumn('executive_action_costs', 'financial_item')) {
            Schema::table('executive_action_costs', function (Blueprint $table) {
                $table->string('financial_item')->after('executive_activity_action_id');
            });
        }
    }
};
