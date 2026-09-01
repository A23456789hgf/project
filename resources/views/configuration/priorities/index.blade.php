@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-star text-primary me-2"></i> قائمة الأولويات
                </h2>
                <div class="title-line"></div>
            </div>
            
            <div class="d-flex gap-2">
                @can('priorities.create')
                <a href="{{ route('priorities.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-priorities-create">
                    <i class="fas fa-plus me-1"></i> إضافة أولوية
                </a>
                @endcan

                <div class="dropdown">
                    <button class="btn btn-navy-gold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i> البيانات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        @can('priorities.export')
                        <li><a class="dropdown-item py-2 auth-perm-priorities-export" href="{{ route('config.export', ['entity' => 'priorities']) }}"><i class="fas fa-download text-success me-2"></i> تصدير Excel</a></li>
                        @endcan
                        @can('priorities.import')
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 auth-perm-priorities-import" href="{{ route('config.import', ['entity' => 'priorities']) }}"><i class="fas fa-upload text-info me-2"></i> استيراد ملف</a></li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>

        

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">الأولوية</th>
                        <th>الحالة</th>
                        <th style="width: 100px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priorities as $priority)
                    <tr>
                        <td class="text-muted fw-bold">{{ $priority->id }}</td>
                        <td class="text-name">{{ $priority->priority }}</td>
                        <td>
                            @if($priority->is_enabled)
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
                            @can('priorities.edit')
                            <a href="{{ route('priorities.edit', $priority->id) }}" class="btn-action btn btn-sm btn-outline-warning auth-perm-priorities-edit" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
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
