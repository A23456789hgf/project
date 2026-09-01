<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تشغيل عملية إنشاء الجدول.
     */
    public function up(): void
    {
        Schema::create('executive_activity_actions', function (Blueprint $table) {
            $table->id();

            // المفاتيح الأجنبية
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            $table->foreignId('executive_activity_id')
                ->constrained('executive_activities')
                ->onDelete('cascade');

            // الأعمدة الخاصة بالإجراء التنفيذي
            $table->string('action');               // اسم الإجراء
            $table->decimal('weight', 5, 2)->nullable(); // الوزن النسبي
            $table->date('start_date')->nullable(); // تاريخ البداية
            $table->date('end_date')->nullable();   // تاريخ النهاية
            $table->text('verification_means')->nullable(); // وسائل التحقق

            $table->timestamps();
        });
    }

    /**
     * عكس العملية (حذف الجدول).
     */
    public function down(): void
    {
        Schema::dropIfExists('executive_activity_actions');
    }
};
