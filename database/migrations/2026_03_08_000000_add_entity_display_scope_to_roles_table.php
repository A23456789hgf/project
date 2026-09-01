<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds entity_display_scope JSON column to roles table.
     * This stores the geographic display scope for internal entities and authorities.
     * Format: {"internal_entities": "all|same_governorate|same_directorate|none",
     *          "authorities":       "all|same_governorate|same_directorate|none"}
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('entity_display_scope')->nullable()->after('module_scopes')
                ->comment('Geographic scope for viewing stakeholders: internal_entities and authorities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('entity_display_scope');
        });
    }
};
