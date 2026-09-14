<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تقرير المصروفات حسب البنود المالية - {{ $company ?? 'الجهة' }}</title>
    
    <link rel="stylesheet" href="{{ asset('css/libs/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/libs/font-awesome.all.min.css') }}">

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 15mm 10mm;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #1a202c;
            font-size: 11px;
            line-height: 1.4;
            padding: 15px;
            margin: 0;
        }

        .report-header {
            border-bottom: 2px solid #f59e0b;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #d97706;
            margin-bottom: 4px;
        }

        .report-meta {
            font-size: 11px;
            color: #4a5568;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5px;
        }

        .table-custom th, .table-custom td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <!-- أزرار الإجراءات للطباعة -->
    <div class="no-print mb-3 text-start">
        <button onclick="window.print();" class="btn btn-warning text-dark btn-sm fw-bold">
            <i class="fas fa-print me-1"></i> طباعة التقرير
        </button>
        <button onclick="window.close();" class="btn btn-secondary btn-sm me-2">
            <i class="fas fa-times me-1"></i> إغلاق
        </button>
    </div>

    <!-- ترويسة التقرير -->
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <div class="report-title">تقرير تجميع المصروفات حسب البنود المالية</div>
            <div class="report-meta">
                <span>الجهة: <strong>{{ $company ?? 'الكل' }}</strong></span> | 
                <span>تاريخ الفلترة: <strong>{{ $filters['from_date'] ?? '-' }} إلى {{ $filters['to_date'] ?? '-' }}</strong></span> | 
                <span>تاريخ الطباعة: <strong>{{ date('Y-m-d H:i') }}</strong></span>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-warning text-dark fs-6">إجمالي النفقات: {{ number_format($totalExpenses, 2) }} ر.ي</span>
        </div>
    </div>

    <!-- جدول البنود المالية المرتبطة -->
    <h6 class="fw-bold text-dark mb-2">البنود المالية المطابقة والنفقات التابعة لها</h6>
    <table class="table-custom">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>بند النفقة المطالب بها (Financial Item)</th>
                <th class="text-center">عدد الحركات</th>
                <th class="text-end">إجمالي المصروف الفعلي</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @forelse($itemsBreakdown as $itemName => $data)
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td class="fw-bold text-dark">{{ $itemName }}</td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-end fw-bold font-monospace text-amber-700">{{ number_format($data['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">لا توجد بنود مالية مسجلة</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="table-secondary fw-bold">
                <td colspan="3" class="text-end">المجموع الكلي:</td>
                <td class="text-end font-monospace text-danger">{{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- التوقيعات -->
    <div class="signature-section">
        <div class="row text-center mt-4">
            <div class="col-4">
                <p class="fw-bold mb-4">المحاسب المختص</p>
                <p>__________________</p>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-4">المراجع المالي</p>
                <p>__________________</p>
            </div>
            <div class="col-4">
                <p class="fw-bold mb-4">مدير الإدارة المالية</p>
                <p>__________________</p>
            </div>
        </div>
    </div>

</body>
</html>
