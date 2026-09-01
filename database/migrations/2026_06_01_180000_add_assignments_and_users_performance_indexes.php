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
        Schema::table('activity_assignments', function (Blueprint $table) {
            $table->index(['assigned_to', 'status'], 'assignments_assigned_to_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('status', 'users_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_assigned_to_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_status_idx');
        });
    }
};
