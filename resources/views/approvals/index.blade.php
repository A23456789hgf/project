@extends('layouts.app')

@section('title', 'مركز المراجعة والاعتمادات')

@section('styles')
<style>
    .approval-center-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.15);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    
    {{-- Top Banner Header --}}
    <div class="approval-center-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 text-white-50 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50 text-decoration-none"><i class="fas fa-home me-1"></i> الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-white-50 text-decoration-none">المشاريع</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">مركز المراجعة والاعتمادات</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-clipboard-check text-info"></i> مركز المراجعة والاعتمادات
                </h3>
                <p class="text-white-50 mb-0 small">
                    منصة اتخاذ القرارات الإدارية والفنية الموحدة ومتابعة مسارات الاعتماد
                </p>
            </div>
            <div>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-semibold">
                    <i class="fas fa-list me-1"></i> قائمة المشاريع العامة
                </a>
            </div>
        </div>
    </div>

    @if($isAdmin)
    {{-- Filter and Search Bar for Admin --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('approvals.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-muted">المشروع:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-start-0" placeholder="بحث...">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small text-muted">الجهة:</label>
                    <select name="entity_id" class="form-select bg-light">
                        <option value="">الكل</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-filter me-1"></i> تصفية</button>
                    @if(request()->anyFilled(['search', 'entity_id']))
                        <a href="{{ route('approvals.index') }}" class="btn btn-outline-secondary px-3"><i class="fas fa-times me-1"></i> إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Content Area: Table View --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">رقم / المشروع</th>
                            <th class="py-3">المرحلة</th>
                            <th class="py-3">الجهة المخصصة</th>
                            <th class="py-3">تاريخ الإجراء</th>
                            <th class="px-4 py-3 text-end">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvalRecords as $record)
                            <tr>
                                <td class="px-4 py-3">
                                    <h6 class="mb-1 fw-bold">
                                        <a href="{{ route('approvals.show', $record->project_id) }}" class="text-decoration-none text-dark">
                                            {{ $record->project?->project_name ?? 'مشروع #'.$record->project_id }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">{{ $record->project?->form_number ?: 'PRJ-'.$record->project_id }}</small>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                        {{ $record->getPhaseLabel() }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="fw-semibold text-dark">{{ $record->entity?->name ?? 'غير محدد' }}</span>
                                </td>
                                <td class="py-3 text-muted small">
                                    {{ $record->updated_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('approvals.show', $record->project_id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i> التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-clipboard-check fs-2 mb-3" style="color:#d1d5db;"></i>
                                        <h5>لا توجد مهام حالياً</h5>
                                        <p class="mb-0">لا توجد سجلات موافقة تتطلب إجراءك في الوقت الحالي.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        @if(isset($approvalRecords) && $approvalRecords instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $approvalRecords->links() }}
        @endif
    </div>
</div>
@endsection
