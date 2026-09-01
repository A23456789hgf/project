@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-users-cog text-primary me-2"></i> الجهات المنفذة
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('executors.create')
                <a href="{{ route('executors.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-executors-create">
                    <i class="fas fa-plus me-1"></i> إضافة جهة جديدة
                </a>
                @endcan
            </div>
        </div>

        

        <div class="mb-4 p-3 bg-light rounded-3">
            <form action="{{ route('executors.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="field-label">البحث</label>
                    <input type="text" name="search" class="form-control custom-field" placeholder="ابحث باسم الجهة..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-navy-gold shadow-sm w-100">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">الاسم</th>
                        <th>الحالة</th>
                        <th style="width: 150px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($executors as $executor)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $executor->name }}</td>
                        <td>
                            @if((int) $executor->is_active === 1)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> مفعلة
                                </span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> معطلة
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                @can('executors.edit')
                                <a href="{{ route('executors.edit', $executor) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-executors-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('executors.toggle', $executor) }}" method="POST" class="d-inline auth-perm-executors-edit">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-info" title="{{ $executor->is_active ? 'تعطيل' : 'تفعيل' }}">
                                        <i class="fas fa-toggle-{{ $executor->is_active ? 'off' : 'on' }}"></i>
                                    </button>
                                </form>
                                @endcan
                                @can('executors.delete')
                                <form action="{{ route('executors.destroy', $executor) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-executors-delete" onclick="return confirmAction(this, 'هل تريد الحذف؟')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($executors->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $executors->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
