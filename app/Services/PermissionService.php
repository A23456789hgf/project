<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    /**
     * Get all permissions assigned to a specific role.
     */
    public function getRolePermissions(string $role)
    {
        return Permission::whereHas('rolePermissions', function ($query) use ($role) {
            $query->where('role', $role);
        })->get();
    }

    /**
     * Assign a permission to a role.
     */
    public function assignPermissionToRole(string $role, string $permissionSlug)
    {
        $permission = Permission::where('slug', $permissionSlug)->firstOrFail();

        return RolePermission::firstOrCreate([
            'role' => $role,
            'permission_id' => $permission->id,
        ]);
    }

    /**
     * Revoke a permission from a role.
     */
    public function revokePermissionFromRole(string $role, string $permissionSlug)
    {
        $permission = Permission::where('slug', $permissionSlug)->firstOrFail();

        return RolePermission::where('role', $role)
            ->where('permission_id', $permission->id)
            ->delete();
    }

    /**
     * Sync permissions for a role.
     */
    public function syncRolePermissions(string $role, array $permissionSlugs)
    {
        // Remove current permissions
        RolePermission::where('role', $role)->delete();

        // Add new permissions
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();

        foreach ($permissions as $permission) {
            RolePermission::create([
                'role' => $role,
                'permission_id' => $permission->id,
            ]);
        }
    }

    /**
     * Get all permissions for a user across all their roles.
     */
    public function getUserPermissions(User $user)
    {
        return $user->permissions();
    }

    /**
     * Check if a user has a specific permission.
     */
    public function checkUserPermission(User $user, string $permissionSlug)
    {
        return $user->hasPermission($permissionSlug);
    }

    /**
     * Get all permissions grouped by module.
     */
    public function getPermissionsByModule()
    {
        return Permission::all()->groupBy('module');
    }

    /**
     * Get auto-registered permissions.
     *
     * @return Collection
     */
    public function getAutoRegisteredPermissions()
    {
        return Permission::where('auto_registered', true)->get();
    }

    /**
     * Get manually created permissions.
     *
     * @return Collection
     */
    public function getManualPermissions()
    {
        return Permission::where('auto_registered', false)->get();
    }

    /**
     * Get permissions for a specific controller.
     *
     * @return Collection
     */
    public function getPermissionsByController(string $controllerClass)
    {
        return Permission::where('controller_class', $controllerClass)->get();
    }

    /**
     * Find orphaned auto-registered permissions (no longer in routes).
     *
     * @param  array  $currentSlugs  Array of slugs currently in routes
     * @return Collection
     */
    public function findOrphanedPermissions(array $currentSlugs)
    {
        return Permission::where('auto_registered', true)
            ->whereNotIn('slug', $currentSlugs)
            ->get();
    }

    /**
     * Remove orphaned permissions.
     *
     * @param  array  $currentSlugs  Array of slugs currently in routes
     * @return int Number of removed permissions
     */
    public function removeOrphanedPermissions(array $currentSlugs): int
    {
        return Permission::where('auto_registered', true)
            ->whereNotIn('slug', $currentSlugs)
            ->delete();
    }

    /**
     * Update or create permission from discovered data.
     */
    public function syncPermission(array $permissionData): Permission
    {
        return Permission::updateOrCreate(
            ['slug' => $permissionData['slug']],
            [
                'name' => $permissionData['name'],
                'description' => $permissionData['description'],
                'module' => $permissionData['module'],
                'auto_registered' => $permissionData['auto_registered'] ?? true,
                'controller_class' => $permissionData['controller_class'] ?? null,
                'controller_method' => $permissionData['controller_method'] ?? null,
            ]
        );
    }

    /**
     * Get permissions statistics.
     */
    public function getStatistics(): array
    {
        $total = Permission::count();
        $auto = Permission::where('auto_registered', true)->count();
        $manual = Permission::where('auto_registered', false)->count();

        return [
            'total' => $total,
            'auto_registered' => $auto,
            'manual' => $manual,
            'by_module' => Permission::select('module')
                ->selectRaw('count(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module')
                ->toArray(),
        ];
    }
}
