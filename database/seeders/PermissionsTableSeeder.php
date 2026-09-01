<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'عرض لوحة التحكم في القائمة الجانبية', 'slug' => 'dashboard.sidebar', 'module' => 'dashboard'],
            ['name' => 'عرض إحصائيات لوحة التحكم', 'slug' => 'dashboard.statistics', 'module' => 'dashboard'],

            // Projects
            ['name' => 'عرض المشاريع في القائمة الجانبية', 'slug' => 'projects.sidebar', 'module' => 'projects'],
            ['name' => 'عرض قائمة المشاريع', 'slug' => 'projects.view', 'module' => 'projects'],
            ['name' => 'إنشاء مشروع جديد', 'slug' => 'projects.create', 'module' => 'projects'],
            ['name' => 'تعديل مشروع', 'slug' => 'projects.edit', 'module' => 'projects'],
            ['name' => 'حذف مشروع', 'slug' => 'projects.delete', 'module' => 'projects'],
            ['name' => 'تصدير بيانات المشاريع', 'slug' => 'projects.export', 'module' => 'projects'],
            ['name' => 'طباعة بيانات المشروع', 'slug' => 'projects.print', 'module' => 'projects'],
            ['name' => 'مراجعة المشروع المكتمل', 'slug' => 'projects.review', 'module' => 'projects'],
            ['name' => 'اعتماد المشروع داخلياً', 'slug' => 'projects.approve_internal', 'module' => 'projects'],
            ['name' => 'تكرار مشروع', 'slug' => 'projects.duplicate', 'module' => 'projects'],
            ['name' => 'تصدير المشروع بصيغة Word', 'slug' => 'projects.export-word', 'module' => 'projects'],
            ['name' => 'استكمال بيانات المشروع القديم', 'slug' => 'projects.complete-data', 'module' => 'projects'],
            ['name' => 'تسجيل إنجاز للمشروع', 'slug' => 'projects.achievements', 'module' => 'projects'],
            ['name' => 'تعديل الجهة المقدمة للمشروع', 'slug' => 'projects.edit-entity', 'module' => 'projects'],

            // Execution & Tracking
            ['name' => 'عرض التنفيذ في القائمة الجانبية', 'slug' => 'execution.sidebar', 'module' => 'execution'],
            ['name' => 'عرض سجلات التنفيذ', 'slug' => 'execution.view', 'module' => 'execution'],
            ['name' => 'إضافة سجل تنفيذ', 'slug' => 'execution.edit', 'module' => 'execution'],
            ['name' => 'اعتماد سجلات التنفيذ', 'slug' => 'execution.approve', 'module' => 'execution'],
            ['name' => 'رفض سجلات التنفيذ', 'slug' => 'execution.reject', 'module' => 'execution'],

            // Quality
            ['name' => 'عرض الجودة في القائمة الجانبية', 'slug' => 'quality.sidebar', 'module' => 'quality'],
            ['name' => 'عرض تقارير الجودة', 'slug' => 'quality.view', 'module' => 'quality'],
            ['name' => 'إضافة/تعديل تقارير الجودة', 'slug' => 'quality.edit', 'module' => 'quality'],

            // Users
            ['name' => 'عرض المستخدمين في القائمة الجانبية', 'slug' => 'users.sidebar', 'module' => 'users'],
            ['name' => 'عرض قائمة المستخدمين', 'slug' => 'users.view', 'module' => 'users'],
            ['name' => 'إضافة مستخدم جديد', 'slug' => 'users.create', 'module' => 'users'],
            ['name' => 'تعديل بيانات مستخدم', 'slug' => 'users.edit', 'module' => 'users'],
            ['name' => 'حذف مستخدم', 'slug' => 'users.delete', 'module' => 'users'],
            ['name' => 'إعادة تعيين كلمة المرور', 'slug' => 'users.reset-password', 'module' => 'users'],
            ['name' => 'عرض سجل نشاط المستخدم', 'slug' => 'users.activity-log', 'module' => 'users'],

            // Roles & Permissions
            ['name' => 'عرض الأدوار في القائمة الجانبية', 'slug' => 'roles-permissions.sidebar', 'module' => 'roles-permissions'],
            ['name' => 'عرض الأدوار والصلاحيات', 'slug' => 'roles-permissions.view', 'module' => 'roles-permissions'],
            ['name' => 'تعديل الأدوار والصلاحيات', 'slug' => 'roles-permissions.edit', 'module' => 'roles-permissions'],
            ['name' => 'حذف دور', 'slug' => 'roles-permissions.delete', 'module' => 'roles-permissions'],

            // Audit Logs
            ['name' => 'عرض السجلات في القائمة الجانبية', 'slug' => 'audit-logs.sidebar', 'module' => 'audit-logs'],
            ['name' => 'عرض سجلات النظام', 'slug' => 'audit-logs.view', 'module' => 'audit-logs'],
            ['name' => 'تصدير سجلات النظام', 'slug' => 'audit-logs.export', 'module' => 'audit-logs'],

            // Import Logs
            ['name' => 'عرض سجلات الاستيراد الشاملة', 'slug' => 'import-logs.view', 'module' => 'audit-logs'],
            ['name' => 'التراجع عن عمليات الاستيراد', 'slug' => 'import-logs.rollback', 'module' => 'audit-logs'],

            // Configuration
            ['name' => 'عرض الإعدادات في القائمة الجانبية', 'slug' => 'configuration.sidebar', 'module' => 'configuration'],
            ['name' => 'إعدادات النظام', 'slug' => 'configuration.view', 'module' => 'configuration'],
            ['name' => 'تعديل الإعدادات', 'slug' => 'configuration.edit', 'module' => 'configuration'],
            ['name' => 'استيراد/تصدير الإعدادات', 'slug' => 'configuration.import_export', 'module' => 'configuration'],

            // Referrals & Correspondence
            ['name' => 'عرض الإحالات في القائمة الجانبية', 'slug' => 'referrals.sidebar', 'module' => 'referrals'],
            ['name' => 'عرض الإحالات والمراسلات', 'slug' => 'referrals.view', 'module' => 'referrals'],
            ['name' => 'إنشاء إحالة جديدة', 'slug' => 'referrals.create', 'module' => 'referrals'],
            ['name' => 'الرد على الإحالة', 'slug' => 'referrals.respond', 'module' => 'referrals'],

            // Planning
            ['name' => 'عرض التخطيط في القائمة الجانبية', 'slug' => 'plans.sidebar', 'module' => 'planning'],
            ['name' => 'إدارة الخطط', 'slug' => 'plans.view', 'module' => 'planning'],

            // Reports
            ['name' => 'عرض التقارير في القائمة الجانبية', 'slug' => 'reports.sidebar', 'module' => 'reports'],
            ['name' => 'لوحة معلومات التقارير (القائمة الجانبية)', 'slug' => 'reports.sidebar-index', 'module' => 'reports'],
            ['name' => 'عرض التقارير العامة', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'عرض تقرير التنفيذ', 'slug' => 'reports.implementation.view', 'module' => 'reports'],
            ['name' => 'عرض تقرير الجودة', 'slug' => 'reports.quality.view', 'module' => 'reports'],
            ['name' => 'عرض التقرير المالي', 'slug' => 'reports.financial.view', 'module' => 'reports'],
            ['name' => 'عرض تقرير التقدم الزمني', 'slug' => 'reports.progress.view', 'module' => 'reports'],
            ['name' => 'عرض تقرير الحالة', 'slug' => 'reports.status.view', 'module' => 'reports'],
            ['name' => 'عرض النظرة الشاملة للتقارير', 'slug' => 'reports.overview.view', 'module' => 'reports'],
            ['name' => 'طباعة وتصدير التقارير', 'slug' => 'reports.print', 'module' => 'reports'],

            // Permissions Report
            ['name' => 'عرض تقرير صلاحيات النظام', 'slug' => 'permissions.report', 'module' => 'roles-permissions'],

            // Encoding / Master Data Modules
            ['name' => 'إدارة البرامج', 'slug' => 'programs.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة المجالات', 'slug' => 'domains.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة الموجهات', 'slug' => 'main-routers.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة المحافظات والمديريات', 'slug' => 'governorates.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة القرى والحارات', 'slug' => 'villages.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة أشكال التمويل', 'slug' => 'financing-forms.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة أنواع التمويل', 'slug' => 'financing-types.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة الجهات', 'slug' => 'authorities.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة الأولويات', 'slug' => 'priorities.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة المانحين', 'slug' => 'donors.sidebar', 'module' => 'encoding'],
            ['name' => 'إدارة المستفيدين', 'slug' => 'beneficiaries.sidebar', 'module' => 'encoding'],

            // Tasks
            ['name' => 'عرض المهام في القائمة الجانبية', 'slug' => 'tasks.sidebar', 'module' => 'tasks'],
            ['name' => 'إدارة المهام المستقلة', 'slug' => 'task.view', 'module' => 'tasks'],
            ['name' => 'إنشاء مهمة', 'slug' => 'task.create', 'module' => 'tasks'],
            ['name' => 'تعديل مهمة', 'slug' => 'task.edit', 'module' => 'tasks'],
            ['name' => 'حذف مهمة', 'slug' => 'task.delete', 'module' => 'tasks'],

            // Requests Descend
            ['name' => 'عرض طلبات النزول في القائمة الجانبية', 'slug' => 'requests_descend.sidebar', 'module' => 'requests_descend'],
            ['name' => 'عرض طلبات النزول', 'slug' => 'requests_descend.view', 'module' => 'requests_descend'],
            ['name' => 'إنشاء طلب نزول', 'slug' => 'requests_descend.create', 'module' => 'requests_descend'],
            ['name' => 'تعديل طلب نزول', 'slug' => 'requests_descend.edit', 'module' => 'requests_descend'],
            ['name' => 'حذف طلب نزول', 'slug' => 'requests_descend.delete', 'module' => 'requests_descend'],
            ['name' => 'الاعتماد المالي لطلب النزول', 'slug' => 'requests_descend.financial', 'module' => 'requests_descend'],

            // Value Chains
            ['name' => 'عرض إدارة سلاسل القيمة في القائمة الجانبية', 'slug' => 'value-chains.sidebar', 'module' => 'value-chains'],
            ['name' => 'عرض قائمة سلاسل القيمة', 'slug' => 'value-chains.view', 'module' => 'value-chains'],
            ['name' => 'إنشاء سلسلة قيمة جديدة', 'slug' => 'value-chains.create', 'module' => 'value-chains'],
            ['name' => 'تعديل سلسلة قيمة', 'slug' => 'value-chains.edit', 'module' => 'value-chains'],
            ['name' => 'حذف سلسلة قيمة', 'slug' => 'value-chains.delete', 'module' => 'value-chains'],
            [
                'name' => 'عرض نوع الجهات',
                'slug' => 'type-entity.view',
                'module' => 'type-entity',
            ],
            [
                'name' => 'إنشاء نوع الجهات',
                'slug' => 'type-entity.create',
                'module' => 'type-entity',
            ],
            [
                'name' => 'تعديل نوع الجهات',
                'slug' => 'type-entity.edit',
                'module' => 'type-entity',
            ],
            [
                'name' => 'حذف نوع الجهات',
                'slug' => 'type-entity.delete',
                'module' => 'type-entity',
            ],
            [
                'name' => 'عرض نوع الجهات في القائمة الجانبية',
                'slug' => 'type-entity.sidebar',
                'module' => 'type-entity',
            ],
            [
                'name' => 'تصدير نوع الجهات',
                'slug' => 'type-entity.export',
                'module' => 'type-entity',
            ],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'name' => $perm['name'],
                    'module' => $perm['module'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
