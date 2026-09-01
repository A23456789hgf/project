<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Drop preliminary activities related tables
        Schema::dropIfExists('preliminary_action_costs');
        Schema::dropIfExists('preliminary_activity_actions');
        Schema::dropIfExists('preliminary_activities');

        // Drop executive activities related tables
        Schema::dropIfExists('executive_action_costs');
        Schema::dropIfExists('executive_action_assignees');
        Schema::dropIfExists('executive_activity_actions');
        Schema::dropIfExists('executive_activities');
        Schema::dropIfExists('executive_assignments');
        Schema::dropIfExists('executive_financial_summaries');
        Schema::dropIfExists('executive_progresses');
        Schema::dropIfExists('executive_schedules');

        // Drop implementation activities related tables (if they exist)
        Schema::dropIfExists('implementation_action_costs');
        Schema::dropIfExists('implementation_action_assignees');
        Schema::dropIfExists('implementation_activity_actions');
        Schema::dropIfExists('implementation_activities');
        Schema::dropIfExists('implementation_financial_summaries');

        // Drop entity related tables
        Schema::dropIfExists('entities');
        Schema::dropIfExists('entity_fathers');
        Schema::dropIfExists('entity_scopes');
        Schema::dropIfExists('entity_types');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration is destructive and cannot be fully reversed
        // You would need to recreate all the tables and their relationships
        // This is left intentionally empty as the data would be lost
    }
};
