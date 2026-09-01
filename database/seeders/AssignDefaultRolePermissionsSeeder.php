<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignDefaultRolePermissionsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Assigning default permissions to roles...');

        $configPermissions = config('permissions.value_chains', []);

        // collect all slugs
        $allSlugs = array_map(function ($p) {
            return $p['slug'] ?? null;
        }, $configPermissions);
        $allSlugs = array_filter($allSlugs);

        // viewer: only page-type and print actions
        $viewerSlugs = [];
        foreach ($configPermissions as $p) {
            if (! isset($p['slug'])) {
                continue;
            }
            $type = $p['type'] ?? null;
            $slug = $p['slug'];
            if ($type === 'page' || str_contains($slug, '.print')) {
                $viewerSlugs[] = $slug;
            }
        }

        // manager: all slugs under value_chains
        $managerSlugs = $allSlugs;

        // Ensure roles exist
        $manager = Role::firstOrCreate(['name' => 'manager'], ['description' => 'Default manager role', 'is_active' => true]);
        $viewer = Role::firstOrCreate(['name' => 'viewer'], ['description' => 'Default viewer role', 'is_active' => true]);

        // helper to sync by role id
        $sync = function (Role $role, array $slugs) {
            $permissionIds = Permission::whereIn('slug', $slugs)->pluck('id')->toArray();
            DB::table('role_permission')->where('role_id', $role->id)->delete();
            $rows = [];
            $now = date('Y-m-d H:i:s');
            foreach ($permissionIds as $pid) {
                $rows[] = ['role_id' => $role->id, 'permission_id' => $pid, 'created_at' => $now, 'updated_at' => $now];
            }
            if (! empty($rows)) {
                DB::table('role_permission')->insert($rows);
            }

            return count($rows);
        };

        $countManager = $sync($manager, $managerSlugs);
        $this->command->info("Assigned {$countManager} permissions to role 'manager'.");

        $countViewer = $sync($viewer, $viewerSlugs);
        $this->command->info("Assigned {$countViewer} permissions to role 'viewer'.");
    }
}
