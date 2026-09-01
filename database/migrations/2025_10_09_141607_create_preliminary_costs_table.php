<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preliminary_costs', function (Blueprint $table) {
            $table->id();

            // العلاقات
            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('activity_id')
                ->constrained('preliminary_activities')
                ->onDelete('cascade');

            $table->foreignId('procedure_id')
                ->constrained('preliminary_procedures')
                ->onDelete('cascade');

            // ✅ البند المالي كمفتاح أجنبي من جدول financial_items
            $table->unsignedBigInteger('financial_item_id');
            $table->foreign('financial_item_id')
                ->references('id')
                ->on('financial_items')
                ->onDelete('cascade');

            // باقي الحقول
            $table->string('unit')->nullable(); // وحدة القياس
            $table->decimal('amount', 15, 2)->default(0); // تكلفة الوحدة
            $table->integer('quantity')->default(1); // الكمية
            $table->decimal('total', 15, 2)->default(0); // المجموع (amount * quantity)

            $table->timestamps();

            // فهرسة للبحث السريع
            $table->index(['project_id', 'activity_id', 'procedure_id'], 'preliminary_costs_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preliminary_costs');
    }
};
