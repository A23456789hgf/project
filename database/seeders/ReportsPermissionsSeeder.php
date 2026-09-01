<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ReportsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // ==========================================
            // 1. لوحة التقارير المركزية (Overview Dashboard)
            // ==========================================
            [
                'name' => 'عرض قسم التقارير في القائمة الجانبية',
                'slug' => 'reports.sidebar',
                'module' => 'reports',
                'type' => Permission::TYPE_SIDEBAR,
                'description' => 'إظهار قسم التقارير في القائمة الجانبية',
            ],
            [
                'name' => 'عرض لوحة معلومات التقارير الموحدة',
                'slug' => 'reports.view',
                'module' => 'reports',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض لوحة معلومات تقارير مركز البيانات الموحد',
            ],
            [
                'name' => 'طباعة لوحة معلومات التقارير الموحدة',
                'slug' => 'reports.print',
                'module' => 'reports',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة بيانات لوحة معلومات التقارير الموحدة',
            ],
            [
                'name' => 'تصدير لوحة معلومات التقارير الموحدة',
                'slug' => 'reports.export',
                'module' => 'reports',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير بيانات لوحة معلومات التقارير الموحدة',
            ],
            [
                'name' => 'عرض النظرة الشاملة للتقارير',
                'slug' => 'reports.overview.view',
                'module' => 'reports',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض النظرة الشاملة لتقارير المشاريع',
            ],
            [
                'name' => 'عرض تقرير حالة المشاريع',
                'slug' => 'reports.status.view',
                'module' => 'reports',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير حالة وتوزيع المشاريع',
            ],

            // ==========================================
            // 2. تقرير التنفيذ الميداني (Implementation Report)
            // ==========================================
            [
                'name' => 'عرض تقرير التنفيذ الميداني',
                'slug' => 'reports.implementation.view',
                'module' => 'reports-implementation',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير تنفيذ الأنشطة الميداني للمشاريع',
            ],
            [
                'name' => 'طباعة تقرير التنفيذ الميداني',
                'slug' => 'reports.implementation.print',
                'module' => 'reports-implementation',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير تنفيذ الأنشطة الميداني',
            ],
            [
                'name' => 'تصدير تقرير التنفيذ الميداني',
                'slug' => 'reports.implementation.export',
                'module' => 'reports-implementation',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير تقرير تنفيذ الأنشطة الميداني إلى ملف إكسل',
            ],

            // ==========================================
            // 3. تقرير مقاييس الجودة (Quality Report)
            // ==========================================
            [
                'name' => 'عرض تقرير الجودة',
                'slug' => 'reports.quality.view',
                'module' => 'reports-quality',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير مقاييس وفحوصات الجودة للمشاريع',
            ],
            [
                'name' => 'طباعة تقرير الجودة',
                'slug' => 'reports.quality.print',
                'module' => 'reports-quality',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير مقاييس وفحوصات الجودة',
            ],
            [
                'name' => 'تصدير تقرير الجودة',
                'slug' => 'reports.quality.export',
                'module' => 'reports-quality',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير تقرير مقاييس الجودة إلى ملف إكسل',
            ],

            // ==========================================
            // 4. تقرير المؤشرات المالية للمشاريع (Financial Report)
            // ==========================================
            [
                'name' => 'عرض التقرير المالي للمشاريع',
                'slug' => 'reports.financial.view',
                'module' => 'reports-financial',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير المؤشرات المالية والموازنات للمشاريع',
            ],
            [
                'name' => 'طباعة التقرير المالي للمشاريع',
                'slug' => 'reports.financial.print',
                'module' => 'reports-financial',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير المؤشرات المالية للمشاريع',
            ],
            [
                'name' => 'تصدير التقرير المالي للمشاريع',
                'slug' => 'reports.financial.export',
                'module' => 'reports-financial',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير التقرير المالي للمشاريع إلى ملف إكسل',
            ],

            // ==========================================
            // 5. تقرير التقدم الزمني والإنجاز (Progress Report)
            // ==========================================
            [
                'name' => 'عرض تقرير التقدم الزمني والإنجاز',
                'slug' => 'reports.progress.view',
                'module' => 'reports-progress',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير تتبع التقدم الزمني ونسب الإنجاز',
            ],
            [
                'name' => 'طباعة تقرير التقدم الزمني والإنجاز',
                'slug' => 'reports.progress.print',
                'module' => 'reports-progress',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير تتبع التقدم الزمني والإنجاز',
            ],
            [
                'name' => 'تصدير تقرير التقدم الزمني والإنجاز',
                'slug' => 'reports.progress.export',
                'module' => 'reports-progress',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير تقرير التقدم الزمني إلى ملف إكسل',
            ],

            // ==========================================
            // 6. التقرير المالي الموحد ERPNext (ERPNext Financial Report)
            // ==========================================
            [
                'name' => 'عرض تقرير ERPNext المالي الموحد',
                'slug' => 'reports.financial_erpnext.view',
                'module' => 'reports-erpnext-financial',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير البيانات المالية الموحدة المتكامل مع نظام ERPNext',
            ],
            [
                'name' => 'طباعة تقرير ERPNext المالي الموحد',
                'slug' => 'reports.financial_erpnext.print',
                'module' => 'reports-erpnext-financial',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير ERPNext المالي الموحد',
            ],
            [
                'name' => 'تصدير تقرير ERPNext المالي الموحد',
                'slug' => 'reports.financial_erpnext.export',
                'module' => 'reports-erpnext-financial',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير بيانات تقرير ERPNext المالي الموحد إلى إكسل',
            ],

            // ==========================================
            // 7. تقرير ملخص الإيرادات والنفقات (P&L Expense Summary)
            // ==========================================
            [
                'name' => 'عرض تقرير ملخص الإيرادات والنفقات',
                'slug' => 'reports.pl_expense_summary.view',
                'module' => 'reports-pl-expense-summary',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير ملخص الإيرادات والنفقات حسب البنود والجهات',
            ],
            [
                'name' => 'طباعة تقرير ملخص الإيرادات والنفقات',
                'slug' => 'reports.pl_expense_summary.print',
                'module' => 'reports-pl-expense-summary',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير ملخص الإيرادات والنفقات',
            ],
            [
                'name' => 'تصدير تقرير ملخص الإيرادات والنفقات',
                'slug' => 'reports.pl_expense_summary.export',
                'module' => 'reports-pl-expense-summary',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير بيانات تقرير ملخص الإيرادات والنفقات إلى إكسل',
            ],

            // ==========================================
            // 8. تقرير الأرباح والخسائر (Profit and Loss Statement)
            // ==========================================
            [
                'name' => 'عرض تقرير الأرباح والخسائر',
                'slug' => 'reports.profit_and_loss.view',
                'module' => 'reports-profit-and-loss',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير قائمة الأرباح والخسائر للجهات والمشاريع',
            ],
            [
                'name' => 'طباعة تقرير الأرباح والخسائر',
                'slug' => 'reports.profit_and_loss.print',
                'module' => 'reports-profit-and-loss',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة بيانات تقرير الأرباح والخسائر',
            ],
            [
                'name' => 'تصدير تقرير الأرباح والخسائر',
                'slug' => 'reports.profit_and_loss.export',
                'module' => 'reports-profit-and-loss',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير تقرير الأرباح والخسائر إلى ملف إكسل',
            ],

            // ==========================================
            // 9. التقرير الرسمي الشامل للمشاريع (Official Summary Report)
            // ==========================================
            [
                'name' => 'عرض التقرير الرسمي الشامل',
                'slug' => 'reports.official_summary.view',
                'module' => 'reports-official-summary',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض التقرير الرسمي الموحد لملخص إنجاز المشاريع',
            ],
            [
                'name' => 'طباعة وتحميل التقرير الرسمي الشامل',
                'slug' => 'reports.official_summary.print',
                'module' => 'reports-official-summary',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة وتحميل تقرير الإنجاز الرسمي الموحد بصيغة PDF',
            ],

            // ==========================================
            // 10. تقرير أصحاب المصلحة والجهات (Stakeholders Report)
            // ==========================================
            [
                'name' => 'عرض تقرير أصحاب المصلحة والجهات',
                'slug' => 'reports.stakeholders.view',
                'module' => 'reports-stakeholders',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير بيانات أصحاب المصلحة والجهات والمستفيدين',
            ],
            [
                'name' => 'طباعة تقرير أصحاب المصلحة والجهات',
                'slug' => 'reports.stakeholders.print',
                'module' => 'reports-stakeholders',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير أصحاب المصلحة والجهات',
            ],
            [
                'name' => 'تصدير تقرير أصحاب المصلحة والجهات',
                'slug' => 'reports.stakeholders.export',
                'module' => 'reports-stakeholders',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير بيانات تقرير أصحاب المصلحة إلى ملف إكسل',
            ],

            // ==========================================
            // 11. تقرير مراجعة صلاحيات النظام (Permissions Audit Report)
            // ==========================================
            [
                'name' => 'عرض تقرير مراجعة صلاحيات النظام',
                'slug' => 'reports.permissions.view',
                'module' => 'reports-permissions',
                'type' => Permission::TYPE_PAGE,
                'description' => 'عرض تقرير تدقيق ومراجعة الصلاحيات والأدوار عبر صفحات النظام',
            ],
            [
                'name' => 'طباعة تقرير مراجعة صلاحيات النظام',
                'slug' => 'reports.permissions.print',
                'module' => 'reports-permissions',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'طباعة تقرير مراجعة وتدقيق الصلاحيات',
            ],
            [
                'name' => 'تصدير تقرير مراجعة صلاحيات النظام',
                'slug' => 'reports.permissions.export',
                'module' => 'reports-permissions',
                'type' => Permission::TYPE_BUTTON,
                'description' => 'تصدير تقرير مراجعة الصلاحيات إلى إكسل',
            ],
            [
                'name' => 'تقرير صلاحيات النظام (مختصر)',
                'slug' => 'permissions.report',
                'module' => 'reports-permissions',
                'type' => Permission::TYPE_PAGE,
                'description' => 'الوصول لصفحة تقرير صلاحيات النظام المباشرة',
            ],
        ];

        $superAdminRole = Role::where('name', 'Super Admin')->orWhere('id', 1)->first();
        $adminRole = Role::where('name', 'Admin')->orWhere('id', 2)->first();

        foreach ($permissions as $permData) {
            $permission = Permission::updateOrCreate(
                ['slug' => $permData['slug']],
                [
                    'name' => $permData['name'],
                    'module' => $permData['module'],
                    'type' => $permData['type'],
                    'description' => $permData['description'],
                ]
            );

            // إعطاء كافة الصلاحيات للأدوار الإدارية بشكل افتراضي
            if ($superAdminRole) {
                RolePermission::firstOrCreate([
                    'role_id' => $superAdminRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
            if ($adminRole) {
                RolePermission::firstOrCreate([
                    'role_id' => $adminRole->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        // تفريغ كاش الصلاحيات للأدوار لضمان سريان التعديلات فوراً
        try {
            $roleIds = Role::pluck('id');
            foreach ($roleIds as $roleId) {
                User::incrementRolePermissionsVersion($roleId);
            }
            Log::info('Reports permissions seeded and cache invalidated for all roles.');
            $this->command?->info('✓ Reports permissions seeded and cache invalidated successfully.');
        } catch (\Exception $e) {
            Log::warning('Could not invalidate permission cache: '.$e->getMessage());
        }
    }
}
