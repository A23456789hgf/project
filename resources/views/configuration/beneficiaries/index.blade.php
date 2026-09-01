@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-users text-primary me-2"></i> قائمة المستفيدين
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('beneficiaries.create')
                <a href="{{ route('beneficiaries.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-beneficiaries-create">
                    <i class="fas fa-plus me-1"></i> إضافة مستفيد جديد
                </a>
                @endcan
            </div>
        </div>

        

        <div class="mb-4 p-3 bg-light rounded-3">
            <form action="{{ route('beneficiaries.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="field-label">البحث</label>
                    <input type="text" name="search" class="form-control custom-field" placeholder="بحث عن مستفيد..." value="{{ request('search') }}">
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
                        <th class="text-start">
                            <a href="{{ route('beneficiaries.index', ['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc', 'search' => request('search')]) }}" class="text-dark text-decoration-none">
                                <i class="fas fa-sort me-1"></i> الاسم
                            </a>
                        </th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beneficiaries as $beneficiary)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $beneficiary->name }}</td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('beneficiaries.edit')
                                <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-beneficiaries-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('beneficiaries.delete')
                                <form action="{{ route('beneficiaries.destroy', $beneficiary->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-beneficiaries-delete" onclick="return confirmAction(this, 'هل تريد الحذف؟')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">لا توجد بيانات متاحة حالياً</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($beneficiaries->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $beneficiaries->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
