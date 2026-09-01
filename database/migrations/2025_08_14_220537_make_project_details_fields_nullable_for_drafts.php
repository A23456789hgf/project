<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support changing column properties
        // These fields should already be nullable in the original migration
        // If not, they will need to be handled differently
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SQLite doesn't support changing column properties
        // No action needed for rollback
    }
};
