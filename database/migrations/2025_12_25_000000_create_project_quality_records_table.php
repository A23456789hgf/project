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
        Schema::create('project_quality_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');

            // Type of activity (preliminary or executive)
            $table->enum('record_type', ['preliminary', 'executive']);

            // Activity reference
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('procedure_or_action_id')->nullable();

            // Quality aspect being assessed
            $table->enum('quality_aspect', ['time', 'financial']);

            // Overall quality status
            $table->enum('quality_status', ['positive', 'negative']);

            // Time-specific status
            $table->enum('time_status', ['ahead', 'on_time', 'late'])->nullable();

            // Financial-specific status
            $table->enum('financial_status', ['below_plan', 'matching_plan', 'over_plan'])->nullable();

            // Date comparisons
            $table->date('planned_start_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // Amount comparisons
            $table->decimal('planned_amount', 15, 2)->nullable();
            $table->decimal('actual_amount', 15, 2)->nullable();

            // Variance calculations
            $table->integer('variance_days')->nullable()->comment('Difference in days for time aspect');
            $table->decimal('variance_amount', 15, 2)->nullable()->comment('Difference in amount for financial aspect');

            // Solution for negative quality cases
            $table->text('proposed_solution')->nullable();

            // File attachments (stored as JSON array of file paths)
            $table->json('attachments')->nullable();

            $table->timestamps();

            // Indexes for better query performance
            $table->index(['project_id', 'record_type']);
            $table->index(['quality_status', 'quality_aspect']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_quality_records');
    }
};
