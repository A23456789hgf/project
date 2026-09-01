<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Retrieve the admin role
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'مدير النظام')->first();

        if (! $adminRole) {
            // If the role doesn't exist yet, we can create it to ensure stability
            $adminRole = Role::create([
                'name' => 'مدير النظام',
                'description' => 'مدير النظام الأساسي ذو الصلاحيات الكاملة',
                'is_active' => true,
                'full_access' => true,
            ]);
        }

        // Get all system permissions
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            return;
        }

        // Fetch existing permissions assigned to the admin role
        $existingPermissionIds = RolePermission::where('role_id', $adminRole->id)
            ->pluck('permission_id')
            ->toArray();

        $now = now();
        $insertData = [];

        foreach ($allPermissions as $permission) {
            if (! in_array($permission->id, $existingPermissionIds)) {
                $insertData[] = [
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($insertData)) {
            RolePermission::insert($insertData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'مدير النظام')->first();

        if ($adminRole) {
            RolePermission::where('role_id', $adminRole->id)->delete();
        }
    }
};
