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
                <p class="mb-0 fw-bold">لا توجد وحدات متاحة في هذا التبويب</p>
            </div>
        </td>
    </tr>
@endif
