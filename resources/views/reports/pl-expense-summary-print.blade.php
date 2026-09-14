<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة تقرير ملخص الإيرادات والنفقات - {{ $company ?? 'الجهة' }}</title>
    
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

        /* بطاقات الملخص */
        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            background-color: #f8fafc;
            text-align: center;
        }

        .summary-box.income {
            border-top: 3px solid #0d6efd;
        }

        .summary-box.expense {
            border-top: 3px solid #f59e0b;
        }

        .summary-box.profit {
            border-top: 3px solid #10b981;
        }

        .summary-box.loss {
            border-top: 3px solid #ef4444;
        }

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

        /* الجداول */
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
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
            .table-custom {
                page-break-inside: auto;
            }
            .table-custom tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
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
                <div class="report-title">تقرير ملخص الإيرادات والنفقات حسب البنود</div>
                <div class="report-meta">
                    <strong>الجهة:</strong> {{ $company ?? 'كافة الجهات' }}
                    @if(request()->boolean('include_children'))
                        <span class="badge bg-light text-primary border border-primary px-1 ms-1">(شامل الجهات الأبناء)</span>
                    @endif
                    @if(request('from_date') && request('to_date'))
                        | <strong>الفترة:</strong> من {{ request('from_date') }} إلى {{ request('to_date') }}
                    @elseif(request('from_fiscal_year'))
                        | <strong>السنة المالية:</strong> {{ request('from_fiscal_year') }}
                    @endif
                    @if(request('cost_center'))
                        | <strong>مركز التكلفة:</strong> {{ request('cost_center') }}
                    @endif
                </div>
            </div>
            <div class="text-start report-meta">
                <div><strong>تاريخ الطباعة:</strong> {{ now()->format('Y-m-d H:i') }}</div>
                <div><strong>المستخدم:</strong> {{ auth()->user()->name ?? '' }}</div>
            </div>
        </div>
    </div>

    <!-- كروت ملخص الإيرادات والنفقات -->
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

    <!-- جدول ملخص التقرير (الأرباح والخسائر) -->
    @if(isset($reportData['success']) && $reportData['success'] && isset($reportData['result']) && count($reportData['result']) > 0)
        <div class="mb-3">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-list-alt me-1"></i> نتائج التقرير حسب الحسابات والبنود
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
                            $isTotal = str_contains($accName, 'إجمالي') || str_contains($accName, 'الربح') || str_contains($accName, 'الخسارة');
                            $isParent = ($row['indent'] ?? 1) == 0;
                        @endphp
                        <tr class="{{ $isTotal ? 'row-total' : ($isParent ? 'row-parent' : '') }}">
                            @foreach($reportData['columns'] as $column)
                                @php
                                    $fieldName = is_array($column) ? ($column['fieldname'] ?? '') : $column;
                                    $value = is_array($row) ? ($row[$fieldName] ?? '') : '';
                                    $isNumeric = is_numeric($value) && !in_array($fieldName, ['acc_number', 'account_number']);
                                @endphp
                                <td class="{{ $isNumeric ? 'text-end' : '' }}">
                                    {{ $isNumeric ? number_format($value, 2) : $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- جدول العمليات المالية (GL Result) إن وجدت -->
    @if(isset($reportData['gl_result']) && count($reportData['gl_result']) > 0)
        <div class="mb-3" style="page-break-before: auto;">
            <h6 style="font-weight: 700; color: #0d6efd; margin-bottom: 8px; font-size: 12px;">
                <i class="fas fa-exchange-alt me-1"></i> العمليات المالية حسب البنود (قيود اليومية)
            </h6>
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>تاريخ العملية</th>
                        @if(request()->boolean('include_children'))
                            <th>الجهة</th>
                        @endif
                        <th>رقم السند</th>
                        <th>الحساب</th>
                        <th>بند النفقة</th>
                        <th>مدين</th>
                        <th>دائن</th>
                        <th>المشروع</th>
                        <th>مركز التكلفة</th>
                        <th>البيان</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['gl_result'] as $glRow)
                        <tr>
                            <td class="text-center">{{ $glRow['posting_date'] ?? '' }}</td>
                            @if(request()->boolean('include_children'))
                                <td>{{ $glRow['company'] ?? '' }}</td>
                            @endif
                            <td class="text-center">{{ $glRow['voucher_no'] ?? '' }}</td>
                            <td>{{ $glRow['account'] ?? '' }}</td>
                            <td>{{ $glRow['claim_expense_type'] ?? '' }}</td>
                            <td class="text-end">{{ number_format($glRow['debit'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($glRow['credit'] ?? 0, 2) }}</td>
                            <td>{{ $glRow['project'] ?? '' }}</td>
                            <td>{{ $glRow['cost_center'] ?? '' }}</td>
                            <td>{{ $glRow['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(isset($reportData['project_gl_result']) && count($reportData['project_gl_result']) > 0)
        <div style="page-break-before: always;"></div>
        <div class="section-title">العمليات المالية المرتبطة بالمشاريع (ERPNext)</div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>تاريخ العملية</th>
                        @if(request()->boolean('include_children'))
                            <th>الجهة</th>
                        @endif
                        <th>رقم السند</th>
                        <th>الحساب</th>
                        <th>بند النفقة</th>
                        <th>المشروع</th>
                        <th>مدين</th>
                        <th>دائن</th>
                        <th>مركز التكلفة</th>
                        <th>البيان</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData['project_gl_result'] as $glRow)
                        <tr>
                            <td class="text-center">{{ $glRow['posting_date'] ?? '' }}</td>
                            @if(request()->boolean('include_children'))
                                <td>{{ $glRow['company'] ?? '' }}</td>
                            @endif
                            <td class="text-center">{{ $glRow['voucher_no'] ?? '' }}</td>
                            <td>{{ $glRow['account'] ?? '' }}</td>
                            <td>{{ $glRow['claim_expense_type'] ?? '' }}</td>
                            <td class="fw-bold">{{ $glRow['project'] ?? '' }}</td>
                            <td class="text-end text-danger">{{ number_format($glRow['debit'] ?? 0, 2) }}</td>
                            <td class="text-end text-success">{{ number_format($glRow['credit'] ?? 0, 2) }}</td>
                            <td>{{ $glRow['cost_center'] ?? '' }}</td>
                            <td>{{ $glRow['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>
</html>