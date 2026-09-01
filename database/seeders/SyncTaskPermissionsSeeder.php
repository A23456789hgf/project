<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncTaskPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting SyncTaskPermissionsSeeder...');
        echo "--- RUNNING PERMISSIONS MATRIX SYNC ---\n";

        // 1. Run the base PermissionsSeeder to reload matrix permissions and update their modules
        $seeder = new PermissionsSeeder;
        $seeder->run();

        echo "Permissions synchronized from matrix.\n";

        // 2. Identify roles that should receive standard task permissions
        // We target:
        // - Specific role 'وكلاء القطاعات' (ID 50)
        // - Any role that already has the 'tasks.sidebar' permission
        $sidebarPermission = Permission::where('slug', 'tasks.sidebar')->first();
        $targetRoleIds = [];

        if ($sidebarPermission) {
            $targetRoleIds = DB::table('role_permission')
                ->where('permission_id', $sidebarPermission->id)
                ->pluck('role_id')
                ->toArray();
        }

        // Always include role ID 50 if it exists
        $sectorRole = Role::find(50);
        if ($sectorRole && ! in_array(50, $targetRoleIds)) {
            $targetRoleIds[] = 50;
        }

        $targetRoleIds = array_unique($targetRoleIds);

        if (empty($targetRoleIds)) {
            echo "No roles found with 'tasks.sidebar' permission.\n";

            return;
        }

        // 3. Fetch all task-related permissions in the tasks module
        $taskPermissions = Permission::where('module', 'tasks')
            ->orWhere('slug', 'like', 'task.%')
            ->get();

        echo 'Found '.$taskPermissions->count()." task-related permissions to map.\n";

        // 4. Map them to target roles
        $now = now();
        $countAssigned = 0;

        foreach ($targetRoleIds as $roleId) {
            $role = Role::find($roleId);
            if (! $role) {
                continue;
            }

            echo "Syncing task permissions for Role: {$role->name} (ID: {$role->id})...\n";

            foreach ($taskPermissions as $perm) {
                // Insert if not already assigned
                $exists = DB::table('role_permission')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $perm->id)
                    ->exists();

                if (! $exists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $perm->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $countAssigned++;
                }
            }

            // 5. Invalidate the user permission cache version for this role
            User::incrementRolePermissionsVersion($role->id);
            echo "  Permission cache version incremented for role ID {$role->id}.\n";
        }

        echo "Sync complete. Assigned {$countAssigned} new permission-role associations.\n";
        Log::info("SyncTaskPermissionsSeeder completed successfully. Assigned {$countAssigned} associations.");
    }
}
