@extends('layouts.print')

@section('report_subject', 'تقرير مراقبة جودة السجلات')
@section('report_title', 'تقرير ضبط جودة المشاريع الموحد')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي السجلات</h6>
            <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">سجلات إيجابية</h6>
            <h4 class="fw-bold text-success mb-0">{{ $stats['positive'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">سجلات سلبية</h6>
            <h4 class="fw-bold text-danger mb-0">{{ $stats['negative'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">مع حلول مقترحة</h6>
            <h4 class="fw-bold text-warning mb-0">{{ $stats['with_solutions'] }}</h4>
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3 text-primary-print">تفاصيل قضايا الجودة</h5>
    <div class="row">
        <div class="col-4">
            <div class="p-3 border rounded text-center">
                <h6 class="fw-bold">قضايا زمنية</h6>
                <h4>{{ $stats['by_aspect']['time'] }}</h4>
                <small>متوسط الانحراف: {{ number_format($stats['avg_time_variance'] ?? 0, 1) }} يوم</small>
            </div>
        </div>
        <div class="col-4">
            <div class="p-3 border rounded text-center">
                <h6 class="fw-bold">قضايا مالية</h6>
                <h4>{{ $stats['by_aspect']['financial'] }}</h4>
                <small>متوسط الانحراف: {{ number_format($stats['avg_financial_variance'] ?? 0, 0) }} ﷼</small>
            </div>
        </div>
        <div class="col-4">
            <div class="p-3 border rounded text-center">
                <h6 class="fw-bold">قضايا فنية</h6>
                <h4>{{ $stats['by_aspect']['technical'] }}</h4>
                <small>مؤشرات الأداء الفني</small>
            </div>
        </div>
    </div>
</div>

<div>
    <h5 class="fw-bold mb-3 text-primary-print">سجلات تفاصيل الجودة</h5>
    @if($qualityRecords->count() > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>المشروع</th>
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
                <td class="text-start fw-bold">
                    {{ $record->project->project_name }}
                    <div class="text-muted small" style="font-weight: normal;">#{{ $record->project->form_number }}</div>
                </td>
                <td class="small">
                    @if($record->record_type === 'preliminary')
                        {{ $record->preliminaryActivity->activity_name ?? 'N/A' }}
                    @else
                        {{ $record->executiveActivity->activity_name ?? 'N/A' }}
                    @endif
                </td>
                <td>
                    @if($record->quality_aspect === 'time')
                        زمني
                    @elseif($record->quality_aspect === 'financial')
                        مالي
                    @else
                        فني
                    @endif
                </td>
                <td>
                    @if($record->quality_status === 'positive')
                        <span class="text-success fw-bold">إيجابي</span>
                    @else
                        <span class="text-danger fw-bold">سلبي</span>
                    @endif
                </td>
                <td class="fw-bold">
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
                        <span class="text-info fw-bold">متوفر</span>
                    @else
                        <span class="text-muted">غير متوفر</span>
                    @endif
                </td>
                <td class="small" dir="ltr">{{ $record->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-center text-muted">لا توجد سجلات جودة متوفرة</p>
    @endif
</div>
@endsection
