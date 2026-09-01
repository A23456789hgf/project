@extends('layouts.app')

@section('title', 'تفاصيل الجهة - ' . $internalEntity->name)

@section('content')
    @include('configuration.shared_styles')

    <div class="container">
        <div class="main-card">
            {{-- رأس الصفحة --}}
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h2>
                        <i class="fas fa-sitemap me-2"></i> تفاصيل الجهة: {{ $internalEntity->name }}
                    </h2>
                    <div class="title-line"></div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('internal-entities.index') }}" class="btn btn-cancel-custom">
                        <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                    </a>
                    @can('internal-entities.edit')
                        <a href="{{ route('internal-entities.edit', $internalEntity) }}" class="btn btn-warning rounded-3 shadow-sm text-white">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                    @endcan
                </div>
            </div>

            {{-- تفاصيل الجهة --}}
            <div class="row g-3 mb-4 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="col-md-3">
                    <strong class="d-block text-muted small">اسم الجهة</strong>
                    <span class="fw-bold">{{ $internalEntity->name }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">الرمز</strong>
                    <span class="badge-label">{{ $internalEntity->entity_code ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">الجهة الأم</strong>
                    <span>{{ $internalEntity->parent->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">الجهة المشرفة</strong>
                    <span>{{ $internalEntity->authority->agency_name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">المحافظة</strong>
                    <span>{{ $internalEntity->governorate->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">المديرية</strong>
                    <span>{{ $internalEntity->directorate->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <strong class="d-block text-muted small">الحالة</strong>
                    @if($internalEntity->is_active)
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> نشط</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> غير نشط</span>
                    @endif
                </div>
            </div>

            {{-- جدول الجهات الفرعية (الأبناء) --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-navy fw-bold">
                    <i class="fas fa-network-wired me-2"></i> الجهات الفرعية التابعة (الأبناء)
                    <span class="badge bg-primary ms-1">{{ $internalEntity->children->count() }}</span>
                </h5>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th class="text-start">اسم الجهة الفرعية</th>
                            <th>الرمز</th>
                            <th>المحافظة</th>
                            <th>المديرية</th>
                            <th>الحالة</th>
                            <th style="width: 100px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($internalEntity->children as $child)
                            <tr>
                                <td class="text-muted fw-bold">{{ $child->id }}</td>
                                <td class="text-name">
                                    <i class="fas fa-sitemap text-muted me-1"></i>
                                    {{ $child->name }}
                                </td>
                                <td><span class="badge-label">{{ $child->entity_code ?? '-' }}</span></td>
                                <td><span class="text-muted small">{{ $child->governorate->name ?? '-' }}</span></td>
                                <td><span class="text-muted small">{{ $child->directorate->name ?? '-' }}</span></td>
                                <td>
                                    @if($child->is_active)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> نشط</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> غير نشط</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('internal-entities.show', $child) }}"
                                            class="btn-action btn-view"
                                            title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('internal-entities.edit')
                                            <a href="{{ route('internal-entities.edit', $child) }}"
                                                class="btn-action btn-edit" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state py-4">
                                        <i class="fas fa-inbox d-block"></i>
                                        <p class="mb-0">لا توجد جهات فرعية تابعة لهذه الجهة</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
