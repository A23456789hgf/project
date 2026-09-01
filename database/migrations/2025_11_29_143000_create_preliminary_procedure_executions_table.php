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
        if (! Schema::hasTable('preliminary_procedure_executions')) {

            Schema::create('preliminary_procedure_executions', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('project_id');
                $table->unsignedBigInteger('preliminary_procedure_id');

                $table->foreign('project_id')->references('id')->on('projects')->name('fk_ppe_project')->onDelete('cascade');
                $table->foreign('preliminary_procedure_id')->references('id')->on('preliminary_procedures')->name('fk_ppe_prelim')->onDelete('cascade');

                $table->integer('sequence')->default(1);

                $table->date('actual_start_date_gregorian')->nullable();
                $table->string('actual_start_date_hijri')->nullable();
                $table->date('actual_finish_date_gregorian')->nullable();
                $table->string('actual_finish_date_hijri')->nullable();

                $table->decimal('actual_amount', 15, 2)->nullable();
                $table->decimal('amount_spent', 15, 2)->nullable();
                $table->decimal('remaining_amount', 15, 2)->nullable();

                $table->enum('status', ['not_started', 'in_progress', 'delayed', 'stalled', 'completed'])->default('not_started');
                $table->decimal('completion_percentage', 5, 2)->default(0);
                $table->json('technical_documents')->nullable();
                $table->json('financial_documents')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->unique(['project_id', 'preliminary_procedure_id', 'sequence'], 'uq_prelim_proc_exec');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preliminary_procedure_executions');
    }
};
