<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executive_action_assigned', function (Blueprint $table) {
            $table->id();

            // المفاتيح الأجنبية
            $table->foreignId('project_id')
                ->constrained('projects')
                ->onDelete('cascade');

            $table->foreignId('executive_activity_id')
                ->constrained('executive_activities')
                ->onDelete('cascade');

            $table->foreignId('executive_activity_action_id')
                ->constrained('executive_activity_actions')
                ->onDelete('cascade');

            // بيانات الجهة المكلفة
            $table->string('entity')->nullable();  // اسم الجهة المنفذة
            $table->string('name')->nullable();    // اسم الشخص أو الفريق المكلف
            $table->text('task')->nullable();      // المهمة أو الدور المكلف به

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executive_action_assigned');
    }
};
