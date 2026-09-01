@extends('layouts.print')

@section('report_subject', 'التقرير المالي التفصيلي')
@section('report_title', 'التقرير المالي الموحد للمشاريع')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي الميزانية</h6>
            <h4 class="fw-bold text-primary-print mb-0">{{ number_format($stats['total_budget'], 0) }} ﷼</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي المصروف</h6>
            <h4 class="fw-bold text-success mb-0">{{ number_format($stats['total_spent'], 0) }} ﷼</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">المبلغ المتبقي</h6>
            <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['remaining'], 0) }} ﷼</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">نسبة الاستخدام</h6>
            <h4 class="fw-bold text-info mb-0">{{ number_format($stats['utilization_percentage'], 1) }}%</h4>
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3 text-primary-print">تفاصيل المصروفات حسب النوع</h5>
    <div class="row">
        <div class="col-6">
            <div class="p-3 border rounded">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">الأنشطة التحضيرية</span>
                    <span class="fw-bold">{{ number_format($stats['preliminary_spent'], 0) }} ﷼</span>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 border rounded">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-bold">الأنشطة التنفيذية</span>
                    <span class="fw-bold">{{ number_format($stats['executive_spent'], 0) }} ﷼</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <h5 class="fw-bold mb-3 text-primary-print">تفاصيل المشاريع</h5>
    @if(count($projectsData) > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>المشروع</th>
                <th>الميزانية</th>
                <th>مصروف تحضيري</th>
                <th>مصروف تنفيذي</th>
                <th>الإجمالي</th>
                <th>المتبقي</th>
                <th>نسبة الاستخدام</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projectsData as $data)
            <tr>
                <td class="text-start fw-bold">
                    {{ $data['project']->project_name }}
                    <div class="text-muted small" style="font-weight: normal;">#{{ $data['project']->form_number }}</div>
                </td>
                <td class="fw-bold">{{ number_format($data['budget'], 0) }}</td>
                <td>{{ number_format($data['preliminary_spent'], 0) }}</td>
                <td>{{ number_format($data['executive_spent'], 0) }}</td>
                <td class="fw-bold text-primary-print">{{ number_format($data['total_spent'], 0) }}</td>
                <td class="{{ $data['remaining'] < 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">{{ number_format($data['remaining'], 0) }}</td>
                <td dir="ltr">{{ number_format($data['utilization_percentage'], 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-center text-muted">لا توجد بيانات مالية للعرض</p>
    @endif
</div>
@endsection
