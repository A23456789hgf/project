<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('value_chain_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('value_chain_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');

            $table->string('role');
            // program_manager, chain_officer, coordinator

            $table->foreignId('governorate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('directorate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('phone', 20)->nullable(); // رقم الهاتف

            $table->boolean('is_active')->default(1);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('value_chain_members');
    }
};
