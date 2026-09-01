<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that contain the authority_type column which needs to be nullable.
     * This allows importing entities without specifying the type (external/internal).
     */
    private array $tables = [
        'supervising_authorities',
        'implementing_entities',
        'participating_entities',
        'beneficiary_entities',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'authority_type')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('authority_type')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'authority_type')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('authority_type')->nullable(false)->change();
                });
            }
        }
    }
};
