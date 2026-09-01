<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add completed_at column if it doesn't exist
            if (! Schema::hasColumn('projects', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('finalized_at')->comment('When the project was completed');
            }
        });

        // Update the status enum to include new values
        // First, we need to modify the status column to accept the new enum values
        DB::statement("ALTER TABLE projects MODIFY status ENUM('draft', 'submitted', 'approved', 'rejected', 'completed', 'rolled_back_for_review', 'pending_approval', 'in_progress', 'assembly_approval') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });

        // Restore the original status enum
        DB::statement("ALTER TABLE projects MODIFY status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft'");
    }
};
