@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-exchange-alt me-2"></i>
                    إحالات المشاريع
                </h2>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('project-referrals.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">البحث</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="اسم المشروع أو رقم النموذج">
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">حالة الإحالة</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">الكل</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="responded" {{ request('status') == 'responded' ? 'selected' : '' }}>تم الرد</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>تم الإرجاع</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="entity_id" class="form-label">الجهة المُحالة إليها</label>
                    <select class="form-select" id="entity_id" name="entity_id">
                        <option value="">جميع الجهات</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Projects Table --}}
    <div class="card">
        <div class="card-body">
            @if($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>رقم المشروع</th>
                                <th>اسم المشروع</th>
                                <th>الجهة المنشئة</th>
                                <th>المرحلة الحالية</th>
                                <th>عدد الإحالات</th>
                                <th class="text-center">العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                                <tr>
                                    <td>
                                        <a href="{{ route('project-referrals.show', $project->id) }}" class="fw-bold">
                                            {{ $project->form_number }}
                                        </a>
                                    </td>
                                    <td>{{ Str::limit($project->project_name, 60) }}</td>
                                    <td>
                                        @if($project->createdBy && $project->createdBy->entity)
                                            {{ $project->createdBy->entity->name }}
                                        @else
                                            {{ $project->created_by_entity }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($project->currentApprovalStage)
                                            <span class="badge bg-info">{{ $project->currentApprovalStage->name_ar }}</span>
                                        @else
                                            <span class="badge bg-secondary">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $project->referrals()->count() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('project-referrals.show', $project->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="عرض الإحالات">
                                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3">
                    {{ $projects->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">لا توجد مشاريع بها إحالات حالياً</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.85rem;
    }
</style>
@endpush
