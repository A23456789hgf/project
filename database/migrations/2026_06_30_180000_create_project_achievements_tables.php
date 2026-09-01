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
        Schema::create('project_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('report_type_id')->constrained('report_types');
            $table->date('start_date_gregorian');
            $table->string('start_date_hijri');
            $table->date('end_date_gregorian');
            $table->string('end_date_hijri');
            $table->string('duration');
            $table->decimal('previous_achievement', 5, 2)->default(0.00);
            $table->decimal('new_achievement', 5, 2);
            $table->text('achieved_outputs');
            $table->text('achieved_indicators');
            $table->integer('number_of_beneficiaries');
            $table->text('notes_on_beneficiaries');
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('project_achievement_fundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_achievement_id', 'p_ach_id') // shorter constraint name
                ->references('id')->on('project_achievements')
                ->cascadeOnDelete();
            $table->string('funding_entity');
            $table->decimal('total_funding', 15, 2);
            $table->decimal('previous_disbursement', 15, 2);
            $table->decimal('previous_remaining_disbursement', 15, 2);
            $table->decimal('new_disbursement', 15, 2);
            $table->decimal('disbursement_percentage', 5, 2);
            $table->timestamps();
        });

        Schema::create('project_achievement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_achievement_id', 'p_ach_doc_id') // shorter constraint name
                ->references('id')->on('project_achievements')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_achievement_documents');
        Schema::dropIfExists('project_achievement_fundings');
        Schema::dropIfExists('project_achievements');
    }
};
