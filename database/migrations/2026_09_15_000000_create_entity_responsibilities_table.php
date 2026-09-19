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
        Schema::create('entity_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internal_entity_id')->constrained('internal_entities')->cascadeOnDelete();
            $table->string('responsibility_type', 50);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['internal_entity_id', 'responsibility_type'], 'entity_resp_unique');

            $table->index(['responsibility_type', 'is_active']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_responsibilities');
    }
};
