{{-- Permissions Matrix - Ultra Modern Version (Full Interactive) --}}
{{--
    المتغيرات المطلوبة من الكونترولر:
    - $allModuleNames : array       أسماء الوحدات (مثل ['users', 'roles', ...])
    - $moduleTranslations : array   ترجمة أسماء الوحدات
    - $categorized : array          الصلاحيات مقسمة (actions, pages, sidebars, settings, scopes)
    - $rolePermissions : array      أرقام الصلاحيات المفعلة للدور الحالي
    - $role : object                 كائن الدور (يحتوي على id, full_access, module_scopes, module_geo_scopes, ...)
    - $moduleConfigs : array         تهيئات خاصة للوحدات (has_dedicated_scope, scope_type, bg_gradient, ...)
    - $viewOnly : bool (اختياري)    وضع العرض فقط بدون تعديل
--}}


--}}
--}}
--}}

{{-- Permissions Matrix - Unified Table Version --}}
{{-- المتغيرات المطلوبة: $allModuleNames, $moduleTranslations, $categorized, $rolePermissions, $role, $moduleConfigs, $viewOnly --}}
<input type="hidden" name="permissions_json" id="permissionsJsonInput" value="">

<div class="permission-table-wrapper mt-4" dir="rtl">

    {{-- Hero Header --}}
    <div class="perm-hero mb-4">
        <div class="perm-hero-bg"></div>
        <div class="perm-hero-content">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-4 w-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="hero-icon">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 class="hero-title mb-1">مركز إدارة الصلاحيات</h3>
                        <p class="hero-subtitle mb-0">
                            <i class="fas fa-sparkles me-1"></i>
                            تحكم دقيق في الوصول والعمليات لكل وحدة في النظام
                        </p>
                    </div>
                </div>

                @if(!isset($viewOnly) || $viewOnly !== true)
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        {{-- View Toggle Buttons --}}
                        <div class="view-toggle-wrapper me-2">
                            <button type="button" class="view-toggle-btn active" data-view="table" title="عرض جدول">
                                <i class="fas fa-table"></i>
                                <span>جدول</span>
                            </button>
                            <button type="button" class="view-toggle-btn" data-view="cards" title="عرض كروت">
                                <i class="fas fa-th-large"></i>
                                <span>كروت</span>
                            </button>
                        </div>

                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="permTableSearch" placeholder="بحث سريع...">
                            <kbd class="search-kbd">⌘K</kbd>
                        </div>

                        @if(isset($roles) && count($roles) > 0)
                            <div class="select-wrapper">
                                <i class="fas fa-download select-icon"></i>
                                <select id="copyFromRole">
                                    <option value="">📋 جلب من دور...</option>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <button type="button" id="copyToClipboardBtn" class="action-btn" style="background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); color: white; border: none;" title="نسخ الصلاحيات">
                            <i class="fas fa-copy"></i>
                            <span>نسخ</span>
                        </button>
                        <button type="button" id="pasteFromClipboardBtn" class="action-btn" style="background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: white; border: none; display: none;" title="لصق الصلاحيات">
                            <i class="fas fa-paste"></i>
                            <span>لصق</span>
                        </button>

                        <button type="button" id="selectAllTable" class="action-btn action-btn-success">
                            <i class="fas fa-check-double"></i>
                            <span>الكل</span>
                        </button>
                        <button type="button" id="deselectAllTable" class="action-btn action-btn-danger">
                            <i class="fas fa-xmark"></i>
                            <span>لا شيء</span>
                        </button>

                        <label class="premium-switch">
                            <input type="checkbox" id="fullAccessToggle" value="1"
                                   {{ (isset($role) && $role->full_access) ? 'checked' : '' }}>
                            <span class="switch-slider">
                                <i class="fas fa-crown switch-icon"></i>
                                <span class="switch-label">وصول كامل</span>
                            </span>
                        </label>

                        <button type="submit" form="roleForm" class="save-btn">
                            <i class="fas fa-save"></i>
                            <span>حفظ التغييرات</span>
                            <span class="save-btn-shine"></span>
                        </button>
                    </div>
                @else
                    <div class="view-only-badge">
                        <i class="fas fa-eye-slash"></i>
                        <span>وضع العرض فقط</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Pre-compute extra slugs and build fast lookups --}}
    @php
        $stdSuffixes = ['sidebar', 'view', 'show', 'index', 'create', 'add', 'edit', 'update', 'delete', 'destroy', 'export', 'import'];
        $allExtraSlugKeysMap = [];
        $modulePermsMap = [];

        foreach ($allModuleNames as $_mn) {
            $_mnPerms = [];
            foreach (['sidebars', 'pages', 'buttons', 'icons', 'actions', 'settings'] as $_cat) {
                if (isset($categorized[$_cat][$_mn])) {
                    foreach ($categorized[$_cat][$_mn] as $p) {
                        $_mnPerms[] = $p;
                    }
                }
            }
            
            $indexedPerms = [];
            foreach ($_mnPerms as $p) {
                $slugParts = explode('.', $p->slug);
                $suffix = end($slugParts);
                $indexedPerms[$suffix] = $p;
            }
            $modulePermsMap[$_mn] = [
                'perms' => $_mnPerms,
                'indexed' => $indexedPerms,
            ];

            $_stdPermsIds = [];
            foreach ($stdSuffixes as $s) {
                 if (isset($indexedPerms[$s])) $_stdPermsIds[$indexedPerms[$s]->id] = true;
                 if (isset($indexedPerms[$s.'s'])) $_stdPermsIds[$indexedPerms[$s.'s']->id] = true;
            }
            
            foreach ($_mnPerms as $_p) {
                if (!isset($_stdPermsIds[$_p->id])) {
                    $slugParts = explode('.', $_p->slug);
                    $suffix = end($slugParts);
                    $allExtraSlugKeysMap[$suffix] = true;
                }
            }
        }
        $uniqueExtraSlugKeys = array_keys($allExtraSlugKeysMap);
        sort($uniqueExtraSlugKeys);
        
        $rolePermsLookup = array_flip($rolePermissions ?? []);


        $slugArabicMap = [
            'disable' => 'تعطيل', 'enable' => 'تفعيل', 'reset-password' => 'إعادة كلمة المرور',
            'view-external' => 'عرض خارجي', 'view-internal' => 'عرض داخلي', 'view-in-dropdowns' => 'عرض في القوائم',
            'approval-path' => 'مسار الاعتماد', 'approve' => 'اعتماد', 'reject' => 'رفض',
            'send' => 'إرسال', 'receive' => 'استلام', 'return' => 'إرجاع', 'forward' => 'تحويل',
            'archive' => 'أرشفة', 'restore' => 'استعادة', 'print' => 'طباعة', 'sign' => 'توقيع',
            'complete' => 'إنهاء', 'close' => 'إغلاق', 'open' => 'فتح', 'assign' => 'تعيين',
            'unassign' => 'إلغاء تعيين', 'chat.view' => 'عرض المحادثة', 'chat.reply' => 'الرد في المحادثة',
            'memo.create' => 'إنشاء مذكرة', 'memo.sign' => 'توقيع مذكرة', 'memo.delete' => 'حذف مذكرة',
            'document-note.create' => 'إضافة ملاحظة مستند', 'execution-note.create' => 'إضافة ملاحظة تنفيذ',
            'attachment.upload' => 'رفع مرفق', 'timeline.view' => 'عرض الجدول الزمني',
            'stages.approval-path' => 'مسار اعتماد المراحل', 'sidebar' => 'القائمة الجانبية',
            'view' => 'عرض', 'create' => 'إنشاء', 'add' => 'إضافة', 'edit' => 'تعديل',
            'update' => 'تحديث', 'delete' => 'حذف', 'destroy' => 'حذف', 'export' => 'تصدير',
            'import' => 'استيراد', 'show' => 'عرض التفاصيل', 'index' => 'عرض القائمة', 'view-list' => 'عرض القائمة',
            'view-details' => 'عرض التفاصيل',
            'manage' => 'إدارة', 'upload' => 'رفع', 'download' => 'تنزيل', 'reply' => 'رد',
            'comment' => 'تعليق', 'notify' => 'إشعار', 'share' => 'مشاركة', 'transfer' => 'نقل',
            'audit' => 'تدقيق', 'report' => 'تقرير',
            'sidebar-index' => 'القائمة الجانبية: الصفحة',
            'sidebar-loans' => 'القائمة الجانبية: القروض',
            'sidebar-beneficiaries' => 'القائمة الجانبية: المستفيدين',
            'request-action' => 'طلب إجراء', 'referral' => 'إحالة', 'view-all' => 'عرض الكل',
            'manage-external' => 'إدارة خارجية', 'manage-internal' => 'إدارة داخلية', 'modify-source' => 'تعديل المصدر',
            'view-last' => 'عرض الأخير', 'execute' => 'تنفيذ', 'finalize' => 'اعتماد نهائي',
            'refer' => 'إحالة', 'resume' => 'استئناف', 'revert' => 'تراجع', 'review' => 'مراجعة',
            'schedule' => 'جدولة', 'respond' => 'الرد', 'financial' => 'مالي', 'technical' => 'فني',
            'toggle' => 'تبديل'
        ];
    @endphp

    {{-- Premium Fast Styles --}}
    <style>
        .permission-table-wrapper {
            font-family: inherit;
            position: relative;
        }

        .perm-hero {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #4338ca 100%);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.2);
            color: #fff;
        }

        .perm-hero-content {
            position: relative;
            padding: 20px 24px;
        }

        .hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
        }

        .hero-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .hero-subtitle {
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .view-toggle-wrapper {
            display: inline-flex;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 3px;
            gap: 2px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .view-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .view-toggle-btn.active {
            background: #fff;
            color: #4f46e5;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            width: 200px;
            padding: 8px 36px 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.95);
            font-size: 0.82rem;
            font-weight: 600;
            color: #1e293b;
            outline: none;
            transition: width 0.2s ease;
        }

        .search-box input:focus {
            width: 230px;
            background: #fff;
            border-color: #fff;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
        }

        .search-icon {
            position: absolute;
            right: 12px;
            color: #6366f1;
            font-size: 0.85rem;
            pointer-events: none;
        }

        .select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .select-wrapper select {
            padding: 8px 32px 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.95);
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            outline: none;
        }

        .select-icon {
            position: absolute;
            right: 10px;
            color: #6366f1;
            pointer-events: none;
            font-size: 0.8rem;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 13px;
            border: 1px solid transparent;
            border-radius: 10px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .action-btn-success {
            background: #10b981;
            color: #fff;
        }

        .action-btn-danger {
            background: #ef4444;
            color: #fff;
        }

        .premium-switch {
            display: inline-flex;
            cursor: pointer;
            user-select: none;
        }

        .premium-switch input { display: none; }

        .switch-slider {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .premium-switch input:checked + .switch-slider {
            background: #f59e0b;
            border-color: #d97706;
            color: #fff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border: none;
            border-radius: 10px;
            background: #fff;
            color: #4f46e5;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.15s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .save-btn:hover {
            transform: translateY(-1px);
            background: #f8fafc;
        }

        .view-only-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .perm-table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .perm-table-scroll {
            max-height: 72vh;
            overflow: auto;
            background: #fff;
        }

        .perm-table-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
        .perm-table-scroll::-webkit-scrollbar-track { background: #f8fafc; }
        .perm-table-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .perm-table-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        #permMatrixTable {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        #permMatrixTable thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8fafc;
            white-space: nowrap;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 700;
            font-size: 0.76rem;
            padding: 12px 10px;
        }

        #permMatrixTable thead th small {
            display: block;
            font-size: 0.68rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
        }

        #permMatrixTable .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
            min-width: 220px;
        }

        #permMatrixTable thead th.sticky-col {
            z-index: 4;
            background: #f8fafc !important;
        }

        #permMatrixTable tbody tr:hover .sticky-col {
            background: #f8fafc !important;
        }

        #permMatrixTable tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        #permMatrixTable tbody tr:hover {
            background: #f8fafc !important;
        }

        #permMatrixTable tbody tr:nth-child(even) {
            background: #fafbff;
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 28px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: 0.68rem;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        }

        .perm-checkbox, .row-select-all, .select-all-header {
            width: 20px;
            height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            margin: 0;
            vertical-align: middle;
            accent-color: #4f46e5;
        }

        .col-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }

        .col-icon.sidebar { background: #dbeafe; color: #1d4ed8; }
        .col-icon.view    { background: #e0f2fe; color: #0369a1; }
        .col-icon.create  { background: #d1fae5; color: #047857; }
        .col-icon.edit    { background: #fef3c7; color: #b45309; }
        .col-icon.delete  { background: #fee2e2; color: #b91c1c; }
        .col-icon.export  { background: #cffafe; color: #0e7490; }
        .col-icon.import  { background: #f3e8ff; color: #7e22ce; }
        .col-icon.extra   { background: #f1f5f9; color: #475569; }

        .scope-select, .geo-select {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 8px !important;
            font-size: 0.72rem !important;
            padding: 4px 6px !important;
            font-weight: 600;
            color: #334155;
            outline: none;
            cursor: pointer;
        }

        .empty-cell {
            display: inline-block;
            width: 18px;
            height: 2px;
            background: #cbd5e1;
            border-radius: 2px;
            opacity: 0.5;
        }

        .btn-scope-dedicated {
            background: #fff;
            border: 1px dashed #6366f1;
            color: #6366f1;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-scope-dedicated:hover {
            background: #6366f1;
            color: #fff;
            border-style: solid;
        }

        .module-row.inactive {
            opacity: 0.5;
        }

        .table-footer {
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            background: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .stat-chip .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .stat-chip .dot.purple { background: #6366f1; }
        .stat-chip .dot.green  { background: #10b981; }
        .stat-chip .dot.red    { background: #ef4444; }

        .cards-view {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 16px;
            padding: 16px 0;
        }

        .cards-view.active {
            display: grid;
        }

        .table-view.hidden {
            display: none;
        }

        .permission-card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .permission-card.inactive {
            opacity: 0.5;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-module-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 42px;
            height: 42px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
        }

        .card-perm-count {
            font-size: 0.72rem;
            color: #94a3b8;
        }

        .card-permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            margin-bottom: 12px;
        }

        .perm-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 8px 6px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            position: relative;
        }

        .perm-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .perm-item.has-permission {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .perm-item input[type="checkbox"] {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .perm-item-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .perm-item-icon.sidebar { background: #dbeafe; color: #1d4ed8; }
        .perm-item-icon.view    { background: #e0f2fe; color: #0369a1; }
        .perm-item-icon.create  { background: #d1fae5; color: #047857; }
        .perm-item-icon.edit    { background: #fef3c7; color: #b45309; }
        .perm-item-icon.delete  { background: #fee2e2; color: #b91c1c; }
        .perm-item-icon.export  { background: #cffafe; color: #0e7490; }
        .perm-item-icon.import  { background: #f3e8ff; color: #7e22ce; }
        .perm-item-icon.extra   { background: #f1f5f9; color: #475569; }

        .perm-item-label {
            font-size: 0.68rem;
            font-weight: 600;
            color: #475569;
            text-align: center;
        }

        .card-scopes-section {
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .card-scope-select {
            flex: 1;
            min-width: 120px;
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            font-size: 0.72rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            outline: none;
        }
    </style>

    @php
    // Filter out main_modules from the rest of the table
    $allModuleNames = is_array($allModuleNames) ? array_filter($allModuleNames, function($n) { return strtolower($n) !== 'main_modules'; }) : (method_exists($allModuleNames, 'filter') ? $allModuleNames->filter(function($n) { return strtolower($n) !== 'main_modules'; }) : $allModuleNames);
    
    // Extract main module permissions
    $mainModulePerms = collect();
    foreach (['sidebars', 'pages', 'actions'] as $cat) {
        if (isset($categorized[$cat]['main_modules'])) {
            $mainModulePerms = $mainModulePerms->merge($categorized[$cat]['main_modules']);
        }
    }
@endphp

{{-- Main Modules Dedicated Card --}}
@if($mainModulePerms->count() > 0)
<div class="card mb-4" style="border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: linear-gradient(to right, #f8fafc, #ffffff);">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color: #1e293b; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-sitemap" style="color: #6366f1;"></i>
            الوحدات الرئيسية (Main Modules)
            <span class="badge bg-primary rounded-pill" style="font-size: 0.75rem;">{{ $mainModulePerms->count() }} وحدات</span>
        </h5>
        
        <div class="row g-3">
            @foreach($mainModulePerms as $perm)
                @php
                    $isChecked = in_array($perm->id, $rolePermissions ?? []);
                    $isReadOnly = (isset($viewOnly) && $viewOnly === true);
                    $isDisabled = $isReadOnly && !$isChecked && !($role->full_access ?? false);
                    $slugParts = explode('.', $perm->slug);
                    $moduleKey = end($slugParts);
                    $moduleNameArabic = [
                        'dashboard' => 'الرئيسية',
                        'projects' => 'المشاريع',
                        'tasks' => 'المهام',
                        'requests-descend' => 'إدارة النزول',
                        'correspondence' => 'إدارة الإحالات',
                        'planning' => 'إدارة التخطيط',
                        'reports' => 'إدارة التقارير',
                        'empowerment' => 'إدارة التمكين',
                        'value-chains' => 'إدارة سلاسل القيمة',
                        'encoding' => 'الترميزات',
                        'users' => 'المستخدمين'
                    ][$moduleKey] ?? ucfirst($moduleKey);
                    
                    $icons = [
                        'dashboard' => 'tachometer-alt',
                        'projects' => 'project-diagram',
                        'tasks' => 'clipboard-list',
                        'requests-descend' => 'arrow-down',
                        'correspondence' => 'exchange-alt',
                        'planning' => 'paste',
                        'reports' => 'chart-pie',
                        'empowerment' => 'hand-holding-usd',
                        'value-chains' => 'sitemap',
                        'encoding' => 'cogs',
                        'users' => 'users-cog'
                    ];
                    $icon = $icons[$moduleKey] ?? 'puzzle-piece';
                @endphp
                <div class="col-md-4 col-sm-6">
                    <div class="p-3 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#6366f1'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fas fa-{{ $icon }}"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="color: #334155; font-size: 0.95rem;">{{ $moduleNameArabic }}</h6>
                                <small style="color: #94a3b8; font-size: 0.75rem;">إظهار / إخفاء الوحدة</small>
                            </div>
                        </div>
                        <div class="form-check form-switch form-switch-lg" style="margin: 0; padding-left: 2.5em;">
                            <input class="form-check-input perm-checkbox main-module-switch module-main-modules" type="checkbox" role="switch" 
                                name="permissions[]" value="{{ $perm->id }}"
                                data-main-module="{{ $moduleKey }}"
                                {{ $isChecked ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}
                                style="width: 2.5em; height: 1.25em; cursor: pointer; border-color: #cbd5e1;" title="{{ $perm->slug }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Table Container --}}
    <div class="perm-table-container">
        
        {{-- TABLE VIEW --}}
        <div class="table-view" id="tableView">
            <div class="perm-table-scroll bg-white">
                <table class="table align-middle mb-0" id="permMatrixTable" data-role-id="{{ $role->id ?? 0 }}">
                    <thead>
                        <tr>
                            <th class="sticky-col border-end py-3 px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-cubes" style="color:#667eea;"></i>
                                    <span>الوحدة / الصفحة</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="القائمة الجانبية">
                                <span class="col-icon sidebar"><i class="fas fa-bars"></i></span>
                                <small>قائمة</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="عرض">
                                <span class="col-icon view"><i class="fas fa-eye"></i></span>
                                <small>عرض</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="إنشاء">
                                <span class="col-icon create"><i class="fas fa-plus"></i></span>
                                <small>إضافة</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="تعديل">
                                <span class="col-icon edit"><i class="fas fa-edit"></i></span>
                                <small>تعديل</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="حذف">
                                <span class="col-icon delete"><i class="fas fa-trash"></i></span>
                                <small>حذف</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="طباعة">
                                <span class="col-icon print"><i class="fas fa-print"></i></span>
                                <small>طباعة</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="بحث">
                                <span class="col-icon search"><i class="fas fa-search"></i></span>
                                <small>بحث</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="تصدير">
                                <span class="col-icon export"><i class="fas fa-file-export"></i></span>
                                <small>تصدير</small>
                            </th>
                            <th class="text-center py-3" style="width:80px;" title="استيراد">
                                <span class="col-icon import"><i class="fas fa-file-import"></i></span>
                                <small>استيراد</small>
                            </th>
                            @foreach($uniqueExtraSlugKeys as $extraSlugKey)
                                @php
                                    $extraLabel = $slugArabicMap[$extraSlugKey]
                                        ?? $slugArabicMap[\Illuminate\Support\Str::afterLast($extraSlugKey, '.')]
                                        ?? str_replace(['-', '.', '_'], ' ', $extraSlugKey);
                                @endphp
                                <th class="text-center py-3 extra-col" style="min-width:100px;" title="{{ $extraSlugKey }}">
                                    <span class="col-icon extra"><i class="fas fa-key" style="font-size:0.8rem;"></i></span>
                                    <small style="font-size:0.7rem; line-height:1.3;">{{ $extraLabel }}</small>
                                </th>
                            @endforeach
                            <th class="py-3 px-3" style="min-width:170px;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-sliders-h" style="color:#667eea;"></i>
                                    <span>النطاقات</span>
                                </div>
                            </th>
                            <th class="text-center py-3" style="width:70px;">
                                <input type="checkbox" class="select-all-header" title="تحديد الكل">
                                <small class="d-block mt-1" style="font-size:0.68rem; color:#64748b;">الكل</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allModuleNames as $moduleName)
                            @php
                                $moduleKey = strtolower($moduleName);
                                $moduleLabel = $moduleTranslations[$moduleKey] ?? $moduleName;
                                $moduleConfig = $moduleConfigs[$moduleKey] ?? [];

                                $allPerms = $modulePermsMap[$moduleName]['perms'] ?? [];
                                $indexedPerms = $modulePermsMap[$moduleName]['indexed'] ?? [];
                                $permCount = count($allPerms);

                                $hasAnyPermission = false;
                                foreach ($allPerms as $p) {
                                    if (isset($rolePermsLookup[$p->id])) {
                                        $hasAnyPermission = true;
                                        break;
                                    }
                                }

                                if (isset($viewOnly) && $viewOnly === true && !($role->full_access ?? false)) {
                                    if (!$hasAnyPermission) continue;
                                }

                                $getPermFast = fn($suffix) => $indexedPerms[$suffix] ?? $indexedPerms[$suffix.'s'] ?? null;

                                $permMap = [
                                    'sidebar' => $getPermFast('sidebar'),
                                    'view' => $getPermFast('view') ?? $getPermFast('show') ?? $getPermFast('index'),
                                    'create' => $getPermFast('create') ?? $getPermFast('add'),
                                    'edit' => $getPermFast('edit') ?? $getPermFast('update'),
                                    'delete' => $getPermFast('delete') ?? $getPermFast('destroy'),
                                    'print' => $getPermFast('print'),
                                    'search' => $getPermFast('search'),
                                    'export' => $getPermFast('export'),
                                    'import' => $getPermFast('import'),
                                ];

                                $savedScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleKey] ?? 'own') : 'own';
                                $savedGeo = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleKey] ?? 'none') : 'none';
                                $hasDedicated = $moduleConfig['has_dedicated_scope'] ?? false;
                                $isReadOnly = (isset($viewOnly) && $viewOnly === true);
                                $rowInactive = !($role->full_access ?? false) && !$hasAnyPermission;
                            @endphp
                            <tr data-module="{{ $moduleKey }}" class="module-row {{ $rowInactive ? 'inactive' : '' }}">

                                {{-- Module Name --}}
                                <td class="sticky-col bg-white border-end py-3 px-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="module-badge">
                                            <span>{{ strtoupper(substr($moduleKey, 0, 3)) }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size:0.92rem;">
                                                @if(!$isReadOnly)
                                                    <input type="checkbox" class="row-select-all" data-module="{{ $moduleKey }}" title="تحديد كل صلاحيات {{ $moduleLabel }}" style="cursor:pointer; transform:scale(1.1);">
                                                @endif
                                                {{ $moduleLabel }}
                                            </div>
                                            <div style="font-size:0.7rem; color:#94a3b8; font-weight:500;">
                                                <i class="fas fa-shield-alt me-1" style="font-size:0.65rem;"></i>
                                                {{ $permCount }} صلاحية متاحة
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Standard columns --}}
                                @foreach($permMap as $colKey => $perm)
                                    <td class="text-center py-3">
                                        @if($perm)
                                            @php
                                                $isChecked = isset($rolePermsLookup[$perm->id]);
                                                $isDisabled = $isReadOnly && !$isChecked && !($role->full_access ?? false);
                                            @endphp
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                   class="perm-checkbox module-{{ $moduleKey }}"
                                                   {{ $isChecked ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}
                                                   data-module="{{ $moduleKey }}" data-action="{{ $colKey }}" title="{{ $perm->slug }}">
                                        @else
                                            <span class="empty-cell"></span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Extra columns --}}
                                @foreach($uniqueExtraSlugKeys as $extraSlugKey)
                                    @php
                                        $extraPerm = $indexedPerms[$extraSlugKey] ?? null;
                                    @endphp
                                    <td class="text-center py-3 extra-col">
                                        @if($extraPerm)
                                            @php
                                                $isExtraChecked = isset($rolePermsLookup[$extraPerm->id]);
                                                $isExtraDisabled = $isReadOnly && !$isExtraChecked && !($role->full_access ?? false);
                                            @endphp
                                            <input type="checkbox" name="permissions[]" value="{{ $extraPerm->id }}"
                                                   class="perm-checkbox module-{{ $moduleKey }}"
                                                   {{ $isExtraChecked ? 'checked' : '' }} {{ $isExtraDisabled ? 'disabled' : '' }}
                                                   data-module="{{ $moduleKey }}" title="{{ $extraPerm->slug }}">
                                        @else
                                            <span class="empty-cell"></span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Scopes --}}
                                <td class="py-3">
                                    <div class="d-flex flex-column gap-1">
                                        @if(!$hasDedicated && !in_array($moduleKey, ['governorates', 'directorates']))
                                            <select name="module_scopes[{{ $moduleKey }}]"
                                                    class="form-select form-select-sm scope-select"
                                                    data-module="{{ $moduleKey }}" data-field="scope"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                <option value="none"   {{ $savedScope === 'none'   ? 'selected' : '' }}>🚫 حجب</option>
                                                <option value="own"    {{ $savedScope === 'own'    ? 'selected' : '' }}>🏢 جهتي</option>
                                                <option value="parent" {{ $savedScope === 'parent' ? 'selected' : '' }}>📂 عامة</option>
                                                <option value="all"    {{ $savedScope === 'all'    ? 'selected' : '' }}>🏛️ الكل</option>
                                            </select>
                                        @endif

                                        @if(!$hasDedicated && !in_array($moduleKey, ['users', 'roles', 'audit-logs']))
                                            <select name="module_geo_scopes[{{ $moduleKey }}]"
                                                    class="form-select form-select-sm geo-select"
                                                    data-module="{{ $moduleKey }}" data-field="geo"
                                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                                <option value="none" {{ $savedGeo === 'none' ? 'selected' : '' }}>🚫 جغرافي</option>
                                                <option value="all"  {{ $savedGeo === 'all'  ? 'selected' : '' }}>🌐 الكل</option>
                                                @if(in_array($moduleKey, ['projects', 'correspondence', 'plans', 'governorates', 'directorates']))
                                                    <option value="gov" {{ $savedGeo === 'same_governorate' ? 'selected' : '' }}>🏙️ المحافظة</option>
                                                    <option value="dir" {{ $savedGeo === 'same_directorate' ? 'selected' : '' }}>🏢 المديرية</option>
                                                @endif
                                            </select>
                                        @endif

                                        @if($hasDedicated)
                                            <button type="button" class="btn-scope-dedicated"
                                                    data-bs-toggle="modal" data-bs-target="#scopeModal_{{ $moduleKey }}">
                                                <i class="fas fa-cog me-1"></i> نطاقات خاصة
                                            </button>

                                            <div class="modal fade" id="scopeModal_{{ $moduleKey }}" tabindex="-1">
                                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                                    <div class="modal-content premium-modal">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title">
                                                                <i class="fas fa-cog me-2"></i>نطاقات: {{ $moduleLabel }}
                                                            </h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body py-3">
                                                            @include('roles.partials.permission_matrix_dedicated_scopes', [
                                                                'moduleKey'   => $moduleKey,
                                                                'moduleName'  => $moduleKey,
                                                                'moduleLabel' => $moduleLabel,
                                                                'moduleConfig' => $moduleConfig,
                                                                'scopeType'   => $moduleConfig['scope_type'] ?? $moduleKey,
                                                                'savedScope'  => $savedScope,
                                                                'savedGeo'    => $savedGeo,
                                                                'role'        => $role ?? null,
                                                                'viewOnly'    => $viewOnly ?? false
                                                            ])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Row select all --}}
                                <td class="text-center py-3">
                                    @if(!$isReadOnly)
                                        <input type="checkbox" class="row-select-all"
                                               data-module="{{ $moduleKey }}" title="تحديد صف {{ $moduleLabel }}">
                                    @else
                                        <span class="empty-cell"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if(count($allModuleNames) === 0)
                            <tr>
                                <td colspan="100" class="text-center py-5">
                                    <div style="color:#94a3b8;">
                                        <i class="fas fa-inbox fs-1 d-block mb-3" style="opacity:0.4;"></i>
                                        <p class="mb-0 fw-bold">لا توجد وحدات متاحة</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                {{-- Stats Footer --}}
                <div class="table-footer">
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="stat-chip">
                            <span class="dot purple"></span>
                            <span>الوحدات: <strong id="statModules">{{ count($allModuleNames) }}</strong></span>
                        </span>
                        <span class="stat-chip">
                            <span class="dot green"></span>
                            <span>المفعّلة: <strong id="statActive" style="color:#059669;">0</strong></span>
                        </span>
                        <span class="stat-chip">
                            <span class="dot red"></span>
                            <span>المعطّلة: <strong id="statInactive" style="color:#dc2626;">0</strong></span>
                        </span>
                    </div>
                    <div class="footer-hint">
                        <i class="fas fa-lightbulb"></i>
                        <span>مرّر فوق الصفوف للتفاعل السريع</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARDS VIEW --}}
        <div class="cards-view" id="cardsView">
            @foreach($allModuleNames as $moduleName)
                @php
                    $moduleKey = strtolower($moduleName);
                    $moduleLabel = $moduleTranslations[$moduleKey] ?? $moduleName;
                    $moduleConfig = $moduleConfigs[$moduleKey] ?? [];

                    $allPerms = $modulePermsMap[$moduleName]['perms'] ?? [];
                    $indexedPerms = $modulePermsMap[$moduleName]['indexed'] ?? [];
                    $permCount = count($allPerms);

                    $hasAnyPermission = false;
                    foreach ($allPerms as $p) {
                        if (isset($rolePermsLookup[$p->id])) {
                            $hasAnyPermission = true;
                            break;
                        }
                    }

                    if (isset($viewOnly) && $viewOnly === true && !($role->full_access ?? false)) {
                        if (!$hasAnyPermission) continue;
                    }

                    $getPermFast = fn($suffix) => $indexedPerms[$suffix] ?? $indexedPerms[$suffix.'s'] ?? null;

                    $permMap = [
                        'sidebar' => ['perm' => $getPermFast('sidebar'), 'label' => 'قائمة', 'icon' => 'bars', 'class' => 'sidebar'],
                        'view' => ['perm' => $getPermFast('view') ?? $getPermFast('show') ?? $getPermFast('index'), 'label' => 'عرض', 'icon' => 'eye', 'class' => 'view'],
                        'create' => ['perm' => $getPermFast('create') ?? $getPermFast('add'), 'label' => 'إضافة', 'icon' => 'plus', 'class' => 'create'],
                        'edit' => ['perm' => $getPermFast('edit') ?? $getPermFast('update'), 'label' => 'تعديل', 'icon' => 'edit', 'class' => 'edit'],
                        'delete' => ['perm' => $getPermFast('delete') ?? $getPermFast('destroy'), 'label' => 'حذف', 'icon' => 'trash', 'class' => 'delete'],
                        'print' => ['perm' => $getPermFast('print'), 'label' => 'طباعة', 'icon' => 'print', 'class' => 'print'],
                        'search' => ['perm' => $getPermFast('search'), 'label' => 'بحث', 'icon' => 'search', 'class' => 'search'],
                        'export' => ['perm' => $getPermFast('export'), 'label' => 'تصدير', 'icon' => 'file-export', 'class' => 'export'],
                        'import' => ['perm' => $getPermFast('import'), 'label' => 'استيراد', 'icon' => 'file-import', 'class' => 'import'],
                    ];

                    $savedScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleKey] ?? 'own') : 'own';
                    $savedGeo = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleKey] ?? 'none') : 'none';
                    $hasDedicated = $moduleConfig['has_dedicated_scope'] ?? false;
                    $isReadOnly = (isset($viewOnly) && $viewOnly === true);
                    $cardInactive = !($role->full_access ?? false) && !$hasAnyPermission;
                @endphp
                <div class="permission-card {{ $cardInactive ? 'inactive' : '' }}" data-module="{{ $moduleKey }}">
                    {{-- Card Header --}}
                    <div class="card-header">
                        <div class="card-module-badge">
                            {{ strtoupper(substr($moduleKey, 0, 3)) }}
                        </div>
                        <div class="card-title-section">
                            <div class="card-title">{{ $moduleLabel }}</div>
                            <div class="card-perm-count">
                                <i class="fas fa-shield-alt me-1"></i>
                                {{ $permCount }} صلاحية متاحة
                            </div>
                        </div>
                        @if(!$isReadOnly)
                            <label class="card-select-all">
                                <input type="checkbox" class="card-select-all-checkbox" data-module="{{ $moduleKey }}" style="margin:0;">
                                <span>الكل</span>
                            </label>
                        @endif
                    </div>

                    {{-- Permissions Grid --}}
                    <div class="card-permissions-grid">
                        @foreach($permMap as $action => $data)
                            @if($data['perm'])
                                @php
                                    $isChecked = isset($rolePermsLookup[$data['perm']->id]);
                                    $isDisabled = $isReadOnly && !$isChecked && !($role->full_access ?? false);
                                @endphp
                                <label class="perm-item {{ $isChecked ? 'has-permission' : '' }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $data['perm']->id }}"
                                           class="perm-checkbox module-{{ $moduleKey }}"
                                           {{ $isChecked ? 'checked' : '' }} {{ $isDisabled ? 'disabled' : '' }}
                                           data-module="{{ $moduleKey }}" data-action="{{ $action }}">
                                    <div class="perm-item-icon {{ $data['class'] }}">
                                        <i class="fas fa-{{ $data['icon'] }}"></i>
                                    </div>
                                    <div class="perm-item-label">{{ $data['label'] }}</div>
                                </label>
                            @endif
                        @endforeach

                        {{-- Extra Permissions --}}
                        @foreach($uniqueExtraSlugKeys as $extraSlugKey)
                            @php
                                $extraPerm = $indexedPerms[$extraSlugKey] ?? null;
                                $extraLabel = $slugArabicMap[$extraSlugKey]
                                    ?? $slugArabicMap[\Illuminate\Support\Str::afterLast($extraSlugKey, '.')]
                                    ?? str_replace(['-', '.', '_'], ' ', $extraSlugKey);
                            @endphp
                            @if($extraPerm)
                                @php
                                    $isExtraChecked = isset($rolePermsLookup[$extraPerm->id]);
                                    $isExtraDisabled = $isReadOnly && !$isExtraChecked && !($role->full_access ?? false);
                                @endphp
                                <label class="perm-item {{ $isExtraChecked ? 'has-permission' : '' }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $extraPerm->id }}"
                                           class="perm-checkbox module-{{ $moduleKey }}"
                                           {{ $isExtraChecked ? 'checked' : '' }} {{ $isExtraDisabled ? 'disabled' : '' }}
                                           data-module="{{ $moduleKey }}">
                                    <div class="perm-item-icon extra">
                                        <i class="fas fa-key" style="font-size:0.75rem;"></i>
                                    </div>
                                    <div class="perm-item-label" style="font-size:0.65rem;">{{ $extraLabel }}</div>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    {{-- Scopes Section --}}
                    <div class="card-scopes-section">
                        @if(!$hasDedicated && !in_array($moduleKey, ['governorates', 'directorates']))
                            <select name="module_scopes[{{ $moduleKey }}]"
                                    class="card-scope-select"
                                    data-module="{{ $moduleKey }}" data-field="scope"
                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                <option value="none"   {{ $savedScope === 'none'   ? 'selected' : '' }}>🚫 حجب</option>
                                <option value="own"    {{ $savedScope === 'own'    ? 'selected' : '' }}>🏢 جهتي</option>
                                <option value="parent" {{ $savedScope === 'parent' ? 'selected' : '' }}>📂 عامة</option>
                                <option value="all"    {{ $savedScope === 'all'    ? 'selected' : '' }}>🏛️ الكل</option>
                            </select>
                        @endif

                        @if(!$hasDedicated && !in_array($moduleKey, ['users', 'roles', 'audit-logs']))
                            <select name="module_geo_scopes[{{ $moduleKey }}]"
                                    class="card-scope-select"
                                    data-module="{{ $moduleKey }}" data-field="geo"
                                    {{ $isReadOnly ? 'disabled' : '' }}>
                                <option value="none" {{ $savedGeo === 'none' ? 'selected' : '' }}>🚫 جغرافي</option>
                                <option value="all"  {{ $savedGeo === 'all'  ? 'selected' : '' }}>🌐 الكل</option>
                                @if(in_array($moduleKey, ['projects', 'correspondence', 'plans', 'governorates', 'directorates']))
                                    <option value="gov" {{ $savedGeo === 'same_governorate' ? 'selected' : '' }}>🏙️ المحافظة</option>
                                    <option value="dir" {{ $savedGeo === 'same_directorate' ? 'selected' : '' }}>🏢 المديرية</option>
                                @endif
                            </select>
                        @endif

                        @if($hasDedicated)
                            <button type="button" class="btn-scope-dedicated"
                                    data-bs-toggle="modal" data-bs-target="#scopeModal_{{ $moduleKey }}_card">
                                <i class="fas fa-cog me-1"></i> نطاقات خاصة
                            </button>

                            <div class="modal fade" id="scopeModal_{{ $moduleKey }}_card" tabindex="-1">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content premium-modal">
                                        <div class="modal-header">
                                            <h6 class="modal-title">
                                                <i class="fas fa-cog me-2"></i>نطاقات: {{ $moduleLabel }}
                                            </h6>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body py-3">
                                            @include('roles.partials.permission_matrix_dedicated_scopes', [
                                                'moduleKey'   => $moduleKey,
                                                'moduleName'  => $moduleKey,
                                                'moduleLabel' => $moduleLabel,
                                                'moduleConfig' => $moduleConfig,
                                                'scopeType'   => $moduleConfig['scope_type'] ?? $moduleKey,
                                                'savedScope'  => $savedScope,
                                                'savedGeo'    => $savedGeo,
                                                'role'        => $role ?? null,
                                                'viewOnly'    => $viewOnly ?? false
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if(count($allModuleNames) === 0)
                <div class="text-center py-5 w-100">
                    <div style="color:#94a3b8;">
                        <i class="fas fa-inbox fs-1 d-block mb-3" style="opacity:0.4;"></i>
                        <p class="mb-0 fw-bold">لا توجد وحدات متاحة</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- Interactive Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.querySelector('.permission-table-wrapper') || document;
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const toggleBtns = document.querySelectorAll('.view-toggle-btn');
    const table = document.getElementById('permMatrixTable');
    const roleForm = document.getElementById('roleForm');

    // Intercept form submission to prevent duplicate payload and send JSON
    if (roleForm) {
        roleForm.addEventListener('submit', function() {
            const checkedPerms = new Set();
            document.querySelectorAll('.perm-checkbox:checked:not(:disabled)').forEach(cb => {
                checkedPerms.add(cb.value);
            });
            const jsonInput = document.getElementById('permissionsJsonInput');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(Array.from(checkedPerms));
            }
            // Remove name attribute from checkboxes to avoid gigantic POST payload
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.removeAttribute('name'));
        });
    }

    // View Toggle Functionality
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            if (view === 'cards') {
                if (tableView) tableView.classList.add('hidden');
                if (cardsView) cardsView.classList.add('active');
            } else {
                if (tableView) tableView.classList.remove('hidden');
                if (cardsView) cardsView.classList.remove('active');
            }
        });
    });

    // Debounced Stats Update
    let statsTimer = null;
    function debouncedUpdateStats() {
        if (statsTimer) cancelAnimationFrame(statsTimer);
        statsTimer = requestAnimationFrame(updateStats);
    }

    function updateStats() {
        if (!table) return;
        const rows = table.querySelectorAll('tbody tr.module-row');
        let active = 0, inactive = 0;
        rows.forEach(row => {
            if (row.classList.contains('inactive')) inactive++;
            else active++;
        });
        
        const sActive = document.getElementById('statActive');
        const sInactive = document.getElementById('statInactive');
        if (sActive) sActive.textContent = active;
        if (sInactive) sInactive.textContent = inactive;
    }

    // ==========================================
    // Event Delegation for Checkbox Changes
    // ==========================================
    wrapper.addEventListener('change', function(e) {
        const target = e.target;
        if (!target) return;

        // Individual Permission Checkbox
        if (target.classList.contains('perm-checkbox')) {
            const isChecked = target.checked;
            const val = target.value;
            const module = target.dataset.module;

            // Sync twin checkboxes (table vs card)
            document.querySelectorAll(`.perm-checkbox[value="${val}"]`).forEach(otherCb => {
                if (otherCb !== target) otherCb.checked = isChecked;
                const item = otherCb.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', isChecked);
            });

            // Update row active state
            if (module && table) {
                const row = table.querySelector(`tr[data-module="${module}"]`);
                if (row) {
                    const allBoxes = Array.from(table.querySelectorAll(`.perm-checkbox.module-${module}:not(:disabled)`));
                    const checkedBoxes = allBoxes.filter(c => c.checked);
                    row.classList.toggle('inactive', checkedBoxes.length === 0);

                    document.querySelectorAll(`.row-select-all[data-module="${module}"]`).forEach(cb => {
                        cb.checked = (allBoxes.length > 0 && allBoxes.length === checkedBoxes.length);
                        cb.indeterminate = (checkedBoxes.length > 0 && checkedBoxes.length < allBoxes.length);
                    });
                }
            }

            if (typeof syncMainSwitchFromSubmodule === 'function' && module) {
                syncMainSwitchFromSubmodule(module);
            }
            debouncedUpdateStats();
        }

        // Card Select All Checkbox
        else if (target.classList.contains('card-select-all-checkbox')) {
            const module = target.dataset.module;
            const checked = target.checked;

            document.querySelectorAll(`.perm-checkbox.module-${module}:not(:disabled)`).forEach(cb => {
                cb.checked = checked;
                const item = cb.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', checked);
            });

            if (table) {
                const row = table.querySelector(`tr[data-module="${module}"]`);
                if (row) {
                    row.classList.toggle('inactive', !checked);
                    const rowSelectAll = row.querySelector('.row-select-all');
                    if (rowSelectAll) rowSelectAll.checked = checked;
                }
            }

            if (typeof syncMainSwitchFromSubmodule === 'function' && module) {
                syncMainSwitchFromSubmodule(module);
            }
            debouncedUpdateStats();
        }

        // Table Row Select All Checkbox
        else if (target.classList.contains('row-select-all')) {
            const module = target.dataset.module;
            const checked = target.checked;

            document.querySelectorAll(`.row-select-all[data-module="${module}"]`).forEach(cb => {
                cb.checked = checked;
            });

            document.querySelectorAll(`.perm-checkbox.module-${module}:not(:disabled)`).forEach(cb => {
                cb.checked = checked;
                const item = cb.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', checked);
            });

            const card = document.querySelector(`.permission-card[data-module="${module}"]`);
            if (card) {
                card.classList.toggle('inactive', !checked);
                const cardSelectAll = card.querySelector('.card-select-all-checkbox');
                if (cardSelectAll) cardSelectAll.checked = checked;
            }

            if (typeof syncMainSwitchFromSubmodule === 'function' && module) {
                syncMainSwitchFromSubmodule(module);
            }
            debouncedUpdateStats();
        }

        // Table Select All Header
        else if (target.classList.contains('select-all-header')) {
            const checked = target.checked;
            document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = checked;
                const item = cb.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', checked);
            });

            document.querySelectorAll('.card-select-all-checkbox, .row-select-all').forEach(cb => {
                cb.checked = checked;
            });

            document.querySelectorAll('.main-module-switch:not(:disabled)').forEach(ms => ms.checked = checked);
            if (table) {
                table.querySelectorAll('tbody tr.module-row').forEach(r => r.classList.toggle('inactive', !checked));
            }
            debouncedUpdateStats();
        }

        // Main Module Switch
        else if (target.classList.contains('main-module-switch')) {
            syncSubmodulesFromMainSwitch(target);
        }
    });

    // ==========================================
    // Main Modules Synchronization System
    // ==========================================
    const mainToSubModulesMap = {
        'dashboard': ['dashboard', 'home'],
        'projects': ['projects', 'projects-implementation', 'project-requests', 'project-drafts', 'project-files', 'project-risks', 'project-outputs', 'project-activity', 'project-documents', 'executive-activities', 'execution', 'execution-log', 'schedule', 'quality'],
        'tasks': ['tasks'],
        'requests-descend': ['requests_descend', 'requests-descend'],
        'correspondence': ['correspondence', 'referrals', 'project-referrals', 'memoirs'],
        'planning': ['planning', 'plans'],
        'reports': [
            'reports',
            'reports-implementation',
            'reports-quality',
            'reports-financial',
            'reports-progress',
            'reports-erpnext-financial',
            'reports-pl-expense-summary',
            'reports-profit-and-loss',
            'reports-official-summary',
            'reports-stakeholders',
            'reports-permissions'
        ],
        'empowerment': ['empowerment'],
        'value-chains': ['value-chains', 'value-chain-members', 'global-financings', 'chain_plans'],
        'encoding': ['encoding', 'configuration', 'programs', 'domains', 'subdomains', 'interventions', 'governorates', 'directorates', 'sub-areas', 'villages', 'financial-items', 'funding-sources', 'financing-types', 'value-chain-financing-types', 'formfinancing', 'subfinancing-forms', 'authorities', 'main-routers', 'sub-routers', 'priorities', 'units', 'beneficiary-groups', 'signatures', 'internal-entities', 'entity-officers', 'entity-authorities', 'entities'],
        'users': ['users', 'roles', 'roles-permissions', 'audit-logs', 'audit_logs']
    };

    function getSubModulesForMain(mainKey) {
        let subs = Array.from(mainToSubModulesMap[mainKey] || [mainKey]);
        if (table) {
            table.querySelectorAll('tr[data-module]').forEach(tr => {
                const m = tr.dataset.module;
                if (m === mainKey || m.startsWith(mainKey + '-') || m.startsWith(mainKey + '_')) {
                    if (!subs.includes(m)) subs.push(m);
                }
            });
        }
        return subs;
    }

    function getMainModuleForSub(subMod) {
        if (!subMod) return null;
        for (const [mainKey, subs] of Object.entries(mainToSubModulesMap)) {
            if (subs.includes(subMod) || subMod === mainKey || subMod.startsWith(mainKey + '-') || subMod.startsWith(mainKey + '_')) {
                return mainKey;
            }
        }
        return null;
    }

    let isMainSyncing = false;

    function syncSubmodulesFromMainSwitch(mainSwitch) {
        if (isMainSyncing || !mainSwitch) return;
        isMainSyncing = true;
        const mainKey = mainSwitch.dataset.mainModule;
        const checked = mainSwitch.checked;
        const subs = getSubModulesForMain(mainKey);

        subs.forEach(sub => {
            document.querySelectorAll(`.perm-checkbox.module-${sub}:not(:disabled)`).forEach(cb => {
                cb.checked = checked;
                const item = cb.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', checked);
            });

            if (table) {
                const row = table.querySelector(`tr[data-module="${sub}"]`);
                if (row) {
                    row.classList.toggle('inactive', !checked);
                    document.querySelectorAll(`.row-select-all[data-module="${sub}"]`).forEach(rcb => {
                        rcb.checked = checked;
                        rcb.indeterminate = false;
                    });
                }
            }

            const card = document.querySelector(`.permission-card[data-module="${sub}"]`);
            if (card) {
                card.classList.toggle('inactive', !checked);
                const csa = card.querySelector('.card-select-all-checkbox');
                if (csa) csa.checked = checked;
            }
        });

        debouncedUpdateStats();
        isMainSyncing = false;
    }

    function syncMainSwitchFromSubmodule(subMod) {
        if (isMainSyncing || !subMod) return;
        const mainKey = getMainModuleForSub(subMod);
        if (!mainKey) return;
        const mainSwitch = document.querySelector(`.main-module-switch[data-main-module="${mainKey}"]`);
        if (!mainSwitch || mainSwitch.disabled) return;

        const subs = getSubModulesForMain(mainKey);
        let anyChecked = false;
        subs.forEach(sub => {
            if (document.querySelector(`.perm-checkbox.module-${sub}:checked:not(:disabled)`)) {
                anyChecked = true;
            }
        });

        mainSwitch.checked = anyChecked;
    }

    // Fast Search with Debounce
    const searchInput = document.getElementById('permTableSearch');
    if (searchInput) {
        let searchTimer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = this.value.trim().toLowerCase();
            searchTimer = setTimeout(() => {
                if (table) {
                    table.querySelectorAll('tbody tr.module-row').forEach(row => {
                        const label = row.querySelector('.sticky-col')?.textContent.toLowerCase() || '';
                        row.style.display = label.includes(q) ? '' : 'none';
                    });
                }
                document.querySelectorAll('.permission-card').forEach(card => {
                    const label = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                    card.style.display = label.includes(q) ? '' : 'none';
                });
            }, 120);
        });
    }

    // Bulk Buttons
    const selectAllBtn = document.getElementById('selectAllTable');
    const deselectAllBtn = document.getElementById('deselectAllTable');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = true;
                const item = cb.closest('.perm-item');
                if (item) item.classList.add('has-permission');
            });
            document.querySelectorAll('.card-select-all-checkbox, .row-select-all, .select-all-header').forEach(cb => {
                cb.checked = true;
            });
            if (table) {
                table.querySelectorAll('tbody tr.module-row').forEach(r => r.classList.remove('inactive'));
            }
            document.querySelectorAll('.main-module-switch:not(:disabled)').forEach(ms => ms.checked = true);
            debouncedUpdateStats();
        });
    }
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            document.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => {
                cb.checked = false;
                const item = cb.closest('.perm-item');
                if (item) item.classList.remove('has-permission');
            });
            document.querySelectorAll('.card-select-all-checkbox, .row-select-all, .select-all-header').forEach(cb => {
                cb.checked = false;
            });
            if (table) {
                table.querySelectorAll('tbody tr.module-row').forEach(r => r.classList.add('inactive'));
            }
            document.querySelectorAll('.main-module-switch:not(:disabled)').forEach(ms => ms.checked = false);
            debouncedUpdateStats();
        });
    }

    // Full Access Toggle
    const fullAccess = document.getElementById('fullAccessToggle');
    if (fullAccess) {
        fullAccess.addEventListener('change', function () {
            const on = this.checked;
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                if (on) cb.checked = true;
                cb.disabled = on ? true : cb.dataset.originalDisabled === 'true';
                const item = cb.closest('.perm-item');
                if (item && on) item.classList.add('has-permission');
            });
            debouncedUpdateStats();
        });
    }

    // Copy from Role AJAX
    const copyFromRole = document.getElementById('copyFromRole');
    if (copyFromRole) {
        copyFromRole.addEventListener('change', async function() {
            const roleId = this.value;
            if (!roleId) return;

            this.disabled = true;
            const originalText = this.options[this.selectedIndex].text;
            this.options[this.selectedIndex].text = 'جاري النسخ...';

            try {
                const response = await fetch(`/roles/${roleId}/permissions`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Network response error');
                const data = await response.json();
                
                if (data.success) {
                    if (fullAccess) fullAccess.checked = data.full_access;

                    document.querySelectorAll('.perm-checkbox').forEach(cb => {
                        cb.checked = false;
                        cb.disabled = data.full_access ? true : cb.dataset.originalDisabled === 'true';
                        const item = cb.closest('.perm-item');
                        if (item) item.classList.remove('has-permission');
                    });

                    if (data.permissions && Array.isArray(data.permissions)) {
                        const permSet = new Set(data.permissions.map(String));
                        document.querySelectorAll('.perm-checkbox').forEach(cb => {
                            if (permSet.has(String(cb.value)) || data.full_access) {
                                cb.checked = true;
                                const item = cb.closest('.perm-item');
                                if (item) item.classList.add('has-permission');
                            }
                        });
                    }

                    if (table) {
                        table.querySelectorAll('tbody tr.module-row').forEach(row => {
                            const anyChecked = Array.from(row.querySelectorAll('.perm-checkbox:checked')).length > 0;
                            row.classList.toggle('inactive', !anyChecked);
                        });
                    }
                    debouncedUpdateStats();
                    if (typeof toastr !== 'undefined') toastr.success('تم نسخ الصلاحيات بنجاح!');
                }
            } catch (error) {
                console.error(error);
                if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء نسخ الصلاحيات');
            } finally {
                this.disabled = false;
                this.options[this.selectedIndex].text = originalText;
                this.value = '';
            }
        });
    }

    // Clipboard Copy & Paste
    const copyToClipboardBtn = document.getElementById('copyToClipboardBtn');
    const pasteFromClipboardBtn = document.getElementById('pasteFromClipboardBtn');
    
    function checkClipboardData() {
        if (pasteFromClipboardBtn) {
            const data = localStorage.getItem('role_permissions_clipboard');
            pasteFromClipboardBtn.style.display = data ? 'inline-flex' : 'none';
        }
    }
    checkClipboardData();
    
    if (copyToClipboardBtn) {
        copyToClipboardBtn.addEventListener('click', function() {
            const checkedPerms = [];
            document.querySelectorAll('.perm-checkbox:checked:not(:disabled)').forEach(cb => checkedPerms.push(cb.value));
            localStorage.setItem('role_permissions_clipboard', JSON.stringify({
                permissions: checkedPerms,
                full_access: fullAccess?.checked || false
            }));
            checkClipboardData();
            if (typeof toastr !== 'undefined') toastr.success('تم نسخ الصلاحيات إلى الحافظة بنجاح!');
        });
    }

    if (pasteFromClipboardBtn) {
        pasteFromClipboardBtn.addEventListener('click', function() {
            try {
                const dataStr = localStorage.getItem('role_permissions_clipboard');
                if (!dataStr) return;
                const data = JSON.parse(dataStr);
                
                if (fullAccess) fullAccess.checked = data.full_access;

                document.querySelectorAll('.perm-checkbox').forEach(cb => {
                    cb.checked = false;
                    cb.disabled = data.full_access ? true : cb.dataset.originalDisabled === 'true';
                    const item = cb.closest('.perm-item');
                    if (item) item.classList.remove('has-permission');
                });

                if (data.permissions && Array.isArray(data.permissions)) {
                    const permSet = new Set(data.permissions.map(String));
                    document.querySelectorAll('.perm-checkbox').forEach(cb => {
                        if (permSet.has(String(cb.value)) || data.full_access) {
                            cb.checked = true;
                            const item = cb.closest('.perm-item');
                            if (item) item.classList.add('has-permission');
                        }
                    });
                }

                if (table) {
                    table.querySelectorAll('tbody tr.module-row').forEach(row => {
                        const anyChecked = Array.from(row.querySelectorAll('.perm-checkbox:checked')).length > 0;
                        row.classList.toggle('inactive', !anyChecked);
                    });
                }
                debouncedUpdateStats();
                if (typeof toastr !== 'undefined') toastr.success('تم لصق الصلاحيات بنجاح!');
            } catch (error) {
                console.error(error);
                if (typeof toastr !== 'undefined') toastr.error('حدث خطأ أثناء لصق الصلاحيات');
            }
        });
    }

    // Initial stats
    debouncedUpdateStats();
});
</script>