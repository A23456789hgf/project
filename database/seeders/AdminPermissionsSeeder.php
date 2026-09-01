<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class AdminPermissionsSeeder extends Seeder
{
    /**
     * Seed the Admin role with all permissions.
     */
    public function run()
    {
        $adminRole = Role::where('name', 'Admin')->orWhere('name', 'مدير النظام')->first();

        if (! $adminRole) {
            $this->command->error('Admin role not found!');

            return;
        }

        // Delete existing permissions for Admin
        RolePermission::where('role_id', $adminRole->id)->delete();

        // Get all permissions
        $allPermissions = Permission::all();

        if ($allPermissions->isEmpty()) {
            $this->command->error('No permissions found! Run PermissionsSeeder first.');

            return;
        }

        $now = now();
        $permissionsData = [];

        foreach ($allPermissions as $permission) {
            $permissionsData[] = [
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        RolePermission::insert($permissionsData);

        $this->command->info("✓ Assigned {$allPermissions->count()} permissions to Admin role");
    }
}
