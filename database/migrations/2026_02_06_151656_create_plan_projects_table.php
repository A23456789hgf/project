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
        Schema::create('plan_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('name');
            $table->enum('status', ['new', 'in_progress']);
            $table->text('indicators')->nullable();
            $table->text('outputs')->nullable();
            $table->string('baseline')->nullable();
            $table->decimal('target_value', 15, 2)->default(0);
            $table->enum('cost_type', ['YER', 'USD', 'EUR']);
            $table->decimal('cost', 15, 2)->default(0);
            $table->boolean('funding_availability')->default(false);
            $table->foreignId('funding_source_id')->nullable()->constrained('funding_sources');
            $table->foreignId('participating_entity_id')->constrained('internal_entities');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_projects');
    }
};
