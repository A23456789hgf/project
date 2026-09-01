<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('preliminary_financial_summaries')) {
            Schema::create('preliminary_financial_summaries', function (Blueprint $table) {
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

                // العلاقة مع جدول التكاليف المبدئية
                $table->foreignId('cost_id')
                    ->constrained('preliminary_costs')
                    ->onDelete('cascade');

                // ✅ ربط البند المالي كمفتاح أجنبي مباشر
                $table->foreignId('financial_item_id')
                    ->constrained('financial_items')
                    ->onDelete('cascade');

                // إجمالي تجميعي
                $table->decimal('aggregated_total', 15, 2)->default(0);

                $table->timestamps();

                // فهرس مشترك
                $table->index(['project_id', 'activity_id', 'procedure_id', 'cost_id'], 'pf_summary_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('preliminary_financial_summaries');
    }
};
