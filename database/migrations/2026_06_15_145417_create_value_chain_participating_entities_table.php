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
        Schema::create('value_chain_participating_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_chain_id')->constrained('value_chains')->cascadeOnDelete();
            $table->enum('entity_type', ['internal', 'external']);
            $table->foreignId('internal_entity_id')->nullable()->constrained('internal_entities')->nullOnDelete();
            $table->foreignId('authority_id')->nullable()->constrained('authorities')->nullOnDelete();

            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_chain_participating_entities');
    }
};
