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
        // Make role_legacy nullable in role_permission
        if (Schema::hasColumn('role_permission', 'role_legacy')) {
            Schema::table('role_permission', function (Blueprint $table) {
                $table->string('role_legacy')->nullable()->change();
            });
        }

        // Make role_legacy nullable in user_roles
        if (Schema::hasColumn('user_roles', 'role_legacy')) {
            Schema::table('user_roles', function (Blueprint $table) {
                $table->string('role_legacy')->nullable()->change();
            });
        }

        // Make role_legacy nullable in users
        if (Schema::hasColumn('users', 'role_legacy')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role_legacy')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('role_permission', 'role_legacy')) {
            Schema::table('role_permission', function (Blueprint $table) {
                $table->string('role_legacy')->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('user_roles', 'role_legacy')) {
            Schema::table('user_roles', function (Blueprint $table) {
                $table->string('role_legacy')->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('users', 'role_legacy')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role_legacy')->nullable(false)->change();
            });
        }
    }
};
