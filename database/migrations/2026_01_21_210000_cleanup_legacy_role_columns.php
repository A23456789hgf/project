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
        // 1. Ensure role_id exists for users and migrate data
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
            }
        });

        // Migrate users data
        if (Schema::hasColumn('users', 'role')) {
            $users = DB::table('users')->whereNull('role_id')->get();
            foreach ($users as $user) {
                if ($user->role) {
                    $role = DB::table('roles')->where('name', $user->role)->first();
                    if ($role) {
                        DB::table('users')->where('id', $user->id)->update(['role_id' => $role->id]);
                    }
                }
            }
        }

        // 2. Cleanup users table - Rename instead of drop for safety, then we'll be done
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->renameColumn('role', 'role_legacy');
            }
        });

        // 3. Update user_roles table
        Schema::table('user_roles', function (Blueprint $table) {
            if (! Schema::hasColumn('user_roles', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('user_id');
            }
        });

        // Migrate data in user_roles
        if (Schema::hasColumn('user_roles', 'role')) {
            $userRoles = DB::table('user_roles')->whereNull('role_id')->get();
            foreach ($userRoles as $ur) {
                if ($ur->role) {
                    $role = DB::table('roles')->where('name', $ur->role)->first();
                    if ($role) {
                        DB::table('user_roles')
                            ->where('id', $ur->id)
                            ->update(['role_id' => $role->id]);
                    }
                }
            }
        }

        // 4. Finalize user_roles table
        Schema::table('user_roles', function (Blueprint $table) {
            if (Schema::hasColumn('user_roles', 'role')) {
                $table->renameColumn('role', 'role_legacy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_legacy')) {
                $table->renameColumn('role_legacy', 'role');
            }
        });

        Schema::table('user_roles', function (Blueprint $table) {
            if (Schema::hasColumn('user_roles', 'role_legacy')) {
                $table->renameColumn('role_legacy', 'role');
            }
        });
    }
};
