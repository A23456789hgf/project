@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/modern-reports.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    .text-report-primary { color: var(--report-primary) !important; }
    .bg-report-primary { background-color: var(--report-primary) !important; }
    .bg-report-secondary { background-color: var(--report-secondary) !important; }
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
                <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">الموضوع: التقرير المالي التفصيلي</div>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="color: #2c5f2d; font-weight: 800; margin: 0; font-family: 'Tajawal';">التقرير المالي الموحد للمشاريع</h2>
    </div>
</div>

<div class="report-container">
    <div class="report-header d-flex justify-content-between align-items-center no-print">
        <div class="report-header-content">
            <h2 class="report-title text-report-primary"><x-icon name="chart-pie" class="me-2" />التقرير المالي</h2>
            <p class="text-muted mb-0">لوحة تحكم شاملة للميزانيات والمصروفات بالهوية الرسمية</p>
        </div>
        <div>
            <a href="{{ route('projects.reports.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                <x-icon name="arrow-left" class="me-2" />العودة للتقارير
            </a>
            @can('reports.print')
            <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-primary rounded-pill px-4 ms-2 bg-report-primary border-0">
                <x-icon name="print" class="me-2" />طباعة رسمية
            </a>
            @endcan
        </div>
    </div>

    {{-- الفلاتر بحسب النطاق الجغرافي والإداري --}}
    @include('projects.reports.partials._report_filters', ['actionUrl' => route('projects.reports.financial')])

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">إجمالي الميزانية</div>
                    <div class="kpi-value">{{ number_format($stats['total_budget'], 0) }} ﷼</div>
                </div>
                <x-icon name="wallet" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">إجمالي المصروف</div>
                    <div class="kpi-value">{{ number_format($stats['total_spent'], 0) }} ﷼</div>
                </div>
                <x-icon name="shopping-cart" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card warning-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">المبلغ المتبقي</div>
                    <div class="kpi-value">{{ number_format($stats['remaining'], 0) }} ﷼</div>
                </div>
                <x-icon name="piggy-bank" class="kpi-icon" size="32" />
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card info-gradient">
                <div class="kpi-content">
                    <div class="kpi-label">نسبة الاستخدام</div>
                    <div class="kpi-value">{{ number_format($stats['utilization_percentage'], 1) }}%</div>
                </div>
                <x-icon name="chart-line" class="kpi-icon" size="32" />
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row mb-4">
        <!-- Breakdown Chart/Bars -->
        <div class="col-lg-7">
            <div class="chart-card h-100">
                <div class="chart-header">
                    <h6 class="m-0 font-weight-bold text-report-primary">توزيع المصروفات حسب النوع</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small font-weight-bold text-secondary">الأنشطة التحضيرية</span>
                            <span class="small font-weight-bold text-dark">{{ number_format($stats['preliminary_spent'], 0) }} ﷼</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 15px;">
                            <div class="progress-bar bg-report-primary" role="progressbar" 
                                style="width: {{ $stats['total_spent'] > 0 ? ($stats['preliminary_spent'] / $stats['total_spent']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small font-weight-bold text-secondary">الأنشطة التنفيذية</span>
                            <span class="small font-weight-bold text-dark">{{ number_format($stats['executive_spent'], 0) }} ﷼</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 15px;">
                            <div class="progress-bar bg-report-secondary" role="progressbar" 
                                style="width: {{ $stats['total_spent'] > 0 ? ($stats['executive_spent'] / $stats['total_spent']) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="col-lg-5 no-print">
            <div class="chart-card h-100">
                 <div class="chart-header">
                    <h6 class="m-0 font-weight-bold text-report-primary">تصفية البيانات</h6>
                </div>
                <div class="card-body d-flex align-items-center">
                    <form method="GET" action="{{ route('projects.reports.financial') }}" class="w-100">
                        <label class="form-label text-muted small">اختر المشروع للعرض</label>
                        <select name="project_id" class="form-select form-select-lg rounded-pill border-0 bg-light shadow-sm" style="font-family: 'Tajawal';" onchange="this.form.submit()">
                            <option value="">-- جميع المشاريع --</option>
                            @foreach($allProjects as $project)
                                <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <div class="mt-4 text-center">
                            <div class="row">
                                <div class="col-6">
                                     <div class="p-3 bg-light rounded-3">
                                         <h3 class="mb-0 text-dark font-weight-bold">{{ $stats['projects_count'] }}</h3>
                                         <small class="text-muted">مشروع معروض</small>
                                     </div>
                                </div>
                                <div class="col-6">
                                     <div class="p-3 bg-light rounded-3">
                                         <h3 class="mb-0 text-danger font-weight-bold">{{ $stats['overspent_count'] }}</h3>
                                         <small class="text-danger">تجاوز الميزانية</small>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="modern-table-card">
        <div class="modern-table-header">
            <h5 class="m-0 font-weight-bold text-report-primary">تفاصيل المشاريع المالية</h5>
        </div>
        <div class="table-responsive">
             @if(count($projectsData) > 0)
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>المشروع</th>
                        <th>الميزانية المعتمدة</th>
                        <th>مصروف تحضيري</th>
                        <th>مصروف تنفيذي</th>
                        <th>الإجمالي</th>
                        <th>المتبقي</th>
                        <th>الإنجاز المالي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                     @foreach($projectsData as $data)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-light rounded-circle text-report-primary me-3 d-flex align-items-center justify-content-center" style="width:35px;height:35px; border: 1px solid rgba(44, 95, 45, 0.1)">
                                    <x-icon name="project-diagram" size="20" />
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $data['project']->project_name }}</div>
                                    <small class="text-muted">#{{ $data['project']->form_number }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="font-weight-bold">{{ number_format($data['budget'], 0) }} ﷼</td>
                        <td>{{ number_format($data['preliminary_spent'], 0) }} ﷼</td>
                        <td>{{ number_format($data['executive_spent'], 0) }} ﷼</td>
                        <td class="font-weight-bold text-report-primary">{{ number_format($data['total_spent'], 0) }} ﷼</td>
                        <td>
                            <span class="{{ $data['remaining'] < 0 ? 'text-danger fw-bold' : 'text-success font-weight-bold' }}">
                                {{ number_format($data['remaining'], 0) }} ﷼
                            </span>
                        </td>
                        <td style="width: 15%">
                            <div class="d-flex align-items-center">
                                <span class="me-2 small font-weight-bold">{{ number_format($data['utilization_percentage'], 1) }}%</span>
                                <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                                    <div class="progress-bar {{ $data['utilization_percentage'] > 100 ? 'bg-danger' : ($data['utilization_percentage'] > 90 ? 'bg-warning' : 'bg-report-primary') }}" 
                                         role="progressbar" 
                                         style="width: {{ min($data['utilization_percentage'], 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                             @if($data['status'] === 'overspent')
                                <span class="status-badge danger">متجاوز</span>
                            @elseif($data['status'] === 'warning')
                                <span class="status-badge warning">تحذير</span>
                            @else
                                <span class="status-badge success">طبيعي</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
             @else
                <div class="text-center py-5">
                    <img src="{{ asset('images/no-data.svg') }}" onerror="this.src='https://via.placeholder.com/150?text=No+Data'" alt="No Data" style="width: 150px; opacity: 0.5">
                    <p class="text-muted mt-3">لا توجد بيانات مالية للعرض</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

