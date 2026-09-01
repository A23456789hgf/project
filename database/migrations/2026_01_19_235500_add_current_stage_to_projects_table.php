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
        Schema::table('projects', function (Blueprint $table) {
            // Add current_stage column if it doesn't exist
            if (! Schema::hasColumn('projects', 'current_stage')) {
                $table->string('current_stage')->nullable()->after('status')->comment('Current approval stage (e.g., entity_1, entity_2)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'current_stage')) {
                $table->dropColumn('current_stage');
            }
        });
    }
};
