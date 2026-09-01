@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">سجل الأنشطة</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تفاصيل العملية #{{ $auditLog->id }}</li>
                    </ol>
                </nav>
                <h2 class="h3 mb-0 text-gray-800">
                    <x-icon name="info-circle" class="text-primary me-2" />تفاصيل السجل الإداري
                </h2>
            </div>
            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary rounded-pill">
                <x-icon name="arrow-right" class="me-1" /> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0">المعلومات الأساسية</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">المستخدم / المسؤول</label>
                            <div class="p-3 bg-light rounded d-flex align-items-center">
                                <x-icon name="user-circle" size="32" class="text-primary me-3" />
                                <div>
                                    <div class="fw-bold">{{ $auditLog->user_name ?? ($auditLog->user->name ?? 'النظام') }}</div>
                                    <small class="text-muted">{{ $auditLog->user->email ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">الجهة / الشركة</label>
                            <div class="p-3 bg-light rounded d-flex align-items-center">
                                <x-icon name="building" size="32" class="text-info me-3" />
                                <div class="fw-bold">{{ $auditLog->entity_name ?? 'غير محدد' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">نوع العملية</label>
                            <div class="p-2 border rounded text-center">
                                <span class="badge rounded-pill bg-primary px-3 py-2 text-uppercase">{{ $auditLog->translated_action }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">القسم (Module)</label>
                            <div class="p-2 border rounded text-center">{{ $auditLog->translated_module }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small mb-1">التوقيت</label>
                            <div class="p-2 border rounded text-center">
                                <div class="p-2 border rounded">{{ $auditLog->arabic_date }}</div>
                                <small class="text-muted">{{ $auditLog->created_at->format('H:i:s') }}</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small mb-1">وصف الملحوظة / العملية</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                {{ $auditLog->description }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($auditLog->old_values || $auditLog->new_values)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0">مقارنة البيانات (تتبع التعديل)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fw-bold text-danger mb-2"><x-icon name="history" class="me-1" /> القيم السابقة</div>
                            <div class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0">@json($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-bold text-success mb-2"><x-icon name="edit" class="me-1" /> القيم الجديدة</div>
                            <div class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre class="mb-0">@json($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <x-icon name="network-wired" size="40" class="text-secondary" />
                    </div>
                    <h5 class="fw-bold px-3">تفاصيل العنوان والاتصال</h5>
                    <hr>
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="text-muted small d-block">IP Address</label>
                            <span class="badge bg-secondary font-monospace">{{ $auditLog->ip_address }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">طريقة الطلب</label>
                            <span class="badge bg-info text-uppercase">{{ $auditLog->method }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small d-block">رابط الصفحة</label>
                            <small class="text-break font-monospace text-primary">{{ $auditLog->url }}</small>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small d-block">بيانات المتصفح (User Agent)</label>
                            <div class="p-2 bg-light rounded small text-muted">
                                {{ $auditLog->user_agent }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4 bg-primary text-white rounded">
                    <h5 class="fw-bold mb-3"><x-icon name="cog" class="me-2" />معلومات تقنية</h5>
                    <div class="small opacity-75 mb-2">معرف السجل: #{{ $auditLog->id }}</div>
                    @if($auditLog->model_type)
                        <div class="small opacity-75 mb-2">نوع النموذج: {{ $auditLog->model_type }}</div>
                        <div class="small opacity-75 mb-0">معرف النموذج: {{ $auditLog->model_id }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
