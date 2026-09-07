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

    {{-- Fast lookups using pre-computed controller data --}}
    @php
        $modulePermsMap = $modulePermsMap ?? [];
        $uniqueExtraSlugKeys = $uniqueExtraSlugKeys ?? [];
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

    {{-- Premium Styles --}}
    <style>
        /* ============ VIEW TOGGLE BUTTONS ============ */
        .view-toggle-wrapper {
            display: inline-flex;
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
            border: 2px solid rgba(102,126,234,0.15);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .view-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #64748b;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .view-toggle-btn i {
            font-size: 0.9rem;
        }

        .view-toggle-btn:hover {
            color: #667eea;
            background: rgba(102,126,234,0.08);
        }

        .view-toggle-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(102,126,234,0.35);
        }

        .view-toggle-btn.active::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 100%);
            border-radius: 10px;
        }

        /* ============ CARDS VIEW ============ */
        .cards-view {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
            padding: 20px 0;
            animation: fadeIn 0.4s ease;
        }

        .cards-view.active {
            display: grid;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .permission-card {
            background: linear-gradient(135deg, #ffffff 0%, #fafbff 100%);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(226,232,240,0.6);
            box-shadow:
                0 4px 16px rgba(0,0,0,0.04),
                0 1px 3px rgba(0,0,0,0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .permission-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .permission-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 12px 32px rgba(102,126,234,0.12),
                0 4px 12px rgba(0,0,0,0.06);
            border-color: rgba(102,126,234,0.2);
        }

        .permission-card:hover::before {
            opacity: 1;
        }

        .permission-card.inactive {
            opacity: 0.5;
            filter: grayscale(0.4);
        }

        .permission-card.inactive:hover {
            opacity: 1;
            filter: grayscale(0);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(226,232,240,0.6);
        }

        .card-module-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            height: 48px;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
            box-shadow:
                0 6px 16px rgba(102,126,234,0.35),
                inset 0 1px 0 rgba(255,255,255,0.3);
            position: relative;
            overflow: hidden;
        }

        .card-module-badge::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        .card-title-section {
            flex: 1;
        }

        .card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .card-perm-count {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .card-select-all {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(102,126,234,0.08);
            color: #667eea;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1.5px solid rgba(102,126,234,0.2);
        }

        .card-select-all:hover {
            background: rgba(102,126,234,0.15);
            border-color: #667eea;
        }

        .card-permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .perm-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 10px 8px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1.5px solid rgba(226,232,240,0.8);
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .perm-item:hover {
            background: linear-gradient(135deg, #fff 0%, #fafbff 100%);
            border-color: rgba(102,126,234,0.3);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.06);
        }

        .perm-item.has-permission {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: rgba(16,185,129,0.3);
        }

        .perm-item input[type="checkbox"] {
            position: absolute;
            top: 6px;
            left: 6px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .perm-item-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .perm-item-icon.sidebar { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8; }
        .perm-item-icon.view    { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; }
        .perm-item-icon.create  { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #047857; }
        .perm-item-icon.edit    { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309; }
        .perm-item-icon.delete  { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #b91c1c; }
        .perm-item-icon.export  { background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); color: #0e7490; }
        .perm-item-icon.import  { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7e22ce; }
        .perm-item-icon.extra   { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #475569; }

        .perm-item-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #475569;
            text-align: center;
        }

        .card-scopes-section {
            padding-top: 16px;
            border-top: 1px solid rgba(226,232,240,0.6);
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .card-scope-select {
            flex: 1;
            min-width: 140px;
            padding: 8px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: linear-gradient(135deg, #fafbff 0%, #fff 100%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.25s ease;
            outline: none;
        }

        .card-scope-select:hover {
            border-color: #667eea;
            background: #fff;
        }

        .card-scope-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.12);
        }

        /* ============ TABLE VIEW ============ */
        .table-view {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        .table-view.hidden {
            display: none;
        }

        /* ============ EXISTING STYLES (Keep all previous styles) ============ */
        .permission-table-wrapper {
            font-family: 'Tajawal', 'Cairo', 'Segoe UI', sans-serif;
            position: relative;
        }

        .perm-hero {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            padding: 2px;
            background: linear-gradient(135deg,
                rgba(102,126,234,0.4) 0%,
                rgba(118,75,162,0.4) 25%,
                rgba(236,72,153,0.3) 50%,
                rgba(59,130,246,0.4) 75%,
                rgba(102,126,234,0.4) 100%);
            background-size: 300% 300%;
            animation: gradientShift 12s ease infinite;
            box-shadow:
                0 20px 60px rgba(102,126,234,0.25),
                0 8px 24px rgba(0,0,0,0.08),
                inset 0 1px 0 rgba(255,255,255,0.5);
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .perm-hero-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(102,126,234,0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236,72,153,0.12) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(59,130,246,0.1) 0%, transparent 50%),
                linear-gradient(135deg, #ffffff 0%, #fafbff 100%);
            z-index: 0;
        }

        .perm-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(102,126,234,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            z-index: 0;
        }

        .perm-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(236,72,153,0.12) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 10s ease-in-out infinite reverse;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }

        .perm-hero-content {
            position: relative;
            z-index: 2;
            padding: 24px 28px;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 22px;
        }

        .hero-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.7rem;
            box-shadow:
                0 10px 30px rgba(102,126,234,0.4),
                inset 0 1px 0 rgba(255,255,255,0.3),
                inset 0 -2px 0 rgba(0,0,0,0.1);
            position: relative;
            animation: iconPulse 3s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .hero-icon::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea, #ec4899);
            z-index: -1;
            opacity: 0.5;
            filter: blur(10px);
        }

        .hero-title {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #1e293b 0%, #667eea 50%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 500;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            width: 220px;
            padding: 10px 40px 10px 55px;
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 14px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            font-size: 0.88rem;
            font-weight: 500;
            color: #1e293b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .search-box input:focus {
            width: 260px;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.15), 0 8px 20px rgba(102,126,234,0.15);
        }

        .search-icon {
            position: absolute;
            right: 14px;
            color: #667eea;
            font-size: 0.9rem;
            z-index: 2;
        }

        .search-kbd {
            position: absolute;
            left: 10px;
            padding: 2px 6px;
            font-size: 0.65rem;
            font-weight: 700;
            color: #94a3b8;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-family: monospace;
        }

        .select-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .select-wrapper select {
            padding: 10px 38px 10px 14px;
            border: 2px solid rgba(102,126,234,0.15);
            border-radius: 14px;
            background: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            outline: none;
            transition: all 0.3s ease;
            appearance: none;
        }

        .select-wrapper select:hover,
        .select-wrapper select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
        }

        .select-icon {
            position: absolute;
            right: 12px;
            color: #667eea;
            pointer-events: none;
            font-size: 0.85rem;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border: 2px solid transparent;
            border-radius: 14px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-btn-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #047857;
            border-color: rgba(16,185,129,0.2);
        }

        .action-btn-success:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16,185,129,0.35);
        }

        .action-btn-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #b91c1c;
            border-color: rgba(239,68,68,0.2);
        }

        .action-btn-danger:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239,68,68,0.35);
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
            gap: 8px;
            padding: 10px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid rgba(245,158,11,0.25);
            color: #92400e;
            font-size: 0.82rem;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .switch-icon {
            font-size: 0.9rem;
            transition: all 0.4s ease;
        }

        .premium-switch input:checked + .switch-slider {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(245,158,11,0.4);
            transform: translateY(-2px);
        }

        .premium-switch input:checked + .switch-slider .switch-icon {
            animation: crownBounce 0.6s ease;
        }

        @keyframes crownBounce {
            0%, 100% { transform: scale(1) rotate(0); }
            50% { transform: scale(1.3) rotate(-15deg); }
        }

        .save-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
            background-size: 200% 200%;
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow:
                0 8px 24px rgba(102,126,234,0.4),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .save-btn:hover {
            transform: translateY(-3px);
            background-position: 100% 0;
            box-shadow:
                0 12px 32px rgba(102,126,234,0.5),
                inset 0 1px 0 rgba(255,255,255,0.4);
        }

        .save-btn-shine {
            position: absolute;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: right 0.6s ease;
        }

        .save-btn:hover .save-btn-shine {
            right: 100%;
        }

        .view-only-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(30,41,59,0.3);
        }

        .perm-table-container {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-radius: 24px;
            padding: 3px;
            background-clip: padding-box;
            position: relative;
            box-shadow:
                0 20px 60px rgba(0,0,0,0.08),
                0 8px 24px rgba(0,0,0,0.04);
            border: 1px solid rgba(255,255,255,0.8);
        }

        .perm-table-container::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(135deg,
                rgba(102,126,234,0.3),
                rgba(236,72,153,0.2),
                rgba(59,130,246,0.3));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .perm-table-scroll {
            max-height: 72vh;
            overflow: auto;
            border-radius: 22px;
            background: #fff;
            position: relative;
        }

        .perm-table-scroll::-webkit-scrollbar { width: 10px; height: 10px; }
        .perm-table-scroll::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }
        .perm-table-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            border: 2px solid #f8fafc;
        }
        .perm-table-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3f8f 100%);
        }
        .perm-table-scroll::-webkit-scrollbar-corner { background: #f8fafc; }

        #permMatrixTable {
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
            width: 100%;
        }

        #permMatrixTable thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: linear-gradient(180deg, #fafbff 0%, #f1f5ff 100%);
            backdrop-filter: blur(10px);
            white-space: nowrap;
            border-bottom: 2px solid rgba(102,126,234,0.15);
            color: #475569;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.3px;
            padding: 16px 12px;
            text-transform: uppercase;
        }

        #permMatrixTable thead th small {
            display: block;
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
            text-transform: none;
            letter-spacing: 0;
        }

        #permMatrixTable .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #fff;
            min-width: 240px;
            transition: all 0.3s ease;
        }

        #permMatrixTable thead th.sticky-col {
            z-index: 4;
            background: linear-gradient(180deg, #fafbff 0%, #f1f5ff 100%) !important;
        }

        #permMatrixTable tbody tr:hover .sticky-col {
            background: linear-gradient(90deg, #f8f9ff 0%, #fff 100%) !important;
        }

        #permMatrixTable tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(226,232,240,0.6);
        }

        #permMatrixTable tbody tr:hover {
            background: linear-gradient(90deg,
                rgba(102,126,234,0.04) 0%,
                rgba(236,72,153,0.02) 50%,
                rgba(59,130,246,0.04) 100%) !important;
        }

        #permMatrixTable tbody tr:nth-child(even) {
            background: linear-gradient(90deg, #fafbff 0%, #fff 100%);
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 32px;
            padding: 0 10px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 4px 12px rgba(102,126,234,0.3),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }

        .module-badge::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
        }

        .module-badge span {
            position: relative;
            z-index: 1;
        }

        .module-badge::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
            transform: rotate(45deg);
        }

        tr:hover .module-badge::after, .permission-card:hover .card-module-badge::after {
            animation: shimmer 1s ease;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .perm-checkbox, .row-select-all, .select-all-header {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            cursor: pointer;
            position: relative;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            margin: 0;
            vertical-align: middle;
        }

        .perm-checkbox:hover:not(:disabled),
        .row-select-all:hover:not(:disabled),
        .select-all-header:hover:not(:disabled) {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
            transform: scale(1.08);
        }

        .perm-checkbox:checked,
        .row-select-all:checked,
        .select-all-header:checked {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            box-shadow:
                0 4px 12px rgba(102,126,234,0.4),
                inset 0 1px 0 rgba(255,255,255,0.3);
            animation: checkBounce 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes checkBounce {
            0% { transform: scale(0.8); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        .perm-checkbox:checked::after,
        .row-select-all:checked::after,
        .select-all-header:checked::after {
            content: '';
            position: absolute;
            top: 3px;
            right: 6px;
            width: 6px;
            height: 11px;
            border: solid #fff;
            border-width: 0 2.5px 2.5px 0;
            transform: rotate(45deg);
        }

        .perm-checkbox:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f1f5f9;
        }

        .col-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            margin-bottom: 6px;
            font-size: 0.9rem;
            position: relative;
            transition: all 0.3s ease;
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.5),
                0 2px 6px rgba(0,0,0,0.06);
        }

        .col-icon.sidebar { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1d4ed8; }
        .col-icon.view    { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); color: #0369a1; }
        .col-icon.create  { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #047857; }
        .col-icon.edit    { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309; }
        .col-icon.delete  { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #b91c1c; }
        .col-icon.export  { background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%); color: #0e7490; }
        .col-icon.import  { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); color: #7e22ce; }
        .col-icon.extra   { background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%); color: #475569; }

        /* Fix for Font Awesome icons rendering inside matrix viewports */
        .permission-table-wrapper .col-icon i,
        .permission-table-wrapper .perm-item-icon i,
        .permission-table-wrapper .hero-icon i,
        .permission-table-wrapper .btn-scope-dedicated i,
        .permission-table-wrapper .modal-title i {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            font-variant: normal !important;
            line-height: 1 !important;
            display: inline-block !important;
            color: inherit !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }

        tr:hover .col-icon {
            transform: translateY(-2px) scale(1.05);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.5),
                0 6px 14px rgba(0,0,0,0.1);
        }

        .scope-select, .geo-select {
            border: 1.5px solid #e2e8f0;
            background: linear-gradient(135deg, #fafbff 0%, #fff 100%);
            border-radius: 10px !important;
            font-size: 0.72rem !important;
            padding: 5px 8px !important;
            font-weight: 600;
            color: #334155;
            transition: all 0.25s ease;
            cursor: pointer;
            outline: none;
        }

        .scope-select:hover, .geo-select:hover {
            border-color: #667eea;
            background: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(102,126,234,0.12);
        }

        .scope-select:focus, .geo-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.15);
        }

        .empty-cell {
            display: inline-block;
            width: 22px;
            height: 3px;
            background: linear-gradient(90deg, #e2e8f0 0%, #cbd5e1 100%);
            border-radius: 3px;
            opacity: 0.6;
        }

        .btn-scope-dedicated {
            background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
            border: 1.5px dashed #667eea;
            color: #667eea;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 6px 14px;
            border-radius: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .btn-scope-dedicated:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-style: solid;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.35);
        }

        .module-row.inactive {
            opacity: 0.5;
            filter: grayscale(0.4);
        }

        .module-row.inactive:hover {
            opacity: 1;
            filter: grayscale(0);
        }

        .table-footer {
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: linear-gradient(135deg, #fafbff 0%, #f1f5ff 100%);
            border-top: 1px solid rgba(102,126,234,0.1);
            border-radius: 0 0 22px 22px;
        }

        .stat-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 12px;
            background: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid rgba(226,232,240,0.8);
            transition: all 0.3s ease;
        }

        .stat-chip:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        }

        .stat-chip .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: relative;
        }

        .stat-chip .dot::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.3;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.5); opacity: 0; }
        }

        .stat-chip .dot.purple { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-chip .dot.green  { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-chip .dot.red    { background: linear-gradient(135deg, #ef4444, #dc2626); }

        .footer-hint {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
        }

        .footer-hint i {
            color: #f59e0b;
            animation: bulbGlow 2s ease-in-out infinite;
        }

        @keyframes bulbGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        .modal-content.premium-modal {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        }

        .premium-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ec4899 100%);
            border: none;
            padding: 18px 22px;
        }

        .premium-modal .modal-title {
            color: #fff;
            font-weight: 800;
        }

        @media (max-width: 992px) {
            .perm-hero-content { padding: 18px; }
            .hero-title { font-size: 1.2rem; }
            .search-box input { width: 180px; }
            .search-box input:focus { width: 200px; }
            .cards-view { grid-template-columns: 1fr; }
        }
    </style>

    @php
        $mainModulePerms = $mainModulePerms ?? collect();
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
                            <th class="text-center py-3" style="width:130px;" title="صلاحيات إضافية خاصة بالوحدة">
                                <span class="col-icon extra"><i class="fas fa-key" style="font-size:0.8rem;"></i></span>
                                <small>صلاحيات خاصة</small>
                            </th>
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

                                {{-- Special / Extra Permissions Cell --}}
                                @php
                                    $extraPerms = $modulePermsMap[$moduleName]['extras'] ?? [];
                                    $checkedExtraCount = 0;
                                    foreach ($extraPerms as $ep) {
                                        if (isset($rolePermsLookup[$ep->id])) {
                                            $checkedExtraCount++;
                                        }
                                    }
                                @endphp
                                <td class="text-center py-3">
                                    @if(count($extraPerms) > 0)
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill py-1 px-2 d-inline-flex align-items-center gap-1 extra-perms-btn"
                                                    type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="font-size:0.75rem;">
                                                <i class="fas fa-key"></i>
                                                <span>{{ count($extraPerms) }} خاصة</span>
                                                <span class="badge bg-primary rounded-pill ms-1 extra-badge-count {{ $checkedExtraCount > 0 ? '' : 'd-none' }}">{{ $checkedExtraCount }}</span>
                                            </button>
                                            <div class="dropdown-menu p-3 shadow-lg" style="min-width: 290px; max-width: 360px; max-height: 320px; overflow-y: auto; z-index: 1060; text-align: right;">
                                                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                                    <h6 class="dropdown-header p-0 text-dark fw-bold mb-0" style="font-size: 0.8rem;">
                                                        صلاحيات خاصة: {{ $moduleLabel }}
                                                    </h6>
                                                    <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">{{ count($extraPerms) }}</span>
                                                </div>
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($extraPerms as $extraPerm)
                                                        @php
                                                            $isExtraChecked = isset($rolePermsLookup[$extraPerm->id]);
                                                            $isExtraDisabled = $isReadOnly && !$isExtraChecked && !($role->full_access ?? false);
                                                            $slugParts = explode('.', $extraPerm->slug);
                                                            $lastPart = end($slugParts);
                                                            $permLabel = ($extraPerm->name ?? null) ?: ($slugArabicMap[$lastPart] ?? str_replace(['-', '.', '_'], ' ', $lastPart));
                                                        @endphp
                                                        <label class="d-flex align-items-center gap-2 p-1 rounded hover-bg" style="cursor: pointer; font-size: 0.76rem; user-select: none;">
                                                            <input type="checkbox" name="permissions[]" value="{{ $extraPerm->id }}"
                                                                   class="perm-checkbox module-{{ $moduleKey }} extra-perm-cb"
                                                                   {{ $isExtraChecked ? 'checked' : '' }} {{ $isExtraDisabled ? 'disabled' : '' }}
                                                                   data-module="{{ $moduleKey }}" title="{{ $extraPerm->slug }}">
                                                            <span class="text-dark">{{ $permLabel }}</span>
                                                            <small class="text-muted ms-auto text-truncate" style="max-width: 100px; font-size: 0.65rem;" title="{{ $extraPerm->slug }}">{{ $lastPart }}</small>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

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

        {{-- CARDS VIEW (Loaded on-demand when user toggles to cards) --}}
        <div class="cards-view" id="cardsView" data-rendered="false"></div>

    </div>
</div>

{{-- Interactive Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableView = document.getElementById('tableView');
    const cardsView = document.getElementById('cardsView');
    const toggleBtns = document.querySelectorAll('.view-toggle-btn');
    const table = document.getElementById('permMatrixTable');
    const isReadOnly = {{ (isset($viewOnly) && $viewOnly === true) ? 'true' : 'false' }};

    // 1. Intercept form submission to collect permissions as JSON
    const roleForm = document.getElementById('roleForm');
    if (roleForm) {
        roleForm.addEventListener('submit', function() {
            const checkedPerms = new Set();
            document.querySelectorAll('#permMatrixTable .perm-checkbox:checked:not(:disabled)').forEach(cb => {
                checkedPerms.add(cb.value);
            });
            document.querySelectorAll('.main-module-switch:checked:not(:disabled)').forEach(cb => {
                checkedPerms.add(cb.value);
            });
            const jsonInput = document.getElementById('permissionsJsonInput');
            if (jsonInput) {
                jsonInput.value = JSON.stringify(Array.from(checkedPerms));
            }
        });
    }

    // 2. View Toggle Functionality (Lazy render cards if clicked)
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.dataset.view;
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (view === 'cards') {
                ensureCardsRendered();
                tableView.classList.add('hidden');
                cardsView.classList.add('active');
            } else {
                tableView.classList.remove('hidden');
                cardsView.classList.remove('active');
            }
        });
    });

    // 3. Lazy Cards View Renderer
    function ensureCardsRendered() {
        if (cardsView.dataset.rendered === 'true') return;
        cardsView.dataset.rendered = 'true';

        const rows = table.querySelectorAll('tbody tr.module-row');
        const frag = document.createDocumentFragment();

        const actionMap = {
            'sidebar': { label: 'قائمة', icon: 'bars', cls: 'sidebar' },
            'view': { label: 'عرض', icon: 'eye', cls: 'view' },
            'create': { label: 'إضافة', icon: 'plus', cls: 'create' },
            'edit': { label: 'تعديل', icon: 'edit', cls: 'edit' },
            'delete': { label: 'حذف', icon: 'trash', cls: 'delete' },
            'print': { label: 'طباعة', icon: 'print', cls: 'print' },
            'search': { label: 'بحث', icon: 'search', cls: 'search' },
            'export': { label: 'تصدير', icon: 'file-export', cls: 'export' },
            'import': { label: 'استيراد', icon: 'file-import', cls: 'import' }
        };

        rows.forEach(row => {
            const module = row.dataset.module;
            const badgeEl = row.querySelector('.module-badge span');
            const badgeText = badgeEl ? badgeEl.textContent.trim() : module.substring(0, 3).toUpperCase();
            const labelEl = row.querySelector('.sticky-col .fw-bold');
            const moduleLabel = labelEl ? labelEl.textContent.trim() : module;
            const countEl = row.querySelector('.sticky-col div[style*="color:#94a3b8"]');
            const countText = countEl ? countEl.textContent.trim() : '';
            const isInactive = row.classList.contains('inactive');

            const card = document.createElement('div');
            card.className = `permission-card ${isInactive ? 'inactive' : ''}`;
            card.dataset.module = module;

            let gridHtml = '';
            row.querySelectorAll('.perm-checkbox').forEach(cb => {
                const action = cb.dataset.action || '';
                const title = cb.getAttribute('title') || '';
                const val = cb.value;
                const checked = cb.checked;
                const disabled = cb.disabled;

                const meta = actionMap[action] || {
                    label: title.split('.').pop(),
                    icon: 'key',
                    cls: 'extra'
                };

                gridHtml += `
                    <label class="perm-item ${checked ? 'has-permission' : ''}">
                        <input type="checkbox" value="${val}" class="card-cb" data-module="${module}"
                               ${checked ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                        <div class="perm-item-icon ${meta.cls}">
                            <i class="fas fa-${meta.icon}"></i>
                        </div>
                        <div class="perm-item-label">${meta.label}</div>
                    </label>
                `;
            });

            let scopesHtml = '';
            const scopesCol = row.querySelector('td:nth-last-child(2)');
            if (scopesCol) {
                const selects = scopesCol.querySelectorAll('select');
                if (selects.length > 0) {
                    scopesHtml += '<div class="card-scopes-section">';
                    selects.forEach(sel => {
                        scopesHtml += `<div style="flex:1; min-width:130px;">${sel.outerHTML}</div>`;
                    });
                    scopesHtml += '</div>';
                }
            }

            card.innerHTML = `
                <div class="card-header">
                    <div class="card-module-badge">${badgeText}</div>
                    <div class="card-title-section">
                        <div class="card-title">${moduleLabel}</div>
                        <div class="card-perm-count">${countText}</div>
                    </div>
                    ${!isReadOnly ? `
                    <label class="card-select-all">
                        <input type="checkbox" class="card-select-all-checkbox" data-module="${module}" style="margin:0;">
                        <span>الكل</span>
                    </label>` : ''}
                </div>
                <div class="card-permissions-grid">${gridHtml}</div>
                ${scopesHtml}
            `;

            frag.appendChild(card);
        });

        cardsView.appendChild(frag);

        cardsView.addEventListener('change', function(e) {
            if (e.target.matches('.card-cb')) {
                const val = e.target.value;
                const checked = e.target.checked;
                const item = e.target.closest('.perm-item');
                if (item) item.classList.toggle('has-permission', checked);

                const tableCb = table.querySelector(`.perm-checkbox[value="${val}"]`);
                if (tableCb && tableCb.checked !== checked) {
                    tableCb.checked = checked;
                    tableCb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else if (e.target.matches('.card-select-all-checkbox')) {
                const module = e.target.dataset.module;
                const checked = e.target.checked;
                const rowSelect = table.querySelector(`.row-select-all[data-module="${module}"]`);
                if (rowSelect) {
                    rowSelect.checked = checked;
                    rowSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    }

    // 4. Optimized Event Delegation for Matrix Table
    if (table) {
        table.addEventListener('change', function(e) {
            const target = e.target;

            // Handle individual permission checkbox change
            if (target.matches('.perm-checkbox')) {
                const row = target.closest('tr');
                const module = target.dataset.module;

                if (row) {
                    const allBoxes = row.querySelectorAll('.perm-checkbox:not(:disabled)');
                    const checkedBoxes = row.querySelectorAll('.perm-checkbox:checked:not(:disabled)');
                    const anyChecked = checkedBoxes.length > 0;
                    row.classList.toggle('inactive', !anyChecked);

                    const rowSelect = row.querySelector('.row-select-all');
                    if (rowSelect) {
                        rowSelect.checked = allBoxes.length > 0 && allBoxes.length === checkedBoxes.length;
                        rowSelect.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < allBoxes.length;
                    }
                }

                // Sync card checkbox if cards are rendered
                if (cardsView.dataset.rendered === 'true') {
                    const card = cardsView.querySelector(`.permission-card[data-module="${module}"]`);
                    if (card) {
                        const cardCb = card.querySelector(`.card-cb[value="${target.value}"]`);
                        if (cardCb) {
                            cardCb.checked = target.checked;
                            const item = cardCb.closest('.perm-item');
                            if (item) item.classList.toggle('has-permission', target.checked);
                        }
                        const cardRow = row.querySelector('.row-select-all');
                        const cardSelectAll = card.querySelector('.card-select-all-checkbox');
                        if (cardSelectAll && cardRow) {
                            cardSelectAll.checked = cardRow.checked;
                        }
                    }
                }

                syncMainSwitchFromSubmodule(module);
                updateStats();
            }

            // Handle Row Select All
            else if (target.matches('.row-select-all')) {
                const module = target.dataset.module;
                const checked = target.checked;
                const row = target.closest('tr');

                if (row) {
                    row.querySelectorAll(`.perm-checkbox.module-${module}:not(:disabled)`).forEach(cb => {
                        cb.checked = checked;
                    });
                    row.classList.toggle('inactive', !checked);
                }

                // Sync card if rendered
                if (cardsView.dataset.rendered === 'true') {
                    const card = cardsView.querySelector(`.permission-card[data-module="${module}"]`);
                    if (card) {
                        card.classList.toggle('inactive', !checked);
                        const csa = card.querySelector('.card-select-all-checkbox');
                        if (csa) csa.checked = checked;
                        card.querySelectorAll('.card-cb:not(:disabled)').forEach(cb => {
                            cb.checked = checked;
                            const item = cb.closest('.perm-item');
                            if (item) item.classList.toggle('has-permission', checked);
                        });
                    }
                }

                syncMainSwitchFromSubmodule(module);
                updateStats();
            }

            // Handle Table Select All Header
            else if (target.matches('.select-all-header')) {
                const checked = target.checked;
                table.querySelectorAll('.perm-checkbox:not(:disabled)').forEach(cb => {
                    cb.checked = checked;
                });
                table.querySelectorAll('.row-select-all').forEach(cb => {
                    cb.checked = checked;
                    cb.indeterminate = false;
                });
                table.querySelectorAll('tbody tr.module-row').forEach(row => {
                    row.classList.toggle('inactive', !checked);
                });
                document.querySelectorAll('.main-module-switch:not(:disabled)').forEach(ms => {
                    ms.checked = checked;
                });

                if (cardsView.dataset.rendered === 'true') {
                    cardsView.querySelectorAll('.card-cb:not(:disabled)').forEach(cb => {
                        cb.checked = checked;
                        const item = cb.closest('.perm-item');
                        if (item) item.classList.toggle('has-permission', checked);
                    });
                    cardsView.querySelectorAll('.card-select-all-checkbox').forEach(cb => cb.checked = checked);
                    cardsView.querySelectorAll('.permission-card').forEach(card => card.classList.toggle('inactive', !checked));
                }

                updateStats();
            }
        });
    }

    // 5. Main Modules Synchronization System
    const mainToSubModulesMap = {
        'dashboard': ['dashboard', 'home'],
        'projects': ['projects', 'projects-implementation', 'project-requests', 'project-drafts', 'project-files', 'project-risks', 'project-outputs', 'project-activity', 'project-documents', 'executive-activities', 'execution', 'execution-log', 'schedule', 'quality'],
        'tasks': ['tasks'],
        'requests-descend': ['requests_descend', 'requests-descend'],
        'correspondence': ['correspondence', 'referrals', 'project-referrals', 'memoirs'],
        'planning': ['planning', 'plans'],
        'reports': ['reports'],
        'empowerment': ['empowerment'],
        'value-chains': ['value-chains', 'value-chain-members', 'global-financings', 'chain_plans'],
        'encoding': ['encoding', 'configuration', 'programs', 'domains', 'subdomains', 'interventions', 'governorates', 'directorates', 'sub-areas', 'villages', 'financial-items', 'funding-sources', 'financing-types', 'value-chain-financing-types', 'formfinancing', 'subfinancing-forms', 'authorities', 'main-routers', 'sub-routers', 'priorities', 'units', 'beneficiary-groups', 'signatures', 'internal-entities', 'entity-officers', 'entity-authorities', 'entities'],
        'users': ['users', 'roles', 'roles-permissions', 'audit-logs', 'audit_logs']
    };

    function getSubModulesForMain(mainKey) {
        return mainToSubModulesMap[mainKey] || [mainKey];
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
            const row = table ? table.querySelector(`tr[data-module="${sub}"]`) : null;
            if (row) {
                row.querySelectorAll(`.perm-checkbox.module-${sub}:not(:disabled)`).forEach(cb => {
                    cb.checked = checked;
                });
                row.classList.toggle('inactive', !checked);
                const rcb = row.querySelector('.row-select-all');
                if (rcb) {
                    rcb.checked = checked;
                    rcb.indeterminate = false;
                }
            }
        });

        if (cardsView.dataset.rendered === 'true') {
            subs.forEach(sub => {
                const card = cardsView.querySelector(`.permission-card[data-module="${sub}"]`);
                if (card) {
                    card.classList.toggle('inactive', !checked);
                    const csa = card.querySelector('.card-select-all-checkbox');
                    if (csa) csa.checked = checked;
                    card.querySelectorAll('.card-cb:not(:disabled)').forEach(cb => {
                        cb.checked = checked;
                        const item = cb.closest('.perm-item');
                        if (item) item.classList.toggle('has-permission', checked);
                    });
                }
            });
        }

        updateStats();
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
        for (let i = 0; i < subs.length; i++) {
            if (document.querySelector(`.perm-checkbox.module-${subs[i]}:checked:not(:disabled)`)) {
                anyChecked = true;
                break;
            }
        }
        mainSwitch.checked = anyChecked;
    }

    document.querySelectorAll('.main-module-switch').forEach(ms => {
        ms.addEventListener('change', function() {
            syncSubmodulesFromMainSwitch(this);
        });
    });

    // 6. Fast Stats Update
    function updateStats() {
        if (!table) return;
        const rows = table.querySelectorAll('tbody tr.module-row');
        const total = rows.length;
        const inactive = table.querySelectorAll('tbody tr.module-row.inactive').length;
        const active = total - inactive;

        const sActive = document.getElementById('statActive');
        const sInactive = document.getElementById('statInactive');
        if (sActive) sActive.textContent = active;
        if (sInactive) sInactive.textContent = inactive;
    }

    // 7. Search Filter
    const searchInput = document.getElementById('permTableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            if (table) {
                table.querySelectorAll('tbody tr.module-row').forEach(row => {
                    const label = row.querySelector('.sticky-col')?.textContent.toLowerCase() || '';
                    row.style.display = label.includes(q) ? '' : 'none';
                });
            }
            if (cardsView.dataset.rendered === 'true') {
                cardsView.querySelectorAll('.permission-card').forEach(card => {
                    const label = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
                    card.style.display = label.includes(q) ? '' : 'none';
                });
            }
        });
    }

    // 8. Bulk Buttons
    const selectAllBtn = document.getElementById('selectAllTable');
    const deselectAllBtn = document.getElementById('deselectAllTable');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            const headerCb = table.querySelector('.select-all-header');
            if (headerCb) {
                headerCb.checked = true;
                headerCb.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            const headerCb = table.querySelector('.select-all-header');
            if (headerCb) {
                headerCb.checked = false;
                headerCb.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    // 9. Full Access Toggle
    const fullAccess = document.getElementById('fullAccessToggle');
    if (fullAccess) {
        fullAccess.addEventListener('change', function () {
            const on = this.checked;
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                if (on) cb.checked = true;
                cb.disabled = on ? true : cb.dataset.originalDisabled === 'true';
            });
            table.querySelectorAll('tbody tr.module-row').forEach(row => {
                row.classList.toggle('inactive', !on);
            });
            updateStats();
        });
    }

    // 10. Copy from Role
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
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();

                if (data.success) {
                    if (fullAccess) fullAccess.checked = data.full_access;

                    const permSet = new Set((data.permissions || []).map(String));
                    document.querySelectorAll('.perm-checkbox').forEach(cb => {
                        cb.checked = permSet.has(String(cb.value)) || data.full_access;
                        cb.disabled = data.full_access ? true : cb.dataset.originalDisabled === 'true';
                    });

                    table.querySelectorAll('tbody tr.module-row').forEach(row => {
                        const anyChecked = row.querySelectorAll('.perm-checkbox:checked').length > 0;
                        row.classList.toggle('inactive', !anyChecked);
                        const rowSelect = row.querySelector('.row-select-all');
                        if (rowSelect) {
                            const all = row.querySelectorAll('.perm-checkbox:not(:disabled)');
                            const checked = row.querySelectorAll('.perm-checkbox:checked:not(:disabled)');
                            rowSelect.checked = all.length > 0 && all.length === checked.length;
                        }
                    });

                    updateStats();
                    alert('تم نسخ الصلاحيات بنجاح!');
                }
            } catch (error) {
                console.error('Error copying permissions:', error);
                alert('حدث خطأ أثناء نسخ الصلاحيات');
            } finally {
                this.disabled = false;
                this.options[this.selectedIndex].text = originalText;
                this.value = '';
            }
        });
    }

    // 11. Clipboard Copy & Paste
    const copyToClipboardBtn = document.getElementById('copyToClipboardBtn');
    const pasteFromClipboardBtn = document.getElementById('pasteFromClipboardBtn');

    function checkClipboardData() {
        if (pasteFromClipboardBtn) {
            pasteFromClipboardBtn.style.display = localStorage.getItem('role_permissions_clipboard') ? 'inline-flex' : 'none';
        }
    }
    checkClipboardData();

    if (copyToClipboardBtn) {
        copyToClipboardBtn.addEventListener('click', function() {
            const checkedPerms = new Set();
            document.querySelectorAll('#permMatrixTable .perm-checkbox:checked:not(:disabled)').forEach(cb => {
                checkedPerms.add(cb.value);
            });
            const clipboardData = {
                permissions: Array.from(checkedPerms),
                full_access: fullAccess?.checked || false
            };
            localStorage.setItem('role_permissions_clipboard', JSON.stringify(clipboardData));
            checkClipboardData();
            alert('تم نسخ الصلاحيات إلى الحافظة بنجاح!');
        });
    }

    if (pasteFromClipboardBtn) {
        pasteFromClipboardBtn.addEventListener('click', function() {
            try {
                const dataStr = localStorage.getItem('role_permissions_clipboard');
                if (!dataStr) return;
                const data = JSON.parse(dataStr);

                if (fullAccess) fullAccess.checked = data.full_access;
                const permSet = new Set((data.permissions || []).map(String));

                document.querySelectorAll('.perm-checkbox').forEach(cb => {
                    cb.checked = permSet.has(String(cb.value)) || data.full_access;
                    cb.disabled = data.full_access ? true : cb.dataset.originalDisabled === 'true';
                });

                table.querySelectorAll('tbody tr.module-row').forEach(row => {
                    const anyChecked = row.querySelectorAll('.perm-checkbox:checked').length > 0;
                    row.classList.toggle('inactive', !anyChecked);
                    const rowSelect = row.querySelector('.row-select-all');
                    if (rowSelect) {
                        const all = row.querySelectorAll('.perm-checkbox:not(:disabled)');
                        const checked = row.querySelectorAll('.perm-checkbox:checked:not(:disabled)');
                        rowSelect.checked = all.length > 0 && all.length === checked.length;
                    }
                });

                updateStats();
                alert('تم لصق الصلاحيات بنجاح!');
            } catch (error) {
                console.error('Error pasting permissions:', error);
                alert('حدث خطأ أثناء لصق الصلاحيات');
            }
        });
    }

    // 12. Fast Initial Stats Setup
    updateStats();
});
</script>