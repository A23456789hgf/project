{-- dedicated_scopes.blade.php --}}
@php
    // This partial receives: $moduleName, $moduleLabel, $moduleConfig, $scopeType, $role, $viewOnly, $selectedScope, $savedGeoScope
@endphp

@switch($scopeType)
    @case('projects')
        @php
            $projectAdminScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleName] ?? 'own') : 'own';
            $projectGeoScope   = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleName] ?? 'none') : 'none';
            $hideProjectsScope = (isset($viewOnly) && $viewOnly === true && $projectAdminScope === 'none' && $projectGeoScope === 'none' && !($role->full_access ?? false));
        @endphp
        @if(!$hideProjectsScope)
        <div class="space-y-3 p-2 rounded-lg" style="background: {{ $moduleConfig['bg_gradient'] ?? 'linear-gradient(135deg, #e0e7ff, #f0f9ff)' }};">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-700"><i class="fas fa-project-diagram"></i> نطاق المشاريع</div>
            <select name="module_geo_scopes[{{ $moduleName }}]" class="geo-scope-select form-select text-sm w-full rounded-lg" data-module="{{ $moduleName }}" data-field="geo_scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $projectGeoScope === 'none' ? 'selected' : '' }}>🚫 حجب جغرافي</option>
                <option value="all" {{ $projectGeoScope === 'all' ? 'selected' : '' }}>🌐 كافة المشاريع</option>
                <option value="same_governorate" {{ $projectGeoScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظة المستخدم</option>
                <option value="same_directorate" {{ $projectGeoScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديرية المستخدم</option>
                <option value="custom" {{ $projectGeoScope === 'custom' ? 'selected' : '' }}>⚙️ مخصص</option>
            </select>
            <select name="module_scopes[{{ $moduleName }}]" class="module-scope-select form-select text-sm w-full rounded-lg" data-module="{{ $moduleName }}" data-field="scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $projectAdminScope === 'none' ? 'selected' : '' }}>🚫 حجب إداري</option>
                <option value="user" {{ $projectAdminScope === 'user' ? 'selected' : '' }}>👤 مشاريع المستخدم</option>
                <option value="own" {{ $projectAdminScope === 'own' ? 'selected' : '' }}>🏢 مشاريع الجهة</option>
                <option value="dept_in_gen_dir" {{ $projectAdminScope === 'dept_in_gen_dir' ? 'selected' : '' }}>📂 كافة جهات القطاع</option>
                <option value="all" {{ $projectAdminScope === 'all' ? 'selected' : '' }}>🏛️ كافة المشاريع</option>
            </select>
        </div>
        @endif
        @break

    @case('planning')
        @php
            $planGeoScope = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleName] ?? 'none') : 'none';
            $planAdminScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleName] ?? 'none') : 'none';
        @endphp
        <div class="space-y-2 p-2 rounded-lg bg-amber-50/40">
            <div class="text-xs font-bold"><i class="fas fa-calendar-alt"></i> نطاق الخطط</div>
            <select name="module_geo_scopes[{{ $moduleName }}]" class="geo-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="geo_scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $planGeoScope === 'none' ? 'selected' : '' }}>🚫 حجب</option>
                <option value="all" {{ $planGeoScope === 'all' ? 'selected' : '' }}>🌐 الكل</option>
                <option value="same_governorate" {{ $planGeoScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظة المستخدم</option>
                <option value="same_directorate" {{ $planGeoScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديرية المستخدم</option>
            </select>
            <select name="module_scopes[{{ $moduleName }}]" class="module-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $planAdminScope === 'none' ? 'selected' : '' }}>🚫 حجب إداري</option>
                <option value="user" {{ $planAdminScope === 'user' ? 'selected' : '' }}>👤 خططي</option>
                <option value="own" {{ $planAdminScope === 'own' ? 'selected' : '' }}>🏢 خطط جهتي</option>
                <option value="all" {{ $planAdminScope === 'all' ? 'selected' : '' }}>🏛️ الكل</option>
            </select>
        </div>
        @break

    @case('correspondence')
        @php
            $corrGeoScope = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleName] ?? 'all') : 'all';
            $corrAdminScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleName] ?? 'all') : 'all';
        @endphp
        <div class="space-y-2 p-2 rounded-lg bg-indigo-50/40">
            <div class="text-xs font-bold"><i class="fas fa-envelope"></i> نطاق المراسلات</div>
            <select name="module_scopes[{{ $moduleName }}]" class="module-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $corrAdminScope === 'none' ? 'selected' : '' }}>🚫 حجب</option>
                <option value="user" {{ $corrAdminScope === 'user' ? 'selected' : '' }}>👤 مراسلاتي</option>
                <option value="own" {{ $corrAdminScope === 'own' ? 'selected' : '' }}>🏢 مراسلات جهتي</option>
                <option value="all" {{ $corrAdminScope === 'all' ? 'selected' : '' }}>🏛️ الكل</option>
            </select>
            <select name="module_geo_scopes[{{ $moduleName }}]" class="geo-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="geo_scope" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $corrGeoScope === 'none' ? 'selected' : '' }}>🚫 حجب جغرافي</option>
                <option value="all" {{ $corrGeoScope === 'all' ? 'selected' : '' }}>🌐 الكل</option>
                <option value="same_governorate" {{ $corrGeoScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظتي</option>
                <option value="same_directorate" {{ $corrGeoScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديريتي</option>
            </select>
        </div>
        @break

    @case('internal-entities')
    @case('authorities')
        @php
            $key = ($moduleName === 'internal-entities') ? 'internal_entities' : 'authorities';
            $savedDisplayScope = isset($role) && is_array($role->entity_display_scope) ? ($role->entity_display_scope[$key] ?? 'none') : 'none';
            $savedAddScope = isset($role) && is_array($role->entity_add_scope) ? ($role->entity_add_scope[$key] ?? 'none') : 'none';
        @endphp
        <div class="space-y-2 p-2 rounded-lg bg-emerald-50/40">
            <div class="text-xs font-bold"><i class="fas fa-building"></i> نطاق {{ $moduleLabel }}</div>
            <select name="entity_display_scope[{{ $key }}]" class="form-select text-sm w-full" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $savedDisplayScope === 'none' ? 'selected' : '' }}>🚫 حجب العرض</option>
                <option value="all" {{ $savedDisplayScope === 'all' ? 'selected' : '' }}>🌐 عرض الكل</option>
                <option value="same_governorate" {{ $savedDisplayScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظتي</option>
                <option value="same_directorate" {{ $savedDisplayScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديريتي</option>
            </select>
            <select name="entity_add_scope[{{ $key }}]" class="form-select text-sm w-full" {{ (isset($viewOnly) && $viewOnly === true) ? 'disabled' : '' }}>
                <option value="none" {{ $savedAddScope === 'none' ? 'selected' : '' }}>🚫 منع الإضافة</option>
                <option value="all" {{ $savedAddScope === 'all' ? 'selected' : '' }}>🌐 إضافة في أي مكان</option>
                <option value="same_governorate" {{ $savedAddScope === 'same_governorate' ? 'selected' : '' }}>🏙️ إضافة في محافظتي</option>
                <option value="same_directorate" {{ $savedAddScope === 'same_directorate' ? 'selected' : '' }}>🏢 إضافة في مديريتي</option>
            </select>
        </div>
        @break

    @case('suggestions')
        @php
            $sugAdminScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes[$moduleName] ?? 'own') : 'own';
            $sugGeoScope = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes[$moduleName] ?? 'none') : 'none';
        @endphp
        <div class="space-y-2 p-2 rounded-lg bg-purple-50/40">
            <div class="text-xs font-bold"><i class="fas fa-comment-dots"></i> نطاق المقترحات</div>
            <select name="module_scopes[{{ $moduleName }}]" class="module-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="scope">
                <option value="none" {{ $sugAdminScope === 'none' ? 'selected' : '' }}>🚫 حجب</option>
                <option value="user" {{ $sugAdminScope === 'user' ? 'selected' : '' }}>👤 مقترحاتي</option>
                <option value="own" {{ $sugAdminScope === 'own' ? 'selected' : '' }}>🏢 مقترحات جهتي</option>
                <option value="all" {{ $sugAdminScope === 'all' ? 'selected' : '' }}>🏛️ الكل</option>
            </select>
            <select name="module_geo_scopes[{{ $moduleName }}]" class="geo-scope-select form-select text-sm" data-module="{{ $moduleName }}" data-field="geo_scope">
                <option value="none" {{ $sugGeoScope === 'none' ? 'selected' : '' }}>🚫 حجب جغرافي</option>
                <option value="all" {{ $sugGeoScope === 'all' ? 'selected' : '' }}>🌐 الكل</option>
                <option value="same_governorate" {{ $sugGeoScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظتي</option>
                <option value="same_directorate" {{ $sugGeoScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديريتي</option>
            </select>
        </div>
        @break

    @case('reports')
        @php
            $reportsGeoScope = isset($role) && is_array($role->module_geo_scopes) ? ($role->module_geo_scopes['reports'] ?? 'all') : 'all';
            $reportsAdminScope = isset($role) && is_array($role->module_scopes) ? ($role->module_scopes['reports'] ?? 'all') : 'all';
        @endphp
        <div class="space-y-2 p-2 rounded-lg bg-orange-50/40">
            <div class="text-xs font-bold"><i class="fas fa-chart-bar"></i> نطاق التقارير</div>
            <select name="module_scopes[reports]" class="module-scope-select form-select text-sm" data-module="reports" data-field="scope">
                <option value="none" {{ $reportsAdminScope === 'none' ? 'selected' : '' }}>🚫 حجب</option>
                <option value="user" {{ $reportsAdminScope === 'user' ? 'selected' : '' }}>👤 تقاريري</option>
                <option value="own" {{ $reportsAdminScope === 'own' ? 'selected' : '' }}>🏢 تقارير جهتي</option>
                <option value="all" {{ $reportsAdminScope === 'all' ? 'selected' : '' }}>🏛️ الكل</option>
            </select>
            <select name="module_geo_scopes[reports]" class="geo-scope-select form-select text-sm" data-module="reports" data-field="geo_scope">
                <option value="none" {{ $reportsGeoScope === 'none' ? 'selected' : '' }}>🚫 حجب جغرافي</option>
                <option value="all" {{ $reportsGeoScope === 'all' ? 'selected' : '' }}>🌐 الكل</option>
                <option value="same_governorate" {{ $reportsGeoScope === 'same_governorate' ? 'selected' : '' }}>🏙️ محافظتي</option>
                <option value="same_directorate" {{ $reportsGeoScope === 'same_directorate' ? 'selected' : '' }}>🏢 مديريتي</option>
            </select>
        </div>
        @break

    @default
        {{-- Fallback: show nothing or generic message --}}
        <p class="text-xs text-slate-400">نطاقات مخصصة غير محددة</p>
@endswitch