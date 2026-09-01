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
        Schema::create('plan_project_activities', function (Blueprint $积极) {
            $积极->id();
            $积极->foreignId('plan_project_id')->constrained('plan_projects')->onDelete('cascade');
            $积极->string('name');
            $积极->decimal('weight', 5, 2); // e.g., 100.00
            $积极->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_project_activities');
    }
};
