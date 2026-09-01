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
        Schema::create('chain_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('directorate_id')->nullable()->constrained('directorates')->nullOnDelete();
            $table->foreignId('value_chain_id')->nullable()->constrained('value_chains')->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('indicator')->nullable();
            $table->integer('number')->nullable();
            $table->foreignId('value_chain_financing_type_id')->nullable()->constrained('value_chain_financing_types')->nullOnDelete();
            $table->foreignId('funding_entity_id')->nullable()->constrained('authorities')->nullOnDelete();
            $table->foreignId('implementing_entity_id')->nullable()->constrained('authorities')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chain_plans');
    }
};
