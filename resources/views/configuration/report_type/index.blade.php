@extends('layouts.app')

@section('content')
@include('configuration.shared_styles')

<div class="container">
    <div class="main-card">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0" style="color: #001f3f;">
                    <i class="fas fa-file-alt text-primary me-2"></i> قائمة أنواع التقارير
                </h2>
                <div class="title-line"></div>
            </div>

            <div class="d-flex gap-2">
                @can('report-types.create')
                <a href="{{ route('report-types.create') }}" class="btn btn-success px-4 rounded-3 fw-bold shadow-sm auth-perm-report-types-create">
                    <i class="fas fa-plus me-1"></i> إضافة نوع تقرير
                </a>
                @endcan

                <div class="dropdown">
                    <button class="btn btn-navy-gold dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i> البيانات
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        @can('report-types.export')
                        <li>
                            <a class="dropdown-item py-2 auth-perm-report-types-export" href="{{ route('report-types.export') }}">
                                <i class="fas fa-download text-success me-2"></i> تصدير Excel
                            </a>
                        </li>
                        @endcan
                        @can('report-types.import')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2 auth-perm-report-types-import" href="{{ route('report-types.import-form') }}">
                                <i class="fas fa-upload text-info me-2"></i> استيراد ملف
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('report-types.download-template') }}">
                                <i class="fas fa-file-excel text-warning me-2"></i> تحميل قالب الاستيراد
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>

        {{-- Flash messages --}}

        @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i> تحذيرات الاستيراد:</strong>
            <ul class="mt-2 mb-0">
                @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">اسم نوع التقرير</th>
                        <th>الحالة</th>
                        <th style="width: 120px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportTypes as $reportType)
                    <tr>
                        <td class="text-muted fw-bold">{{ $reportType->id }}</td>
                        <td class="text-name fw-semibold">{{ $reportType->name }}</td>
                        <td>
                            @if($reportType->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-2">
                                    <i class="fas fa-check me-1"></i> مفعل
                                </span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 py-2">
                                    <i class="fas fa-times me-1"></i> معطل
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @can('report-types.edit')
                                <a href="{{ route('report-types.edit', $reportType->id) }}"
                                   class="btn-action btn btn-sm btn-outline-warning auth-perm-report-types-edit"
                                   title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('report-types.delete')
                                <form action="{{ route('report-types.destroy', $reportType->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف «{{ $reportType->name }}»؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn-action btn btn-sm btn-outline-danger auth-perm-report-types-delete"
                                            title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            لا توجد بيانات متاحة حالياً
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
