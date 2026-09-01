<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء الجدول فقط إذا لم يكن موجودًا مسبقًا
        if (! Schema::hasTable('executive_financial_summaries')) {
            Schema::create('executive_financial_summaries', function (Blueprint $table) {
                $table->id();

                // 🔗 المفاتيح الأجنبية
                $table->foreignId('project_id')
                    ->constrained('projects')
                    ->onDelete('cascade');

                $table->foreignId('executive_activity_id')
                    ->constrained('executive_activities')
                    ->onDelete('cascade');

                $table->foreignId('executive_activity_action_id')
                    ->constrained('executive_activity_actions', 'id', 'exec_act_action_fk')
                    ->onDelete('cascade');

                $table->foreignId('executive_action_cost_id')
                    ->constrained('executive_action_costs')
                    ->onDelete('cascade');

                // ✅ ربط البند المالي كمفتاح أجنبي
                $table->foreignId('financial_item_id')
                    ->constrained('financial_items')
                    ->onDelete('cascade');

                // 💰 بيانات الملخص المالي
                $table->decimal('amount', 15, 2)->default(0); // المبلغ المالي

                $table->timestamps();

                // فهرسة لتسريع البحث
                $table->index(['project_id', 'executive_activity_id', 'executive_activity_action_id', 'executive_action_cost_id'], 'exec_fin_summary_index');
            });
        }
    }

    public function down(): void
    {
        // حذف الجدول إذا كان موجودًا
        if (Schema::hasTable('executive_financial_summaries')) {
            Schema::dropIfExists('executive_financial_summaries');
        }
    }
};
