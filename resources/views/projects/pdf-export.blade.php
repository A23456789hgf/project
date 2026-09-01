<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $project->project_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 25mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            counter-reset: page;
        }

        body {
            font-family: 'Almarai', 'DejaVu Sans', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            background: white;
        }

        h1 { font-size: 18pt; font-weight: 800; color: #003d7a; margin: 20px 0 15px 0; }
        h2 { font-size: 14pt; font-weight: 700; color: #1e8449; margin: 15px 0 10px 0; }
        h3 { font-size: 12pt; font-weight: 700; color: #555; margin: 10px 0 8px 0; }

        .page-break { page-break-after: always; }
        .page-break-inside { page-break-inside: avoid; }

        /* ==================================================== */
        /* ===================== COVER PAGE ===================== */
        /* ==================================================== */
        .cover-page {
            height: 100vh;
            text-align: center;
            padding: 50px 20px;
            position: relative;
            background: linear-gradient(135deg, #f9fdf9 0%, #e8f5e9 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .cover-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(90deg, #1e8449 0%, #145a32 100%);
        }

        .cover-org-name {
            font-size: 28pt;
            font-weight: 800;
            color: #145a32;
            margin-bottom: 10px;
            border-bottom: 4px solid #1e8449;
            display: inline-block;
            padding-bottom: 15px;
        }

        .cover-project-name {
            font-size: 42pt;
            font-weight: 900;
            color: #1e8449;
            margin: 60px 0 80px 0;
            line-height: 1.3;
        }

        .qr-code-box {
            display: inline-block;
            padding: 40px;
            border: 6px solid #1e8449;
            border-radius: 15px;
            background: white;
            box-shadow: 0 10px 40px rgba(30, 132, 73, 0.2);
        }

        .qr-code-image {
            width: 250px;
            height: 250px;
            border: 3px solid #1e8449;
            border-radius: 8px;
            background: white;
        }

        .qr-label {
            font-size: 14pt;
            color: #145a32;
            font-weight: 700;
            margin-top: 15px;
            border-top: 2px dashed #ccc;
            padding-top: 10px;
        }

        .cover-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-around;
            padding: 20px 30px;
            font-size: 11pt;
            background: #e8f5e9;
            border-top: 4px solid #1e8449;
        }

        .cover-footer-item {
            text-align: center;
        }

        .cover-footer-label {
            font-weight: 800;
            color: #145a32;
            margin-bottom: 5px;
            font-size: 10pt;
        }

        /* ==================================================== */
        /* ================= DOCUMENT CONTENT ================= */
        /* ==================================================== */
        .content-page {
            padding: 0;
        }

        .document-header {
            text-align: center;
            border-bottom: 5px solid #1e8449;
            padding: 20px;
            margin-bottom: 30px;
            background: #f0fff0;
            border-radius: 8px;
        }

        .document-header h1 {
            color: #003d7a;
            font-size: 22pt;
            margin-bottom: 10px;
        }

        .document-header .subtitle {
            color: #666;
            font-size: 12pt;
            margin-bottom: 15px;
        }

        .header-info-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .header-info-row {
            display: table-row;
        }

        .header-info-cell {
            display: table-cell;
            border: 1px solid #ddd;
        }

        .header-info-label {
            font-weight: 700;
            color: white;
            background: #1e8449;
            padding: 10px;
            text-align: center;
            width: 25%;
        }

        .header-info-value {
            color: #333;
            padding: 10px;
            background: white;
            width: 25%;
        }

        /* ===================== SECTIONS ===================== */
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-title {
            background: linear-gradient(135deg, #1e8449 0%, #145a32 100%);
            color: white;
            padding: 14px 18px;
            font-size: 14pt;
            font-weight: 800;
            margin-bottom: 0;
            border-left: 6px solid #a9c6a9;
        }

        .section-content {
            padding: 18px;
            background: white;
        }

        /* ===================== TABLES ===================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10pt;
        }

        table thead {
            background: #145a32;
            color: white;
        }

        table th {
            padding: 10px 8px;
            text-align: center;
            font-weight: 700;
            border: 1px solid #145a32;
            font-size: 9pt;
        }

        table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: right;
        }

        table tbody tr:nth-child(even) {
            background: #f8fdf8;
        }

        table tbody tr:nth-child(odd) {
            background: white;
        }

        table tbody tr.activity-header-row {
            background: #f0fff0;
            font-weight: 700;
            color: #145a32;
        }

        table tbody tr.cost-row {
            background: #f9fdf9;
            font-size: 9pt;
        }

        /* Master-Detail rows */
        table td.activity-col {
            background: #e8f5e9;
            font-weight: 700;
            color: #145a32;
        }

        table td.action-col {
            background: #f0fff0;
            font-weight: 600;
        }

        /* ===================== INFO GRID (TWO COLUMNS) ===================== */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 12px;
            border: 1px solid #ddd;
            width: 50%;
            vertical-align: top;
        }

        .info-label {
            font-weight: 700;
            color: #1e8449;
            display: block;
            margin-bottom: 5px;
            font-size: 11pt;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 10pt;
            line-height: 1.5;
        }

        /* ===================== BADGES ===================== */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
            color: white;
        }

        .badge-success { background: #1e8449; }
        .badge-warning { background: #f39c12; color: white; }
        .badge-danger { background: #c0392b; }
        .badge-info { background: #3498db; }
        .badge-primary { background: #1e8449; }

        /* ===================== FINANCIAL SUMMARY ===================== */
        .financial-summary {
            background: #f0fff0;
            border: 2px solid #1e8449;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }

        .financial-summary h4 {
            color: #145a32;
            margin-bottom: 12px;
            font-size: 11pt;
        }

        .financial-summary table {
            margin: 0;
        }

        .financial-summary table thead {
            background: #145a32;
        }

        .financial-summary table tbody tr:last-child {
            background: #c3e6cb;
            font-weight: 700;
        }

        /* ===================== SIGNATURE SECTION ===================== */
        .signature-section {
            margin-top: 50px;
            border-top: 2px solid #ddd;
            padding-top: 20px;
        }

        .signature-line {
            display: inline-block;
            width: 45%;
            margin: 30px 2.5%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line .label {
            font-weight: 700;
            color: #1e8449;
            margin-top: 60px;
            padding-top: 10px;
            border-top: 2px solid #333;
            font-size: 10pt;
        }

        /* ===================== FOOTER ===================== */
        .document-footer {
            text-align: center;
            color: #999;
            font-size: 9pt;
            margin-top: 50px;
            border-top: 2px solid #ddd;
            padding-top: 15px;
        }

        .page-number {
            text-align: center;
            margin-top: 20px;
            font-size: 10pt;
            color: #666;
        }

        /* ===================== PRINT MEDIA ===================== */
        @media print {
            @page {
                size: A4;
                margin: 20mm 20mm 25mm 20mm;
                @bottom-center {
                    content: "الصفحة " counter(page) " من " counter(pages);
                    font-size: 10pt;
                    color: #666;
                }
            }

            .cover-page {
                page-break-after: always;
            }

            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="cover-page">
        <div class="cover-org-name">اللجنة الزراعية والسمكية العليا</div>
        
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
            <h1 class="cover-project-name">{{ $project->project_name ?? 'اسم المشروع غير محدد' }}</h1>
            
            <div class="qr-code-box">
                <div class="qr-label">امسح رمز الاستجابة السريعة لعرض التفاصيل</div>
                @if(isset($qrCodeBase64) && $qrCodeBase64)
                    <img src="{{ $qrCodeBase64 }}" class="qr-code-image" alt="QR Code">
                @else
                    <div class="qr-code-image" style="display: flex; align-items: center; justify-content: center; color: #c0392b; font-size: 14pt; border-color: #c0392b;">
                        ❌ غير متوفر
                    </div>
                @endif
            </div>
        </div>

        <div class="cover-footer">
            <div class="cover-footer-item">
                <div class="cover-footer-label">رقم المشروع</div>
                <div><span class="badge badge-primary">{{ $project->form_number ?? 'غير محدد' }}</span></div>
            </div>
            <div class="cover-footer-item">
                <div class="cover-footer-label">تاريخ الطباعة</div>
                <div>{{ $exportDate ?? now()->format('Y-m-d') }}</div>
            </div>
            <div class="cover-footer-item">
                <div class="cover-footer-label">الحالة</div>
                <div>
                    @php
                        $statusText = $project->status === 'draft' ? 'مسودة' : ($project->status === 'final' ? 'نهائي' : ($project->status ?? 'غير محدد'));
                        $statusClass = $project->status === 'final' ? 'badge-success' : ($project->status === 'draft' ? 'badge-warning' : 'badge-info');
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="content-page page-break">
        <div class="document-header">
            <h1>{{ $project->project_name ?? 'اسم المشروع غير محدد' }}</h1>
            <p class="subtitle">تقرير تفصيلي - اللجنة الزراعية والسمكية العليا</p>
            
            <div class="header-info-grid">
                <div class="header-info-row">
                    <div class="header-info-label">رقم المشروع:</div>
                    <div class="header-info-value">{{ $project->form_number ?? 'غير محدد' }}</div>
                    <div class="header-info-label">حالة المشروع:</div>
                    <div class="header-info-value">
                        <span class="badge status-badge {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">البيانات الأساسية</h2>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">البرنامج</span>
                            <div class="info-value">{{ $project->program->name ?? '—' }}</div>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">المجال</span>
                            <div class="info-value">{{ $project->domain->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">المجال الفرعي</span>
                            <div class="info-value">{{ $project->subdomain->name ?? '—' }}</div>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">نوع التدخل</span>
                            <div class="info-value">{{ $project->intervention->name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">الأولوية</span>
                            <div class="info-value">
                                @php
                                    $priorityClass = 'badge-success';
                                    if(optional($project->priority)->name === 'عالية') {
                                        $priorityClass = 'badge-danger';
                                    } elseif(optional($project->priority)->name === 'متوسطة') {
                                        $priorityClass = 'badge-warning';
                                    }
                                @endphp
                                <span class="badge {{ $priorityClass }}">
                                    {{ optional($project->priority)->name ?? '—' }}
                                </span>
                            </div>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">عدد المستفيدين</span>
                            <div class="info-value">{{ isset($project->number_of_beneficiaries) ? number_format($project->number_of_beneficiaries) : '—' }} مستفيد</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell">
                            <span class="info-label">تاريخ البداية</span>
                            <div class="info-value">{{ optional($project->start_date_gregorian)->format('Y-m-d') ?? '—' }}</div>
                        </div>
                        <div class="info-cell">
                            <span class="info-label">تاريخ النهاية</span>
                            <div class="info-value">{{ optional($project->end_date_gregorian)->format('Y-m-d') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($project->detail)
        <div class="section">
            <h2 class="section-title">تفاصيل المشروع والأهداف</h2>
            <div class="section-content">
                <div style="margin-bottom: 20px;">
                    <strong class="info-label" style="border-bottom: 2px dashed #e8f5e9;">ملخص المشروع:</strong>
                    <div class="info-value">{{ $project->detail->project_summary ?? '—' }}</div>
                </div>
                <div style="margin-bottom: 20px;">
                    <strong class="info-label" style="border-bottom: 2px dashed #e8f5e9;">المشكلة والمبررات:</strong>
                    <div class="info-value">{{ $project->detail->problem_and_justification ?? '—' }}</div>
                </div>
                <div style="margin-bottom: 20px;">
                    <strong class="info-label" style="border-bottom: 2px dashed #e8f5e9;">النتائج المتوقعة:</strong>
                    <div class="info-value">{{ $project->detail->expected_results ?? '—' }}</div>
                </div>
                <div style="margin-bottom: 0;">
                    <strong class="info-label" style="border-bottom: 2px dashed #e8f5e9;">المخرجات المتوقعة:</strong>
                    <div class="info-value">{{ $project->detail->expected_outputs ?? '—' }}</div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($project->locations) && $project->locations->count())
        <div class="section">
            <h2 class="section-title">مواقع التنفيذ الجغرافية</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>المحافظة</th>
                            <th>المديرية</th>
                            <th>المنطقة الفرعية</th>
                            <th>القرية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->locations as $loc)
                            <tr>
                                <td>{{ $loc->governorate->name ?? '—' }}</td>
                                <td>{{ $loc->directorate->name ?? '—' }}</td>
                                <td>{{ $loc->subArea->name ?? '—' }}</td>
                                <td>{{ $loc->village->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(isset($project->mainObjectives) && $project->mainObjectives->count())
        <div class="section">
            <h2 class="section-title">الأهداف العامة ومؤشرات القياس</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>الهدف</th>
                            <th>المؤشر</th>
                            <th>وحدة المؤشر</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->mainObjectives as $objective)
                            <tr>
                                <td>{{ $objective->objective ?? '—' }}</td>
                                <td>{{ $objective->indicator ?? '—' }}</td>
                                <td style="text-align: center;">{{ $objective->indicator_unit ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Executive Activities Section -->
        @if(isset($project->executiveActivities) && $project->executiveActivities->count())
        <div class="section page-break-inside">
            <h2 class="section-title">الأنشطة التنفيذية والإجراءات والتكاليف</h2>
            <div class="section-content">
                @php
                    $executiveActivitiesData = [];
                    foreach($project->executiveActivities as $activity) {
                        $activityRowCount = 0;
                        $actionsData = [];
                        
                        if($activity->actions && $activity->actions->count() > 0) {
                            foreach($activity->actions as $action) {
                                $costsData = [];
                                $costCount = 0;
                                
                                if($action->costs && $action->costs->count() > 0) {
                                    foreach($action->costs as $cost) {
                                        $costsData[] = $cost;
                                        $costCount++;
                                    }
                                } else {
                                    $costsData[] = null;
                                    $costCount = 1;
                                }
                                
                                $assignedEntities = [];
                                if($action->assigned_entities && $action->assigned_entities->count() > 0) {
                                    foreach($action->assigned_entities as $assigned) {
                                        $assignedEntities[] = $assigned;
                                    }
                                }
                                
                                $actionsData[] = [
                                    'action' => $action,
                                    'costs' => $costsData,
                                    'assigned' => $assignedEntities,
                                    'rowspan' => $costCount > 0 ? $costCount : 1
                                ];
                                $activityRowCount += ($costCount > 0 ? $costCount : 1);
                            }
                        }
                        
                        $executiveActivitiesData[] = [
                            'activity' => $activity,
                            'actions' => $actionsData,
                            'rowspan' => $activityRowCount > 0 ? $activityRowCount : 1
                        ];
                    }
                @endphp
                <table>
                    <thead>
                        <tr>
                            <th>النشاط</th>
                            <th>الإجراء</th>
                            <th>الوزن%</th>
                            <th>المكلفون</th>
                            <th>البند المالي</th>
                            <th>المبلغ</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($executiveActivitiesData as $actData)
                            @php $firstActivityRow = true; @endphp
                            @foreach($actData['actions'] as $actionIndex => $actionData)
                                @php $firstActionRow = true; @endphp
                                @foreach($actionData['costs'] as $costIndex => $cost)
                                    <tr class="cost-row">
                                        @if($firstActivityRow && $costIndex == 0)
                                            <td rowspan="{{ $actData['rowspan'] }}" class="activity-col">
                                                <strong>{{ $actData['activity']->name ?? '—' }}</strong>
                                                <br><small>(وزن: {{ $actData['activity']->weight ?? 0 }}%)</small>
                                            </td>
                                            @php $firstActivityRow = false; @endphp
                                        @endif

                                        @if($firstActionRow)
                                            <td rowspan="{{ $actionData['rowspan'] }}" class="action-col">
                                                {{ $actionData['action']->action ?? '—' }}
                                            </td>
                                            <td rowspan="{{ $actionData['rowspan'] }}" style="text-align: center;">
                                                {{ $actionData['action']->weight ?? 0 }}%
                                            </td>
                                            <td rowspan="{{ $actionData['rowspan'] }}" style="font-size: 9pt;">
                                                @if($actionData['assigned'] && count($actionData['assigned']) > 0)
                                                    @foreach($actionData['assigned'] as $assigned)
                                                        • {{ $assigned->authority?->agency_name ?? $assigned->agency_name ?? '—' }}<br>
                                                    @endforeach
                                                @else
                                                    <em>—</em>
                                                @endif
                                            </td>
                                            @php $firstActionRow = false; @endphp
                                        @endif

                                        @if($cost)
                                            <td>{{ $cost->financialItem?->name ?? '—' }}</td>
                                            <td style="text-align: center;">{{ number_format($cost->amount ?? 0, 2) }}</td>
                                            <td style="text-align: center;">{{ $cost->quantity ?? 0 }}</td>
                                            <td style="text-align: center; font-weight: 700;">{{ number_format($cost->total ?? 0, 2) }}</td>
                                        @else
                                            <td colspan="4" style="text-align: center; font-style: italic;">—</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                <!-- Executive Financial Summary -->
                @php
                    $executiveFinancialSummary = [];
                    foreach($project->executiveActivities as $activity) {
                        foreach($activity->actions as $action) {
                            foreach($action->costs as $cost) {
                                $itemName = $cost->financialItem?->name ?? 'غير محدد';
                                if (!isset($executiveFinancialSummary[$itemName])) {
                                    $executiveFinancialSummary[$itemName] = 0;
                                }
                                $executiveFinancialSummary[$itemName] += $cost->total ?? 0;
                            }
                        }
                    }
                    $executiveTotal = array_sum($executiveFinancialSummary);
                @endphp

                @if(count($executiveFinancialSummary) > 0)
                <div class="financial-summary">
                    <h4>الخلاصة المالية للأنشطة التنفيذية</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>البند المالي</th>
                                <th>الإجمالي (ر.س)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($executiveFinancialSummary as $itemName => $total)
                            <tr>
                                <td>{{ $itemName }}</td>
                                <td style="text-align: center;">{{ number_format($total, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td><strong>المجموع الكلي</strong></td>
                                <td style="text-align: center;"><strong>{{ number_format($executiveTotal, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if(isset($project->risks) && $project->risks->count())
        <div class="section">
            <h2 class="section-title">إدارة المخاطر واقتراحات الحلول</h2>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>نوع المخاطرة</th>
                            <th>المخاطرة</th>
                            <th>مستوى المخاطر</th>
                            <th>الحل المقترح</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->risks as $risk)
                            <tr>
                                <td>{{ $risk->risk_type ?? '—' }}</td>
                                <td>{{ $risk->risk ?? '—' }}</td>
                                <td style="text-align: center;">
                                    @php
                                        $riskBadgeClass = 'badge-success';
                                        if($risk->risk_rate === 'مرتفع') {
                                            $riskBadgeClass = 'badge-danger';
                                        } elseif($risk->risk_rate === 'متوسط') {
                                            $riskBadgeClass = 'badge-warning';
                                        }
                                    @endphp
                                    <span class="badge {{ $riskBadgeClass }}">
                                        {{ $risk->risk_rate ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ $risk->proposed_solution ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="section page-break-inside">
            <h2 class="section-title">التوقيعات والاعتمادات</h2>
            <div class="section-content">
                <div class="signature-section">
                    <div class="signature-line">
                        <div>معد التقرير</div>
                        <div class="label">التوقيع والتاريخ</div>
                    </div>
                    <div class="signature-line">
                        <div>المدقق</div>
                        <div class="label">التوقيع والتاريخ</div>
                    </div>
                </div>
                <div class="signature-section">
                    <div class="signature-line">
                        <div>المشرف المباشر</div>
                        <div class="label">التوقيع والتاريخ</div>
                    </div>
                    <div class="signature-line">
                        <div>جهة الاعتماد</div>
                        <div class="label">التوقيع والتاريخ</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="document-footer">
            <p><strong>معلومات الطباعة:</strong></p>
            <p>تم إنشاء هذا التقرير في: {{ $exportDate ?? now()->format('Y-m-d H:i') }}</p>
            <p>اللجنة الزراعية والسمكية العليا - جميع الحقوق محفوظة ©</p>
            <div class="page-number">
                نظام إدارة المشاريع - وثيقة طباعة بيانات المشروع
            </div>
        </div>
    </div>
</body>
</html>
