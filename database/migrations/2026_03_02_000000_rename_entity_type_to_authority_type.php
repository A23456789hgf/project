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
        Schema::table('implementing_entities', function (Blueprint $table) {
            $table->renameColumn('entity_type', 'authority_type');
        });

        Schema::table('participating_entities', function (Blueprint $table) {
            $table->renameColumn('entity_type', 'authority_type');
        });

        Schema::table('beneficiary_entities', function (Blueprint $table) {
            $table->renameColumn('entity_type', 'authority_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('implementing_entities', function (Blueprint $table) {
            $table->renameColumn('authority_type', 'entity_type');
        });

        Schema::table('participating_entities', function (Blueprint $table) {
            $table->renameColumn('authority_type', 'entity_type');
        });

        Schema::table('beneficiary_entities', function (Blueprint $table) {
            $table->renameColumn('authority_type', 'entity_type');
        });
    }
};
