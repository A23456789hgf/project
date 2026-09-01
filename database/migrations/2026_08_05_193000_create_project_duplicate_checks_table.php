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
        Schema::create('project_duplicate_checks', function (Blueprint $table) {
            $table->id();
            $table->string('project_name_input');
            $table->decimal('similarity_percentage', 5, 2)->default(0);
            $table->foreignId('matched_project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('matched_project_name')->nullable();
            $table->string('match_level')->default('medium'); // exact, high, medium
            $table->string('decision')->default('checked'); // blocked, proceeded_override, cancelled, checked
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('input_details')->nullable();
            $table->timestamps();

            $table->index(['similarity_percentage', 'match_level']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_duplicate_checks');
    }
};
