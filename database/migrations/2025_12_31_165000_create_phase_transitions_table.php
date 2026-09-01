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
        Schema::create('phase_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('from_stage_id');
            $table->string('from_stage_name');
            $table->unsignedBigInteger('to_stage_id')->nullable();
            $table->string('to_stage_name')->nullable();
            $table->string('transition_type');
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->string('triggered_by_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('project_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phase_transitions');
    }
};
