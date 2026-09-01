<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Services\PermissionAuditService;
use App\Services\PermissionAutomationService;
use Illuminate\Http\Request;

class PermissionMatrixAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ─────────────────────────────────────────────
    // GET /permissions-audit   — main audit dashboard
    // ─────────────────────────────────────────────
    public function index(PermissionAuditService $auditService)
    {
        $this->authorize('permissions.audit');
        $stats = $auditService->summaryStats();
        $routeAudit = $auditService->auditRoutes();
        $controllerGaps = $auditService->auditControllerMethods();
        $coverageReport = $auditService->perRoleCoverageReport();
        $visibilityMatrix = $auditService->entityVisibilityMatrix();

        $roles = Role::withCount('permissions')->orderBy('name')->get();
        $totalPermissions = Permission::count();
        $moduleStats = Permission::selectRaw('module, count(*) as count')
            ->groupBy('module')
            ->orderBy('module')
            ->get();

        // All unique module names (sorted by count desc)
        $allModules = Permission::pluck('module')->unique()->sort()->values();

        return view('reports.permissions_audit', compact(
            'stats',
            'routeAudit',
            'controllerGaps',
            'coverageReport',
            'visibilityMatrix',
            'roles',
            'totalPermissions',
            'moduleStats',
            'allModules'
        ));
    }

    // ─────────────────────────────────────────────
    // GET /permissions-audit/matrix  — full role×perm matrix
    // ─────────────────────────────────────────────
    public function matrix()
    {
        $this->authorize('permissions.audit');
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get();

        // Preload all role_permissions as [role_id][permission_id] = true
        $assigned = [];
        RolePermission::select('role_id', 'permission_id')->get()->each(function ($rp) use (&$assigned) {
            $assigned[$rp->role_id][$rp->permission_id] = true;
        });

        $modules = $permissions->pluck('module')->unique()->sort()->values();

        return view('reports.permissions_matrix_full', compact('roles', 'permissions', 'assigned', 'modules'));
    }

    // ─────────────────────────────────────────────
    // POST /permissions-audit/apply  — bulk apply
    // ─────────────────────────────────────────────
    public function bulkApply(Request $request, PermissionAutomationService $autoService)
    {
        $this->authorize('roles.edit');
        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'integer|exists:roles,id',
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $count = $autoService->batchApplyToRoles(
            $request->input('role_ids'),
            $request->input('permission_ids')
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'applied' => $count]);
        }

        session()->flash('success', "تم تطبيق {$count} صلاحية على الأدوار المحددة بنجاح.");

        return redirect()->route('permissions.audit.index');
    }

    // ─────────────────────────────────────────────
    // POST /permissions-audit/revoke  — bulk revoke
    // ─────────────────────────────────────────────
    public function bulkRevoke(Request $request, PermissionAutomationService $autoService)
    {
        $this->authorize('roles.edit');
        $request->validate([
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'integer|exists:roles,id',
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $count = $autoService->revokeFromRoles(
            $request->input('role_ids'),
            $request->input('permission_ids')
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'revoked' => $count]);
        }

        session()->flash('success', "تم إلغاء {$count} صلاحية من الأدوار المحددة بنجاح.");

        return redirect()->route('permissions.audit.index');
    }

    // ─────────────────────────────────────────────
    // POST /permissions-audit/auto-register  — register missing route permissions
    // ─────────────────────────────────────────────
    public function autoRegister(Request $request, PermissionAuditService $auditService, PermissionAutomationService $autoService)
    {
        $this->authorize('roles.edit');
        $type = $request->input('type', 'routes'); // 'routes' | 'controllers'

        if ($type === 'routes') {
            $gaps = $auditService->auditRoutes();
            $count = $autoService->autoRegisterRouteGaps($gaps);
        } else {
            $gaps = $auditService->auditControllerMethods();
            $count = $autoService->autoRegisterGaps($gaps);
        }

        // Optionally also apply to roles provided
        if ($request->filled('apply_to_role_ids')) {
            $roleIds = (array) $request->input('apply_to_role_ids');
            $newPermIds = Permission::where('auto_registered', true)->pluck('id')->toArray();
            $autoService->batchApplyToRoles($roleIds, $newPermIds);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'registered' => $count]);
        }

        session()->flash('success', "تم تسجيل {$count} صلاحية جديدة في قاعدة البيانات.");

        return redirect()->route('permissions.audit.index');
    }

    // ─────────────────────────────────────────────
    // GET /permissions-audit/export  — JSON export
    // ─────────────────────────────────────────────
    public function export(PermissionAuditService $auditService)
    {
        $this->authorize('permissions.audit');
        $report = [
            'generated_at' => now()->toDateTimeString(),
            'summary' => $auditService->summaryStats(),
            'route_audit' => $auditService->auditRoutes(),
            'controller_gaps' => $auditService->auditControllerMethods(),
            'coverage_report' => collect($auditService->perRoleCoverageReport())->map(function ($row) {
                return [
                    'role' => $row['role']->only(['id', 'name', 'is_active', 'full_access']),
                    'overall_pct' => $row['overall_pct'],
                    'assigned' => $row['assigned'],
                    'modules' => $row['modules'],
                ];
            })->values(),
            'visibility_matrix' => collect($auditService->entityVisibilityMatrix())->map(function ($row) {
                return [
                    'role' => $row['role']->only(['id', 'name']),
                    'scopes' => $row['scopes'],
                ];
            })->values(),
        ];

        return response()->json($report, 200, [
            'Content-Disposition' => 'attachment; filename="permissions_audit_'.now()->format('Y-m-d_His').'.json"',
        ]);
    }
}
