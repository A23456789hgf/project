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
        Schema::table('role_permission', function (Blueprint $table) {
            if (! Schema::hasColumn('role_permission', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('role');
            }
        });

        // Migrate data in role_permission
        if (Schema::hasColumn('role_permission', 'role')) {
            $rolePermissions = DB::table('role_permission')->whereNull('role_id')->get();
            foreach ($rolePermissions as $rp) {
                if ($rp->role) {
                    $role = DB::table('roles')->where('name', $rp->role)->first();
                    if ($role) {
                        DB::table('role_permission')
                            ->where('id', $rp->id)
                            ->update(['role_id' => $role->id]);
                    }
                }
            }
        }

        Schema::table('role_permission', function (Blueprint $table) {
            if (Schema::hasColumn('role_permission', 'role')) {
                $table->renameColumn('role', 'role_legacy');
            }
            if (Schema::hasColumn('role_permission', 'role_id')) {
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_permission', function (Blueprint $table) {
            if (Schema::hasColumn('role_permission', 'role_legacy')) {
                $table->renameColumn('role_legacy', 'role');
            }
            if (Schema::hasColumn('role_permission', 'role_id')) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            }
        });
    }
};
