@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-compass text-primary me-2"></i> الموجهات الرئيسية
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('main-guides.create')
                <a href="{{ route('configuration.main_guides.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-main-guides-create">
                    <i class="fas fa-plus me-1"></i> إضافة جديد
                </a>
                @endcan
            </div>
        </div>

        

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">الموجه الرئيسي</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guides as $guide)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $guide->guide_name }}</td>
                        <td>
                            @if($guide->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> مفعل
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> معطل
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('main-guides.edit')
                                <a href="{{ route('configuration.main_guides.edit', $guide->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-main-guides-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('main-guides.delete')
                                <form action="{{ route('configuration.main_guides.destroy', $guide->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-main-guides-delete">
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
    </div>
</div>
@endsection