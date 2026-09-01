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
        Schema::table('users', function (Blueprint $table) {
            // 1. Delete the role enum field if it exists
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            // 2. Ensure role_id exists and is after where role was (approx. after email)
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('email');
            }
        });

        // 3. Link it to the roles table if not already linked (Manual check to avoid 150 error)
        try {
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE SET NULL');
        } catch (Exception $e) {
            // Constraint probably already exists or column issue. Ignore to proceed.
        }
    }

    /**
     * Get foreign keys for a table. - Removed as it causes issues
     */
    private function getForeignKeys($table)
    {
        return [];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            // Adding back the enum if needed
            $table->enum('role', ['Creator', 'Financer', 'Manager', 'Funder', 'Admin'])->nullable()->after('email');
        });
    }
};
