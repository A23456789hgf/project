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
        Schema::create('project_activity_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('action_type'); // approved, rejected, requires_action, resubmitted, sent_for_review
            $table->integer('from_stage_order')->nullable();
            $table->string('from_stage_name')->nullable();
            $table->integer('to_stage_order')->nullable();
            $table->string('to_stage_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('action_details')->nullable(); // For required_action or rejection_reason
            $table->json('metadata')->nullable(); // For additional data
            $table->timestamps();

            // Indexes for better query performance
            $table->index('project_id');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_activity_history');
    }
};
