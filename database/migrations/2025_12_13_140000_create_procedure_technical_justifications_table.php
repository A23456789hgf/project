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
        Schema::create('procedure_technical_justifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preliminary_procedure_id');
            $table->foreign('preliminary_procedure_id', 'proc_tech_proc_fk')->references('id')->on('preliminary_procedures')->onDelete('cascade');
            $table->date('planned_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->integer('delay_days')->default(0);
            $table->longText('justification')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->longText('reviewer_notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by', 'proc_tech_reviewed_fk')->references('id')->on('users')->onDelete('set null');
            $table->dateTime('reviewed_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by', 'proc_tech_created_fk')->references('id')->on('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_technical_justifications');
    }
};
