<?php

return [
    /*
     * Central permissions definition used for seeding and management.
     * Structure: module => [permissions...]
     * Each permission: [slug, name, type]
     */
    'value_chains' => [
        // Chain plans
        ['slug' => 'chain_plans.view', 'name' => 'عرض خطط السلاسل', 'type' => 'page'],
        ['slug' => 'chain_plans.create', 'name' => 'إضافة خطة سلسلة', 'type' => 'action'],
        ['slug' => 'chain_plans.edit', 'name' => 'تعديل خطة سلسلة', 'type' => 'action'],
        ['slug' => 'chain_plans.delete', 'name' => 'حذف خطة سلسلة', 'type' => 'action'],
        ['slug' => 'chain_plans.export', 'name' => 'تصدير خطط السلاسل', 'type' => 'action'],
        ['slug' => 'chain_plans.import', 'name' => 'استيراد خطط السلاسل', 'type' => 'action'],
        ['slug' => 'chain_plans.print', 'name' => 'طباعة خطة السلسلة', 'type' => 'action'],
        ['slug' => 'chain_plans.batch-print', 'name' => 'طباعة دفعة من خطط السلاسل', 'type' => 'action'],
        ['slug' => 'chain_plans.comprehensive-batch-print', 'name' => 'طباعة شاملة لخطط السلاسل', 'type' => 'action'],
        ['slug' => 'chain_plans.scope-all', 'name' => 'نطاق: جميع المحافظات - خطط السلاسل', 'type' => 'scope'],
        ['slug' => 'chain_plans.scope-governorate', 'name' => 'نطاق: نفس المحافظة - خطط السلاسل', 'type' => 'scope'],
        ['slug' => 'chain_plans.scope-directorate', 'name' => 'نطاق: نفس المديرية - خطط السلاسل', 'type' => 'scope'],

        // Participating entities
        ['slug' => 'participating_entities.view', 'name' => 'عرض الجهات المشاركة', 'type' => 'page'],
        ['slug' => 'participating_entities.create', 'name' => 'إضافة جهة مشاركة', 'type' => 'action'],
        ['slug' => 'participating_entities.edit', 'name' => 'تعديل جهة مشاركة', 'type' => 'action'],
        ['slug' => 'participating_entities.delete', 'name' => 'حذف جهة مشاركة', 'type' => 'action'],

        // Series financing
        ['slug' => 'series_financing.view', 'name' => 'عرض تمويل السلاسل', 'type' => 'page'],
        ['slug' => 'series_financing.create', 'name' => 'إضافة تمويل', 'type' => 'action'],
        ['slug' => 'series_financing.edit', 'name' => 'تعديل تمويل', 'type' => 'action'],
        ['slug' => 'series_financing.delete', 'name' => 'حذف تمويل', 'type' => 'action'],

        // Value chain members
        ['slug' => 'value_chain_members.view', 'name' => 'عرض أعضاء السلسلة', 'type' => 'page'],
        ['slug' => 'value_chain_members.create', 'name' => 'إضافة عضو سلسلة', 'type' => 'action'],
        ['slug' => 'value_chain_members.edit', 'name' => 'تعديل عضو سلسلة', 'type' => 'action'],
        ['slug' => 'value_chain_members.delete', 'name' => 'حذف عضو سلسلة', 'type' => 'action'],

        // Value chains
        ['slug' => 'value_chains.view', 'name' => 'عرض السلاسل', 'type' => 'page'],
        ['slug' => 'value_chains.create', 'name' => 'إضافة سلسلة', 'type' => 'action'],
        ['slug' => 'value_chains.edit', 'name' => 'تعديل سلسلة', 'type' => 'action'],
        ['slug' => 'value_chains.delete', 'name' => 'حذف سلسلة', 'type' => 'action'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery Settings
    |--------------------------------------------------------------------------
    */
    'auto_discovery' => [
        'enabled' => env('PERMISSIONS_AUTO_DISCOVERY', true),
        'sync_on_boot' => env('PERMISSIONS_SYNC_ON_BOOT', false),
        'remove_orphaned' => env('PERMISSIONS_REMOVE_ORPHANED', false),
    ],

    /* Excluded routes */
    'excluded_routes' => [
        'login', 'logout', 'register', 'password.*', 'verification.*', 'api.*', 'sanctum.*',
        '_debugbar.*', '_ignition.*', 'horizon.*', 'telescope.*', 'welcome', 'home',
    ],

    /* Operation translations */
    'operation_translations' => [
        'view' => 'عرض', 'create' => 'إنشاء', 'store' => 'إنشاء', 'edit' => 'تعديل', 'update' => 'تعديل',
        'delete' => 'حذف', 'destroy' => 'حذف', 'show' => 'عرض تفاصيل', 'export' => 'تصدير', 'print' => 'طباعة',
        'import' => 'استيراد', 'approve' => 'موافقة', 'reject' => 'رفض', 'download' => 'تحميل', 'upload' => 'رفع',
    ],

    /* Module translations */
    'module_translations' => [
        'authentication' => 'لوحة التحكم والاتصال', 'dashboard' => 'لوحة التحكم', 'projects' => 'إدارة المشاريع',
        'project-requests' => 'طلبات المشاريع', 'executive-activities' => 'الأنشطة التنفيذية', 'donors' => 'الجهات المانحة',
        'financing-types' => 'أنواع التمويل', 'governorates' => 'المحافظات', 'directorates' => 'المديريات',
        'entities' => 'الجهات', 'notifications' => 'التنبيهات والإخطارات', 'users' => 'إدارة المستخدمين',
        'roles' => 'الأدوار الوظيفية', 'audit-logs' => 'سجلات العمليات', 'configuration' => 'إعدادات النظام',
    ],

    /* Inference rules for route -> operation */
    'inference_rules' => [
        'http_methods' => ['GET' => 'view', 'POST' => 'create', 'PUT' => 'edit', 'PATCH' => 'edit', 'DELETE' => 'delete'],
        'route_patterns' => ['/create$/' => 'create', '/edit$/' => 'edit', '/export/' => 'export', '/print/' => 'print'],
    ],
];
