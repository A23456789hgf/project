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
                <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">الموضوع: تقرير مراقبة جودة السجلات</div>
            </td>
        </tr>
    </table>
    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="color: #2c5f2d; font-weight: 800; margin: 0; font-family: 'Tajawal';">تقرير ضبط جودة المشاريع الموحد</h2>
    </div>
</div>

<div class="report-container">
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 text-report-primary"><x-icon name="chart-line" class="me-2" />تقرير الجودة</h2>
                    <p class="text-muted">تحليل شامل لمؤشرات الجودة والانحرافات بالهوية الرسمية</p>
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-4 border-report-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">إجمالي السجلات</h6>
                    <h3 class="mb-0 font-weight-bold">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">سجلات إيجابية</h6>
                    <h3 class="mb-0 text-success font-weight-bold">{{ $stats['positive'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">سجلات سلبية</h6>
                    <h3 class="mb-0 text-danger font-weight-bold">{{ $stats['negative'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-4 border-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">مع حلول مقترحة</h6>
                    <h3 class="mb-0 text-warning font-weight-bold">{{ $stats['with_solutions'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Aspects Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center py-4">
                    <x-icon name="clock" class="text-report-primary mb-3 opacity-75" size="48" />
                    <h6 class="text-muted font-weight-bold">قضايا زمنية</h6>
                    <h2 class="font-weight-bold text-dark">{{ $stats['by_aspect']['time'] }}</h2>
                    <div class="badge bg-white text-dark shadow-sm rounded-pill mt-2 px-3">متوسط الانحراف: {{ number_format($stats['avg_time_variance'] ?? 0, 1) }} يوم</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center py-4">
                    <x-icon name="dollar-sign" class="text-report-secondary mb-3 opacity-75" size="48" />
                    <h6 class="text-muted font-weight-bold">قضايا مالية</h6>
                    <h2 class="font-weight-bold text-dark">{{ $stats['by_aspect']['financial'] }}</h2>
                    <div class="badge bg-white text-dark shadow-sm rounded-pill mt-2 px-3">متوسط الانحراف: {{ number_format($stats['avg_financial_variance'] ?? 0, 0) }} ﷼</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center py-4">
                    <x-icon name="cogs" class="text-warning mb-3 opacity-75" size="48" />
                    <h6 class="text-muted font-weight-bold">قضايا فنية</h6>
                    <h2 class="font-weight-bold text-dark">{{ $stats['by_aspect']['technical'] }}</h2>
                    <div class="badge bg-white text-dark shadow-sm rounded-pill mt-2 px-3">مؤشرات الأداء الفني</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    @component('projects.reports.partials._report_filters', ['actionUrl' => route('projects.reports.quality')])
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">المشروع</label>
            <select name="project_id" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">-- كل المشاريع --</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ ($projectId ?? request('project_id')) == $project->id ? 'selected' : '' }}>
                        {{ $project->project_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">حالة الجودة</label>
            <select name="quality_status" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="all" {{ ($qualityStatus ?? request('quality_status')) === 'all' ? 'selected' : '' }}>الكل</option>
                <option value="positive" {{ ($qualityStatus ?? request('quality_status')) === 'positive' ? 'selected' : '' }}>إيجابي فقط</option>
                <option value="negative" {{ ($qualityStatus ?? request('quality_status')) === 'negative' ? 'selected' : '' }}>سلبي فقط</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label text-muted small fw-bold mb-1">جانب الجودة</label>
            <select name="aspect" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="all" {{ ($aspect ?? request('aspect')) === 'all' ? 'selected' : '' }}>كل الجوانب</option>
                <option value="time" {{ ($aspect ?? request('aspect')) === 'time' ? 'selected' : '' }}>زمني</option>
                <option value="financial" {{ ($aspect ?? request('aspect')) === 'financial' ? 'selected' : '' }}>مالي</option>
                <option value="technical" {{ ($aspect ?? request('aspect')) === 'technical' ? 'selected' : '' }}>فني</option>
            </select>
        </div>
    @endcomponent

    <!-- Quality Records Table -->
    <div class="modern-table-card shadow-sm border-0">
        <div class="modern-table-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-report-primary font-weight-bold"><x-icon name="list" class="me-2" />سجلات تفاصيل الجودة</h5>
            <span class="badge bg-light text-dark rounded-pill px-3">{{ $qualityRecords->count() }} سجل متوافق مع الفلتر</span>
        </div>
        <div class="table-responsive">
            @if($qualityRecords->count() > 0)
            <table class="table modern-table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="padding: 15px 20px;">المشروع</th>
                        <th>النشاط المستهدف</th>
                        <th>جانب الجودة</th>
                        <th>الحالة</th>
                        <th>قيمة الانحراف</th>
                        <th>الحل المقترح</th>
                        <th>تاريخ السجل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qualityRecords as $record)
                    <tr>
                        <td style="padding: 15px 20px;">
                            <div class="fw-bold text-dark">{{ $record->project->project_name }}</div>
                            <small class="text-muted">#{{ $record->project->form_number }}</small>
                        </td>
                        <td>
                            <div class="small fw-500">
                                @if($record->record_type === 'preliminary')
                                    {{ $record->preliminaryActivity->activity_name ?? 'N/A' }}
                                @else
                                    {{ $record->executiveActivity->activity_name ?? 'N/A' }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($record->quality_aspect === 'time')
                                <span class="badge bg-report-primary opacity-75 rounded-pill px-3">زمني</span>
                            @elseif($record->quality_aspect === 'financial')
                                <span class="badge bg-report-secondary opacity-75 rounded-pill px-3">مالي</span>
                            @else
                                <span class="badge bg-warning text-dark opacity-75 rounded-pill px-3">فني</span>
                            @endif
                        </td>
                        <td>
                            @if($record->quality_status === 'positive')
                                <span class="status-badge success">إيجابي</span>
                            @else
                                <span class="status-badge danger">سلبي</span>
                            @endif
                        </td>
                        <td class="font-weight-bold">
                            @if($record->quality_aspect === 'time')
                                {{ abs($record->variance_days ?? 0) }} يوم
                            @elseif($record->quality_aspect === 'financial')
                                {{ number_format(abs($record->variance_amount ?? 0), 0) }} ﷼
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($record->proposed_solution)
                                <span class="badge bg-soft-info text-info border border-info rounded-pill px-3" style="background-color: rgba(13, 202, 240, 0.1);">متوفر</span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill px-3">غير متوفر</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $record->created_at->format('Y-m-d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-5">
                <img src="{{ asset('images/no-data.svg') }}" onerror="this.src='https://via.placeholder.com/150?text=No+Data'" alt="No Data" style="width: 150px; opacity: 0.5">
                <p class="text-muted mt-3">لا توجد سجلات جودة متوفرة لهذا الفلتر</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

