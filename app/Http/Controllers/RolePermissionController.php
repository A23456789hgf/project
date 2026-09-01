<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolePermissionController extends Controller
{
    /**
     * عرض مصفوفة الأدوار والصلاحيات مع التصنيف ونطاقات الجهات.
     */
    public function index(Request $request)
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        $selectedRole = null;
        $rolePermissions = [];

        // الترجمة العربية للوحدات
        $moduleTranslations = [
            'authentication' => 'لوحة التحكم والاتصال',
            'projects' => 'إدارة المشاريع',
            'project-drafts' => 'مسودات المشاريع',
            'project-files' => 'ملفات المشروع',
            'project-risks' => 'مخاطر المشروع',
            'project-outputs' => 'مخرجات المشروع',
            'project-activity' => 'نشاط وسجل المشروع',
            'projects-implementation' => 'مشاريع التنفيذ',
            'executive-activities' => 'الأنشطة التنفيذية',
            'erp-integration' => 'التكامل مع ERPNext',
            'execution' => 'التنفيذ والمتابعة',
            'execution-procedures' => 'تنفيذ الإجراءات',
            'schedule' => 'الجدول الزمني',
            'quality' => 'إدارة الجودة',
            'financial-justifications' => 'المبررات المالية',
            'procedure-budget-justifications' => 'مبررات الإنفاق الإضافي',
            'project-documents' => 'مستندات المشروع',
            'approvals' => 'سير عمل الموافقات',
            'main-routers' => 'المحاور الرئيسية',
            'sub-routers' => 'المحاور الفرعية',
            'stages' => 'مراحل المشروع',
            'programs' => 'البرامج',
            'domains' => 'المجالات',
            'priorities' => 'الأولويات',
            'donors' => 'الجهات المانحة',
            'financing-types' => 'أنواع التمويل',
            'financing-forms' => 'نماذج التمويل',
            'governorates' => 'المحافظات',
            'villages' => 'القرى',
            'authorities' => 'الجهات الإشرافية',
            'beneficiaries' => 'المستفيدون',
            'users' => 'إدارة المستخدمين',
            'roles-permissions' => 'الأدوار والصلاحيات',
            'audit-logs' => 'سجلات العمليات',
            'configuration' => 'إعدادات النظام',
            'associations' => 'الجمعيات',
            'beneficiary-groups' => 'مجموعات المستفيدين',
            'directorates' => 'المديريات',
            'entities' => 'الجهات',
            'entity-father' => 'الجهات المرجعية',
            'entity-scopes' => 'نطاقات الجهات',
            'executors' => 'المنفذون',
            'financial-items' => 'البنود المالية',
            'funded-entities' => 'الجهات الممولة',
            'funding-sources' => 'مصادر التمويل',
            'interventions' => 'التدخلات',
            'participation' => 'المشاركة',
            'sub-areas' => 'المناطق الفرعية',
            'subdomains' => 'المجالات الفرعية',
            'subfinancing-forms' => 'نماذج التمويل الفرعية',
            'supervisors' => 'المشرفون',
            'target-categories' => 'الفئات المستهدفة',
            'units' => 'الوحدات',
            'supervising-entities' => 'الجهات الإشرافية',
            'project-drafts-enhanced' => 'مسودات المشاريع المحسّنة',
            'referrals' => 'إدارة الإحالات',
            'reports' => 'التقارير: لوحة التحكم الموحدة',
            'reports-implementation' => 'التقارير: ملخص التنفيذ الميداني',
            'reports-quality' => 'التقارير: مقاييس الجودة',
            'reports-financial' => 'التقارير: المؤشرات المالية للمشاريع',
            'reports-progress' => 'التقارير: تتبع الإنجاز والتقدم الزمني',
            'reports-erpnext-financial' => 'التقارير: التقرير المالي ERPNext',
            'reports-pl-expense-summary' => 'التقارير: ملخص الإيرادات والنفقات',
            'reports-profit-and-loss' => 'التقارير: الأرباح والخسائر',
            'reports-official-summary' => 'التقارير: التقرير الرسمي الشامل',
            'reports-stakeholders' => 'التقارير: أصحاب المصلحة والجهات',
            'reports-permissions' => 'التقارير: مراجعة صلاحيات النظام',
            'planning' => 'إدارة التخطيط',
            'correspondence' => 'المراسلات بين الأطراف',
            'department-reports' => 'التقارير بين الإدارات',
            'messaging' => 'الرسائل بين الإدارات',
        ];

        $structure = [];

        if ($request->has('role_id')) {
            $selectedRole = Role::find($request->role_id);
            if ($selectedRole) {
                $rolePermissions = RolePermission::where('role_id', $selectedRole->id)
                    ->pluck('permission_id')->toArray();

                $allPermissions = Permission::orderBy('module')->get();

                $user = auth()->user();

                foreach ($allPermissions as $perm) {
                    $module = $perm->module;
                    $type = 'actions';

                    if (str_contains($perm->slug, 'sidebar')) {
                        $type = 'sidebar';
                    } elseif (str_contains($perm->slug, 'view') && ! str_contains($perm->slug, 'own') && ! str_contains($perm->slug, 'all')) {
                        $type = 'pages';
                    } elseif (str_contains($perm->slug, 'view-own') || str_contains($perm->slug, 'view-all')) {
                        $type = 'scopes';
                    }

                    // إضافة نطاق عرض الجهات لكل صلاحية نوع "scopes"
                    if ($type === 'scopes') {
                        $perm->scope_options = [
                            'none' => '🚫 لا يعرض شيئاً',
                            'all' => '🌐 جميع الجهات',
                        ];

                        if ($user) {
                            if ($user->governorate_id && ! $user->directorate_id) {
                                $perm->scope_options['governorate'] = '🏙️ جهات المحافظة فقط';
                            } elseif ($user->directorate_id) {
                                $perm->scope_options['directorate'] = '🏢 جهات المديرية فقط';
                            }
                        }

                        // تعيين القيمة الافتراضية
                        $perm->selected_scope = in_array($perm->id, $rolePermissions) ? 'all' : 'none';
                    }

                    $structure[$module][$type][] = $perm;
                }
            }
        }

        ksort($structure);

        $categorized = [
            'sidebars' => [],
            'pages' => [],
            'actions' => [],
            'scopes' => [],
        ];

        foreach ($structure as $module => $types) {
            foreach ($types as $type => $perms) {
                $category = match ($type) {
                    'sidebar' => 'sidebars',
                    'pages' => 'pages',
                    'scopes' => 'scopes',
                    default => 'actions',
                };
                $categorized[$category][$module] = $perms;
            }
        }

        return view('roles-permissions.index', compact(
            'roles',
            'selectedRole',
            'categorized',
            'rolePermissions',
            'moduleTranslations'
        ));
    }

    /**
     * تحديث صلاحيات الدور.
     */
    public function update(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'scopes' => 'nullable|array', // إضافة نطاقات الجهات
        ]);

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($request->role_id);
            $role->update(['full_access' => $request->boolean('full_access')]);

            RolePermission::where('role_id', $role->id)->delete();

            $permissionIds = $request->input('permissions', []);
            $scopeOptions = $request->input('scopes', []); // نطاقات الجهات

            if (! empty($permissionIds)) {
                $now = now();
                $newPermissions = [];

                foreach ($permissionIds as $permId) {
                    $newPermissions[] = [
                        'role_id' => $role->id,
                        'permission_id' => $permId,
                        'scope' => $scopeOptions[$permId] ?? null, // حفظ نطاق العرض
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                RolePermission::insert($newPermissions);
            }

            DB::commit();
            User::incrementRolePermissionsVersion($role->id);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_role_permissions',
                'model_type' => 'Role',
                'model_id' => $role->id,
                'description' => 'تم تحديث صلاحيات الدور: '.$role->name,
                'ip_address' => $request->ip(),
            ]);

            Log::info("Role permissions updated for role {$role->name} (ID: {$role->id}) by user ID ".auth()->id());

            return redirect()->route('roles-permissions.index', ['role_id' => $role->id])
                ->with('success', 'تم تحديث صلاحيات الدور بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update role permissions (ID: {$role->id}): ".$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حفظ الصلاحيات: '.$e->getMessage());
        }
    }
}
