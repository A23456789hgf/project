<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* تعريف الخطوط من المجلد الخاص بك */
        @font-face {
            font-family: 'Cairo';
            src: url('{{ public_path("fonts/cairo-regular.ttf") }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Cairo';
            src: url('{{ public_path("fonts/Cairo-ExtraBold.ttf") }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        @page { margin: 0; }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            direction: rtl;
            text-align: right;
            color: #1a1a1a;
            background-color: #fff;
            line-height: 1.5;
        }

        /* تصميم الغلاف */
        .cover-page {
            page-break-after: always;
            height: 297mm;
            border: 15px solid #1e3a5f;
            position: relative;
            background: #fff;
        }

        .gold-border {
            position: absolute;
            top: 10px; left: 10px; right: 10px; bottom: 10px;
            border: 2px solid #d4af37;
        }

        .cover-header { text-align: center; margin-top: 50px; }

        .republic-emblem {
            width: 180px;
            margin: 20px auto;
            display: block;
        }

        .cover-title {
            color: #1e3a5f;
            font-size: 38pt;
            font-weight: bold; /* سيستخدم ExtraBold تلقائياً */
            margin-top: 20px;
        }

        .cover-subtitle {
            font-size: 20pt;
            color: #d4af37;
            margin-top: 10px;
        }

        /* صناديق الإحصائيات */
        .stats-summary {
            margin: 50px auto;
            width: 85%;
            background: #fcfcfc;
            border-radius: 20px;
            padding: 25px;
            border-top: 6px solid #1e3a5f;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .stat-box { text-align: center; }
        .stat-value { font-size: 22pt; color: #1e3a5f; font-weight: bold; display: block; }
        .stat-label { font-size: 13pt; color: #666; display: block; margin-top: 5px; }

        /* الجداول */
        .content-page { padding: 2cm 1.5cm; }

        .section-header {
            background: #1e3a5f;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-right: 10px solid #d4af37;
        }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: #f4f6f8;
            color: #1e3a5f;
            padding: 15px;
            font-size: 11pt;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 12px;
            border: 1px solid #ddd;
            font-size: 10.5pt;
            text-align: center;
        }

        /*badges للحالات */
        .badge {
            padding: 4px 12px;
            border-radius: 4px;
            color: white;
            font-size: 9pt;
            font-weight: bold;
        }
        .status-draft { background-color: #95a5a6; }
        .status-progress { background-color: #3498db; }
        .status-completed { background-color: #27ae60; }
    </style>
</head>
<body>

    @php
        $statusMap = [
            'draft' => 'مسودة',
            'in_progress' => 'قيد التنفيذ',
            'progress_in' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'approved' => 'معتمد'
        ];

        $totalProjects = $projects->count();
        $totalBeneficiaries = $projects->sum('number_of_beneficiaries');
        $totalCost = $projects->sum(function($q) { return $q->cost->total_cost ?? 0; });
    @endphp

    <div class="cover-page">
        <div class="gold-border"></div>
        <div class="cover-header">
            <h2 style="font-weight: bold;">الجمهورية اليمنية</h2>
            <p>اللجنة الزراعية والسمكية العليا</p>

            <img src="{{ public_path('images/logo.png') }}" class="republic-emblem">

            <h1 class="cover-title">ملخص المشاريع الميدانية</h1>
            <p class="cover-subtitle">نظام تتبع المشاريع والأنشطة</p>

            <div class="stats-summary">
                <table style="border: none; background: transparent;">
                    <tr>
                        <td style="border: none; width: 33.3%;">
                            <div class="stat-box">
                                <span class="stat-value">{{ $totalProjects }}</span>
                                <span class="stat-label">إجمالي المشاريع</span>
                            </div>
                        </td>
                        <td style="border: none; border-right: 1px solid #eee; border-left: 1px solid #eee; width: 33.3%;">
                            <div class="stat-box">
                                <span class="stat-value">{{ number_format($totalBeneficiaries) }}</span>
                                <span class="stat-label">عدد المستفيدين</span>
                            </div>
                        </td>
                        <td style="border: none; width: 33.3%;">
                            <div class="stat-box">
                                <span class="stat-value" style="font-size: 18pt;">{{ number_format($totalCost) }}</span>
                                <span class="stat-label">التكلفة الإجمالية</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="content-page">
        <div class="section-header">
            <h2 style="font-weight: bold;">بيانات المشاريع التفصيلية</h2>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم المشروع</th>
                    <th>اسم المشروع</th>
                    <th>المستفيدين</th>
                    <th>الحالة</th>
                    <th>التكلفة (ريال)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $index => $project)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong style="color:#1e3a5f;">{{ $project->form_number }}</strong></td>
                    <td style="text-align: right; font-weight: bold;">{{ $project->project_name }}</td>
                    <td style="font-weight: bold;">{{ number_format($project->number_of_beneficiaries ?? 0) }}</td>
                    <td>
                        @php
                            $statusKey = strtolower($project->status);
                            $arabicStatus = $statusMap[$statusKey] ?? $project->status;
                            $statusClass = (strpos($statusKey, 'progress') !== false) ? 'status-progress' :
                                           (($statusKey == 'draft') ? 'status-draft' : 'status-completed');
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $arabicStatus }}</span>
                    </td>
                    <td style="font-weight: bold;">{{ number_format($project->cost->total_cost ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>