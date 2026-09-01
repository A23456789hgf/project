@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0 font-weight-bold"><i class="fas fa-tasks"></i> إدارة موافقات المشاريع</h1>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list"></i> المشاريع قيد الموافقة</h5>
        </div>
        <div class="card-body p-0">
            @if ($projects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم المشروع</th>
                                <th>رقم النموذج</th>
                                <th>المطور</th>
                                <th>المرحلة الحالية</th>
                                <th>الحالة</th>
                                <th>تاريخ الإرسال</th>
                                <th class="text-center">الإجراءات</th>
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
                                <tr class="align-middle">
                                    <td>
                                        <strong>{{ $project->project_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $project->form_number }}</span>
                                    </td>
                                    <td>
                                        <small>
                                            @if ($project->createdBy)
                                                <i class="fas fa-user-circle"></i> {{ $project->createdBy->name }}
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if ($project->currentApprovalStage)
                                            <span class="badge bg-primary">{{ $project->currentApprovalStage->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBg }}">
                                            {{ $statusAr }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $project->created_at ? $project->created_at->format('Y-m-d H:i') : '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('projects.approval.show', $project) }}" class="btn btn-sm btn-info" title="عرض تفاصيل الموافقة">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($projects->hasPages())
                    <div class="card-footer bg-light">
                        <nav>
                            {{ $projects->links() }}
                        </nav>
                    </div>
                @endif
            @else
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> لا توجد مشاريع قيد الموافقة في الوقت الحالي.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
