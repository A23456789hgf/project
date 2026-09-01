@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-coins text-primary me-2"></i> أشكال التمويل
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('formfinancing.create')
                <a href="{{ route('formfinancing.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-formfinancing-create">
                    <i class="fas fa-plus me-1"></i> إضافة جديد
                </a>
                @endcan
            </div>
        </div>

        

        <div class="mb-4 p-3 bg-light rounded-3">
            <form action="{{ route('formfinancing.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="field-label">البحث</label>
                    <input type="text" name="search" class="form-control custom-field" placeholder="بحث..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="field-label">الترتيب</label>
                    <select name="sort" class="form-select custom-field">
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>تاريخ الإنشاء</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>شكل التمويل</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-navy-gold shadow-sm w-100">
                        <i class="fas fa-search me-1"></i> تطبيق
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">شكل التمويل</th>
                        <th>تاريخ الإنشاء</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formFinancings as $item)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration + ($formFinancings->currentPage() - 1) * $formFinancings->perPage() }}</td>
                        <td class="text-name">{{ $item->name }}</td>
                        <td><span class="badge-label">{{ $item->created_at->format('Y-m-d') }}</span></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('formfinancing.edit')
                                <a href="{{ route('formfinancing.edit', $item->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-formfinancing-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('formfinancing.delete')
                                <form action="{{ route('formfinancing.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-formfinancing-delete">
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

        @if($formFinancings->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $formFinancings->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
