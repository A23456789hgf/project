<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PermissionAutomationService
{
    /**
     * Auto-register missing permissions from controller gap array.
     */
    public function autoRegisterGaps(array $gaps): int
    {
        $count = 0;
        foreach ($gaps as $gap) {
            DB::beginTransaction();
            try {
                Permission::firstOrCreate(
                    ['slug' => $gap['suggested_slug']],
                    [
                        'name' => $gap['suggested_name'],
                        'controller_class' => $gap['controller'],
                        'controller_method' => $gap['method'],
                        'module' => Str::before($gap['suggested_slug'], '.'),
                        'auto_registered' => true,
                    ]
                );
                $count++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to auto-register permission: '.$e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Auto-register missing route-based permissions (slugs used in middleware but absent in DB).
     */
    public function autoRegisterRouteGaps(array $routeGaps): int
    {
        $count = 0;
        foreach ($routeGaps as $gap) {
            if ($gap['exists_in_db']) {
                continue;
            }

            DB::beginTransaction();
            try {
                $slug = $gap['permission_slug'];
                $module = Str::before($slug, '.');
                $action = Str::after($slug, '.');

                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::title($action).' '.Str::title($module),
                        'module' => $module,
                        'auto_registered' => true,
                    ]
                );
                $count++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to auto-register route permission: '.$e->getMessage());
            }
        }

        return $count;
    }

    // ─────────────────────────────────────────────
    // Batch apply permissions to roles
    // ─────────────────────────────────────────────

    /**
     * Bulk-apply an explicit list of permission IDs to a list of role IDs.
     * Skips duplicates via insertOrIgnore.
     */
    public function batchApplyToRoles(array $roleIds, array $permissionIds): int
    {
        $now = now();
        $insertData = [];

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permId) {
                $insertData[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (empty($insertData)) {
            return 0;
        }

        RolePermission::insertOrIgnore($insertData);

        $this->logBulkAction('bulk_apply', $roleIds, $permissionIds);

        return count($insertData);
    }

    // ─────────────────────────────────────────────
    // Bulk revoke  (NEW)
    // ─────────────────────────────────────────────

    /**
     * Bulk-revoke an explicit list of permission IDs from a list of role IDs.
     */
    public function revokeFromRoles(array $roleIds, array $permissionIds): int
    {
        if (empty($roleIds) || empty($permissionIds)) {
            return 0;
        }

        $deleted = RolePermission::whereIn('role_id', $roleIds)
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        $this->logBulkAction('bulk_revoke', $roleIds, $permissionIds);

        return $deleted;
    }

    // ─────────────────────────────────────────────
    // Module-level bulk operations  (NEW)
    // ─────────────────────────────────────────────

    /**
     * Grant every permission in a module to the given roles.
     */
    public function applyFullModuleToRoles(array $roleIds, string $module): int
    {
        $permissionIds = Permission::where('module', $module)->pluck('id')->toArray();
        if (empty($permissionIds)) {
            return 0;
        }

        return $this->batchApplyToRoles($roleIds, $permissionIds);
    }

    /**
     * Revoke all permissions in a module from the given roles.
     */
    public function revokeModuleFromRoles(array $roleIds, string $module): int
    {
        if (empty($roleIds)) {
            return 0;
        }

        $permissionIds = Permission::where('module', $module)->pluck('id')->toArray();
        if (empty($permissionIds)) {
            return 0;
        }

        return $this->revokeFromRoles($roleIds, $permissionIds);
    }

    // ─────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────

    private function logBulkAction(string $action, array $roleIds, array $permissionIds): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => 'RolePermission',
                'model_id' => 0,
                'new_values' => ['role_ids' => $roleIds, 'permission_ids' => $permissionIds],
                'description' => "عملية مجمعة ({$action}): ".count($roleIds).' أدوار × '.count($permissionIds).' صلاحية',
                'ip_address' => request()->ip(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Could not write audit log for bulk action: '.$e->getMessage());
        }
    }
}
