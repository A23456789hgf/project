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
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            if (! Schema::hasColumn('project_supervising_authorities', 'internal_entity_id')) {
                $table->foreignId('internal_entity_id')->nullable()->constrained('internal_entities')->onDelete('cascade');
            }
        });

        Schema::table('implementing_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('implementing_entities', 'internal_entity_id')) {
                $table->foreignId('internal_entity_id')->nullable()->constrained('internal_entities')->onDelete('cascade');
            }
        });

        Schema::table('participating_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('participating_entities', 'internal_entity_id')) {
                $table->foreignId('internal_entity_id')->nullable()->constrained('internal_entities')->onDelete('cascade');
            }
        });

        Schema::table('beneficiary_entities', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiary_entities', 'internal_entity_id')) {
                $table->foreignId('internal_entity_id')->nullable()->constrained('internal_entities')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_supervising_authorities', function (Blueprint $table) {
            if (Schema::hasColumn('project_supervising_authorities', 'internal_entity_id')) {
                $table->dropForeign(['internal_entity_id']);
                $table->dropColumn('internal_entity_id');
            }
        });

        Schema::table('implementing_entities', function (Blueprint $table) {
            if (Schema::hasColumn('implementing_entities', 'internal_entity_id')) {
                $table->dropForeign(['internal_entity_id']);
                $table->dropColumn('internal_entity_id');
            }
        });

        Schema::table('participating_entities', function (Blueprint $table) {
            if (Schema::hasColumn('participating_entities', 'internal_entity_id')) {
                $table->dropForeign(['internal_entity_id']);
                $table->dropColumn('internal_entity_id');
            }
        });

        Schema::table('beneficiary_entities', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiary_entities', 'internal_entity_id')) {
                $table->dropForeign(['internal_entity_id']);
                $table->dropColumn('internal_entity_id');
            }
        });
    }
};
