@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-user-friends text-primary me-2"></i> الجهات المشاركة
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('participation.create')
                <a href="{{ route('participation.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-participation-create">
                    <i class="fas fa-plus me-1"></i> إضافة جهة
                </a>
                @endcan
            </div>
        </div>

        

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم الجهة</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td class="text-name">{{ $participant->name }}</td>
                        <td>
                            @if($participant->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> مفعلة
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> معطلة
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center">
                                @can('participation.edit')
                                <a href="{{ route('participation.edit', $participant->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-participation-edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('participation.delete')
                                <form action="{{ route('participation.destroy', $participant->id) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'هل تريد الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn btn-sm btn-outline-danger auth-perm-participation-delete">
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
