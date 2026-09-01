<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionAuditService;

class PermissionsReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:permissions.report']);
    }

    /**
     * Generate and display the comprehensive report.
     */
    public function index(PermissionAuditService $auditService)
    {
        if (! auth()->user()->can('permissions.report') && ! auth()->user()->can('reports.permissions.view')) {
            abort(403, 'غير مصرح لك بالوصول لتقرير الصلاحيات.');
        }
        $gaps = $auditService->auditControllerMethods();
        $roles = Role::withCount('rolePermissions as permissions_count')->get();
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();

        $moduleStats = Permission::selectRaw('module, count(*) as count')
            ->groupBy('module')
            ->orderBy('module')
            ->get();

        if (request()->has('print')) {
            return view('reports.print_permissions_report', compact(
                'gaps',
                'roles',
                'totalPermissions',
                'totalRoles',
                'moduleStats'
            ));
        }

        return view('reports.permissions_report', compact(
            'gaps',
            'roles',
            'totalPermissions',
            'totalRoles',
            'moduleStats'
        ));
    }

    public function autoRegister(PermissionAuditService $auditService)
    {
        $this->authorize('permissions.report');
        $gaps = $auditService->auditControllerMethods();
        $count = 0;

        foreach ($gaps as $gap) {
            $module = explode('.', $gap['suggested_slug'])[0];
            $perm = Permission::firstOrNew(['slug' => $gap['suggested_slug']]);
            if (! $perm->exists) {
                $perm->name = $gap['suggested_name'];
                $perm->module = $module;
            }
            $perm->controller_class = $gap['controller'];
            $perm->controller_method = $gap['method'];
            $perm->save();
            $count++;
        }

        return redirect()->back()->with('success', "تم تسجيل {$count} صلاحية مفقودة آلياً بنجاح.");
    }
}
