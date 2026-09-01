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
        Schema::create('plan_project_activity_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_project_activity_id')->constrained('plan_project_activities')->onDelete('cascade');
            $table->string('name');
            $table->decimal('weight', 5, 2);
            $table->date('start_date_g')->nullable();
            $table->string('start_date_h')->nullable();
            $table->date('end_date_g')->nullable();
            $table->string('end_date_h')->nullable();
            $table->integer('duration')->nullable(); // in days
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_project_activity_actions');
    }
};
