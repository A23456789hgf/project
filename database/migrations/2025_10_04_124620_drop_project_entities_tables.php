<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop project entities related tables
     */
    public function up(): void
    {
        // Drop tables in reverse order of dependencies
        Schema::dropIfExists('entity_tasks_summaries');
        Schema::dropIfExists('entity_team_assignments');
        Schema::dropIfExists('project_entities');
    }

    /**
     * Reverse the migrations.
     * Recreate the tables if needed (not recommended - kept for reference only)
     */
    public function down(): void
    {
        // Note: This is kept for migration rollback purposes only
        // The actual table structures are removed from the codebase

        Schema::create('project_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('entity_type');
            $table->string('entity_name');
            $table->text('entity_task')->nullable();
            $table->boolean('is_sub_entity')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('project_entities')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('entity_team_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_entity_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('work');
            $table->string('entity');
            $table->timestamps();
        });

        Schema::create('entity_tasks_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_entity_id')->constrained()->onDelete('cascade');
            $table->text('entity_task');
            $table->string('entity_name');
            $table->integer('assigned_team_count')->default(0);
            $table->timestamps();
        });
    }
};
