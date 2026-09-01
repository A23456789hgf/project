@extends('layouts.print')

@section('report_subject', 'تقرير إنجاز المشاريع الفني')
@section('report_title', 'تقرير المتابعة الفنية والإنجاز الموحد')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي المشاريع</h6>
            <h4 class="fw-bold mb-0">{{ $stats['total_projects'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">في المسار الصحيح</h6>
            <h4 class="fw-bold text-success mb-0">{{ $stats['on_track'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">في خطر</h6>
            <h4 class="fw-bold text-warning mb-0">{{ $stats['at_risk'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">متأخرة</h6>
            <h4 class="fw-bold text-danger mb-0">{{ $stats['delayed'] }}</h4>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="p-3 border rounded text-center">
        <h6 class="text-muted fw-bold mb-2">متوسط نسبة الإنجاز الإجمالية للفترة الحالية</h6>
        <h3 class="fw-bold text-primary-print mb-0" dir="ltr">{{ number_format($stats['avg_progress'], 1) }}%</h3>
    </div>
</div>

<div>
    <h5 class="fw-bold mb-3 text-primary-print">تفاصيل تقدم المشاريع</h5>
    @if(count($projectsProgress) > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>المشروع</th>
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
                <td class="text-start fw-bold">
                    {{ $progress['project']->project_name }}
                    <div class="text-muted small" style="font-weight: normal;">#{{ $progress['project']->form_number }}</div>
                </td>
                <td dir="ltr">{{ number_format($progress['preliminary_progress'], 0) }}%</td>
                <td dir="ltr">{{ number_format($progress['executive_progress'], 0) }}%</td>
                <td class="fw-bold text-primary-print" dir="ltr">{{ number_format($progress['overall_progress'], 0) }}%</td>
                <td class="text-info fw-bold" dir="ltr">{{ number_format($progress['time_progress'], 0) }}%</td>
                <td>{{ $progress['days_remaining'] }} يوم</td>
                <td>
                    @if($progress['status'] === 'on-track')
                        <span class="text-success fw-bold">في المسار</span>
                    @elseif($progress['status'] === 'at-risk')
                        <span class="text-warning fw-bold">في خطر</span>
                    @else
                        <span class="text-danger fw-bold">متأخر</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-center text-muted">لا توجد بيانات تقدم للعرض</p>
    @endif
</div>
@endsection
