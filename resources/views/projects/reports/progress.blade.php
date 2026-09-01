@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/modern-reports.css') }}" rel="stylesheet">
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
                <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">الموضوع: تقرير إنجاز المشاريع الفني</div>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="color: #2c5f2d; font-weight: 800; margin: 0; font-family: 'Tajawal';">تقرير المتابعة الفنية والإنجاز الموحد</h2>
    </div>
</div>

<div class="report-container">
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 text-report-primary"><x-icon name="tasks" class="me-2" />تقرير التقدم</h2>
                    <p class="text-muted">متابعة تقدم المشاريع ونسب الإنجاز بالهوية الرسمية</p>
                </div>
                <div>
                    <a href="{{ route('projects.reports.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <x-icon name="arrow-right" class="me-2" />العودة للتقارير
                    </a>
                    @can('reports.print')
                    <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-primary rounded-pill px-4 ms-2 bg-report-primary border-0">
                        <x-icon name="print" class="me-2" />طباعة رسمية
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-4 border-report-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">إجمالي المشاريع</h6>
                    <h3 class="mb-0 font-weight-bold">{{ $stats['total_projects'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">في المسار الصحيح</h6>
                    <h3 class="mb-0 text-success font-weight-bold">{{ $stats['on_track'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">في خطر</h6>
                    <h3 class="mb-0 text-warning font-weight-bold">{{ $stats['at_risk'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">متأخرة</h6>
                    <h3 class="mb-0 text-danger font-weight-bold">{{ $stats['delayed'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-3 font-weight-bold">متوسط نسبة الإنجاز الإجمالية للفترة الحالية</h6>
                    <div class="progress" style="height: 45px; border-radius: 12px; background-color: #e2e8f0; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);">
                        <div class="progress-bar bg-report-primary" style="width: {{ $stats['avg_progress'] }}%; transition: width 1s ease;">
                            <strong class="fs-4">{{ number_format($stats['avg_progress'], 1) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    @component('projects.reports.partials._report_filters', ['actionUrl' => route('projects.reports.progress')])
        <div class="col-6 col-md-3">
            <label class="form-label text-muted small fw-bold mb-1">المشروع المحدد</label>
            <select name="project_id" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">-- كل المشاريع --</option>
                @foreach($allProjects as $project)
                    <option value="{{ $project->id }}" {{ ($projectId ?? request('project_id')) == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endcomponent

    <!-- Projects Progress Details -->
    <div class="modern-table-card shadow-sm">
        <div class="modern-table-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-report-primary font-weight-bold"><x-icon name="list" class="me-2" />تفاصيل تقدم المشاريع</h5>
            <span class="badge bg-light text-dark rounded-pill px-3">{{ count($projectsProgress) }} مشروع</span>
        </div>
        <div class="table-responsive">
            @if(count($projectsProgress) > 0)
            <table class="table modern-table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="padding: 15px 20px;">المشروع</th>
                        <th>تقدم تحضيري</th>
                        <th>تقدم تنفيذي</th>
                        <th>التقدم الإجمالي</th>
                        <th>التقدم الزمني</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectsProgress as $progress)
                    <tr>
                        <td style="padding: 15px 20px;">
                            <div class="fw-bold text-dark">{{ $progress['project']->project_name }}</div>
                            <small class="text-muted">#{{ $progress['project']->form_number }}</small>
                        </td>
                        <td>
                            <div class="progress" style="height: 22px; width: 110px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-report-primary" style="width: {{ $progress['preliminary_progress'] }}%">
                                    <small>{{ number_format($progress['preliminary_progress'], 0) }}%</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="progress" style="height: 22px; width: 110px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-report-secondary" style="width: {{ $progress['executive_progress'] }}%">
                                    <small>{{ number_format($progress['executive_progress'], 0) }}%</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="progress" style="height: 22px; width: 110px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar {{ $progress['overall_progress'] >= 75 ? 'bg-success' : ($progress['overall_progress'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                     style="width: {{ $progress['overall_progress'] }}%">
                                    <small>{{ number_format($progress['overall_progress'], 0) }}%</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="progress" style="height: 22px; width: 110px; border-radius: 6px; background-color: #f1f5f9;">
                                <div class="progress-bar bg-info" style="width: {{ min($progress['time_progress'], 100) }}%">
                                    <small>{{ number_format($progress['time_progress'], 0) }}%</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1">{{ $progress['days_remaining'] }} يوم</span>
                        </td>
                        <td>
                            @if($progress['status'] === 'on-track')
                                <span class="status-badge success">في المسار</span>
                            @elseif($progress['status'] === 'at-risk')
                                <span class="status-badge warning">في خطر</span>
                            @else
                                <span class="status-badge danger">متأخر</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-5">
                <img src="{{ asset('images/no-data.svg') }}" onerror="this.src='https://via.placeholder.com/150?text=No+Data'" alt="No Data" style="width: 150px; opacity: 0.5">
                <p class="text-muted mt-3">لا توجد بيانات تقدم للعرض</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

