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
            // Add target categories field
            $table->text('target_categories')->nullable();

            // Add priority foreign key
            $table->foreignId('priority_id')
                ->nullable()
                ->constrained('priorities')
                ->nullOnDelete(); // بدال onDelete('set null')

            // Add main router foreign key
            $table->foreignId('main_router_id')
                ->nullable()
                ->constrained('main_routers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['priority_id']);
            $table->dropForeign(['main_router_id']);

            // Drop columns
            $table->dropColumn(['target_categories', 'priority_id', 'main_router_id']);
        });
    }
};
