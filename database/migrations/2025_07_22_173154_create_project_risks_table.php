<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();

            // المفتاح الخارجي للمشروع
            $table->foreignId('project_id')
                ->constrained()
                ->onDelete('cascade');

            // حقل المخاطرة
            $table->string('risk');

            // مستوى المخاطرة (مرتفع، متوسط، منخفض مثلاً)
            $table->string('risk_rate');

            // الحل المقترح
            $table->text('proposed_solution')->nullable();

            // التوقيتات
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};
