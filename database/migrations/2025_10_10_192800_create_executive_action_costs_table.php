<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_action_costs', function (Blueprint $table) {
            $table->id();

            // 🔗 المفاتيح الأجنبية
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            $table->foreignId('executive_activity_id')
                ->constrained('executive_activities')
                ->onDelete('cascade');

            $table->foreignId('executive_activity_action_id')
                ->constrained('executive_activity_actions')
                ->onDelete('cascade');

            // ✅ البند المالي كمفتاح أجنبي
            $table->foreignId('financial_item_id')
                ->constrained('financial_items')
                ->onDelete('cascade');

            // 💰 بيانات التكلفة
            $table->string('unit')->nullable(); // وحدة القياس (مثلاً: قطعة، يوم، متر...)
            $table->decimal('amount', 15, 2)->default(0);   // تكلفة الوحدة
            $table->decimal('quantity', 15, 2)->default(1); // الكمية
            $table->decimal('total', 15, 2)->default(0);    // المجموع (amount * quantity)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executive_action_costs');
    }
};
