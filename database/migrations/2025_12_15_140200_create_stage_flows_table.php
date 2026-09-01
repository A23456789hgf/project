<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_stage_id')->constrained('stages')->onDelete('cascade')->comment('Stage transitioning from');
            $table->foreignId('to_stage_id')->constrained('stages')->onDelete('cascade')->comment('Stage transitioning to');
            $table->string('trigger_status')->comment('The status that triggers this transition (e.g., approved)');
            $table->text('conditions')->nullable()->comment('JSON conditions that must be met for transition');
            $table->boolean('auto_create_next')->default(false)->comment('Automatically create next stage on trigger');
            $table->boolean('is_active')->default(true)->comment('Whether this flow is active');
            $table->integer('order')->default(0)->comment('Order of transition when multiple exist');
            $table->timestamps();

            $table->unique(['from_stage_id', 'to_stage_id', 'trigger_status']);
            $table->index('from_stage_id');
            $table->index('to_stage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_flows');
    }
};
