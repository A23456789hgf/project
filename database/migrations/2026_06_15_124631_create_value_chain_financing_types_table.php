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
        Schema::create('value_chain_financing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('import_batch')->nullable();

            // Audit fields commonly used in the system
            $table->string('creator_username')->nullable();
            $table->unsignedBigInteger('creator_entity_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->string('created_by_entity')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('value_chain_financing_types');
    }
};
