<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>الملخص المالي - {{ $project->project_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            direction: rtl;
            text-align: right;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            background: white;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1e8449;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1e8449;
            font-size: 22pt;
            font-weight: 800;
        }

        .header p {
            color: #666;
            font-size: 12pt;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            background-color: #f4f7f4;
            color: #145a32;
            padding: 10px 15px;
            font-size: 14pt;
            font-weight: 700;
            border-right: 5px solid #1e8449;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #1e8449;
            color: white;
            font-weight: 700;
            padding: 10px;
            text-align: center;
            border: 1px solid #145a32;
        }

        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row {
            background-color: #e8f5e9 !important;
            font-weight: 800;
            color: #145a32;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10pt;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .currency {
            font-size: 0.9em;
            color: #666;
            margin-right: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>الملخص المالي للمشروع</h1>
        <p>{{ $project->project_name }}</p>
        <p>رقم المشروع: {{ $project->form_number }}</p>
    </div>

    <div class="section">
        <div class="section-title">مصادر التمويل</div>
        <table>
            <thead>
                <tr>
                    <th>م</th>
                    <th>مصدر التمويل</th>
                    <th>جهة التمويل</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @php $totalFinancing = 0; @endphp
                @forelse($project->financings as $index => $financing)
                    @php $totalFinancing += $financing->amount; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $financing->fundingSource->name ?? '-' }}</td>
                        <td>{{ $financing->authority->name ?? '-' }}</td>
                        <td>{{ number_format($financing->amount, 2) }} <span class="currency">﷼</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">لا توجد بيانات تمويل متاحة</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3">إجمالي التمويل</td>
                    <td>{{ number_format($totalFinancing, 2) }} <span class="currency">﷼</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ملخص البنود المالية (الأنشطة التمهيدية)</div>
        <table>
            <thead>
                <tr>
                    <th>م</th>
                    <th>البند المالي</th>
                    <th>المبلغ المخطط</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPrelim = 0; @endphp
                @forelse($project->preliminaryFinancialSummaries as $index => $summary)
                    @php $totalPrelim += $summary->planned_amount; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $summary->financialItem->name ?? '-' }}</td>
                        <td>{{ number_format($summary->planned_amount, 2) }} <span class="currency">﷼</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">لا توجد بنود مالية تمهيدية</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">إجمالي التمهيدي</td>
                    <td>{{ number_format($totalPrelim, 2) }} <span class="currency">﷼</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ملخص البنود المالية (الأنشطة التنفيذية)</div>
        <table>
            <thead>
                <tr>
                    <th>م</th>
                    <th>البند المالي</th>
                    <th>المبلغ المخطط</th>
                </tr>
            </thead>
            <tbody>
                @php $totalExec = 0; @endphp
                @forelse($project->executiveFinancialSummaries as $index => $summary)
                    @php $totalExec += $summary->planned_amount; @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $summary->financialItem->name ?? '-' }}</td>
                        <td>{{ number_format($summary->planned_amount, 2) }} <span class="currency">﷼</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">لا توجد بنود مالية تنفيذية</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="2">إجمالي التنفيذي</td>
                    <td>{{ number_format($totalExec, 2) }} <span class="currency">﷼</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">الملخص العام للتكاليف</div>
        <table>
            <tbody>
                <tr class="total-row">
                    <td style="text-align: right; width: 70%;">إجمالي التكاليف المخططة (تمهيدي + تنفيذي)</td>
                    <td>{{ number_format($totalPrelim + $totalExec, 2) }} <span class="currency">﷼</span></td>
                </tr>
                <tr>
                    <td style="text-align: right;">إجمالي التمويل المتاح</td>
                    <td>{{ number_format($totalFinancing, 2) }} <span class="currency">﷼</span></td>
                </tr>
                @php $difference = $totalFinancing - ($totalPrelim + $totalExec); @endphp
                <tr style="background-color: {{ $difference >= 0 ? '#f1f8e9' : '#ffebee' }}">
                    <td style="text-align: right;">الفارق (التمويل - التكاليف)</td>
                    <td style="font-weight: bold; color: {{ $difference >= 0 ? '#2e7d32' : '#c62828' }}">
                        {{ number_format($difference, 2) }} <span class="currency">﷼</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>تم استخراج هذا التقرير في: {{ $exportDate }}</p>
        <p>نظام إدارة المشاريع - اللجنة الزراعية والسمكية العليا</p>
    </div>
</body>
</html>
