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
        Schema::create('result_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('special_objective_id')->constrained()->onDelete('cascade');
            $table->foreignId('objective_result_id')->constrained()->onDelete('cascade');
            $table->string('output');
            $table->decimal('target_value', 10, 2);
            $table->enum('indicator_type', ['quantitative', 'relative', 'qualitative'])->default('quantitative');
            $table->string('indicator_unit');
            $table->timestamps();

            // Composite index for faster lookups - using shorter name
            $table->index(['project_id', 'objective_result_id'], 'result_outputs_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_outputs');
    }
};
