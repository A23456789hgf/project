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
        Schema::create('value_chain_financings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('value_chain_id')->constrained('value_chains')->onDelete('cascade');
            $table->enum('entity_type', ['internal', 'external']);
            $table->unsignedBigInteger('internal_entity_id')->nullable();
            $table->unsignedBigInteger('authority_id')->nullable();

            $table->foreign('internal_entity_id')->references('id')->on('internal_entities')->onDelete('cascade');
            $table->foreign('authority_id')->references('id')->on('authorities')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_chain_financings');
    }
};
