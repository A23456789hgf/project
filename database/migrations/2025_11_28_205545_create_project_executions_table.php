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
        // التحقق مما إذا كان الجدول موجوداً بالفعل لتجنب خطأ (Table already exists)
        if (! Schema::hasTable('project_executions')) {

            Schema::create('project_executions', function (Blueprint $table) {
                $table->id();

                // العلاقات (Foreign Keys)
                $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
                $table->foreignId('executive_activity_action_id')->constrained('executive_activity_actions')->onDelete('cascade');

                // تواريخ التنفيذ الفعلي
                $table->date('actual_start_date_gregorian')->nullable();
                $table->string('actual_start_date_hijri')->nullable();
                $table->date('actual_finish_date_gregorian')->nullable();
                $table->string('actual_finish_date_hijri')->nullable();

                // البيانات المالية الفعلية
                $table->decimal('actual_amount', 15, 2)->nullable();
                $table->decimal('amount_spent', 15, 2)->nullable();
                $table->decimal('remaining_amount', 15, 2)->nullable();

                // الحالة والمستندات والملاحظات
                $table->enum('status', ['not_started', 'in_progress', 'delayed', 'stalled'])->default('not_started');
                $table->json('technical_documents')->nullable();
                $table->json('financial_documents')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                // منع تكرار نفس النشاط لنفس المشروع (Unique Constraint)
                $table->unique(['project_id', 'executive_activity_action_id'], 'uq_proj_exec');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_executions');
    }
};
