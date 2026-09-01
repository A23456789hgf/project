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
        Schema::table('plan_projects', function (Blueprint $table) {
            $table->dropColumn(['indicators', 'outputs', 'target_value']);
        });

        Schema::create('plan_project_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_project_id')->constrained('plan_projects')->onDelete('cascade');
            $table->string('specific_goal');
            $table->decimal('weight', 5, 2)->default(0);
            $table->decimal('indicator_value', 15, 2)->default(0);
            $table->string('unit_of_measurement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_project_goals');

        Schema::table('plan_projects', function (Blueprint $table) {
            $table->text('indicators')->nullable();
            $table->text('outputs')->nullable();
            $table->decimal('target_value', 15, 2)->default(0);
        });
    }
};
