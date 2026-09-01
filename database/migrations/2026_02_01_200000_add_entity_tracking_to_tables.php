<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add entity tracking fields to tables that need entity-based filtering
     */
    public function up(): void
    {
        $tables = [
            'programs',
            'domains',
            'subdomains',
            'interventions',
            'authorities',
            'beneficiary_groups',
            'financial_items',
            'funding_sources',
            'financing_types',
            'financing_forms',
            'sub_financing_forms',
            'priorities',
            'main_routers',
            'sub_routers',
            'target_categories',
            'units',
            'donors',
            'executors',
            'beneficiaries',
            'associations',
            'funded_entities',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Only add columns if they don't exist
                    if (! Schema::hasColumn($table->getTable(), 'created_by_entity')) {
                        $table->string('created_by_entity')->nullable()->after('id');
                    }
                    if (! Schema::hasColumn($table->getTable(), 'created_by_user_id')) {
                        $table->unsignedBigInteger('created_by_user_id')->nullable()->after('created_by_entity');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'programs',
            'domains',
            'subdomains',
            'interventions',
            'authorities',
            'beneficiary_groups',
            'financial_items',
            'funding_sources',
            'financing_types',
            'financing_forms',
            'sub_financing_forms',
            'priorities',
            'main_routers',
            'sub_routers',
            'target_categories',
            'units',
            'donors',
            'executors',
            'beneficiaries',
            'associations',
            'funded_entities',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (Schema::hasColumn($table->getTable(), 'created_by_entity')) {
                        $table->dropColumn('created_by_entity');
                    }
                    if (Schema::hasColumn($table->getTable(), 'created_by_user_id')) {
                        $table->dropColumn('created_by_user_id');
                    }
                });
            }
        }
    }
};
