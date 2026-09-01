@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/modern-reports.css') }}" rel="stylesheet">
<link href="{{ asset('css/design-system/master.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    .text-report-primary { color: var(--report-primary) !important; }
    .bg-report-primary { background-color: var(--report-primary) !important; }
    .bg-report-secondary { background-color: var(--report-secondary) !important; }
    .border-report-primary { border-color: var(--report-primary) !important; }
</style>
@endpush

@section('content')
<!-- Official Print Header -->
<div class="official-print-header">
    <table style="width: 100%; border-bottom: 2px solid #2c5f2d; padding-bottom: 10px; margin-bottom: 20px; direction: rtl;">
        <tr>
            <td width="33%" style="text-align: right; vertical-align: top;">
                <div style="font-size: 16px; font-weight: bold; color: #2c5f2d; font-family: 'Tajawal';">الجمهورية اليمنية</div>
                <div style="font-size: 14px; color: #1f2937; font-family: 'Tajawal';">وزارة الزراعة والثروة السمكية والموارد المائية</div>
            </td>
            <td width="33%" style="text-align: center; vertical-align: middle;">
                <img src="{{ asset('images/logo.png') }}" style="height: 90px;" alt="Logo">
            </td>
            <td width="33%" style="text-align: left; vertical-align: top;">
                <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">التاريخ: {{ date('Y-m-d') }}</div>
                <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">الموضوع: تقرير سجلات التنفيذ الميداني</div>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="color: #2c5f2d; font-weight: 800; margin: 0; font-family: 'Tajawal';">تقرير تنفيذ الأنشطة الميداني الموحد</h2>
    </div>
</div>

<div class="report-container">
    <div class="report-header d-flex justify-content-between align-items-center no-print">
        <div class="report-header-content">
            <h2 class="report-title text-report-primary"><x-icon name="clipboard-check" class="me-2" />تقرير التنفيذ</h2>
            <p class="text-muted mb-0">متابعة شاملة لسجلات التنفيذ التحضيرية والتنفيذية بالهوية الرسمية</p>
        </div>
        <div class="d-flex gap-2">
            <x-ui.button href="{{ route('projects.reports.index') }}" variant="outline-primary" size="sm" icon="fas fa-arrow-right">العودة للتقارير</x-ui.button>
            @can('reports.print')
                <x-ui.button href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" variant="primary" size="sm" icon="fas fa-print">طباعة رسمية</x-ui.button>
            @endcan
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">الأنشطة التحضيرية</div>
                    <div class="kpi-value">{{ $stats['preliminary_count'] }}</div>
                </div>
                <x-icon name="clipboard-list" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">الأنشطة التنفيذية</div>
                    <div class="kpi-value">{{ $stats['executive_count'] }}</div>
                </div>
                <x-icon name="tasks" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card warning-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">قيد المراجعة</div>
                    <div class="kpi-value">{{ $stats['pending_approval'] }}</div>
                </div>
                <x-icon name="hourglass-half" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card info-gradient border-report-primary">
                <div class="kpi-content">
                    <div class="kpi-label">إجمالي السجلات</div>
                    <div class="kpi-value">{{ $stats['total_records'] }}</div>
                </div>
                <x-icon name="database" class="kpi-icon" size="32" />
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    @component('projects.reports.partials._report_filters', ['actionUrl' => route('projects.reports.implementation')])
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">المشروع</label>
            <select name="project_id" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">-- كل المشاريع --</option>
                @foreach($allProjects as $project)
                    <option value="{{ $project->id }}" {{ ($projectId ?? request('project_id')) == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">نوع التنفيذ</label>
            <select name="type" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">-- كل الأنواع --</option>
                <option value="preliminary" {{ request('type') == 'preliminary' ? 'selected' : '' }}>تحضيري</option>
                <option value="executive" {{ request('type') == 'executive' ? 'selected' : '' }}>تنفيذي</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">حالة الموافقة</label>
            <select name="status" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">-- كل الحالات --</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد رسمياً</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
            </select>
        </div>
    @endcomponent

    <!-- Implementation Records Table -->
    <div class="modern-table-card shadow-sm border-0">
        <div class="modern-table-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-report-primary">سجلات التنفيذ التفصيلية</h5>
            <span class="badge bg-light text-dark rounded-pill px-3">{{ count($implementationData) }} سجل</span>
        </div>
        <div class="table-responsive">
            @if(count($implementationData) > 0)
            <table class="table modern-table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="padding: 15px 20px;">المشروع</th>
                        <th>النوع</th>
                        <th>النشاط</th>
                        <th>تاريخ التنفيذ</th>
                        <th>المبلغ المنفذ</th>
                        <th>نسبة الإنجاز</th>
                        <th>حالة الموافقة</th>
                        <th>المعتمد من</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($implementationData as $data)
                    <tr>
                        <td style="padding: 15px 20px;">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle text-report-primary me-3 d-flex align-items-center justify-content-center" 
                                     style="width:35px;height:35px; border: 1px solid rgba(44, 95, 45, 0.1)">
                                    <x-icon name="project-diagram" size="20" />
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $data['project_name'] }}</div>
                                    <small class="text-muted">#{{ $data['project_code'] }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($data['type'] === 'preliminary')
                                <span class="badge" style="background-color: rgba(44, 95, 45, 0.1); color: var(--report-primary); padding: 0.45rem 0.9rem; border-radius: 20px;">
                                    <x-icon name="clipboard-list" class="me-1" size="12" />تحضيري
                                </span>
                            @else
                                <span class="badge" style="background-color: rgba(151, 188, 98, 0.1); color: #709440; padding: 0.45rem 0.9rem; border-radius: 20px;">
                                    <x-icon name="cogs" class="me-1" size="12" />تنفيذي
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small fw-500">{{ $data['activity_name'] }}</div>
                        </td>
                        <td class="small">{{ $data['execution_date'] }}</td>
                        <td class="font-weight-bold text-report-primary">{{ number_format($data['amount_spent'], 0) }} ﷼</td>
                        <td style="width: 15%">
                            <div class="d-flex align-items-center">
                                <span class="me-2 small font-weight-bold">{{ number_format($data['completion_percentage'], 1) }}%</span>
                                <div class="progress flex-grow-1 rounded-pill" style="height: 6px; background-color: #f1f5f9;">
                                    <div class="progress-bar {{ $data['completion_percentage'] >= 100 ? 'bg-success' : ($data['completion_percentage'] >= 50 ? 'bg-report-secondary' : 'bg-warning') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($data['completion_percentage'], 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($data['approval_status'] === 'approved')
                                <span class="status-badge success"><x-icon name="check-circle" class="me-1" size="12" />معتمد</span>
                            @elseif($data['approval_status'] === 'pending')
                                <span class="status-badge warning"><x-icon name="clock" class="me-1" size="12" />مراجعة</span>
                            @else
                                <span class="status-badge danger"><x-icon name="times-circle" class="me-1" size="12" />مرفوض</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $data['approved_by'] ?? 'لم يعتمد بعد' }}</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-5">
                <img src="{{ asset('images/no-data.svg') }}" onerror="this.src='https://via.placeholder.com/150?text=No+Data'" alt="No Data" style="width: 150px; opacity: 0.5">
                <p class="text-muted mt-3">لا توجد سجلات تنفيذ متوفرة لهذا البحث</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

