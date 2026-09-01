<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إنشاء جدول مشاريع إدارة التمكين (القروض والحسابات)
     */
    public function up(): void
    {
        Schema::create('empowerment_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->unique(); // One record per project
            $table->string('project_number')->nullable();       // رقم المشروع
            $table->string('project_name');                     // اسم المشروع
            $table->string('submitting_entity')->nullable();    // الجهة مقدمة المشروع
            $table->decimal('total_project_cost', 18, 2)->default(0); // إجمالي تكلفة المشروع
            $table->decimal('total_loan_amount', 18, 2)->default(0);  // إجمالي مبلغ القرض (تمويل التمكين)
            $table->decimal('loan_percentage', 8, 4)->default(0);     // نسبة القرض من إجمالي التكلفة
            $table->unsignedInteger('number_of_beneficiaries')->default(0); // عدد المستفيدين
            $table->date('start_date_gregorian')->nullable();   // تاريخ البداية (م)
            $table->string('start_date_hijri')->nullable();     // تاريخ البداية (هـ)
            $table->date('end_date_gregorian')->nullable();     // تاريخ النهاية (م)
            $table->string('end_date_hijri')->nullable();       // تاريخ النهاية (هـ)
            $table->string('status')->default('pending');       // الحالة: pending | under_review | processed | rejected
            $table->text('notes')->nullable();                  // ملاحظات موظف القروض
            $table->unsignedBigInteger('processed_by')->nullable(); // المعالج
            $table->timestamp('processed_at')->nullable();      // تاريخ المعالجة
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empowerment_projects');
    }
};
