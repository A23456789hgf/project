<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير ملخص إنجاز المشاريع | {{ date('Y-m-d') }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: dejavusans;
            margin: 0;
            padding: 0;
            background: #ffffff;
            direction: rtl;
            text-align: right;
            color: #1f2937;
        }

        .page {
            width: 210mm;
            height: 297mm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
        }

        .cover-header-bg {
            background-color: #2c5f2d;
            height: 480px; 
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .cover-content {
            position: relative;
            z-index: 1;
            padding: 60px 40px;
            text-align: center;
        }

        .gov-title {
            color: #ffffff;
            font-size: 16pt;
            margin-bottom: 5px;
        }

        .org-title {
            color: #ffffff;
            font-size: 22pt;
            font-weight: bold;
            margin: 0;
        }

        .logo-container {
            margin-top: 50px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .logo-wrapper {
            display: inline-block;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e5e5e5;
        }
        
        .logo-img {
            width: 180px;
            height: auto;
        }

        .doc-badge {
            background-color: #97bc62;
            color: #2c5f2d;
            padding: 12px 50px;
            font-weight: bold;
            font-size: 18pt;
            display: inline-block;
            margin: 30px 0;
        }

        .summary-card-cover {
            background: #ffffff;
            border-top: 1px solid #eeeeee;
            border-bottom: 1px solid #eeeeee;
            border-left: 1px solid #eeeeee;
            border-right: 15px solid #2c5f2d;
            padding: 30px;
            width: 80%;
            margin: 40px auto;
        }

        .page-body {
            padding: 40px;
        }

        .page-header {
            border-bottom: 3px solid #2c5f2d;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .section-title {
            background: #f8faf8;
            border-top: 1px solid transparent;
            border-bottom: 1px solid transparent;
            border-left: 1px solid transparent;
            border-right: 8px solid #2c5f2d;
            padding: 12px 20px;
            margin: 30px 0 20px 0;
            font-size: 16pt;
            font-weight: bold;
            color: #2c5f2d;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
            margin: 0 -15px;
        }

        .kpi-box {
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            border-left: 1px solid #e5e7eb;
            border-right: 6px solid #97bc62;
            padding: 20px;
        }

        .kpi-label {
            font-size: 11pt;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .kpi-value {
            font-size: 20pt;
            font-weight: bold;
            color: #111827;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 12px;
            text-align: right;
            font-weight: bold;
            color: #374151;
        }

        .data-table td {
            border: 1px solid #d1d5db;
            padding: 12px;
            vertical-align: middle;
        }

        .progress-container {
            width: 100%;
            background: #e5e7eb;
            height: 12px;
            border-radius: 6px;
        }

        .progress-bar {
            background: #2c5f2d;
            height: 12px;
            border-radius: 6px;
        }

        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
            font-size: 10pt;
            color: #6b7280;
        }
    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="page">
        <div class="cover-header-bg"></div>
        <div class="cover-content">
            <div class="gov-title">الجمهورية اليمنية</div>
            <div class="gov-title">وزارة الزراعة والثروة السمكية والموارد المائية</div>
            <div class="org-title">اللجنة الزراعية والسمكية العليا</div>
            
            <div class="logo-container">
                <div class="logo-wrapper">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" class="logo-img">
                    @endif
                </div>
            </div>

            <div class="doc-badge">تقرير ملخص الإنجاز الموحد</div>

            <h1 style="font-size: 30pt; color: #111827; margin: 30px 0;">أداء ومتابعة المشاريع الزراعية</h1>

            <div class="summary-card-cover">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%; text-align: right;">
                            <div style="font-size: 12pt; color: #6b7280;">إجمالي عدد المشاريع</div>
                            <div style="font-size: 32pt; font-weight: bold; color: #2c5f2d;">{{ $stats['total_projects'] }}</div>
                        </td>
                        <td style="width: 50%; text-align: left; border-right: 1px solid #e5e7eb; padding-right: 30px;">
                            <div style="font-size: 12pt; color: #6b7280;">متوسط نسبة الإنجاز</div>
                            @php 
                                $avgProgress = collect($stats['progress_series'])->avg() ?? 0;
                            @endphp
                            <div style="font-size: 32pt; font-weight: bold; color: #2c5f2d;">{{ number_format($avgProgress, 1) }}%</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: right;">تاريخ التقرير: <strong>{{ date('Y-m-d') }}</strong></td>
                    <td style="text-align: left;">مركز التقارير الموحد - وزارة الزراعة</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Details Page -->
    <div class="page page-body">
        <div class="page-header">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 70%;">
                        <div style="font-size: 20pt; font-weight: bold; color: #2c5f2d;">المؤشرات التشغيلية والمالية</div>
                        <div style="font-size: 11pt; color: #6b7280;">نظرة شاملة على مستويات التنفيذ والاستغلال المالي</div>
                    </td>
                    <td style="width: 30%; text-align: left;">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" style="height: 60px;">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">أولاً: ملخص الأداء المالي والجودة</div>
        <table class="kpi-table">
            <tr>
                <td style="width: 50%;">
                    <div class="kpi-box">
                        <div class="kpi-label">إجمالي الميزانية المرصودة</div>
                        <div class="kpi-value">{{ number_format($stats['financial']['total_budget']) }} ﷼</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="kpi-box" style="border-right: 6px solid #10b981;">
                        <div class="kpi-label">إجمالي المنصرف الفعلي</div>
                        <div class="kpi-value">{{ number_format($stats['financial']['total_spent']) }} ﷼</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="kpi-box" style="border-right: 6px solid #3b82f6;">
                        <div class="kpi-label">نسبة الاستغلال المالي</div>
                        <div class="kpi-value">{{ number_format($stats['financial']['utilization_percentage'] ?? 0, 1) }}%</div>
                    </div>
                </td>
                <td>
                    <div class="kpi-box" style="border-right: 6px solid #f59e0b;">
                        <div class="kpi-label">سجلات الجودة المسجلة</div>
                        <div class="kpi-value">{{ $stats['quality']['total_records'] ?? 0 }} سجل</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">ثانياً: تحليل حالات المشاريع</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">حالة المشروع</th>
                    <th style="width: 20%;">عدد المشاريع</th>
                    <th style="width: 50%;">التوزيع المئوي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['projects_by_status'] as $label => $count)
                <tr>
                    <td style="font-weight: bold; color: #2c5f2d;">{{ $label }}</td>
                    <td style="text-align: center;">{{ $count }}</td>
                    <td>
                        @php 
                            $percent = $stats['total_projects'] > 0 ? ($count / $stats['total_projects'] * 100) : 0; 
                        @endphp
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 80%;">
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                                    </div>
                                </td>
                                <td style="width: 20%; text-align: left; font-size: 10pt; font-weight: bold;">
                                    {{ number_format($percent, 1) }}%
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">ثالثاً: سجلات التنفيذ الميداني</div>
        <table class="kpi-table">
            <tr>
                <td style="width: 50%;">
                    <div class="kpi-box" style="border-right: 6px solid #6366f1;">
                        <div class="kpi-label">الأنشطة التمهيدية</div>
                        <div class="kpi-value">{{ $stats['implementation']['preliminary'] ?? 0 }}</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="kpi-box" style="border-right: 6px solid #ec4899;">
                        <div class="kpi-label">الأنشطة التنفيذية</div>
                        <div class="kpi-value">{{ $stats['implementation']['executive'] ?? 0 }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td>صفحة 1 من 1</td>
                    <td style="text-align: left;">تم استخراج هذا التقرير آلياً من نظام إدارة المشاريع</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
