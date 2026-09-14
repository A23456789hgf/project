<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تقرير الأرباح والخسائر - {{ $company ?? ($filters['company'] ?? 'الجهة') }}</title>
    
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
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            background-color: #f8fafc;
            text-align: center;
        }

        .summary-box.income { border-top: 3px solid #0d6efd; }
        .summary-box.expense { border-top: 3px solid #f59e0b; }
        .summary-box.profit { border-top: 3px solid #10b981; }
        .summary-box.loss { border-top: 3px solid #ef4444; }

        .summary-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 15px;
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
            padding: 5px 8px;
            vertical-align: middle;
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-align: center;
        }

        .table-custom tr.row-total {
            background-color: #e2e8f0 !important;
            font-weight: 800;
        }

        .table-custom tr.row-parent {
            background-color: #f8fafc;
            font-weight: 700;
        }

        .actions-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .table-custom { page-break-inside: auto; }
            .table-custom tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

    <div class="actions-bar no-print">
        <a href="javascript:window.close()" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-right me-1"></i> إغلاق النافذة
        </a>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="fas fa-print me-1"></i> طباعة التقرير
            </button>
        </div>
    </div>

    <!-- ترويسة التقرير الرسمية -->
    <div class="report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="report-title">تقرير الأرباح والخسائر (Profit and Loss Statement)</div>
                <div class="report-meta">
                    <strong>الجهة:</strong> {{ $company ?? ($filters['company'] ?? 'كافة الجهات') }}
                    @if(request('filter_based_on') === 'Date Range' && request('from_date') && request('to_date'))
                        | <strong>الفترة:</strong> من {{ request('from_date') }} إلى {{ request('to_date') }}
                    @elseif(request('from_fiscal_year'))
                        | <strong>السنة المالية:</strong> {{ request('from_fiscal_year') }}
                    @endif
                    @if(request('cost_center'))
                        | <strong>مركز التكلفة:</strong> {{ request('cost_center') }}
                    @endif
                    @if(request('project'))
                        | <strong>المشروع:</strong> {{ request('project') }}
                    @endif
                </div>
            </div>
            <div class="text-start report-meta">
                <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y-m-d H:i') }}</div>
                <div><strong>المستخدم:</strong> {{ auth()->user()->name ?? '' }}</div>
            </div>
        </div>
    </div>

    <!-- كروت ملخص التقرير -->
    @if(isset($summaryData) && count($summaryData) > 0)
        <div class="row g-2 mb-3">
            @foreach($summaryData as $idx => $summary)
                @php
                    $boxClass = 'income';
                    if ($idx === 1) $boxClass = 'expense';
                    elseif ($idx === 2) $boxClass = ($summary['value'] ?? 0) >= 0 ? 'profit' : 'loss';
                @endphp
                <div class="col-4">
                    <div class="summary-box {{ $boxClass }}">
                        <div class="summary-label">{{ $summary['label'] ?? '' }}</div>
                        <div class="summary-value">
                            {{ number_format($summary['value'] ?? 0, 2) }}
                            <small style="font-size: 10px; font-weight: normal;">{{ $summary['currency'] ?? 'YER' }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- جدول بيانات الأرباح والخسائر -->
    @if(isset($reportData['success']) && $reportData['success'] && isset($reportData['result']) && count($reportData['result']) > 0)
        <div class="mb-3">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-list-alt me-1"></i> بيان الأرباح والخسائر التفصيلي
            </h6>
            <table class="table-custom">
                <thead>
                    <tr>
                        @foreach($reportData['columns'] as $column)
                            <th>{{ is_array($column) ? ($column['label'] ?? $column['fieldname']) : $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['result'] as $row)
                        @php
                            $accName = $row['account'] ?? '';
                            $isTotal = str_contains($accName, 'Total') || str_contains($accName, 'إجمالي') || str_contains($accName, 'الربح') || str_contains($accName, 'الخسار');
                            $indent = $row['indent'] ?? 0;
                        @endphp
                        <tr class="{{ $isTotal ? 'row-total' : ($indent == 0 ? 'row-parent' : '') }}">
                            @foreach($reportData['columns'] as $column)
                                @php
                                    $fieldName = is_array($column) ? ($column['fieldname'] ?? '') : $column;
                                    $value = is_array($row) ? ($row[$fieldName] ?? '') : '';
                                    $isNumeric = is_numeric($value) && !in_array($fieldName, ['acc_number', 'account_number', 'posting_date']);
                                    $indentPadding = ($fieldName === 'account' && $indent > 0) ? ($indent * 12) : 0;
                                @endphp
                                <td class="{{ $isNumeric ? 'text-end' : '' }}" style="{{ $indentPadding ? 'padding-right: ' . $indentPadding . 'px;' : '' }}">
                                    {{ $isNumeric ? number_format($value, 2) : $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>
</html>
