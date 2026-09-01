@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>
                    إحالات المشروع: {{ $project->project_name }}
                </h2>
                <div>
                    <a href="{{ route('project-referrals.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Project Information --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>معلومات المشروع</h5>
            <span class="badge bg-white text-primary fw-bold">{{ $project->form_number }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="text-muted small d-block">رقم المشروع</label>
                    <a href="{{ route('projects.show', $project->id) }}" 
                       class="fw-bold text-decoration-none" target="_blank">
                        {{ $project->form_number }}
                        <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                    </a>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small d-block">اسم المشروع</label>
                    <span class="fw-bold">{{ $project->project_name }}</span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">تكلفة المشروع</label>
                    <span class="text-primary fw-bold fs-5">
                        {{ number_format($project->cost->total_cost ?? 0, 2) }} ريال
                    </span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">الجهة المقدمة</label>
                    <span class="badge bg-info">
                        {{ $project->createdBy->entity->name ?? 'غير محدد' }}
                    </span>
                </div>
                <div class="col-md-3">
                    <label class="text-muted small d-block">المرحلة الحالية</label>
                    <span class="badge bg-secondary">
                        {{ $project->currentApprovalStage->name ?? 'غير محدد' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Unified Approval Form & Activity Log --}}
    <div class="mt-4">
        @include('projects.partials.approval-form', [
            'project' => $project,
            'approvalStages' => $approvalStages,
            'reviewerType' => $reviewerType
        ])
    </div>
</div>

</div>
@endsection
