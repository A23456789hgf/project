<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تقرير المالية العامة (خارج المشاريع) - {{ $company ?? 'الجهة' }}</title>
    
    <link rel="stylesheet" href="{{ asset('css/libs/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/libs/font-awesome.all.min.css') }}">

    <style>
        @page {
            size: A4 landscape;
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
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 800;
            color: #0d6efd;
            margin-bottom: 4px;
        }

        .report-meta {
            font-size: 11px;
            color: #4a5568;
        }

        .summary-box {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 12px;
            background-color: #f8fafc;
            text-align: center;
        }

        .summary-box.income { border-top: 3px solid #0d6efd; }
        .summary-box.expense { border-top: 3px solid #f59e0b; }
        .summary-box.profit { border-top: 3px solid #10b981; }

        .summary-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 2px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
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
        <button onclick="window.print();" class="btn btn-primary btn-sm fw-bold">
            <i class="fas fa-print me-1"></i> طباعة التقرير
        </button>
        <button onclick="window.close();" class="btn btn-secondary btn-sm me-2">
            <i class="fas fa-times me-1"></i> إغلاق
        </button>
    </div>

    <!-- ترويسة التقرير -->
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <div class="report-title">تقرير المالية العامة للجهة (خارج نطاق المشاريع)</div>
            <div class="report-meta">
                <span>الجهة: <strong>{{ $company ?? 'الكل' }}</strong></span> | 
                <span>تاريخ الفلترة: <strong>{{ $filters['from_date'] ?? '-' }} إلى {{ $filters['to_date'] ?? '-' }}</strong></span> | 
                <span>تاريخ الطباعة: <strong>{{ date('Y-m-d H:i') }}</strong></span>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">المالية العامة فقط</span>
        </div>
    </div>

    <!-- ملخص الأرقام العامة -->
    <div class="row g-3 mb-4">
        @foreach($summaryData as $sum)
            <div class="col-4">
                <div class="summary-box {{ $sum['indicator'] == 'blue' ? 'income' : ($sum['indicator'] == 'orange' ? 'expense' : 'profit') }}">
                    <div class="summary-label">{{ $sum['label'] }}</div>
                    <div class="summary-value">{{ number_format($sum['value'], 2) }} {{ $sum['currency'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- جدول قيود المالية العامة -->
    <h6 class="fw-bold text-dark mb-2">جدول القيود المالية العامة (بدون حركات المشاريع)</h6>
    @if(isset($reportData['gl_result']) && count($reportData['gl_result']) > 0)
        <table class="table-custom">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>التاريخ</th>
                    <th>رقم السند</th>
                    <th>الحساب</th>
                    <th>بند النفقة</th>
                    <th class="text-end">مدين</th>
                    <th class="text-end">دائن</th>
                    <th>مركز التكلفة</th>
                    <th>البيان</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['gl_result'] as $idx => $gl)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $gl['posting_date'] ?? '' }}</td>
                        <td>{{ $gl['voucher_no'] ?? '' }}</td>
                        <td>{{ $gl['account'] ?? '' }}</td>
                        <td>{{ $gl['claim_expense_type'] ?? '' }}</td>
                        <td class="text-end text-danger font-monospace">{{ number_format($gl['debit'] ?? 0, 2) }}</td>
                        <td class="text-end text-success font-monospace">{{ number_format($gl['credit'] ?? 0, 2) }}</td>
                        <td>{{ $gl['cost_center'] ?? '' }}</td>
                        <td>{{ $gl['remarks'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-light text-center border">لا توجد قيود مالية عامة مسجلة للفترة المحددة.</div>
    @endif

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
