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
    
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.25s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-3px);
    }
    .stat-card {
        border-radius: 1rem;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .stat-card.active-tab-card {
        border-color: #3b82f6;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.2);
    }
    
    .nav-tabs-custom {
        border-bottom: 2px solid #e2e8f0;
        gap: 0.5rem;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 600;
        padding: 0.85rem 1.25rem;
        border-radius: 0.5rem 0.5rem 0 0;
        transition: all 0.2s ease;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #1e293b;
        background-color: #f8fafc;
    }
    .nav-tabs-custom .nav-link.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background-color: #eff6ff;
    }

    /* Approval Record Cards */
    .approval-record-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
        background: #ffffff;
        position: relative;
    }
    .approval-record-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 12px 28px -5px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .approval-record-card.card-active {
        border: 2px solid #2563eb;
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.15);
    }
    .approval-record-card.card-approved {
        border-top: 4px solid #10b981;
    }
    .approval-record-card.card-rejected {
        border-top: 4px solid #ef4444;
    }
    .approval-record-card.card-returned {
        border-top: 4px solid #f59e0b;
    }
    .approval-record-card.card-locked {
        background-color: #fafbfc;
        border-top: 4px solid #94a3b8;
    }

    .hover-primary:hover {
        color: #2563eb !important;
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
                    منصة اتخاذ القرارات الإدارية والفنية الموحدة ومتابعة سجلات ومسارات سلاسل الاعتماد لجميع المشاريع
                </p>
            </div>
            <div>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-semibold">
                    <i class="fas fa-list me-1"></i> قائمة المشاريع العامة
                </a>
            </div>
        </div>
    </div>

    {{-- Notice Banner Directing to Projects Archive --}}
    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 p-3 px-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-info d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                <i class="fas fa-info-circle fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">مركز المراجعة والاعتمادات مخصص للمهام التي تتطلب قرارك حالياً</h6>
                <p class="mb-0 text-muted small">المشاريع قيد انتظار الآخرين أو الموافقات المكتملة والأرشيف متاحة رسمياً في <a href="{{ route('projects.index') }}" class="fw-bold text-decoration-underline text-info">صفحة المشاريع</a>.</p>
            </div>
        </div>
        <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
            <i class="fas fa-list me-1"></i> قائمة المشاريع والأرشيف
        </a>
    </div>

    {{-- Statistics Cards (Active Task Inbox) --}}
    <div class="row g-3 mb-4">
        {{-- Card 1: All Action Required --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'my_action'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'my_action' ? 'active-tab-card bg-primary bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">بانتظار إجرائي</span>
                            <h3 class="fw-bold text-primary mb-0">{{ number_format($myActionCount) }}</h3>
                            <small class="text-muted">موافقات تتطلب قرارك الآن</small>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-clock fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 2: Technical Review --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'technical'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'technical' ? 'active-tab-card bg-info bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">المراجعة الفنية</span>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($technicalCount) }}</h3>
                            <small class="text-muted">مهام تدقيق وتعديل الأنشطة</small>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-tools fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 3: Financial Review --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'financial'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'financial' ? 'active-tab-card bg-success bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">المراجعة المالية</span>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($financialCount) }}</h3>
                            <small class="text-muted">مهام تدقيق وتعديل التكاليف</small>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-coins fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Card 4: Stage Approvals --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'stages'])) }}" class="stat-card-link">
                <div class="card stat-card h-100 shadow-sm p-3 {{ $activeTab === 'stages' ? 'active-tab-card bg-warning bg-opacity-10' : '' }}">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-semibold small d-block mb-1">اعتمادات المراحل</span>
                            <h3 class="fw-bold text-warning mb-0">{{ number_format($stagesCount) }}</h3>
                            <small class="text-muted">موافقات قيادية ومرحلية</small>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="fas fa-layer-group fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <ul class="nav nav-tabs nav-tabs-custom mb-4 overflow-auto flex-nowrap text-nowrap">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'my_action' ? 'active' : '' }}" href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'my_action'])) }}">
                <i class="fas fa-user-clock me-1"></i> الكل (بانتظار إجرائي)
                <span class="badge rounded-pill {{ $activeTab === 'my_action' ? 'bg-primary' : 'bg-secondary' }} ms-1">
                    {{ $myActionCount }}
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'technical' ? 'active' : '' }}" href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'technical'])) }}">
                <i class="fas fa-tools me-1"></i> المراجعة الفنية
                <span class="badge rounded-pill {{ $activeTab === 'technical' ? 'bg-info' : 'bg-secondary' }} ms-1">
                    {{ $technicalCount }}
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'financial' ? 'active' : '' }}" href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'financial'])) }}">
                <i class="fas fa-coins me-1"></i> المراجعة المالية
                <span class="badge rounded-pill {{ $activeTab === 'financial' ? 'bg-success' : 'bg-secondary' }} ms-1">
                    {{ $financialCount }}
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'stages' ? 'active' : '' }}" href="{{ route('approvals.index', array_merge(request()->except('tab', 'page'), ['tab' => 'stages'])) }}">
                <i class="fas fa-layer-group me-1"></i> اعتمادات المراحل
                <span class="badge rounded-pill {{ $activeTab === 'stages' ? 'bg-warning text-dark' : 'bg-secondary' }} ms-1">
                    {{ $stagesCount }}
                </span>
            </a>
        </li>

        {{-- Fallback navigation for archive/other tabs if queried explicitly --}}
        @if(in_array($activeTab, ['waiting_others', 'completed', 'rejected', 'returned'], true))
            <li class="nav-item">
                <a class="nav-link active" href="#">
                    <i class="fas fa-history me-1"></i>
                    @if($activeTab === 'waiting_others')
                        قيد انتظار الآخرين
                    @elseif($activeTab === 'completed')
                        الموافقات المكتملة والأرشيف
                    @elseif($activeTab === 'rejected')
                        المرفوضة
                    @elseif($activeTab === 'returned')
                        المعادة للاستكمال
                    @endif
                </a>
            </li>
        @endif
    </ul>

    {{-- Filter and Search Bar (Approval Records Filter) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('approvals.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                
                {{-- Search --}}
                <div class="col-12 col-md-3">
                    <label class="form-label fw-bold small text-muted">المشروع:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control bg-light border-start-0" placeholder="اسم المشروع أو رقم الاستمارة...">
                    </div>
                </div>

                {{-- Entity Filter --}}
                <div class="col-12 col-md-2">
                    <label class="form-label fw-bold small text-muted">الجهة:</label>
                    <select name="entity_id" class="form-select bg-light">
                        <option value="">جميع الجهات</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Phase Filter --}}
                <div class="col-12 col-md-2">
                    <label class="form-label fw-bold small text-muted">مرحلة الموافقة:</label>
                    <select name="phase" class="form-select bg-light">
                        <option value="">جميع المراحل</option>
                        @foreach($phases as $phase)
                            <option value="{{ $phase->value }}" {{ request('phase') === $phase->value ? 'selected' : '' }}>
                                {{ $phase->arabicName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-md-2">
                    <label class="form-label fw-bold small text-muted">الحالة:</label>
                    <select name="status" class="form-select bg-light">
                        <option value="">جميع الحالات</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>الخطوة النشطة (Active)</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار (Pending)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>معتمد (Approved)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>مرفوض (Rejected)</option>
                        <option value="need_action" {{ in_array(request('status'), ['need_action', 'returned']) ? 'selected' : '' }}>معاد للاستكمال (Returned)</option>
                        <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>مقفل (Locked)</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-12 col-md-3 d-flex gap-2">
                    <div class="w-50">
                        <label class="form-label fw-bold small text-muted">من تاريخ:</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control bg-light">
                    </div>
                    <div class="w-50">
                        <label class="form-label fw-bold small text-muted">إلى تاريخ:</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control bg-light">
                    </div>
                </div>

                {{-- Submit & Reset Buttons --}}
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                    @if(request()->hasAny(['search', 'entity_id', 'phase', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('approvals.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary rounded-pill px-3" title="إعادة ضبط الفلاتر">
                            <i class="fas fa-times me-1"></i> إلغاء التصفية
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Content Area: Cards View --}}
    <div class="row g-4">
        {{-- Approval Records Cards View --}}
        @php
            $recordsList = $approvalRecords ?? $projects;
        @endphp

            @if(empty($recordsList) || $recordsList->isEmpty())
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <div class="mb-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 80px; height: 80px;">
                                <i class="fas fa-clipboard-list text-muted fs-2"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">لا توجد سجلات موافقة في هذا التبويب</h5>
                        <p class="text-muted small mb-0">لم يتم العثور على أي سجلات موافقة مطابقة للمعايير المحددة أو التصفية.</p>
                    </div>
                </div>
            @else
                @foreach($recordsList as $record)
                    @php
                        $isStepActive = (bool) $record->is_active;
                        $isLocked = ($record->status === 'locked');
                        $isApproved = ($record->status === 'approved' || (bool) $record->is_completed);
                        $isRejected = ($record->status === 'rejected');
                        $isReturned = in_array($record->status, ['need_action', 'requires_action'], true);
                        $isPending = ($record->status === 'pending');
                        
                        $cardClass = '';
                        if ($isStepActive) {
                            $cardClass = 'card-active';
                        } elseif ($isApproved) {
                            $cardClass = 'card-approved';
                        } elseif ($isRejected) {
                            $cardClass = 'card-rejected';
                        } elseif ($isReturned) {
                            $cardClass = 'card-returned';
                        } elseif ($isLocked) {
                            $cardClass = 'card-locked';
                        }
                    @endphp
                    <div class="col-12 col-lg-6 col-xl-4">
                        <div class="card approval-record-card h-100 shadow-sm d-flex flex-column {{ $cardClass }}">
                            <div class="card-body p-4 flex-grow-1">
                                
                                {{-- Card Header: Step Order + Phase + Status Badge --}}
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-semibold small">
                                            <i class="fas fa-layer-group text-primary me-1"></i> الخطوة {{ $record->step_order }}
                                        </span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5 fw-semibold small">
                                            <i class="fas fa-tasks me-1"></i> {{ $record->getPhaseLabel() }}
                                        </span>
                                    </div>

                                    {{-- Distinct Authentic Status Badges --}}
                                    <div>
                                        @if($isStepActive)
                                            <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill fw-bold shadow-sm">
                                                <i class="fas fa-bolt text-warning me-1"></i> الخطوة النشطة
                                            </span>
                                        @elseif($isApproved)
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-check-circle me-1"></i> معتمد
                                            </span>
                                        @elseif($isRejected)
                                            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-times-circle me-1"></i> مرفوض
                                            </span>
                                        @elseif($isReturned)
                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-undo me-1"></i> معاد للاستكمال
                                            </span>
                                        @elseif($isLocked)
                                            <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="fas fa-lock me-1"></i> مقفلة
                                            </span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold">
                                                <i class="far fa-clock me-1"></i> قيد الانتظار
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Project Name & Code --}}
                                <h5 class="fw-bold text-dark mb-1">
                                    <a href="{{ route('approvals.show', $record->project_id) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $record->project?->project_name ?? 'مشروع #'.$record->project_id }}
                                    </a>
                                </h5>
                                <div class="text-muted small mb-3">
                                    <i class="fas fa-hashtag text-secondary me-1"></i>
                                    <span>{{ $record->project?->form_number ?: 'PRJ-'.$record->project_id }}</span>
                                </div>

                                {{-- Metadata Container --}}
                                <div class="bg-light rounded-3 p-3 border mb-3 small">
                                    {{-- Entity --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted"><i class="fas fa-building text-secondary me-1"></i> الجهة:</span>
                                        <strong class="text-dark">{{ $record->entity?->name ?? ($record->project?->creatorEntity?->name ?? 'غير محدد') }}</strong>
                                    </div>

                                    {{-- Stage / Phase Resolved Name --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted"><i class="fas fa-stream text-secondary me-1"></i> مرحلة الاعتماد:</span>
                                        <span class="text-dark fw-semibold">{{ $record->getResolvedStageName() }}</span>
                                    </div>

                                    {{-- Reviewer --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="text-muted"><i class="fas fa-user-check text-secondary me-1"></i> المراجع:</span>
                                        <span class="text-dark">{{ $record->reviewedByUser?->name ?? ($record->technicalReviewer?->name ?? ($record->financialReviewer?->name ?? 'بانتظار المراجعة')) }}</span>
                                    </div>

                                    {{-- Dates --}}
                                    <div class="d-flex align-items-center justify-content-between border-top pt-2 mt-2">
                                        <span class="text-muted"><i class="far fa-calendar-alt text-secondary me-1"></i> تاريخ الإنشاء:</span>
                                        <span dir="ltr" class="text-secondary">{{ $record->created_at?->format('Y-m-d') ?? '-' }}</span>
                                    </div>

                                    @if($record->reviewed_at || $record->status === 'approved' || $record->status === 'rejected')
                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                        <span class="text-muted"><i class="fas fa-history text-secondary me-1"></i> تاريخ الإجراء:</span>
                                        <span dir="ltr" class="text-secondary">{{ $record->reviewed_at?->format('Y-m-d') ?? ($record->updated_at?->format('Y-m-d') ?? '-') }}</span>
                                    </div>
                                    @endif
                                </div>

                                {{-- Notes / Alerts if present --}}
                                @if(!empty($record->rejection_reason))
                                    <div class="alert alert-danger py-2 px-3 mb-2 small rounded-3 border-0">
                                        <i class="fas fa-exclamation-circle me-1"></i> <strong>سبب الرفض:</strong> {{ Str::limit($record->rejection_reason, 100) }}
                                    </div>
                                @elseif(!empty($record->required_action))
                                    <div class="alert alert-warning py-2 px-3 mb-2 small rounded-3 border-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i> <strong>الإجراء المطلوب:</strong> {{ Str::limit($record->required_action, 100) }}
                                    </div>
                                @elseif(!empty($record->notes))
                                    <div class="bg-light border rounded-3 py-2 px-3 mb-2 small text-muted">
                                        <i class="fas fa-comment-alt text-secondary me-1"></i> {{ Str::limit($record->notes, 90) }}
                                    </div>
                                @endif

                            </div>

                            {{-- Card Footer with Actions --}}
                            <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i> {{ $record->updated_at?->diffForHumans() ?? $record->created_at?->diffForHumans() }}
                                </small>
                                
                                <div class="d-flex align-items-center gap-2">
                                    {{-- View Project Detail Button --}}
                                    <a href="{{ route('approvals.show', $record->project_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                                        <i class="fas fa-eye me-1"></i> عرض المشروع
                                    </a>

                                    {{-- Take Action Button: Strictly rendered only if backend confirms authorization --}}
                                    @if(!empty($record->can_user_act))
                                        <a href="{{ route('approvals.show', $record->project_id) }}#decision-board" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-sm">
                                            <i class="fas fa-pen-fancy me-1"></i> اتخاذ الإجراء
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
    </div>

    {{-- Pagination --}}
    <div class="mt-4 d-flex justify-content-center">
        @if(isset($approvalRecords) && $approvalRecords instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $approvalRecords->links() }}
        @elseif(isset($projects) && $projects instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {{ $projects->links() }}
        @endif
    </div>

</div>
@endsection
