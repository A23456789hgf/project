<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chain_plans')) {
            return;
        }

        foreach ($this->foreignKeys() as $foreignKey) {
            try {
                DB::statement("ALTER TABLE chain_plans DROP FOREIGN KEY {$foreignKey}");
            } catch (Throwable $e) {
                // The key may not exist depending on migration history.
            }
        }

        foreach ($this->textColumns() as $column) {
            if (Schema::hasColumn('chain_plans', $column)) {
                DB::statement("ALTER TABLE chain_plans MODIFY {$column} VARCHAR(191) NULL");
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('chain_plans')) {
            return;
        }

        foreach ($this->textColumns() as $column) {
            if (Schema::hasColumn('chain_plans', $column)) {
                DB::statement("UPDATE chain_plans SET {$column} = NULL WHERE {$column} IS NOT NULL AND {$column} NOT REGEXP '^[0-9]+$'");
                DB::statement("ALTER TABLE chain_plans MODIFY {$column} BIGINT UNSIGNED NULL");
            }
        }
    }

    private function textColumns(): array
    {
        return [
            'value_chain_id',
            'domain_id',
            'number',
            'value_chain_financing_type_id',
            'funding_source_id',
            'funding_entity_id',
            'authority_id',
            'implementing_entity_id',
        ];
    }

    private function foreignKeys(): array
    {
        return [
            'chain_plans_value_chain_id_foreign',
            'chain_plans_domain_id_foreign',
            'chain_plans_value_chain_financing_type_id_foreign',
            'chain_plans_funding_entity_id_foreign',
            'chain_plans_funding_source_id_foreign',
            'chain_plans_authority_id_foreign',
            'chain_plans_implementing_entity_id_foreign',
        ];
    }
};
