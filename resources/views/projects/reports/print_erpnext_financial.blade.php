<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة التقرير المالي الموحد (ERPNext) - {{ $company ?? 'الجهة' }}</title>
    
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
            font-size: 10px;
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
                <div class="report-title">التقرير المالي الموحد للمشاريع (ERPNext)</div>
                <div class="report-meta">
                    <strong>الجهة:</strong> {{ $company ?? 'كافة الجهات' }}
                    @if(request()->boolean('include_children'))
                        <span class="badge bg-light text-primary border border-primary px-1 ms-1">(شامل الجهات الأبناء)</span>
                    @endif
                    @if(request('from_fiscal_year'))
                        | <strong>السنة المالية:</strong> {{ request('from_fiscal_year') }}
                    @endif
                    @if(request('period_start_date') && request('period_end_date'))
                        | <strong>الفترة:</strong> {{ request('period_start_date') }} إلى {{ request('period_end_date') }}
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

    <!-- كروت ملخص التقرير (P&L Summary) -->
    @if(isset($plSummary) && is_array($plSummary) && count($plSummary) > 0)
        <div class="row g-2 mb-3">
            @foreach($plSummary as $idx => $summary)
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

    <!-- 1. بيان الأرباح والخسائر الشامل (P&L Statement) -->
    @php
        $plItems = $plStatement['result'] ?? ($plStatement['message']['result'] ?? (is_array($plStatement) && isset($plStatement[0]) ? $plStatement : []));
    @endphp
    @if(count($plItems) > 0)
        <div class="mb-4">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-file-invoice-dollar me-1"></i> بيان الأرباح والخسائر الشامل (P&L Statement)
            </h6>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 50%;">الحساب</th>
                        <th style="width: 15%;">العملة</th>
                        <th style="width: 35%;" class="text-end">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plItems as $row)
                        @php
                            $accName = $row['account_name'] ?? ($row['account'] ?? '');
                            if (empty($accName) || $accName === '-') continue;
                            $isTotal = str_contains($accName, 'Total') || str_contains($accName, 'إجمالي') || str_contains($accName, 'Profit') || str_contains($accName, 'الربح') || str_contains($accName, 'Loss') || str_contains($accName, 'الخسار');
                            $indent = $row['indent'] ?? 0;
                            $val = 0;
                            foreach ($row as $k => $v) {
                                if ((str_starts_with($k, 'dec_') || preg_match('/^\d{4}$/', $k) || $k === 'total') && is_numeric($v)) {
                                    $val = (float)$v;
                                }
                            }
                        @endphp
                        <tr class="{{ $isTotal ? 'row-total' : ($indent == 0 ? 'row-parent' : '') }}">
                            <td style="{{ $indent > 0 ? 'padding-right: ' . ($indent * 12) . 'px;' : '' }}">
                                {{ $accName }}
                            </td>
                            <td class="text-center">{{ $row['currency'] ?? 'YER' }}</td>
                            <td class="text-end fw-bold">{{ number_format($val, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- 2. نفقات المشاريع حسب البنود (PL Expense Type Summary) -->
    @php
        $expenseItems = $plExpenses['result'] ?? ($plExpenses['message']['result'] ?? (is_array($plExpenses) && isset($plExpenses[0]) ? $plExpenses : []));
    @endphp
    @if(count($expenseItems) > 0)
        <div class="mb-4" style="page-break-before: auto;">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-tags me-1"></i> نفقات المشاريع حسب البنود (PL Expense Type Summary)
            </h6>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="width: 40%;">الحساب</th>
                        <th style="width: 30%;">بند النفقة المطالب به</th>
                        <th style="width: 10%;">العملة</th>
                        <th style="width: 20%;" class="text-end">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenseItems as $row)
                        @php
                            $accName = $row['account_name'] ?? ($row['account'] ?? '');
                            if (empty($accName)) continue;
                            $val = 0;
                            foreach ($row as $k => $v) {
                                if ((str_starts_with($k, 'dec_') || preg_match('/^\d{4}$/', $k) || $k === 'total') && is_numeric($v)) {
                                    $val = (float)$v;
                                }
                            }
                        @endphp
                        <tr>
                            <td>{{ $accName }}</td>
                            <td>{{ $row['claim_expense_type'] ?? '-' }}</td>
                            <td class="text-center">{{ $row['currency'] ?? 'YER' }}</td>
                            <td class="text-end fw-bold">{{ number_format($val, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- 3. دفتر الأستاذ العام (General Ledger Report) -->
    @php
        $glItems = $glReport['result'] ?? ($glReport['message']['result'] ?? (is_array($glReport) && isset($glReport[0]) ? $glReport : []));
    @endphp
    @if(count($glItems) > 0)
        <div class="mb-3" style="page-break-before: auto;">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-book me-1"></i> دفتر الأستاذ العام (General Ledger Report)
            </h6>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>تاريخ القيد</th>
                        <th>رقم السند</th>
                        <th>الحساب</th>
                        <th>بند النفقة</th>
                        <th>المشروع</th>
                        <th class="text-end">مدين</th>
                        <th class="text-end">دائن</th>
                        <th>البيان</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($glItems as $glRow)
                        <tr>
                            <td class="text-center">{{ $glRow['posting_date'] ?? '' }}</td>
                            <td class="text-center">{{ $glRow['voucher_no'] ?? '' }}</td>
                            <td>{{ $glRow['account'] ?? '' }}</td>
                            <td>{{ $glRow['claim_expense_type'] ?? '' }}</td>
                            <td>{{ $glRow['project'] ?? '' }}</td>
                            <td class="text-end text-danger">{{ number_format($glRow['debit'] ?? 0, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($glRow['credit'] ?? 0, 2) }}</td>
                            <td>{{ $glRow['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>
</html>
