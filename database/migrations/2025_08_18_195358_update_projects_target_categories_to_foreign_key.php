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
            // Add target_category_id foreign key
            $table->foreignId('target_category_id')
                ->nullable()
                ->constrained('target_categories')
                ->onDelete('set null')
                ->after('target_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['target_category_id']);

            // Drop the new column
            $table->dropColumn('target_category_id');
        });
    }
};
