<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // في ملف الترحيل الجديد
    public function up()
    {
        Schema::create('project_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->boolean('is_part_of_plan')->default(false);
            $table->text('project_summary');
            $table->text('problem_and_justification');
            $table->text('expected_results');
            $table->text('expected_outputs');
            $table->text('project_components');
            $table->text('expected_impact');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};
