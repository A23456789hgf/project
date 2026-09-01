@extends('layouts.print')

@section('report_subject', 'تقرير سجلات التنفيذ الميداني')
@section('report_title', 'تقرير تنفيذ الأنشطة الميداني الموحد')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الأنشطة التحضيرية</h6>
            <h4 class="fw-bold text-primary-print mb-0">{{ $stats['preliminary_count'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الأنشطة التنفيذية</h6>
            <h4 class="fw-bold text-success mb-0">{{ $stats['executive_count'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">قيد المراجعة</h6>
            <h4 class="fw-bold text-warning mb-0">{{ $stats['pending_approval'] }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي السجلات</h6>
            <h4 class="fw-bold text-info mb-0">{{ $stats['total_records'] }}</h4>
        </div>
    </div>
</div>

<div>
    <h5 class="fw-bold mb-3 text-primary-print">سجلات التنفيذ التفصيلية</h5>
    @if(count($implementationData) > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>المشروع</th>
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
                <td class="text-start fw-bold">
                    {{ $data['project_name'] }}
                    <div class="text-muted small" style="font-weight: normal;">#{{ $data['project_code'] }}</div>
                </td>
                <td>{{ $data['type'] === 'preliminary' ? 'تحضيري' : 'تنفيذي' }}</td>
                <td class="small">{{ $data['activity_name'] }}</td>
                <td class="small" dir="ltr">{{ $data['execution_date'] }}</td>
                <td class="fw-bold text-primary-print">{{ number_format($data['amount_spent'], 0) }} ﷼</td>
                <td dir="ltr">{{ number_format($data['completion_percentage'], 1) }}%</td>
                <td>
                    @if($data['approval_status'] === 'approved')
                        <span class="text-success fw-bold">معتمد</span>
                    @elseif($data['approval_status'] === 'pending')
                        <span class="text-warning fw-bold">مراجعة</span>
                    @else
                        <span class="text-danger fw-bold">مرفوض</span>
                    @endif
                </td>
                <td class="small">{{ $data['approved_by'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-center text-muted">لا توجد سجلات تنفيذ متوفرة</p>
    @endif
</div>
@endsection
