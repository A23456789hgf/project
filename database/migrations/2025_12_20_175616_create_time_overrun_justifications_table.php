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
        if (! Schema::hasTable('time_overrun_justifications')) {
            Schema::create('time_overrun_justifications', function (Blueprint $table) {
                $table->id();
                $table->string('justifiable_type');
                $table->unsignedBigInteger('justifiable_id');
                $table->index(['justifiable_type', 'justifiable_id'], 'time_overrun_justifiable_idx');
                $table->date('planned_end_date');
                $table->date('actual_end_date');
                $table->integer('overrun_days')->nullable();
                $table->longText('justification')->nullable();
                $table->json('attachments')->nullable();
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->longText('reviewer_notes')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('reviewed_at')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
                $table->timestamps();

                $table->index('justifiable_type');
                $table->index('justifiable_id');
                $table->index('approval_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_overrun_justifications');
    }
};
