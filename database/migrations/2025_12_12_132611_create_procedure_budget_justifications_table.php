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
        Schema::create('procedure_budget_justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preliminary_procedure_id')->constrained('preliminary_procedures')->onDelete('cascade');
            $table->decimal('planned_total', 15, 2);
            $table->decimal('actual_total', 15, 2);
            $table->decimal('overage_amount', 15, 2);
            $table->longText('justification')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->longText('reviewer_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_budget_justifications');
    }
};
