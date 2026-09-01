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
        // 1. Fix role_permission table
        Schema::table('role_permission', function (Blueprint $table) {
            // Drop legacy unique index if it exists
            // Based on audit, it's called 'role_permission_role_permission_id_unique'
            $table->dropUnique('role_permission_role_permission_id_unique');

            // Add new unique index on role_id and permission_id
            $table->unique(['role_id', 'permission_id']);
        });

        // 2. Fix user_roles table
        Schema::table('user_roles', function (Blueprint $table) {
            // Drop legacy unique index if it exists
            // Based on audit, it's called 'user_roles_user_id_role_unique'
            $table->dropUnique('user_roles_user_id_role_unique');

            // Add new unique index on user_id and role_id
            $table->unique(['user_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropUnique(['role_id', 'permission_id']);
            $table->unique(['role_legacy', 'permission_id'], 'role_permission_role_permission_id_unique');
        });

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'role_id']);
            $table->unique(['user_id', 'role_legacy'], 'user_roles_user_id_role_unique');
        });
    }
};
