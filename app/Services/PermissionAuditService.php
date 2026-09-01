<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class PermissionAuditService
{
    // ─────────────────────────────────────────────
    // 1. Controller-method gaps  (original + improved)
    // ─────────────────────────────────────────────

    /**
     * Scan all controllers to identify methods without a registered permission.
     */
    public function auditControllerMethods(): array
    {
        $controllers = $this->getControllerFiles();
        $gaps = [];
        $existingPermissions = Permission::all()->groupBy('controller_class');

        // Helper methods that are _not_ route actions – skip them
        $skipMethods = [
            'categorizePermissions', 'getModuleSortOrder', 'getModuleTranslations',
            'getModuleConfigs', 'suggestSlug', 'suggestName', 'getControllerFiles',
            '__invoke', 'middleware', 'authorize', 'validate', 'callAction',
            'authorizeForUser', 'authorizeResource', 'validateWith', 'validateWithBag',
            'getMiddleware', 'setMiddleware', 'getMethods', 'getMethod', 'getAction',
            'getRouter', 'setRouter', 'callAction', 'missingMethod', 'handle',
        ];

        foreach ($controllers as $controllerClass) {
            if (! class_exists($controllerClass)) {
                continue;
            }

            $reflection = new ReflectionClass($controllerClass);
            if ($reflection->isAbstract()) {
                continue;
            }

            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $classPermissions = $existingPermissions->get($controllerClass, collect());

            foreach ($methods as $method) {
                if ($method->isConstructor()) {
                    continue;
                }
                if (Str::startsWith($method->getName(), '__')) {
                    continue;
                }
                if ($method->getDeclaringClass()->getName() === 'Illuminate\Routing\Controller') {
                    continue;
                }
                if (in_array($method->getName(), $skipMethods)) {
                    continue;
                }

                $methodName = $method->getName();
                $permissionForMethod = $classPermissions->firstWhere('controller_method', $methodName);

                if (! $permissionForMethod) {
                    $gaps[] = [
                        'controller' => $controllerClass,
                        'method' => $methodName,
                        'suggested_slug' => $this->suggestSlug($controllerClass, $methodName),
                        'suggested_name' => $this->suggestName($methodName, $controllerClass),
                    ];
                }
            }
        }

        return $gaps;
    }

    // ─────────────────────────────────────────────
    // 2. Route-middleware gaps  (NEW)
    // ─────────────────────────────────────────────

    /**
     * Scan all named routes for `permission:xxx` middleware and report
     * which slugs are NOT present in the permissions table.
     *
     * Returns array of:
     *   [ 'route_name', 'uri', 'method', 'permission_slug', 'exists_in_db' ]
     */
    public function auditRoutes(): array
    {
        $allRoutes = Route::getRoutes()->getRoutes();
        $existingSlugs = Permission::pluck('slug')->toArray();
        $results = [];

        foreach ($allRoutes as $route) {
            $middlewareList = $route->middleware();

            foreach ($middlewareList as $mw) {
                if (! Str::startsWith($mw, 'permission:')) {
                    continue;
                }

                $slug = Str::after($mw, 'permission:');
                $existsInDb = in_array($slug, $existingSlugs);

                $results[] = [
                    'route_name' => $route->getName() ?: '(unnamed)',
                    'uri' => $route->uri(),
                    'http_method' => implode('|', $route->methods()),
                    'permission_slug' => $slug,
                    'exists_in_db' => $existsInDb,
                ];
            }
        }

        // Sort: missing first
        usort($results, fn ($a, $b) => $a['exists_in_db'] <=> $b['exists_in_db']);

        return $results;
    }

    // ─────────────────────────────────────────────
    // 3. Per-role coverage report  (NEW)
    // ─────────────────────────────────────────────

    /**
     * For every role, return per-module coverage stats.
     *
     * Returns:
     *   [
     *     role_id => [
     *       'role'    => Role,
     *       'modules' => [
     *         module_name => ['total' => N, 'assigned' => M, 'pct' => float, 'status' => 'none|partial|full']
     *       ],
     *       'total_permissions' => N,
     *       'assigned'          => M,
     *       'overall_pct'       => float,
     *     ]
     *   ]
     */
    public function perRoleCoverageReport(): array
    {
        $roles = Role::with('permissions')->get();
        $allPermissions = Permission::all();
        $totalAll = $allPermissions->count();

        // Group all permissions by module
        $byModule = $allPermissions->groupBy('module');

        $report = [];

        foreach ($roles as $role) {
            $assignedIds = RolePermission::where('role_id', $role->id)->pluck('permission_id')->toArray();
            $assignedIds = array_map('intval', $assignedIds);

            $moduleStats = [];
            foreach ($byModule as $module => $perms) {
                $total = $perms->count();
                $assigned = $perms->filter(fn ($p) => in_array((int) $p->id, $assignedIds))->count();
                $pct = $total > 0 ? round(($assigned / $total) * 100, 1) : 0;
                $status = $assigned === 0 ? 'none' : ($assigned === $total ? 'full' : 'partial');

                $moduleStats[$module] = compact('total', 'assigned', 'pct', 'status');
            }

            $totalAssigned = count($assignedIds);
            $overallPct = $totalAll > 0 ? round(($totalAssigned / $totalAll) * 100, 1) : 0;

            $report[$role->id] = [
                'role' => $role,
                'modules' => $moduleStats,
                'total_permissions' => $totalAll,
                'assigned' => $totalAssigned,
                'overall_pct' => $overallPct,
            ];
        }

        return $report;
    }

    // ─────────────────────────────────────────────
    // 4. Entity visibility matrix  (NEW)
    // ─────────────────────────────────────────────

    /**
     * Return a matrix showing admin_scope + geo_scope for each role × module combination.
     *
     * Returns:
     *   [
     *     role_id => [
     *       'role' => Role,
     *       'scopes' => [
     *         module_name => ['admin' => string, 'geo' => string, 'entity_display' => ..., 'entity_add' => ...]
     *       ]
     *     ]
     *   ]
     */
    public function entityVisibilityMatrix(): array
    {
        $roles = Role::all();
        $modules = Permission::pluck('module')->unique()->sort()->values();

        $matrix = [];

        foreach ($roles as $role) {
            $moduleScopes = is_array($role->module_scopes) ? $role->module_scopes : [];
            $geoScopes = is_array($role->module_geo_scopes) ? $role->module_geo_scopes : [];
            $displayScope = is_array($role->entity_display_scope) ? $role->entity_display_scope : [];
            $addScope = is_array($role->entity_add_scope) ? $role->entity_add_scope : [];

            $scopeRows = [];
            foreach ($modules as $mod) {
                $scopeRows[$mod] = [
                    'admin' => $moduleScopes[$mod] ?? 'not_set',
                    'geo' => $geoScopes[$mod] ?? 'not_set',
                    'entity_display' => $displayScope[$mod] ?? null,
                    'entity_add' => $addScope[$mod] ?? null,
                    'full_access' => (bool) $role->full_access,
                ];
            }

            // Also add top-level entity scopes
            $scopeRows['__internal_entities'] = [
                'admin' => 'N/A',
                'geo' => 'N/A',
                'entity_display' => $displayScope['internal_entities'] ?? 'not_set',
                'entity_add' => $addScope['internal_entities'] ?? 'not_set',
                'full_access' => (bool) $role->full_access,
            ];
            $scopeRows['__authorities'] = [
                'admin' => 'N/A',
                'geo' => 'N/A',
                'entity_display' => $displayScope['authorities'] ?? 'not_set',
                'entity_add' => $addScope['authorities'] ?? 'not_set',
                'full_access' => (bool) $role->full_access,
            ];

            $matrix[$role->id] = [
                'role' => $role,
                'scopes' => $scopeRows,
            ];
        }

        return $matrix;
    }

    // ─────────────────────────────────────────────
    // 5. Aggregate summary stats  (NEW)
    // ─────────────────────────────────────────────

    /**
     * Single call returning all high-level numbers for the dashboard.
     */
    public function summaryStats(): array
    {
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();
        $totalRolePerms = RolePermission::count();
        $routeAudit = $this->auditRoutes();
        $routeGapsCount = collect($routeAudit)->where('exists_in_db', false)->count();
        $controllerGaps = $this->auditControllerMethods();

        // Average coverage across roles
        $coverageReport = $this->perRoleCoverageReport();
        $avgCoverage = count($coverageReport) > 0
            ? round(collect($coverageReport)->avg('overall_pct'), 1)
            : 0;

        // Roles with 0 permissions assigned
        $rolesWithNoPerms = Role::whereDoesntHave('permissions')->count();

        return [
            'total_permissions' => $totalPermissions,
            'total_roles' => $totalRoles,
            'total_role_perms' => $totalRolePerms,
            'route_gaps' => $routeGapsCount,
            'controller_gaps' => count($controllerGaps),
            'avg_coverage_pct' => $avgCoverage,
            'roles_with_no_perms' => $rolesWithNoPerms,
        ];
    }

    // ─────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────

    private function getControllerFiles(): array
    {
        $path = app_path('Http/Controllers');
        $controllers = [];

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }
            if (Str::endsWith($file->getFilename(), '.bak')) {
                continue;
            }

            $relativePath = str_replace([$path, '.php', '/'], ['', '', '\\'], $file->getPathname());
            $class = 'App\\Http\\Controllers'.$relativePath;

            // Skip backup files
            if (Str::contains($class, ' copy') || Str::contains($class, '.bak')) {
                continue;
            }

            $controllers[] = $class;
        }

        return $controllers;
    }

    private function suggestSlug(string $class, string $method): string
    {
        $module = Str::kebab(Str::replace('Controller', '', class_basename($class)));

        return "{$module}.{$method}";
    }

    private function suggestName(string $method, string $class): string
    {
        $action = Str::title($method);
        $module = Str::title(Str::replace('Controller', '', class_basename($class)));

        return "{$action} {$module}";
    }
}
