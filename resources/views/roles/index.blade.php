@extends('layouts.app')

@section('styles')
<style>
    /* ─── إعادة ضبط العرض الكامل والتناسق ─── */
    .roles-index {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    .roles-index .container,
    .roles-index .container-fluid {
        max-width: 100% !important;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    /* ─── تصغير الخطوط العامة ─── */
    .roles-index,
    .roles-index .form-control,
    .roles-index .form-select,
    .roles-index .btn,
    .roles-index .dropdown-item,
    .roles-index .badge,
    .roles-index .small {
        font-size: 0.72rem !important;
    }
    .roles-index h6 { font-size: 0.8rem !important; }
    .roles-index .form-label {
        font-size: 0.65rem !important;
        font-weight: 600;
    }

    /* ─── الفلاتر ─── */
    .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.6rem;
        margin-bottom: 0.5rem;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        font-size: 0.7rem !important;
        padding: 0.3rem 0.5rem;
        height: 30px;
        border-radius: 6px;
    }
    .filter-card .row.g-3 { --bs-gutter-y: 0.4rem; }

    /* ─── الجدول بعرض الشاشة ─── */
    .roles-table-wrapper {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .roles-table {
        width: 100%;
        table-layout: fixed;
        margin-bottom: 0;
        border-collapse: collapse;
    }
    .roles-table thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
        color: #334155;
        font-weight: 700;
        font-size: 0.68rem;
        padding: 0.45rem 0.4rem;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
        vertical-align: middle;
    }
    .roles-table tbody td {
        padding: 0.4rem 0.4rem;
        font-size: 0.7rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .roles-table tbody tr:hover {
        background: #f8fafc;
    }

    /* ─── منع التفاف النص ─── */
    .no-wrap {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .text-truncate-custom {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ─── البادجات الفنية (Artistic Badges) ─── */
    .badge-artistic {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 50rem;
        font-size: 0.65rem !important;
        font-weight: 600;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1.2;
    }
    .badge-artistic.badge-active {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .badge-artistic.badge-disabled {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .badge-artistic.badge-users {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #7dd3fc;
    }
    .badge-artistic.badge-full-access {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .badge-artistic.badge-custom-access {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }

    /* ─── أزرار الإجراءات الصغيرة (Artistic Buttons) ─── */
    .btn-action-artistic {
        width: 26px;
        height: 26px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.68rem;
        padding: 0;
        vertical-align: middle;
        text-decoration: none;
    }
    .btn-action-artistic:hover {
        transform: translateY(-1px);
        filter: brightness(1.1);
        color: white;
    }
    .btn-action-artistic.btn-view { background: #3b82f6; color: white; }
    .btn-action-artistic.btn-edit { background: #f59e0b; color: white; }
    .btn-action-artistic.btn-delete { background: #ef4444; color: white; }
    .btn-action-artistic.btn-enable { background: #10b981; color: white; }
    .btn-action-artistic.btn-disable { background: #6b7280; color: white; }

    /* ─── رقم الدور ─── */
    .role-number {
        font-family: monospace;
        font-weight: 700;
        font-size: 0.68rem;
        color: #1e293b;
        background: #f1f5f9;
        padding: 0.15rem 0.45rem;
        border-radius: 5px;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    /* ─── أزرار الترويسة ─── */
    .header-btn {
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        font-weight: 600;
        font-size: 0.72rem !important;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: none;
        white-space: nowrap;
        text-decoration: none;
    }
    .header-btn.btn-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .header-btn.btn-primary-gradient:hover {
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .header-btn.btn-outline-gradient {
        background: white;
        border: 1px solid #e2e8f0;
        color: #475569;
    }

    /* ─── الحالة الفارغة ─── */
    .empty-state { padding: 2.5rem 1rem; text-align: center; }
    .empty-state .empty-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #64748b;
        font-size: 1.5rem;
    }

    /* ─── أحجام الأعمدة ─── */
    .roles-table th:nth-child(1), .roles-table td:nth-child(1) { width: 45px; }
    .roles-table th:nth-child(2), .roles-table td:nth-child(2) { width: 200px; }
    .roles-table th:nth-child(3), .roles-table td:nth-child(3) { width: auto; }
    .roles-table th:nth-child(4), .roles-table td:nth-child(4) { width: 120px; }
    .roles-table th:nth-child(5), .roles-table td:nth-child(5) { width: 120px; }
    .roles-table th:nth-child(6), .roles-table td:nth-child(6) { width: 90px; }
    .roles-table th:nth-child(7), .roles-table td:nth-child(7) { width: 150px; }

    @media (max-width: 768px) {
        .roles-table th:nth-child(3), .roles-table td:nth-child(3) { display: none; }
        .roles-table th:nth-child(4), .roles-table td:nth-child(4) { display: none; }
    }
</style>
@endsection

@section('content')
<div class="roles-index" dir="rtl">
    <x-index-page title="إدارة الأدوار والصلاحيات" icon="user-tag">

        <x-slot name="headerActions">
            @can('roles.create')
                <a href="{{ route('roles.create') }}" class="header-btn btn-primary-gradient auth-perm-roles-create">
                    <x-icon name="plus" size="12" /> إضافة دور جديد
                </a>
            @endcan
        </x-slot>

        <x-slot name="filters">
            <form id="auto-filter-form" action="{{ route('roles.index') }}" method="GET">
                <div class="filter-card">
                    <div class="row g-2 align-items-end">
                        <div class="col-8 col-md-4">
                            <label for="search" class="form-label">بحث</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="اسم الدور أو الوصف...">
                        </div>
                        <div class="col-4 col-md-2">
                            <label for="status" class="form-label">الحالة</label>
                            <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                                <option value="">الكل</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>معطل</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn header-btn btn-primary-gradient w-100" style="height: 30px; justify-content: center;">
                                <x-icon name="search" size="12" /> بحث
                            </button>
                        </div>
                        @if(request()->hasAny(['search', 'status']))
                            <div class="col-12 col-md-2">
                                <a href="{{ route('roles.index') }}" class="btn header-btn btn-outline-gradient w-100" style="height: 30px; justify-content: center;">
                                    <x-icon name="x" size="12" /> إلغاء الفلترة
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </x-slot>

        <x-slot name="table">
            <div class="roles-table-wrapper">
                <table class="table roles-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center no-wrap">#</th>
                            <th class="no-wrap" style="text-align: right; padding-right: 0.8rem;">اسم الدور</th>
                            <th style="text-align: right; padding-right: 0.8rem;">الوصف</th>
                            <th class="text-center no-wrap">الوصول</th>
                            <th class="text-center no-wrap">المستخدمين</th>
                            <th class="text-center no-wrap">الحالة</th>
                            <th class="text-center no-wrap">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                {{-- # --}}
                                <td class="text-center">
                                    <span class="role-number">
                                        {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                                    </span>
                                </td>

                                {{-- اسم الدور --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 22px; height: 22px; border-radius: 6px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.65rem;">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <span class="fw-bold text-dark text-truncate-custom" title="{{ $role->name }}">
                                            {{ $role->name }}
                                        </span>
                                    </div>
                                </td>

                                {{-- الوصف --}}
                                <td>
                                    <div class="text-muted text-truncate-custom" title="{{ $role->description }}">
                                        {{ $role->description ?: 'لا يوجد وصف' }}
                                    </div>
                                </td>

                                {{-- نوع الوصول --}}
                                <td class="text-center">
                                    @if($role->full_access)
                                        <span class="badge-artistic badge-full-access" title="وصول كامل للنظام">
                                            <x-icon name="crown" size="10" /> كامل
                                        </span>
                                    @else
                                        <span class="badge-artistic badge-custom-access" title="صلاحيات محددة">
                                            <x-icon name="sliders" size="10" /> مخصص
                                        </span>
                                    @endif
                                </td>

                                {{-- عدد المستخدمين --}}
                                <td class="text-center">
                                    <span class="badge-artistic badge-users">
                                        <x-icon name="users" size="10" /> {{ $role->users_count }}
                                    </span>
                                </td>

                                {{-- الحالة --}}
                                <td class="text-center">
                                    @if($role->is_active)
                                        <span class="badge-artistic badge-active">
                                            <x-icon name="check" size="10" /> نشط
                                        </span>
                                    @else
                                        <span class="badge-artistic badge-disabled">
                                            <x-icon name="x" size="10" /> معطل
                                        </span>
                                    @endif
                                </td>

                                {{-- الإجراءات --}}
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @can('users.view')
                                            <a href="{{ route('users.index', ['role_id' => $role->id]) }}"
                                                class="btn-action-artistic btn-view auth-perm-users-view"
                                                title="عرض المستخدمين">
                                                <x-icon name="users" size="11" />
                                            </a>
                                        @endcan

                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $role) }}"
                                                class="btn-action-artistic btn-edit auth-perm-roles-edit"
                                                title="تعديل الدور والصلاحيات">
                                                <x-icon name="edit-2" size="11" />
                                            </a>
                                        @endcan

                                        @can('roles.toggle')
                                            <form action="{{ route('roles.toggle', $role) }}" method="POST"
                                                class="d-inline auth-perm-roles-toggle" onsubmit="return confirmAction(this, '{{ $role->is_active ? 'هل أنت متأكد من تعطيل الدور؟' : 'هل أنت متأكد من تفعيل الدور؟' }}')">
                                                @csrf
                                                @method('PATCH')
                                                @if($role->is_active)
                                                    <button type="submit" class="btn-action-artistic btn-disable" title="تعطيل">
                                                        <x-icon name="pause" size="11" />
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn-action-artistic btn-enable" title="تفعيل">
                                                        <x-icon name="play" size="11" />
                                                    </button>
                                                @endif
                                            </form>
                                        @endcan

                                        @can('roles.delete')
                                            @if($role->users_count == 0 && $role->name !== 'مدير النظام')
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل أنت متأكد من حذف الدور نهائياً؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action-artistic btn-delete auth-perm-roles-delete" title="حذف">
                                                        <x-icon name="trash-2" size="11" />
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-user-tag"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">لا توجد أدوار مطابقة</h6>
                                        <p class="text-muted small mb-3">لم يتم العثور على أي أدوار مسجلة حالياً أو مطابقة لشروط البحث.</p>
                                        @can('roles.create')
                                            <a href="{{ route('roles.create') }}" class="header-btn btn-primary-gradient d-inline-flex">
                                                <x-icon name="plus" size="12" /> إضافة دور جديد
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-slot>

        <x-slot name="pagination">
            {{ $roles->withQueryString()->links() }}
        </x-slot>

        <x-slot name="total">
            {{ $roles->total() }}
        </x-slot>

    </x-index-page>
</div>
@endsection