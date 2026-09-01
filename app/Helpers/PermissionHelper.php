<?php

namespace App\Helpers;

use Illuminate\Support\Facade;

class PermissionHelper extends Facade
{
    /**
     * Check if user has permission for a module action
     *
     * @param  string  $module
     * @param  string  $action
     * @return bool
     */
    public static function hasModulePermission($module, $action)
    {
        // Check in role permissions (1st priority)
        $userRoles = auth()->user()->roles;
        $permissionKeys = implode(',', array_map(fn ($role) => "{$module}.{$action}.role.{$role}", $userRoles ?? []));
        if (auth()->user()->hasPermissions($permissionKeys)) {
            return true;
        }

        // Check in global permissions (if role-based not found)
        return auth()->user()->hasPermission("{$module}.{$action}");
    }
}
