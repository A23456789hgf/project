@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <h1 class="h3 mb-0 font-weight-bold"><i class="fas fa-tasks"></i> إدارة موافقات المشاريع</h1>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($projects->count() > 0)
        <!-- Table view for records -->
        <div class="table-responsive shadow-sm border-0 mb-4">
            <table class="table table-hover align-middle">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">المشروع / رقم النموذج</th>
                        <th scope="col">المرحلة الحالية</th>
                        <th scope="col">الحالة</th>
                        <th scope="col" class="text-end">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        @php
                            $statusBg = match($project->approval_status) {
                                'in_process' => 'bg-info',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            $statusAr = match($project->approval_status) {
                                'in_process' => 'قيد المعالجة',
                                'approved' => 'موافق عليه',
                                'rejected' => 'مرفوض',
                                default => 'قيد الانتظار'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $project->project_name }}</div>
                                <div class="badge bg-primary text-wrap">{{ $project->form_number }}</div>
                                <div class="text-muted small mt-1">
                                    <i class="fas fa-user-circle"></i>
                                    {{ $project->createdBy ? $project->createdBy->name : 'غير محدد' }}
                                </div>
                            </td>
                            <td>
                                @if ($project->currentApprovalStage)
                                    <span class="badge bg-light text-dark border">{{ $project->currentApprovalStage->name }}</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusBg }} px-3 py-2 rounded-pill">{{ $statusAr }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('projects.approval.show', $project) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye"></i> التفاصيل
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($projects->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    @else
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle"></i> لا توجد مشاريع قيد الموافقة في الوقت الحالي.
        </div>
    @endif
</div>
@endsection