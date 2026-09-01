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
            if (! Schema::hasColumn('projects', 'current_approval_stage_id')) {
                $table->foreignId('current_approval_stage_id')->nullable()->constrained('approval_stages')->onDelete('set null');
            }
            if (! Schema::hasColumn('projects', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('projects', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'in_process', 'approved', 'rejected'])->default('pending')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['current_approval_stage_id']);
            $table->dropForeignKeyIfExists(['created_by_user_id']);
            $table->dropColumn(['current_approval_stage_id', 'created_by_user_id', 'approval_status']);
        });
    }
};
