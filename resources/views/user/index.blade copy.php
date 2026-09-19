@extends('layouts.app')

@push('styles')
    <style>
        /* Full width table fix - إزالة الهوامش وجعل الجدول ممتداً بالكامل */
        .index-page-content,
        .card-body,
        .table-wrapper,
        [class*="container"] {
            padding-left: 0 !important;
            padding-right: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        .table-responsive {
            width: 100% !important;
            margin: 0 !important;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100% !important;
            min-width: 800px;
            /* لضمان تمرير أفقي إذا كان المحتوى ضيقاً على الشاشات الصغيرة */
        }

        /* تحسين مظهر الفلاتر والإحصائيات في العرض الكامل */
        .stats-bar,
        .filters-row {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
    </style>
@endpush

@section('content')
    <x-index-page title="قائمة المستخدمين" icon="users">

        <x-slot name="headerActions">
            @can('users.create')
                <a href="{{ route('users.create') }}" class="btn btn-primary auth-perm-users-create">
                    <x-icon name="user-plus" size="14" />
                    إضافة مستخدم
                </a>
            @endcan
        </x-slot>

        <x-slot name="filters">
            <div class="row g-2 mb-3">
                {{-- Search --}}
                <div class="col-md-3 col-sm-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text border-end-0">
                            <x-icon name="search" size="13" class="text-muted" />
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0"
                            placeholder="بحث بالمعرف أو الاسم..." value="{{ request('search') }}" id="search-input"
                            style="border-left-color: #dee2e6;">
                    </div>
                </div>

                {{-- Role Filter --}}
                <div class="col-md-2 col-sm-6">
                    <select name="role_id" class="form-select form-select-sm" id="role-filter">
                        <option value="">جميع الأدوار</option>
                        @foreach($roles as $id => $name)
                            <option value="{{ $id }}" {{ request('role_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-2 col-sm-6">
                    <select name="status" class="form-select form-select-sm" id="status-filter">
                        <option value="">جميع الحالات</option>
                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>✅ مفعل</option>
                        <option value="Disabled" {{ request('status') === 'Disabled' ? 'selected' : '' }}>⏸ معطل</option>
                    </select>
                </div>

                {{-- Entity Filter --}}
                <div class="col-md-2 col-sm-6">
                    <select name="entity_id" class="form-select form-select-sm" id="entity-filter">
                        <option value="">جميع الجهات</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort Controls --}}
                <div class="col-md-2 col-sm-6">
                    <div class="d-flex gap-1">
                        <select name="sort_by" class="form-select form-select-sm" id="sort-by" title="ترتيب حسب">
                            <option value="">🔄 الفرز</option>
                            <option value="user_id" {{ request('sort_by') == 'user_id' ? 'selected' : '' }}>🔢 المعرف
                            </option>
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>👤 الاسم</option>
                            <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>📊 الحالة
                            </option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>📅
                                التاريخ</option>
                        </select>
                        <select name="sort_order" class="form-select form-select-sm" id="sort-order" style="width: 42px;"
                            title="اتجاه الفرز">
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>⬇</option>
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>⬆</option>
                        </select>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="col-md-1 col-sm-6">
                    <div class="d-grid gap-1">
                        <button type="button" class="btn btn-primary-compact" id="apply-filters" title="تطبيق الفلاتر">
                            <x-icon name="filter" size="13" />
                        </button>
                        @if(request()->hasAny(['search', 'role_id', 'status', 'entity_id', 'sort_by']))
                            <a href="{{ route('users.index', request()->only(['per_page'])) }}"
                                class="btn btn-outline-danger-compact" title="إعادة تعيين">
                                <x-icon name="refresh-ccw" size="13" />
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Active Filters Tags --}}
            @if(request()->hasAny(['search', 'role_id', 'status', 'entity_id', 'sort_by']))
                <div class="row mb-2">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            <small class="text-muted me-1">الفلاتر النشطة:</small>

                            @if(request('search'))
                                <span class="filter-badge badge-search">
                                    🔍 {{ request('search') }}
                                    <a href="{{ route('users.index', request()->except(['search'])) }}" class="remove-filter"
                                        title="إزالة">
                                        <x-icon name="x" size="10" />
                                    </a>
                                </span>
                            @endif

                            @if(request('role_id'))
                                @php $role = \App\Models\Role::find(request('role_id')) @endphp
                                <span class="filter-badge badge-role">
                                    🎭 {{ $role->name ?? 'غير محدد' }}
                                    <a href="{{ route('users.index', request()->except(['role_id'])) }}" class="remove-filter"
                                        title="إزالة">
                                        <x-icon name="x" size="10" />
                                    </a>
                                </span>
                            @endif

                            @if(request('status'))
                                <span class="filter-badge {{ request('status') === 'Active' ? 'badge-active' : 'badge-disabled' }}">
                                    {{ request('status') === 'Active' ? '✅ مفعل' : '⏸ معطل' }}
                                    <a href="{{ route('users.index', request()->except(['status'])) }}" class="remove-filter"
                                        title="إزالة">
                                        <x-icon name="x" size="10" />
                                    </a>
                                </span>
                            @endif

                            @if(request('entity_id'))
                                @php $entity = \App\Models\InternalEntity::find(request('entity_id')) @endphp
                                <span class="filter-badge badge-filter">
                                    🏢 {{ $entity->name ?? 'غير محدد' }}
                                    <a href="{{ route('users.index', request()->except(['entity_id'])) }}" class="remove-filter"
                                        title="إزالة">
                                        <x-icon name="x" size="10" />
                                    </a>
                                </span>
                            @endif

                            @if(request('sort_by'))
                                        <span class="filter-badge badge-filter">
                                            📋 {{ 
                                                                                    request('sort_by') == 'user_id' ? 'المعرف' :
                                (request('sort_by') == 'name' ? 'الاسم' :
                                    (request('sort_by') == 'status' ? 'الحالة' : 'التاريخ'))
                                                                                }} {{ request('sort_order') == 'desc' ? '⬇' : '⬆' }}
                                            <a href="{{ route('users.index', request()->except(['sort_by', 'sort_order'])) }}"
                                                class="remove-filter" title="إزالة">
                                                <x-icon name="x" size="10" />
                                            </a>
                                        </span>
                            @endif

                            <a href="{{ route('users.index', request()->only(['per_page'])) }}"
                                class="text-muted small text-decoration-none ms-2">
                                <x-icon name="trash-2" size="12" /> مسح الكل
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="stats">
            <div class="stats-bar mb-3 w-100">
                <div class="d-flex gap-3 flex-wrap">
                    <span class="stat-item text-muted">
                        <x-icon name="users" size="14" />
                        الإجمالي: <strong class="text-dark ms-1">{{ $users->total() }}</strong>
                    </span>

                    <span class="stat-item text-success">
                        <x-icon name="check-circle" size="14" />
                        مفعل: <strong class="ms-1">{{ $activeCount }}</strong>
                    </span>
                    <span class="stat-item text-danger">
                        <x-icon name="ban" size="14" />
                        معطل: <strong class="ms-1">{{ $disabledCount }}</strong>
                    </span>
                </div>
                <div class="text-muted small">
                    📄 صفحة <strong>{{ $users->currentPage() }}</strong> من <strong>{{ $users->lastPage() }}</strong>
                </div>
        </x-slot>

        <x-slot name="table">
            <div class="table-responsive w-100">
                <table class="table table-hover table-striped align-middle table-compact w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 75px">
                                <div class="d-flex align-items-center gap-1">
                                    <span>#</span>
                                    @if(request('sort_by') == 'user_id')
                                        <x-icon name="arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}" size="10" />
                                    @endif
                                </div>
                            </th>
                            <th style="min-width: 150px">
                                <div class="d-flex align-items-center gap-1">
                                    <span>المستخدم</span>
                                    @if(request('sort_by') == 'name')
                                        <x-icon name="arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}" size="10" />
                                    @endif
                                </div>
                            </th>
                            <th style="min-width: 90px">الدور</th>
                            <th style="min-width: 120px" class="hide-xl">الجهة</th>
                            <th style="min-width: 75px">
                                <div class="d-flex align-items-center gap-1">
                                    <span>الحالة</span>
                                    @if(request('sort_by') == 'status')
                                        <x-icon name="arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}" size="10" />
                                    @endif
                                </div>
                            </th>
                            <th style="min-width: 100px">
                                <div class="d-flex align-items-center gap-1">
                                    <span>التاريخ</span>
                                    @if(request('sort_by') == 'created_at')
                                        <x-icon name="arrow-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}" size="10" />
                                    @endif
                                </div>
                            </th>
                            <th style="min-width: 95px" class="hide-lg">بواسطة</th>
                            <th class="text-center" style="min-width: 180px">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                {{-- User ID --}}
                                <td>
                                    <span class="fw-bold text-primary small">#{{ $user->user_id }}</span>
                                </td>

                                {{-- User Name --}}
                                <td>
                                    <div class="user-name-cell">
                                        <div class="text-truncate-custom-lg small fw-medium" title="{{ $user->name }}">
                                            {{ $user->name }}
                                        </div>
                                        @if($user->is_geographic_subset)
                                            <span class="badge-compact badge-geo">
                                                <x-icon name="map-pin" size="10" /> جغرافي
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Role --}}
                                <td>
                                    <span class="badge-compact badge-role">
                                        {{ $user->role->name ?? 'ـ' }}
                                    </span>
                                </td>

                                {{-- Entity --}}
                                <td class="hide-xl">
                                    <div class="text-truncate-custom small text-muted" title="{{ $user->entity->name ?? '' }}">
                                        {{ $user->entity->name ?? 'ـ' }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if($user->status === 'Active')
                                        <span class="badge-compact badge-active">
                                            <x-icon name="check" size="10" /> نشط
                                        </span>
                                    @else
                                        <span class="badge-compact badge-disabled">
                                            <x-icon name="x" size="10" /> معطل
                                        </span>
                                    @endif
                                </td>

                                {{-- Created Date --}}
                                <td>
                                    <div class="date-cell">
                                        <span class="date-main">{{ $user->created_at->format('Y-m-d') }}</span>
                                        <span class="date-time">{{ $user->created_at->format('h:i A') }}</span>
                                    </div>
                                </td>

                                {{-- Created By --}}
                                <td class="hide-lg">
                                    @if($user->creator_username || $user->createdBy)
                                        <div class="creator-cell">
                                            <span class="creator-name"
                                                title="{{ $user->creator_username ?? $user->createdBy->user_id ?? $user->createdBy->username ?? '-' }}">
                                                {{ $user->creator_username ?? $user->createdBy->user_id ?? $user->createdBy->username ?? 'ـ' }}
                                            </span>
                                            <span class="creator-entity">
                                                {{ $user->creatorEntity->name ?? $user->createdBy->entity->name ?? 'ـ' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted small">ـ</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        @can('users.view')
                                            <a href="{{ route('users.show', $user) }}"
                                                class="btn btn-action-view btn-icon auth-perm-users-view" title="عرض التفاصيل">
                                                <x-icon name="eye" class="action-icon" />
                                            </a>
                                            <a href="{{ route('users.activity-log', $user) }}"
                                                class="btn btn-action-log btn-icon auth-perm-users-view" title="سجل الأنشطة">
                                                <x-icon name="clock" class="action-icon" />
                                            </a>
                                        @endcan

                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="btn btn-action-edit btn-icon auth-perm-users-edit" title="تعديل">
                                                <x-icon name="edit-2" class="action-icon" />
                                            </a>
                                        @endcan

                                        @if($user->status === 'Active')
                                            @can('users.disable')
                                                <form action="{{ route('users.disable', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="button"
                                                        class="btn btn-action-disable btn-icon auth-perm-users-disable"
                                                        onclick="confirmAction('تعطيل', this.form)" title="تعطيل المستخدم">
                                                        <x-icon name="pause" class="action-icon" />
                                                    </button>
                                                </form>
                                            @endcan
                                        @else
                                            @can('users.enable')
                                                <form action="{{ route('users.enable', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="button" class="btn btn-action-enable btn-icon auth-perm-users-enable"
                                                        onclick="confirmAction('تفعيل', this.form)" title="تفعيل المستخدم">
                                                        <x-icon name="play" class="action-icon" />
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif

                                        @can('users.delete')
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-action-delete btn-icon auth-perm-users-delete"
                                                    onclick="confirmAction('حذف', this.form)" title="حذف المستخدم">
                                                    <x-icon name="trash-2" class="action-icon" />
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <div class="p-4 rounded-circle bg-light">
                                            <x-icon name="users" size="40" class="text-muted" />
                                        </div>
                                        <div class="text-center">
                                            <h6 class="text-muted mb-1">لا توجد مستخدمين</h6>
                                            <small class="text-muted d-block">جرب تعديل معايير البحث أو الفلاتر</small>
                                        </div>
                                        @if(request()->hasAny(['search', 'role_id', 'status', 'entity_id']))
                                            <a href="{{ route('users.index', request()->only(['per_page'])) }}"
                                                class="btn btn-primary-compact btn-compact">
                                                <x-icon name="refresh-ccw" size="12" /> إعادة تعيين الفلاتر
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-slot name="pagination">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-muted small">
                            📊 عرض <strong>{{ $users->firstItem() }}</strong> إلى <strong>{{ $users->lastItem() }}</strong>
                            من <strong>{{ $users->total() }}</strong> مستخدم
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label for="recordsPerPage" class="small text-muted mb-0">عرض:</label>
                            <select class="form-select form-select-sm" id="recordsPerPage"
                                style="width: auto; padding: 0.15rem 1.5rem 0.15rem 0.5rem; font-size: 0.75rem;">
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 سجل</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 سجل</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 سجل</option>
                                <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500 سجل</option>
                            </select>
                        </div>
                    </div>
                    <div class="pagination-sm">
                        {{ $users->appends([
            'search' => request('search'),
            'role_id' => request('role_id'),
            'status' => request('status'),
            'entity_id' => request('entity_id'),
            'sort_by' => request('sort_by'),
            'sort_order' => request('sort_order'),
            'per_page' => request('per_page')
        ])->links() }}
                    </div>
                </div>
            </x-slot>
        </x-slot>

    </x-index-page>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            // تطبيق الفلاتر عند النقر على الزر
            $('#apply-filters').click(function () {
                applyFilters();
            });

            // تطبيق الفلاتر عند تغيير عدد السجلات
            $('#recordsPerPage').change(function () {
                applyFilters();
            });

            // تطبيق الفلاتر عند ضغط Enter في حقل البحث
            $('#search-input').keypress(function (e) {
                if (e.which == 13) {
                    applyFilters();
                    return false;
                }
            });

            // دالة تطبيق الفلاتر
            function applyFilters() {
                const search = $('#search-input').val().trim();
                const roleId = $('#role-filter').val();
                const status = $('#status-filter').val();
                const entityId = $('#entity-filter').val();
                const sortBy = $('#sort-by').val();
                const sortOrder = $('#sort-order').val();
                const perPage = $('#recordsPerPage').val();

                let params = {};

                if (search) params.search = search;
                if (roleId) params.role_id = roleId;
                if (status) params.status = status;
                if (entityId) params.entity_id = entityId;
                if (sortBy) params.sort_by = sortBy;
                if (sortOrder) params.sort_order = sortOrder;
                if (perPage) params.per_page = perPage;

                const queryString = $.param(params);
                window.location.href = '{{ route("users.index") }}' + (queryString ? '?' + queryString : '');
            }

            // دالة تأكيد الإجراءات الخطرة
            window.confirmAction = function (action, form) {
                const messages = {
                    'تعطيل': 'هل أنت متأكد من تعطيل هذا المستخدم؟',
                    'تفعيل': 'هل أنت متأكد من تفعيل هذا المستخدم؟',
                    'حذف': '⚠️ تحذير: هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء!'
                };

                Swal.fire({
                    title: 'تأكيد العملية',
                    text: messages[action] || 'هل أنت متأكد؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'لا',
                    reverseButtons: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            };

            // تهيئة Select2 لجميع القوائم المنسدلة
            $('select').each(function () {
                if (!$(this).hasClass('select2-initialized')) {
                    $(this).addClass('select2-initialized');
                    $(this).select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dir: 'rtl',
                        placeholder: $(this).find('option:first').text() || '-- اختر --',
                        minimumResultsForSearch: 5,
                        dropdownParent: $(this).closest('.card'),
                        language: {
                            noResults: function () { return "لا توجد نتائج"; },
                            searching: function () { return "جاري البحث..."; }
                        }
                    });
                }
            });

            // تأثيرات إضافية عند التحميل
            setTimeout(function () {
                $('.table-compact tbody tr').css('animation', 'fadeIn 0.3s ease forwards');
            }, 100);
        });
    </script>
@endsection