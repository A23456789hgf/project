@extends('layouts.app')

@section('title', 'مراجعة واعتماد: ' . $project->project_name)

@section('styles')
<style>
    .approval-show-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 1rem;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.15);
    }

    .decision-panel {
        position: sticky;
        top: 85px;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .action-choice-btn {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: right;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        background: #f8fafc;
    }
    .action-choice-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .action-choice-btn.active-approve {
        background: #f0fdf4;
        border-color: #22c55e;
        color: #15803d;
    }
    .action-choice-btn.active-completion {
        background: #fffbeb;
        border-color: #f59e0b;
        color: #b45309;
    }
    .action-choice-btn.active-reject {
        background: #fef2f2;
        border-color: #ef4444;
        color: #b91c1c;
    }
    .action-choice-btn.active-consultation {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
    }

    .tracker-card {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .step-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .step-item:last-child {
        padding-bottom: 0;
    }
    .step-line {
        position: absolute;
        top: 36px;
        right: 17px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .step-item.is-completed .step-line {
        background: #22c55e;
    }
    .step-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: bold;
        z-index: 2;
        position: relative;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    {{-- Project Header --}}
    <div class="approval-show-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2 text-white-50 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50 text-decoration-none"><i class="fas fa-home me-1"></i> الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('approvals.index') }}" class="text-white-50 text-decoration-none">مركز المراجعة والاعتمادات</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $project->form_number ?: 'مشروع #'.$project->id }}</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <h3 class="fw-bold mb-0 text-white">
                        {{ $project->project_name }}
                    </h3>
                    <span class="badge bg-light text-dark px-3 py-1 rounded-pill small fw-semibold">
                        <i class="fas fa-hashtag me-1"></i>{{ $project->form_number ?: 'PRJ-'.$project->id }}
                    </span>
                    @if($project->status === 'rolled_back_for_review')
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">
                            <i class="fas fa-undo me-1"></i> بانتظار استكمال النواقص
                        </span>
                    @elseif($project->status === 'rejected')
                        <span class="badge bg-danger text-white px-3 py-1 rounded-pill">
                            <i class="fas fa-times-circle me-1"></i> مرفوض
                        </span>
                    @elseif(in_array($project->status, ['in_execution', 'in_progress', 'completed'], true))
                        <span class="badge bg-success text-white px-3 py-1 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> معتمد بالكامل
                        </span>
                    @else
                        <span class="badge bg-primary text-white px-3 py-1 rounded-pill">
                            <i class="fas fa-clock me-1"></i> قيد الاعتماد والمراجعة
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-white-50 small d-flex align-items-center gap-3 flex-wrap">
                    <span><i class="fas fa-building me-1"></i> الجهة المنشئة: <strong>{{ $project->creatorEntity?->name ?? 'غير محدد' }}</strong></span>
                    <span><i class="fas fa-user me-1"></i> المنشئ: <strong>{{ $project->createdBy?->name ?? 'غير معروف' }}</strong></span>
                    <span><i class="far fa-calendar-alt me-1"></i> تاريخ الإنشاء: {{ $project->created_at?->format('Y/m/d') }}</span>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('approvals.index') }}" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-semibold">
                    <i class="fas fa-arrow-right me-1"></i> العودة للمركز
                </a>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-light rounded-pill px-4 btn-sm fw-bold" target="_blank">
                    <i class="fas fa-file-alt me-1"></i> صفحة المشروع العامة
                </a>
            </div>
        </div>
    </div>

    {{-- Main Grid: Content (8) + Decision Panel (4) --}}
    <div class="row g-4">
        
        {{-- Left / Main Column (8) --}}
        <div class="col-12 col-lg-8">

            {{-- 1. Dynamic Approval Tracker --}}
            <div class="card tracker-card shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-route text-primary"></i> مسار وسلسلة الاعتمادات (Dynamic Approval Chain)
                    </h5>
                    @if($activeStep)
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                            الخطوة الحالية: #{{ $activeStep->step_order }}
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($approvalChain->isEmpty())
                        <div class="alert alert-info border-0 rounded-3 text-center mb-0">
                            <i class="fas fa-info-circle me-1"></i> لا توجد سلسلة موافقات مولدة لهذا المشروع بعد (المشروع في طور المسودة).
                        </div>
                    @else
                        @php
                            // Group chain steps by entity for visual clarity
                            $groupedChain = $approvalChain->groupBy('entity_id');
                        @endphp

                        @foreach($groupedChain as $entityId => $steps)
                            @php
                                $firstStep = $steps->first();
                                $entityName = $firstStep->entity?->name ?? 'الجهة المعنية';
                                $isCurrentEntity = $steps->contains(fn($s) => $s->is_active);
                                $isAllCompleted = $steps->every(fn($s) => $s->is_completed);
                            @endphp
                            <div class="mb-4 p-3 rounded-4 border {{ $isCurrentEntity ? 'border-primary bg-primary bg-opacity-10' : ($isAllCompleted ? 'border-success bg-success bg-opacity-10' : 'bg-light') }}">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle {{ $isAllCompleted ? 'bg-success text-white' : ($isCurrentEntity ? 'bg-primary text-white' : 'bg-secondary text-white') }} d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fas {{ $isAllCompleted ? 'fa-check' : ($isCurrentEntity ? 'fa-spinner fa-spin' : 'fa-building') }}"></i>
                                        </div>
                                        <div>
                                            <strong class="text-dark fs-6">{{ $entityName }}</strong>
                                            @if($loop->first)
                                                <span class="badge bg-secondary ms-1 small">الجهة المنشئة</span>
                                            @elseif($loop->last)
                                                <span class="badge bg-dark ms-1 small">الجهة العليا (Root)</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        @if($isAllCompleted)
                                            <span class="badge bg-success text-white px-2 py-1 rounded-pill small"><i class="fas fa-check me-1"></i> مكتملة</span>
                                        @elseif($isCurrentEntity)
                                            <span class="badge bg-primary text-white px-2 py-1 rounded-pill small"><i class="fas fa-hourglass-half me-1"></i> قيد المراجعة النشطة</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1 rounded-pill small"><i class="fas fa-lock me-1"></i> بانتظار الدور</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-2">
                                    @foreach($steps as $step)
                                        <div class="col-12 col-md-4">
                                            <div class="p-2 rounded-3 border bg-white h-100">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="small fw-bold text-dark">{{ $step->getPhaseArabicName() }}</span>
                                                    @if($step->is_completed)
                                                        <i class="fas fa-check-circle text-success" title="معتمد"></i>
                                                    @elseif($step->is_active)
                                                        <span class="spinner-grow spinner-grow-sm text-primary" role="status" title="نشط"></span>
                                                    @elseif($step->status === 'rejected')
                                                        <i class="fas fa-times-circle text-danger" title="مرفوض"></i>
                                                    @else
                                                        <i class="fas fa-lock text-muted opacity-50" title="مغلق"></i>
                                                    @endif
                                                </div>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    @if($step->is_completed)
                                                        <div class="text-success fw-semibold">تم الاعتماد</div>
                                                        <div>بواسطة: {{ $step->reviewedByUser?->name ?? 'المراجع' }}</div>
                                                        <div>{{ $step->reviewed_at?->format('Y/m/d H:i') }}</div>
                                                    @elseif($step->is_active)
                                                        <div class="text-primary fw-bold">الخطوة النشطة حالياً</div>
                                                    @else
                                                        <div class="text-secondary">بانتظار اكتمال الخطوات السابقة</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- 2. Project Information Accordion --}}
            <div class="accordion mb-4 shadow-sm rounded-4 overflow-hidden" id="projectDetailsAccordion">
                
                {{-- Accordion 1: Financial Summary --}}
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingFinancial">
                        <button class="accordion-button fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinancial" aria-expanded="true" aria-controls="collapseFinancial">
                            <i class="fas fa-coins text-success me-2"></i> الملخص المالي ومصادر التمويل
                        </button>
                    </h2>
                    <div id="collapseFinancial" class="accordion-collapse collapse show" aria-labelledby="headingFinancial" data-bs-parent="#projectDetailsAccordion">
                        <div class="accordion-body bg-light p-4">
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-white rounded-3 border text-center">
                                        <small class="text-muted d-block mb-1">التكلفة الإجمالية للمشروع</small>
                                        <h5 class="fw-bold text-success mb-0">
                                            {{ $project->cost ? number_format((float)$project->cost->total_cost, 2) : '0.00' }} {{ $project->cost?->currency ?? 'USD' }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-white rounded-3 border text-center">
                                        <small class="text-muted d-block mb-1">إجمالي التكاليف الأولية</small>
                                        <h5 class="fw-bold text-primary mb-0">
                                            {{ $project->preliminaryCost ? number_format((float)$project->preliminaryCost->preliminary_total_cost, 2) : '0.00' }} {{ $project->cost?->currency ?? 'USD' }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-white rounded-3 border text-center">
                                        <small class="text-muted d-block mb-1">إجمالي التكاليف التنفيذية</small>
                                        <h5 class="fw-bold text-info mb-0">
                                            {{ number_format((float)$project->executiveActionCosts->sum('cost'), 2) }} {{ $project->cost?->currency ?? 'USD' }}
                                        </h5>
                                    </div>
                                </div>
                            </div>

                            @if($project->financings->isNotEmpty())
                                <h6 class="fw-bold text-dark mb-2 small">تفاصيل مصادر التمويل:</h6>
                                <div class="table-responsive bg-white rounded-3 border">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>مصدر التمويل</th>
                                                <th>نوع التمويل</th>
                                                <th>المبلغ</th>
                                                <th>النسبة</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($project->financings as $financing)
                                                <tr>
                                                    <td>{{ $financing->fundingSource?->name ?? 'غير محدد' }}</td>
                                                    <td>{{ $financing->financing_type ?? '-' }}</td>
                                                    <td class="fw-bold">{{ number_format((float)$financing->amount, 2) }}</td>
                                                    <td>{{ $financing->percentage ? $financing->percentage.'%' : '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Accordion 2: Basic Info & Objectives --}}
                <div class="accordion-item border-0 border-bottom">
                    <h2 class="accordion-header" id="headingBasic">
                        <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic" aria-expanded="false" aria-controls="collapseBasic">
                            <i class="fas fa-info-circle text-primary me-2"></i> المعلومات الأساسية والأهداف والمستفيدين
                        </button>
                    </h2>
                    <div id="collapseBasic" class="accordion-collapse collapse" aria-labelledby="headingBasic" data-bs-parent="#projectDetailsAccordion">
                        <div class="accordion-body bg-light p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <strong class="text-dark small d-block mb-1">القطاع والمجال:</strong>
                                        <span class="text-muted">{{ $project->domain?->name ?? 'غير محدد' }} @if($project->subdomain) / {{ $project->subdomain->name }} @endif</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <strong class="text-dark small d-block mb-1">البرنامج الاستراتيجي:</strong>
                                        <span class="text-muted">{{ $project->program?->name ?? 'غير محدد' }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <strong class="text-dark small d-block mb-1">الوصف والملخص التنفيذي:</strong>
                                        <p class="text-muted mb-0 small">{{ $project->project_description ?: 'لا يوجد وصف مسجل للمشروع.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Accordion 3: Documents --}}
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingDocs">
                        <button class="accordion-button collapsed fw-bold text-dark bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocs" aria-expanded="false" aria-controls="collapseDocs">
                            <i class="fas fa-paperclip text-secondary me-2"></i> المستندات والمرفقات ({{ $project->documents->count() }})
                        </button>
                    </h2>
                    <div id="collapseDocs" class="accordion-collapse collapse" aria-labelledby="headingDocs" data-bs-parent="#projectDetailsAccordion">
                        <div class="accordion-body bg-light p-4">
                            @if($project->documents->isEmpty())
                                <p class="text-muted small mb-0 text-center">لا توجد ملفات أو وثائق مرفقة بهذا المشروع.</p>
                            @else
                                <ul class="list-group list-group-flush rounded-3 border bg-white">
                                    @foreach($project->documents as $doc)
                                        <li class="list-group-item d-flex align-items-center justify-content-between p-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-file-pdf text-danger fs-5"></i>
                                                <div>
                                                    <strong class="d-block text-dark small">{{ $doc->document_name ?: basename($doc->file_path) }}</strong>
                                                    <small class="text-muted">{{ $doc->created_at?->format('Y/m/d') }}</small>
                                                </div>
                                            </div>
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-download me-1"></i> تحميل
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- 3. Activity History --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-history text-secondary"></i> سجل الإجراءات والتدقيق (Activity & Audit History)
                    </h5>
                    <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill small">
                        {{ $project->activityHistory->count() }} إجراء مسجل
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($project->activityHistory->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-inbox fs-3 mb-2 d-block opacity-50"></i>
                            لم يتم تسجيل أي إجراءات سابقة على هذا المشروع.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">التاريخ والوقت</th>
                                        <th>المستخدم</th>
                                        <th>المرحلة / الخطوة</th>
                                        <th>الإجراء</th>
                                        <th>الملاحظات والتفاصيل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->activityHistory as $history)
                                        <tr>
                                            <td class="ps-4 text-muted" style="white-space: nowrap;">
                                                {{ $history->created_at?->format('Y/m/d H:i') }}
                                            </td>
                                            <td class="fw-semibold text-dark">
                                                {{ $history->user?->name ?? 'النظام' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $history->to_stage_name ?: $history->from_stage_name ?: 'المرحلة' }}</span>
                                            </td>
                                            <td>
                                                @if(in_array($history->action_type, ['approved', 'completed', 'finalized']))
                                                    <span class="badge bg-success text-white px-2 py-1 rounded-pill">اعتماد / موافقة</span>
                                                @elseif(in_array($history->action_type, ['rejected']))
                                                    <span class="badge bg-danger text-white px-2 py-1 rounded-pill">رفض</span>
                                                @elseif(in_array($history->action_type, ['returned_for_revision', 'stage_regression', 'requires_action']))
                                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">طلب استكمال</span>
                                                @elseif(in_array($history->action_type, ['resubmitted']))
                                                    <span class="badge bg-info text-white px-2 py-1 rounded-pill">إعادة تقديم</span>
                                                @else
                                                    <span class="badge bg-secondary text-white px-2 py-1 rounded-pill">{{ $history->action_type }}</span>
                                                @endif
                                            </td>
                                            <td class="text-muted">
                                                {{ $history->notes ?: '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right / Decision Board Column (4) --}}
        <div class="col-12 col-lg-4">
            <div class="decision-panel p-4 shadow-sm">
                
                {{-- Current Active Step Authority Card --}}
                <div class="p-3 rounded-4 bg-light border mb-4">
                    <small class="text-muted d-block fw-semibold mb-1">صلاحية الاعتماد الحالية:</small>
                    @if($activeStep)
                        <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                            <i class="fas fa-shield-alt text-primary"></i> {{ $activeStep->entity?->name ?? 'الجهة المختصة' }}
                        </h6>
                        <span class="badge bg-primary text-white px-3 py-1 rounded-pill small">
                            {{ $activeStep->getPhaseArabicName() }}
                        </span>
                    @elseif($project->status === 'rolled_back_for_review')
                        <h6 class="fw-bold text-warning mb-1">
                            <i class="fas fa-undo me-1"></i> الجهة المنشئة (استكمال نواقص)
                        </h6>
                        <small class="text-muted">{{ $project->creatorEntity?->name }}</small>
                    @elseif(in_array($project->status, ['in_execution', 'in_progress', 'completed'], true))
                        <h6 class="fw-bold text-success mb-1">
                            <i class="fas fa-check-circle me-1"></i> مكتمل ومعتمد
                        </h6>
                    @else
                        <h6 class="fw-bold text-muted mb-1">لا توجد مرحلة نشطة</h6>
                    @endif

                    {{-- Quick Access to Dedicated Reviews if Permitted --}}
                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->can('reviews.technical') || ($activeStep && $activeStep->phase === 'technical_review')))
                        <div class="mt-3 pt-2 border-top">
                            <a href="{{ route('projects.review.technical', $project) }}" class="btn btn-sm w-100 text-white fw-bold mb-2 shadow-sm rounded-pill" style="background-color: #6f42c1;">
                                <i class="fas fa-tools me-1"></i> إجراء المراجعة الفنية للمشروع
                            </a>
                        </div>
                    @endif
                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->can('reviews.financial') || ($activeStep && $activeStep->phase === 'financial_review')))
                        <div class="mt-1">
                            <a href="{{ route('projects.review.financial', $project) }}" class="btn btn-sm w-100 btn-info text-white fw-bold shadow-sm rounded-pill">
                                <i class="fas fa-dollar-sign me-1"></i> إجراء المراجعة المالية للمشروع
                            </a>
                        </div>
                    @endif
                </div>

                {{-- DECISION FORM / ACTIONS --}}
                @if($project->status === 'rolled_back_for_review')
                    {{-- Resubmit Form --}}
                    @can('resubmit', $project)
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark mb-2">إعادة تقديم المشروع</h5>
                            <p class="text-muted small mb-3">تم إرجاع المشروع لاستكمال الملاحظات. يرجى تدوين ما تم استكماله وإعادة إرساله لسلسلة الاعتماد.</p>
                            
                            <form action="{{ route('approvals.resubmit', $project) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">ملاحظات الاستكمال / المعالجة: <span class="text-danger">*</span></label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="اكتب ما تم تعديله أو إرفاقه بناءً على طلب الاستكمال..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">مرفقات داعمة (اختياري):</label>
                                    <input type="file" name="attachment" class="form-control form-control-sm">
                                </div>

                                <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold py-2 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> إعادة تقديم المشروع للاعتماد
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-secondary border-0 rounded-3 text-center mb-0">
                            <i class="fas fa-lock me-1"></i> إعادة التقديم متاحة فقط للمستخدمين المخولين من الجهة المنشئة للمشروع.
                        </div>
                    @endcan

                @elseif($project->status === 'pending_approval' && $activeStep)
                    @if($canActOnActiveStep)
                        <div class="mb-3">
                            <h5 class="fw-bold text-dark mb-3">لوحة اتخاذ القرار</h5>
                            
                            {{-- Action Choice Selector --}}
                            <div class="d-flex flex-column gap-2 mb-4" id="actionChoiceList">
                                <div class="action-choice-btn active-approve" onclick="selectApprovalAction('approve')">
                                    <i class="fas fa-check-circle fs-5 text-success"></i>
                                    <div>
                                        <strong class="d-block">اعتماد وموافقة</strong>
                                        <small class="text-muted">الموافقة على المرحلة ونقل المشروع للخطوة التالية</small>
                                    </div>
                                </div>

                                <div class="action-choice-btn" onclick="selectApprovalAction('completion')">
                                    <i class="fas fa-undo fs-5 text-warning"></i>
                                    <div>
                                        <strong class="d-block">طلب استكمال نواقص</strong>
                                        <small class="text-muted">إرجاع المشروع للجهة المنشئة أو للخطوة السابقة</small>
                                    </div>
                                </div>

                                <div class="action-choice-btn" onclick="selectApprovalAction('consultation')">
                                    <i class="fas fa-comments fs-5 text-primary"></i>
                                    <div>
                                        <strong class="d-block">إحالة واستشارة فنية</strong>
                                        <small class="text-muted">إرسال استفسار فني/إداري لجهة أخرى دون تغيير الدور</small>
                                    </div>
                                </div>

                                <div class="action-choice-btn" onclick="selectApprovalAction('reject')">
                                    <i class="fas fa-times-circle fs-5 text-danger"></i>
                                    <div>
                                        <strong class="d-block">رفض المشروع</strong>
                                        <small class="text-muted">رفض المشروع نهائياً وإيقاف سلسلة الاعتماد</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Form 1: Approve --}}
                            <form id="formApprove" action="{{ route('approvals.approve', $project) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">ملاحظات الاعتماد (اختياري):</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="أدخل أي ملاحظات فنية أو توصيات..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">مرفق الاعتماد (اختياري):</label>
                                    <input type="file" name="attachment" class="form-control form-control-sm">
                                </div>
                                <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold py-2 shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> تأكيد الاعتماد والموافقة
                                </button>
                            </form>

                            {{-- Form 2: Request Completion --}}
                            <form id="formCompletion" action="{{ route('approvals.requestAction', $project) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">إرجاع المشروع إلى: <span class="text-danger">*</span></label>
                                    <div class="form-check p-2 bg-light rounded-2 border mb-2">
                                        <input class="form-check-input ms-2" type="radio" name="return_target" id="retTargetCreator" value="creator_entity" checked>
                                        <label class="form-check-label small fw-bold text-dark" for="retTargetCreator">
                                            الجهة المنشئة (Creator Entity)
                                        </label>
                                    </div>
                                    <div class="form-check p-2 bg-light rounded-2 border">
                                        <input class="form-check-input ms-2" type="radio" name="return_target" id="retTargetPrev" value="previous_step">
                                        <label class="form-check-label small fw-bold text-dark" for="retTargetPrev">
                                            الخطوة السابقة مباشرة (Previous Step)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">سبب طلب الاستكمال / الملاحظات: <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="وضّح بالتفصيل النواقص والملاحظات المطلوبة (10 أحرف كحد أدنى)..." required minlength="10"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">مرفق توضيحي (اختياري):</label>
                                    <input type="file" name="attachment" class="form-control form-control-sm">
                                </div>

                                <button type="submit" class="btn btn-warning rounded-pill w-100 fw-bold py-2 shadow-sm text-dark">
                                    <i class="fas fa-undo me-1"></i> إرسال طلب استكمال النواقص
                                </button>
                            </form>

                            {{-- Form 3: Consultation / Referral --}}
                            <form id="formConsultation" action="{{ route('approvals.referral', $project) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">إحالة واستشارة إلى: <span class="text-danger">*</span></label>
                                    <select name="referred_entity_id" id="consultation_referred_entity_id" class="form-select form-select-sm" required>
                                        <option value="">-- اختر الجهة المستشارة --</option>
                                        @foreach($entitiesList as $ent)
                                            <option value="{{ $ent->id }}">{{ $ent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">المستخدم المستلم: <span class="text-danger">*</span></label>
                                    <select name="referred_user_id" id="consultation_referred_user_id" class="form-select form-select-sm" required>
                                        <option value="">-- اختر المستخدم المستلم --</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">نص الاستفسار أو الاستشارة: <span class="text-danger">*</span></label>
                                    <textarea name="referral_text" class="form-control" rows="3" placeholder="اكتب الاستفسار أو النقطة المطلوب إبداء الرأي الفني فيها..." required minlength="10"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">المرفقات الداعمة (اختياري):</label>
                                    <input type="file" name="attachments[]" multiple class="form-control form-control-sm" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx,.txt">
                                    <div class="form-text small text-muted">يمكن إرفاق أكثر من ملف.</div>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold py-2 shadow-sm">
                                    <i class="fas fa-paper-plane me-1"></i> إرسال طلب الاستشارة
                                </button>
                            </form>

                            {{-- Form 4: Reject --}}
                            <form id="formReject" action="{{ route('approvals.reject', $project) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                                @csrf
                                <div class="alert alert-danger border-0 rounded-3 small p-2 mb-3">
                                    <i class="fas fa-exclamation-triangle me-1"></i> تنبيه: رفض المشروع سيؤدي إلى إيقاف المسار وإعادته للمسودة.
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-dark">سبب الرفض: <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="اذكر سبب الرفض بالتفصيل (إلزامي)..." required minlength="5"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">مرفق قرار الرفض (اختياري):</label>
                                    <input type="file" name="attachment" class="form-control form-control-sm">
                                </div>

                                <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold py-2 shadow-sm">
                                    <i class="fas fa-times-circle me-1"></i> تأكيد رفض المشروع
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-secondary border-0 rounded-4 p-4 text-center mb-0">
                            <i class="fas fa-lock fs-3 text-secondary mb-2 d-block"></i>
                            <strong class="d-block text-dark mb-1">بانتظار إجراء الجهة المختصة</strong>
                            <p class="small text-muted mb-0">
                                الخطوة النشطة الحالية مسندة إلى <strong>{{ $activeStep->entity?->name ?? 'الجهة المختصة' }}</strong>. لا تملك صلاحية اتخاذ القرار على هذه الخطوة.
                            </p>
                        </div>
                    @endif
                @elseif(in_array($project->status, ['in_execution', 'in_progress', 'completed'], true))
                    <div class="alert alert-success border-0 rounded-4 p-4 text-center mb-0">
                        <i class="fas fa-check-double fs-3 text-success mb-2 d-block"></i>
                        <strong class="d-block text-dark mb-1">المشروع معتمد بالكامل</strong>
                        <p class="small text-muted mb-0">تم اعتماد كافة خطوات المسار الهرمي للمشروع، وهو الآن في طور التنفيذ.</p>
                    </div>
                @elseif($project->status === 'rejected')
                    <div class="alert alert-danger border-0 rounded-4 p-4 text-center mb-0">
                        <i class="fas fa-ban fs-3 text-danger mb-2 d-block"></i>
                        <strong class="d-block text-dark mb-1">المشروع مرفوض</strong>
                        <p class="small text-muted mb-0">تم إنهاء مسار الاعتماد بالرفض.</p>
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    function selectApprovalAction(action) {
        // Toggle forms
        document.getElementById('formApprove').style.display = (action === 'approve') ? 'block' : 'none';
        document.getElementById('formCompletion').style.display = (action === 'completion') ? 'block' : 'none';
        document.getElementById('formConsultation').style.display = (action === 'consultation') ? 'block' : 'none';
        document.getElementById('formReject').style.display = (action === 'reject') ? 'block' : 'none';

        // Toggle active button styling
        const buttons = document.querySelectorAll('#actionChoiceList .action-choice-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active-approve', 'active-completion', 'active-consultation', 'active-reject');
        });

        const activeBtn = event.currentTarget;
        if (action === 'approve') activeBtn.classList.add('active-approve');
        if (action === 'completion') activeBtn.classList.add('active-completion');
        if (action === 'consultation') activeBtn.classList.add('active-consultation');
        if (action === 'reject') activeBtn.classList.add('active-reject');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const entitySelect = document.getElementById('consultation_referred_entity_id');
        const userSelect = document.getElementById('consultation_referred_user_id');

        if (entitySelect) {
            entitySelect.addEventListener('change', function() {
                const entityId = this.value;
                userSelect.innerHTML = '<option value="">-- اختر المستخدم المستلم --</option>';
                
                if (entityId) {
                    fetch(`/api/entities/${entityId}/users`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.users) {
                                data.users.forEach(user => {
                                    const option = document.createElement('option');
                                    option.value = user.id;
                                    option.textContent = user.name;
                                    userSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error fetching users:', error));
                }
            });
        }
    });
</script>
@endsection
