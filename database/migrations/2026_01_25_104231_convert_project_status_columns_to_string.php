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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status', 50)->default('draft')->change();
            $table->string('approval_status', 50)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Reverting to the last known ENUM values (approximate)
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'completed', 'rolled_back_for_review', 'pending_approval', 'in_progress', 'assembly_approval'])->default('draft')->change();
            $table->enum('approval_status', ['pending', 'in_process', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
