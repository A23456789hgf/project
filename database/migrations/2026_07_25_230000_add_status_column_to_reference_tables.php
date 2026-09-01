<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'programs',
            'domains',
            'subdomains',
            'interventions',
            'priorities',
            'authorities',
            'internal_entities',
            'financial_items',
            'units',
            'financing_types',
            'beneficiary_groups',
            'target_categories',
            'directorates',
            'sub_areas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $tableSchema) {
                    $tableSchema->tinyInteger('status')->default(1)->comment('1: Approved, 0: Pending, 2: Rejected');
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
            'priorities',
            'authorities',
            'internal_entities',
            'financial_items',
            'units',
            'financing_types',
            'beneficiary_groups',
            'target_categories',
            'directorates',
            'sub_areas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                Schema::table($table, function (Blueprint $tableSchema) {
                    $tableSchema->dropColumn('status');
                });
            }
        }
    }
};
