@extends('layouts.app')

@section('styles')
<style>
    /* ─── إعادة ضبط العرض الكامل ─── */
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
        margin-bottom: 0.2rem;
    }

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
        padding: 0.4rem 0.3rem;
        border-bottom: 2px solid #cbd5e1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
        vertical-align: middle;
    }
    .roles-table tbody td {
        padding: 0.35rem 0.3rem;
        font-size: 0.7rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .roles-table tbody tr:hover {
        background: #f8fafc;
    }

    /* ─── منع التفاف النص والتمرير الأفقي ─── */
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

    /* ─── البادجات الفنية الصغيرة ─── */
    .badge-artistic {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        padding: 0.2rem 0.5rem;
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
    .badge-artistic.badge-role {
        background: #ede9fe;
        color: #5b21b6;
        border-color: #c4b5fd;
    }

    /* ─── أزرار الإجراءات الصغيرة الفنية ─── */
    .btn-action-artistic {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.65rem;
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
    .btn-action-artistic.btn-enable { background: #10b981; color: white; }
    .btn-action-artistic.btn-disable { background: #6b7280; color: white; }
    .btn-action-artistic.btn-delete { background: #ef4444; color: white; }

    /* ─── أزرار الهيدر ─── */
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
        filter: brightness(1.08);
    }

    /* ─── الحالة الفارغة ─── */
    .empty-state { padding: 2rem 1rem; text-align: center; }
    .empty-state .empty-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    /* ─── أحجام الأعمدة المحددة ─── */
    .roles-table th:nth-child(1), .roles-table td:nth-child(1) { width: 35px; }
    .roles-table th:nth-child(2), .roles-table td:nth-child(2) { width: 140px; }
    .roles-table th:nth-child(3), .roles-table td:nth-child(3) { width: auto; min-width: 150px; }
    .roles-table th:nth-child(4), .roles-table td:nth-child(4) { width: 90px; }
    .roles-table th:nth-child(5), .roles-table td:nth-child(5) { width: 75px; }
    .roles-table th:nth-child(6), .roles-table td:nth-child(6) { width: 120px; }

    /* ─── منع التمرير الأفقي على مستوى الصفحة ─── */
    html, body {
        overflow-x: hidden;
        max-width: 100vw;
    }
</style>
@endsection

@section('content')
<div class="roles-index">
    <x-index-page title="إدارة الأدوار" icon="user-tag">
        <x-slot name="headerActions">
            @can('roles.create')
                <a href="{{ route('roles.create') }}" class="header-btn btn-primary-gradient auth-perm-roles-create">
                    <x-icon name="plus" size="12" /> إضافة دور جديد
                </a>
            @endcan
        </x-slot>

        <x-slot name="table">
            <div class="roles-table-wrapper">
                <table class="table roles-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center no-wrap">#</th>
                            <th class="no-wrap">اسم الدور</th>
                            <th>الوصف</th>
                            <th class="text-center no-wrap">المستخدمين</th>
                            <th class="text-center no-wrap">الحالة</th>
                            <th class="text-center no-wrap">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="text-center no-wrap">
                                    <span class="text-muted fw-bold">{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</span>
                                </td>
                                <td class="no-wrap">
                                    <span class="fw-bold text-dark text-truncate-custom d-block" title="{{ $role->name }}">
                                        {{ $role->name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted text-truncate-custom d-block" title="{{ $role->description }}">
                                        {{ $role->description ?: 'ـ' }}
                                    </span>
                                </td>
                                <td class="text-center no-wrap">
                                    <span class="badge-artistic badge-role">
                                        <x-icon name="users" size="9" /> {{ $role->users_count }}
                                    </span>
                                </td>
                                <td class="text-center no-wrap">
                                    @if($role->is_active)
                                        <span class="badge-artistic badge-active">نشط</span>
                                    @else
                                        <span class="badge-artistic badge-disabled">معطل</span>
                                    @endif
                                </td>
                                <td class="text-center no-wrap">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        @can('users.view')
                                            <a href="{{ route('users.index', ['role_id' => $role->id]) }}"
                                                class="btn-action-artistic btn-view auth-perm-users-view"
                                                title="عرض مستخدمي الدور">
                                                <x-icon name="users" size="10" />
                                            </a>
                                        @endcan

                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $role) }}"
                                                class="btn-action-artistic btn-edit auth-perm-roles-edit"
                                                title="تعديل الدور والصلاحيات">
                                                <x-icon name="edit-2" size="10" />
                                            </a>
                                        @endcan

                                        @can('roles.toggle')
                                            <form action="{{ route('roles.toggle', $role) }}" method="POST"
                                                class="d-inline auth-perm-roles-toggle">
                                                @csrf
                                                @method('PATCH')
                                                @if($role->is_active)
                                                    <button type="submit"
                                                        class="btn-action-artistic btn-disable"
                                                        title="تعطيل"
                                                        onclick="return confirmAction(this, 'هل أنت متأكد من عملية التعطيل؟')">
                                                        <x-icon name="pause" size="10" />
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                        class="btn-action-artistic btn-enable"
                                                        title="تفعيل"
                                                        onclick="return confirmAction(this, 'هل أنت متأكد من عملية التفعيل؟')">
                                                        <x-icon name="play" size="10" />
                                                    </button>
                                                @endif
                                            </form>
                                        @endcan

                                        @can('roles.delete')
                                            @if($role->users_count == 0)
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action-artistic btn-delete auth-perm-roles-delete" title="حذف" onclick="return confirmAction(this, 'هل أنت متأكد من حذف الدور؟')">
                                                        <x-icon name="trash-2" size="10" />
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <x-icon name="user-tag" size="24" class="text-muted" />
                                        </div>
                                        <h6 class="text-dark fw-bold mb-1">لا توجد أدوار حالياً</h6>
                                        <p class="text-muted small mb-3">لم يتم العثور على أي أدوار مسجلة في النظام.</p>
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