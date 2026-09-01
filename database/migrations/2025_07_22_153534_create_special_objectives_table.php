<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('special_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('objective');
            $table->decimal('objective_weight', 5, 2)->nullable();
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('target_percentage', 5, 2)->nullable();
            $table->enum('indicator_type', ['quantitative', 'relative', 'qualitative'])->default('quantitative');
            $table->string('measurement_unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_objectives');
    }
};
