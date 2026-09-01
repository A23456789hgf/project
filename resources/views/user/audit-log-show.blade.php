@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>تفاصيل العملية</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> العودة إلى السجل
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">معلومات العملية</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">المستخدم:</label>
                            <p>{{ $auditLog->user->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">نوع الإجراء:</label>
                            <p><span class="badge bg-info fs-6">{{ $auditLog->action }}</span></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">نوع البيانات:</label>
                            <p>{{ $auditLog->model_type ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">معرف البيانات:</label>
                            <p>{{ $auditLog->model_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">عنوان IP:</label>
                            <p>{{ $auditLog->ip_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">التاريخ والوقت:</label>
                            <p>{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>
                    @if($auditLog->description)
                    <div class="row">
                        <div class="col-md-12">
                            <label class="fw-bold">الوصف:</label>
                            <p>{{ $auditLog->description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($auditLog->old_values || $auditLog->new_values)
            <div class="row">
                @if($auditLog->old_values)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">القيم السابقة</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($auditLog->old_values as $key => $value)
                                    <tr>
                                        <td><strong>{{ $key }}:</strong></td>
                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if($auditLog->new_values)
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">القيم الجديدة</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tbody>
                                    @foreach($auditLog->new_values as $key => $value)
                                    <tr>
                                        <td><strong>{{ $key }}:</strong></td>
                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
