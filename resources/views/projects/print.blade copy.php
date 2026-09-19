<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>وثيقة المشروع | {{ $project->project_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-color: #2c5f2d;
            --sec-color: #97bc62;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --qr-color: #666666;
            /* لون رمادي للQR */
        }

        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background: #e5e5e5;
            -webkit-print-color-adjust: exact !important;
        }

        .page {
            width: 210mm;
            height: 297mm;
            background: white;
            margin: 10mm auto;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-after: always;
        }

        /* --- تعديل مساحة اللون الأخضر في الغلاف --- */
        .cover-header-bg {
            background-color: var(--main-color);
            height: 55%;
            /* زيادة المساحة لتغطي أكثر من نصف الصفحة */
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            /* تصميم مائل بزاوية أوسع */
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
            z-index: 0;
        }

        .cover-content {
            position: relative;
            z-index: 1;
            padding: 60px 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .gov-title {
            color: white;
            font-size: 20px;
            /* كان 18 */
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .org-title {
            color: white;
            font-size: 30px;
            /* كان 28 */
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* حاوية الشعار الجديدة - دائرية بيضاء */
        .logo-container {
            margin-top: 60px;
            margin-bottom: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        /* حاوية الشعار الدائرية البيضاء */
        .logo-wrapper {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 380px;
            /* حجم الحاوية */
            height: 380px;
            /* نفس العرض لتصبح دائرية */
            background-color: white;
            border-radius: 50%;
            /* تجعلها دائرية */
            padding: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        /* تأثير إشعاعي خفيف داخل الحاوية */
        .logo-wrapper::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            right: -10%;
            bottom: -10%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 50%, rgba(255, 255, 255, 0.4) 100%);
            border-radius: 50%;
            z-index: 1;
        }

        .logo-wrapper img {
            width: 320px;
            /* حجم الشعار داخل الحاوية الدائرية */
            height: auto;
            display: block;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.15));
            position: relative;
            z-index: 2;
        }

        /* تأثير عند الطباعة */
        @media print {
            .logo-wrapper {
                background: white;
                border: 2px solid #ddd;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .logo-wrapper::before {
                display: none;
            }
        }

        .doc-badge {
            background-color: var(--sec-color);
            color: var(--main-color);
            padding: 8px 35px;
            border-radius: 5px;
            font-weight: 800;
            font-size: 16px;
            margin-bottom: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }

        /* تفاصيل المشروع بالأسفل على الخلفية البيضاء */
        .project-details-bottom {
            margin-top: auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 40px;
            position: relative;
            z-index: 2;
        }

        .project-name-main {
            font-size: 28px;
            color: var(--dark-text);
            font-weight: 800;
            margin: 0 0 25px 0;
            line-height: 1.4;
            max-width: 90%;
            position: relative;
            z-index: 2;
        }

        .applicant-info-card {
            background: #fff;
            border-right: 12px solid var(--main-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 25px 40px;
            border-radius: 4px;
            width: 85%;
            text-align: right;
            border: 1px solid #eee;
            border-right-width: 12px;
            position: relative;
            z-index: 2;
        }

        .app-label {
            font-size: 14px;
            color: var(--light-text);
            display: block;
            margin-bottom: 5px;
        }

        .app-value {
            display: block;
            text-align: center;
            font-size: 18px;
            color: var(--main-color);
            /* نفس اللون السابق */
            font-weight: bold;
        }

        .cover-footer {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 20px 0;
            margin-top: 30px;
            border-top: 1px solid #eee;
            position: relative;
            z-index: 2;
        }

        /* --- الصفحات الداخلية --- */
        .internal-padding {
            padding: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--main-color);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .header-qr-box {
            width: 70px;
            height: 70px;
            padding: 8px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .section-title {
            font-size: 18px;
            color: white;
            font-weight: 700;
            margin: 30px 0 15px 0;
            background: var(--main-color);
            padding: 8px 15px;
            border-radius: 4px;
            display: inline-block;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .modern-table th {
            background: #f1f5f1;
            color: var(--main-color);
            padding: 12px;
            text-align: right;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .modern-table td {
            padding: 12px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            color: var(--dark-text);
        }

        .location-highlight {
            font-weight: 700;
            color: var(--main-color);
        }

        /* QR cover style */
        #qrcode_cover {
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: inline-block;
        }

        /* QR styling */
        .qr-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            display: inline-block;
        }

        /* استبدال الصور الموجودة بأخرى رمادية */
        .qr-gray canvas {
            filter: grayscale(100%) contrast(1.2);
        }

        /* --- New Hierarchical & Print Styles --- */
        .page-break {
            page-break-before: always;
        }

        .nested-row {
            background-color: #fafafa;
        }

        .result-row {
            background-color: #f8fafc;
            padding-right: 30px !important;
        }

        .output-row {
            background-color: #ffffff;
            padding-right: 60px !important;
        }

        .hierarchy-label {
            font-weight: 700;
            color: var(--main-color);
            margin-bottom: 4px;
            display: block;
        }

        .section-header {
            margin-top: 40px;
            margin-bottom: 20px;
            border-right: 5px solid var(--main-color);
            padding-right: 15px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 22px;
            color: var(--dark-text);
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .data-item {
            border-bottom: 1px solid #edf2f7;
            padding: 8px 0;
        }

        .data-label {
            font-size: 13px;
            color: var(--light-text);
            display: block;
        }

        .data-value {
            font-size: 15px;
            color: var(--dark-text);
            font-weight: 600;
        }

        .summary-card {
            background-color: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-final {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .status-draft {
            background-color: #feebc8;
            color: #744210;
        }

        @media print {
            body {
                background: white;
            }

            .page {
                margin: 0;
                box-shadow: none;
                height: auto;
                min-height: 297mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="page">
        <div class="cover-header-bg"></div>

        <div class="cover-content">
            <div class="gov-title">الجمهورية اليمنية</div>
            <div class="gov-title">وزارة الزراعة والثروة والسمكية والموارد المائية </div>
            <!-- <div class="gov-title">{{ $user->entity->name ?? 'N/A' }}  </div> -->


            <!-- حاوية الشعار الجديدة - دائرية بيضاء -->
            <div class="logo-container">
                <div class="logo-wrapper">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار اللجنة الزراعية والسمكية العليا">
                </div>
            </div>

            <span class="doc-badge">وثيقة اعتماد مشروع</span>

            <div class="project-details-bottom">
                <div class="project-name-main">{{ $project->project_name }}</div>

                <div class="applicant-info-card">
                    <span class="app-label">الجهة المقدمة للمشروع</span>
                    <span class="app-value"
                        style="display: block; text-align: center; font-size: 18px; font-weight: bold;">
                        {{ $project->creator_entity_name }}
                    </span>
                </div>

                <div class="cover-footer">
                    <div style="text-align: right;">
                        <div style="font-size: 13px; color: #777; font-weight: bold;">الرقم المرجعي</div>
                        <div style="font-weight: 800; font-size: 22px; color: var(--main-color);">
                            {{ $project->form_number }}
                        </div>
                    </div>
                    <div class="qr-container">
                        <div id="qrcode_cover"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}
                </div>
                <div style="font-size: 12px; color: #666;">كود المشروع الموحد: {{ $project->form_number }}</div>
            </div>
            <div class="header-qr-box">
                <div id="qrcode_header"></div>
            </div>
        </div>

        <div class="section-header">
            <h2>أولاً: السياق المؤسسي والبيانات الأساسية</h2>
        </div>

        <div class="summary-card">
            <div class="data-grid">
                <div class="data-item">
                    <span class="data-label">البرنامج</span>
                    <span class="data-value">{{ $project->program->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">المجال / المجال الفرعي</span>
                    <span class="data-value">{{ $project->domain->name ?? '-' }} /
                        {{ $project->subdomain->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">نوع التدخل</span>
                    <span class="data-value">{{ $project->intervention->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">الأولوية</span>
                    <span class="data-value">{{ $project->priority->priority ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">الفئة المستهدفة</span>
                    <span class="data-value">{{ $project->targetCategory->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">حالة المشروع</span>
                    <span class="status-badge {{ $project->status === 'final' ? 'status-final' : 'status-draft' }}">
                        {{ $project->status === 'final' ? 'معتمد' : 'مسودة' }}
                    </span>
                </div>
                <div class="data-item">
                    <span class="data-label">مسجل المشروع</span>
                    <span class="data-value">{{ $project->createdBy->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">تاريخ التسجيل</span>
                    <span class="data-value">{{ $project->created_at->format('Y-m-d') }}</span>
                </div>

                <div class="data-item">
                    <span class="data-label">تاريخ البداية (هجري / ميلادي)</span>
                    <span class="data-value">{{ $project->start_date_hijri ?? '-' }} /
                        {{ $project->start_date_gregorian ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">تاريخ النهاية (هجري / ميلادي)</span>
                    <span class="data-value">{{ $project->end_date_hijri ?? '-' }} /
                        {{ $project->end_date_gregorian ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">مدة المشروع</span>
                    <span class="data-value">{{ $project->project_duration }} يوم</span>
                </div>
                <div class="data-item">
                    <span class="data-label">إجمالي عدد المستفيدين</span>
                    <span class="data-value">{{ number_format($project->number_of_beneficiaries) }} مستفيد</span>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h2>ثانياً: النطاق الجغرافي للمشروع</h2>
        </div>
        <table class="modern-table">
            <thead>
                <tr>
                    <th width="40">م</th>
                    <th>المحافظة</th>
                    <th>المديرية</th>
                    <th>العزلة / القرية</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->locations as $index => $loc)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td class="location-highlight">{{ $loc->governorate->name ?? 'جميع المحافظات' }}</td>
                        <td>{{ $loc->directorate->name ?? 'جميع المديريات' }}</td>
                        <td>{{ $loc->subArea->name ?? $loc->area ?? 'جميع المناطق/العزل' }} /
                            {{ $loc->village->name ?? 'جميع القرى/الحارات' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($project->detail)
            <div class="section-header">
                <h2>ثالثاً: تفاصيل المشروع (المقدمة، المبررات، المكونات، الأثر)</h2>
            </div>
            <div class="summary-card">
                <div style="margin-bottom: 15px; border-bottom: 1px dotted #eee; padding-bottom: 10px;">
                    <span class="data-label">هل المشروع ضمن الخطة؟</span>
                    <span class="data-value">{{ $project->detail->is_part_of_plan ? 'نعم' : 'لا' }}</span>
                </div>
                @if($project->detail->project_introduction)
                    <div style="margin-bottom: 15px;">
                        <span class="data-label">مقدمة المشروع</span>
                        <p style="font-size: 14px; line-height: 1.6; text-align: justify;">
                            {{ $project->detail->project_introduction }}
                        </p>
                    </div>
                @endif
                @if($project->detail->problem_and_justification)
                    <div style="margin-bottom: 15px;">
                        <span class="data-label">المشكلة والمبررات والاحتياج</span>
                        <p style="font-size: 14px; line-height: 1.6; text-align: justify;">
                            {{ $project->detail->problem_and_justification }}
                        </p>
                    </div>
                @endif
                @if($project->detail->project_summary)
                    <div style="margin-bottom: 15px;">
                        <span class="data-label">ملخص المشروع</span>
                        <p style="font-size: 14px; line-height: 1.6; text-align: justify;">
                            {{ $project->detail->project_summary }}
                        </p>
                    </div>
                @endif
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    @if($project->detail->project_components)
                        <div>
                            <span class="data-label">مكونات المشروع</span>
                            <p style="font-size: 14px;">{{ $project->detail->project_components }}</p>
                        </div>
                    @endif
                    @if($project->detail->expected_impact)
                        <div>
                            <span class="data-label">الأثر الاجتماعي والاقتصادي المتوقع</span>
                            <p style="font-size: 14px;">{{ $project->detail->expected_impact }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}
                </div>
                <!-- <div style="font-size: 12px; color: #666;">الأهداف الاستراتيجية وسلسلة النتائج</div> -->
            </div>
        </div>

        <div class="section-header">
            <h2>رابعاً: الأهداف </h2>
        </div>

        @if($project->mainObjectives->count() > 0)
            <div style="margin-bottom: 25px; background-color: #fef2f2; padding: 15px; border-right: 4px solid #b91c1c;">
                <span class="hierarchy-label" style="display: block; margin-bottom: 5px;">الهدف العام (Overall
                    Objective):</span>
                @foreach($project->mainObjectives as $mainObj)
                    <div style="font-size: 16px; font-weight: 700; color: #b91c1c;">- {{ $mainObj->objective }}</div>
                @endforeach
            </div>
        @endif
        <span class="hierarchy-label" style="display: block; margin-bottom: 5px;"> الأهداف الخاصة </span>
        @php
            $indicatorTypesMapping = [
                'quantitative' => 'كمي',
                'relative' => 'نسبي',
                'qualitative' => 'كيفي',
            ];
        @endphp

        @foreach($project->specialObjectives as $objIndex => $objective)
            <div
                style="margin-bottom: 30px; border: 2px solid var(--main-color); border-radius: 8px; overflow: hidden; page-break-inside: avoid;">

                <!-- رأس الهدف الخاص -->
                <div style="background-color: var(--main-color); color: white; padding: 15px 20px;">
                    <div style="margin-bottom: 12px;">
                        <span style="font-weight: 800; font-size: 18px;">
                            الهدف الخاص {{ $objIndex + 1 }}:
                        </span>
                        <span style="font-size: 18px; font-weight: bold;">
                            {{ $objective->objective }}
                        </span>
                    </div>

                    <div
                        style="display: flex; gap: 20px; font-size: 14px; background: rgba(255,255,255,0.15); padding: 8px 15px; border-radius: 4px;">
                        <span>
                            الوزن:
                            <b
                                style="background: white; color: var(--main-color); padding: 2px 8px; border-radius: 4px; margin-right: 5px;">
                                {{ $objective->objective_weight }}%
                            </b>
                        </span>

                        <span>
                            القيمة المستهدفة:
                            <b>
                                {{ isset($objective->target_value) && $objective->target_value !== '' ? $objective->target_value : '-' }}
                            </b>
                        </span>

                        <span>
                            وحدة القياس:
                            <b>{{ $objective->measurement_unit ?? '-' }}</b>
                        </span>
                    </div>
                </div>

                <!-- النتائج والمخرجات -->
                <div>

                    @if($objective->results && $objective->results->count() > 0)

                        <table style="width:100%; border-collapse:collapse; font-size:14px;">

                            <thead>
                                <tr style="background-color:#f1f5f1; color:var(--main-color); border-bottom:2px solid #e2e8f0;">
                                    <th style="padding:12px; text-align:right; width:35%;">
                                        النتيجة المتوقعة
                                    </th>

                                    <th style="padding:12px; text-align:right; width:35%;">
                                        المخرج
                                    </th>

                                    <th style="padding:12px; text-align:center; width:10%;">
                                        المستهدف
                                    </th>

                                    <th style="padding:12px; text-align:center; width:10%;">
                                        نوع المؤشر
                                    </th>

                                    <th style="padding:12px; text-align:center; width:10%;">
                                        وحدة القياس
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($objective->results as $resIndex => $result)

                                    @php
                                        $outputsCount = max($result->outputs->count(), 1);
                                    @endphp

                                    @if($result->outputs && $result->outputs->count() > 0)

                                        @foreach($result->outputs as $outIndex => $output)

                                            <tr style="border-bottom:1px solid #e2e8f0;">

                                                @if($outIndex == 0)
                                                    <td rowspan="{{ $outputsCount }}"
                                                        style="padding:12px; vertical-align:top; background:#f8fafc; font-weight:bold; border-left:1px solid #e2e8f0;">

                                                        <span style="color:var(--sec-color); font-size:18px;">
                                                            ▪
                                                        </span>

                                                        النتيجة {{ $objIndex + 1 }}.{{ $resIndex + 1 }}

                                                        <div style="margin-top:6px; color:#1f2937;">
                                                            {{ $result->result_name }}
                                                        </div>
                                                    </td>
                                                @endif

                                                <td style="padding:10px; color:#4b5563;">
                                                    <span style="color:#94a3b8;">
                                                        ↳
                                                    </span>

                                                    {{ $output->output }}
                                                </td>

                                                <td style="padding:10px; text-align:center; font-weight:bold; color:var(--main-color);">
                                                    {{ isset($output->target_value) && $output->target_value !== '' ? $output->target_value : '-' }}
                                                </td>

                                                <td style="padding:10px; text-align:center; color:#64748b;">
                                                    {{ $indicatorTypesMapping[$output->indicator_type] ?? $output->indicator_type ?? '-' }}
                                                </td>

                                                <td style="padding:10px; text-align:center; color:#64748b;">
                                                    {{ $output->indicator_unit ?? '-' }}
                                                </td>

                                            </tr>

                                        @endforeach

                                    @else

                                        <tr style="border-bottom:1px solid #e2e8f0;">

                                            <td style="padding:12px; background:#f8fafc; font-weight:bold;">
                                                <span style="color:var(--sec-color);">▪</span>

                                                النتيجة {{ $objIndex + 1 }}.{{ $resIndex + 1 }}

                                                <div style="margin-top:6px;">
                                                    {{ $result->result_name }}
                                                </div>
                                            </td>

                                            <td style="padding:10px; text-align:center; color:#94a3b8;">
                                                لا توجد مخرجات
                                            </td>

                                            <td style="padding:10px; text-align:center;">
                                                -
                                            </td>

                                            <td style="padding:10px; text-align:center;">
                                                -
                                            </td>

                                            <td style="padding:10px; text-align:center;">
                                                -
                                            </td>

                                        </tr>

                                    @endif

                                @endforeach

                            </tbody>

                        </table>

                    @else

                        <div style="text-align:center; color:#64748b; padding:20px;">
                            لا توجد نتائج مسجلة لهذا الهدف
                        </div>

                    @endif

                </div>

            </div>
        @endforeach
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}
                </div>
                <div style="font-size: 12px; color: #666;">المخاطر والجهات ذات العلاقة</div>
            </div>
        </div>

        <div class="section-header">
            <h2>خامساً: المخاطر والافتراضات</h2>
        </div>
        <table class="modern-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th width="50">م</th>
                    <th>نوع الخطر</th>
                    <th>مستوى الخطر</th>
                    <th>إجراءات الحد من المخاطر</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->risks as $index => $risk)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $risk->risk }}</td>
                        <td style="font-weight: 700;">{{ $risk->risk_rate ?? 'عام' }}</td>
                        <td>{{ $risk->proposed_solution }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">لا توجد مخاطر مسجلة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-header">
            <h2>سادساً: الجهات المشرفة</h2>
        </div>
        <table class="modern-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>نوع الجهة</th>
                    <th>الجهة</th>
                    <th>الجهة الأب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->supervisingAuthorities as $entity)
                    <tr>
                        <td>
                            @if($entity->authority_type == 'internal')
                                <span class="badge bg-primary">داخلية</span>
                            @else
                                <span class="badge bg-secondary">خارجية</span>
                            @endif
                        </td>
                        <td>{{ $entity->authority->agency_name ?? ($entity->internalEntity->name ?? '-') }}</td>
                        <td>
                            @if($entity->authority_type == 'internal')
                                @php
                                    $internalEntity = null;
                                    foreach ($internalEntities ?? [] as $intEnt) {
                                        if ($intEnt['entity_name'] == ($entity->internalEntity->name ?? '')) {
                                            $internalEntity = $intEnt;
                                            break;
                                        }
                                    }
                                @endphp
                                {{ $internalEntity['father_name'] ?? 'لا توجد جهة أب' }}
                            @else
                                {{ $entity->parentAuthority->agency_name ?? ($entity->parentAuthority->name ?? ($entity->parent->agency_name ?? ($entity->parent->name ?? '-'))) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #light-text;">لا توجد جهات مشرفة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-header">
            <h2>سابعاً: الجهات المشاركة</h2>
        </div>
        <table class="modern-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>نوع الجهة</th>
                    <th>الجهة</th>
                    <th>الجهة الأب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->participatingEntities as $entity)
                    <tr>
                        <td>
                            @if($entity->authority_type == 'internal')
                                <span class="badge bg-primary">داخلية</span>
                            @else
                                <span class="badge bg-secondary">خارجية</span>
                            @endif
                        </td>
                        <td>{{ $entity->authority->agency_name ?? ($entity->internalEntity->name ?? '-') }}</td>
                        <td>
                            @if($entity->authority_type == 'internal')
                                @php
                                    $internalEntity = null;
                                    foreach ($internalEntities ?? [] as $intEnt) {
                                        if ($intEnt['entity_name'] == ($entity->internalEntity->name ?? '')) {
                                            $internalEntity = $intEnt;
                                            break;
                                        }
                                    }
                                @endphp
                                {{ $internalEntity['father_name'] ?? 'لا توجد جهة أب' }}
                            @else
                                {{ $entity->parentAuthority->agency_name ?? ($entity->parentAuthority->name ?? ($entity->parent->agency_name ?? ($entity->parent->name ?? '-'))) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #light-text;">لا توجد جهات مشاركة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-header">
            <h2>ثامناً: الجهات المنفذة</h2>
        </div>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>نوع الجهة</th>
                    <th>الجهة</th>
                    <th>الجهة الأم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->implementingEntities as $entity)
                    <tr>
                        <td>
                            @if($entity->authority_type == 'internal')
                                <span class="badge bg-primary">داخلية</span>
                            @else
                                <span class="badge bg-secondary">خارجية</span>
                            @endif
                        </td>
                        <td>
                            {{ $entity->authority->agency_name ?? ($entity->internalEntity->name ?? '-') }}
                        </td>
                        <td>
                            @if($entity->authority_type == 'internal')
                                @php
                                    // البحث عن الجهة الداخلية في المصفوفة
                                    $internalEntity = null;
                                    foreach ($internalEntities ?? [] as $intEnt) {
                                        if ($intEnt['entity_name'] == ($entity->internalEntity->name ?? '')) {
                                            $internalEntity = $intEnt;
                                            break;
                                        }
                                    }
                                @endphp
                                {{ $internalEntity['father_name'] ?? 'لا توجد جهة أب' }}
                            @else
                                {{ $entity->parentAuthority->agency_name ?? ($entity->parentAuthority->name ?? '-') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-3">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <br>
                                لا توجد جهات منفذة مضافة
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}
                </div>
                <div style="font-size: 12px; color: #666;">الأنشطة المبدئية والتمهيدية</div>
            </div>
        </div>

        <div class="section-header">
            <h2>تاسعاً: الأنشطة المبدئية والتمهيدية</h2>
        </div>
        <table border="1" style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead style="background-color: #fafafa;">
                <tr>
                    <th style="padding: 8px; text-align: center;">#</th>
                    <th style="padding: 8px; text-align: center;">النشاط</th>
                    <th style="padding: 8px; text-align: center;">الإجراء</th>
                    <th style="padding: 8px; text-align: center;">البند المالي</th>
                    <th style="padding: 8px; text-align: center;">الوحدة</th>
                    <th style="padding: 8px; text-align: center;">الكمية</th>
                    <th style="padding: 8px; text-align: center;">السعر</th>
                    <th style="padding: 8px; text-align: center;">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @php $activityCounter = 1; @endphp

                @foreach($project->preliminaryActivities as $activity)
                    @php
                        // حساب عدد الصفوف للنشاط
                        $activityRowCount = $activity->procedures->sum(function ($procedure) {
                            return max($procedure->costs->count(), 1);
                        });
                        $activityDisplayed = false;
                    @endphp

                    @foreach($activity->procedures as $procedure)
                        @php
                            $procedureRowCount = max($procedure->costs->count(), 1);
                            $procedureDisplayed = false;
                        @endphp

                        @forelse($procedure->costs as $cost)
                            <tr style="border-bottom: 1px dotted #eee;">
                                {{-- الرقم التسلسلي والنشاط --}}
                                @if(!$activityDisplayed)
                                    <td rowspan="{{ $activityRowCount }}"
                                        style="padding: 8px; vertical-align: top; text-align: center; font-weight: 700;">
                                        {{ $activityCounter++ }}
                                    </td>
                                    <td rowspan="{{ $activityRowCount }}" style="padding: 8px; vertical-align: top; font-weight: 700;">
                                        {{ $activity->name }}
                                    </td>
                                    @php $activityDisplayed = true; @endphp
                                @endif

                                {{-- الإجراء --}}
                                @if(!$procedureDisplayed)
                                    <td rowspan="{{ $procedureRowCount }}" style="padding: 8px; vertical-align: top;">
                                        {{ $procedure->procedure_name }}
                                    </td>
                                    @php $procedureDisplayed = true; @endphp
                                @endif

                                {{-- بيانات التكلفة --}}
                                <td style="padding: 8px;">{{ $cost->financialItem->name ?? '-' }}</td>
                                <td style="padding: 8px;">{{ $cost->unit->unit_name ?? '-' }}</td>
                                <td style="padding: 8px; text-align: center;">{{ $cost->quantity }}</td>
                                <td style="padding: 8px; text-align: center;">{{ number_format($cost->amount) }}</td>
                                <td style="padding: 8px; text-align: center; font-weight: 700; color: var(--main-color);">
                                    {{ number_format($cost->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- الرقم التسلسلي والنشاط --}}
                                @if(!$activityDisplayed)
                                    <td rowspan="{{ $activityRowCount }}"
                                        style="padding: 8px; vertical-align: top; text-align: center; font-weight: 700;">
                                        {{ $activityCounter++ }}
                                    </td>
                                    <td rowspan="{{ $activityRowCount }}" style="padding: 8px; vertical-align: top; font-weight: 700;">
                                        {{ $activity->name }}
                                    </td>
                                    @php $activityDisplayed = true; @endphp
                                @endif

                                {{-- الإجراء --}}
                                <td>{{ $procedure->procedure_name }}</td>
                                <td colspan="5" style="text-align: center;">-</td>
                            </tr>
                        @endforelse
                    @endforeach
                @endforeach
            </tbody>

            <!-- إجمالي كل النشاطات -->
            <tfoot style="background-color: #f8fafc;">
                <tr>
                    <td colspan="7" style="padding: 10px; text-align: left; font-weight: 800;">إجمالي كل النشاطات:</td>
                    <td style="padding: 10px; text-align: center; font-weight: 800; color: #b91c1c; font-size: 14px;">
                        {{ number_format($project->preliminaryActivities->flatMap->procedures->flatMap->costs->sum('total')) }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @if($project->preliminaryFinancialSummaries->count() > 0)
            <div class="section-header">
                <h3>الملخص المالي للأنشطة التمهيدية</h3>
            </div>
            <table class="modern-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>البند المالي</th>
                        <th>النشاط</th>
                        <th>الإجراء</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->preliminaryFinancialSummaries as $summary)
                        <tr>
                            <td>{{ $summary->financialItem->name ?? '-' }}</td>
                            <td>{{ $summary->activity->name ?? '-' }}</td>
                            <td>{{ $summary->procedure->procedure_name ?? '-' }}</td>
                            <td style="font-weight: 700;">{{ number_format($summary->aggregated_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="section-header">
            <h2>عاشراً: أنشطة التنفيذ (Implementation Activities)</h2>
        </div>
        @foreach($project->executiveActivities as $activity)
            <div style="margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div
                    style="background-color: #f1f5f1; padding: 10px 15px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">
                    {{ $activity->name }}
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead style="background-color: #fafafa;">
                        <tr style="text-align: right; color: #555;">
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">الإجراء التنفيذي</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">المنفذين (Contractors)</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">البند المالي</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">الوحدة</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">الكمية</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">السعر</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activity->actions as $action)
                            @php $costCount = $action->costs->count(); @endphp
                            @foreach($action->costs as $index => $cost)
                                <tr style="border-bottom: 1px dotted #eee;">
                                    @if($index === 0)
                                        <td style="padding: 8px;" rowspan="{{ $costCount }}">{{ $action->action }}</td>
                                        <td style="padding: 8px;" rowspan="{{ $costCount }}">
                                            @foreach($action->assignedEntities as $entity)
                                                <div style="font-weight: 600;">- {{ $entity->name ?? '-' }}</div>
                                                <div style="font-size: 10px; color: #666; margin-right: 10px;">(المهمة:
                                                    {{ $entity->task ?? 'غير محددة' }})
                                                </div>
                                            @endforeach
                                        </td>
                                    @endif
                                    <td style="padding: 8px;">{{ $cost->financialItem->name ?? '-' }}</td>
                                    <td style="padding: 8px;">{{ $cost->unit->unit_name ?? '-' }}</td>
                                    <td style="padding: 8px; text-align: center;">{{ $cost->quantity }}</td>
                                    <td style="padding: 8px; text-align: center;">{{ number_format($cost->amount) }}</td>
                                    <td style="padding: 8px; text-align: center; font-weight: 700; color: var(--main-color);">
                                        {{ number_format($cost->total) }}
                                    </td>
                                </tr>
                            @endforeach
                            @if($costCount == 0)
                                <tr style="border-bottom: 1px dotted #eee;">
                                    <td style="padding: 8px;">{{ $action->action }}</td>
                                    <td style="padding: 8px;">
                                        @foreach($action->assignedEntities as $entity)
                                            <span style="font-weight: 600;">{{ $entity->agency_name ?? ($entity->name ?? '-') }}</span>
                                        @endforeach
                                    </td>
                                    <td colspan="5" style="padding: 8px; text-align: center; color: #999;">لا توجد تكاليف مسجلة</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f8fafc;">
                        <tr>
                            <td colspan="6" style="padding: 10px; text-align: left; font-weight: 800;">إجمالي النشاط
                                التنفيذي:</td>
                            <td
                                style="padding: 10px; text-align: center; font-weight: 800; color: #b91c1c; font-size: 14px;">
                                {{ number_format($activity->actions->flatMap->costs->sum('total')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        @if($project->executiveFinancialSummaries->count() > 0)
            <div class="section-header">
                <h3>الملخص المالي لأنشطة التنفيذ</h3>
            </div>
            <table class="modern-table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>البند المالي</th>
                        <th>النشاط التنفيذي</th>
                        <th>الإجراء التنفيذي</th>
                        <th>المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->executiveFinancialSummaries as $summary)
                        <tr>
                            <td>{{ $summary->financialItem->name ?? '-' }}</td>
                            <td>{{ $summary->activity->name ?? '-' }}</td>
                            <td>{{ $summary->action->action_name ?? '-' }}</td>
                            <td style="font-weight: 700;">{{ number_format($summary->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="section-header">
            <h2>الحادي عشر: الفئات والجهات المستفيدة</h2>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3
                    style="font-size: 16px; color: var(--main-color); border-bottom: 2px solid #eee; padding-bottom: 8px;">
                    المجموعات المستهدفة</h3>
                <ul style="list-style: none; padding: 0;">
                    @foreach($project->beneficiaryGroups as $group)
                        <li style="padding: 10px; border-bottom: 1px dotted #eee; font-size: 14px;">-
                            {{ $group->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>


            </div>
        </div>
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}
                </div>
                <div style="font-size: 12px; color: #666;">تكلفة المشروع ومصادر التمويل</div>
            </div>
        </div>

        <div class="section-header">
            <h2>الثاني عشر: مصادر التمويل وموازنة المشروع</h2>
        </div>



        <table class="modern-table">
            <thead>
                <tr>
                    <th width="40">م</th>
                    <th>مصدر التمويل</th>
                    <th>الجهة الممولة</th>
                    <th>نوع التمويل</th>
                    <th>شكل التمويل</th>
                    <th>المبلغ</th>
                    <th width="70">النسبة %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->financings as $index => $financing)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td style="font-weight: 700;">{{ $financing->fundingSource->name ?? '-' }}</td>
                        <td>{{ $financing->authority->agency_name ?? ($financing->authority->name ?? '-') }}</td>
                        <td>{{ $financing->financingType->name ?? '-' }}</td>
                        <td>
                            {{ $financing->financingForm->name ?? '-' }}
                            <div style="font-size: 10px; color: #666;">({{ $financing->subFinancingForm->name ?? '-' }})
                            </div>
                        </td>
                        <td style="font-weight: bold; color: #b91c1c;">{{ number_format($financing->financing_amount) }}
                        </td>
                        <td style="text-align: center;">{{ $financing->financing_percentage }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999; padding: 20px;">لا توجد بيانات تمويل مسجلة
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <!-- @if($project->financings->count() > 0)
                <tfoot>
                    <tr style="background-color: #f1f5f1;">
                        <td colspan="5" style="text-align: left; font-weight: 800; font-size: 16px; padding: 15px;">إجمالي
                            الميزانية المعلنة:</td>
                        <td colspan="2" style="font-weight: 800; color: #b91c1c; font-size: 20px; padding: 15px;">
                            {{ number_format($totalProjectCost) }} ريال
                        </td>
                    </tr>
                </tfoot>
            @endif -->
        </table>
        @if($project->cost)
            <div class="summary-card" style="margin-bottom: 25px;">
                <div class="data-grid" style="grid-template-columns: 1fr;">
                    <div class="data-item" style="text-align: center;">
                        <span class="data-label">إجمالي تكلفة المشروع المعتمدة</span>
                        <span class="data-value" style="color: #b91c1c; font-weight: 800; font-size: 18px;">
                            {{ number_format($project->cost->total_cost) }} ريال
                            <br>
                            <small style="color: #666; font-size: 14px; font-weight: normal;">
                                (فقط {{ numberToArabicText($project->cost->total_cost) }} ريال لا غير)
                            </small>
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="section-header">
            <h2>الثالث عشر: الوثائق والمرفقات</h2>
        </div>
        @if($project->documents->count() > 0)
            <table class="modern-table">
                <thead>
                    <tr>
                        <th width="50">م</th>
                        <th>اسم الوثيقة</th>
                        <th>تاريخ الرفع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->documents as $index => $doc)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $doc->document_name }}</td>
                            <td>{{ $doc->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 20px; text-align: center; color: #666; font-style: italic;">لا توجد وثائق مرفقة بهذا
                المشروع</div>
        @endif

        <div style="margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; text-align: center;">
            <div>
                <div style="font-weight: 800; margin-bottom: 40px;">ختم وتوقيع الجهة المقدمة</div>
                <div style="border-bottom: 2px dotted #ccc; width: 200px; margin: 0 auto;"></div>
            </div>
            <div>
                <div style="font-weight: 800; margin-bottom: 40px;">اعتماد اللجنة الزراعية والسمكية العليا</div>
                <div style="border-bottom: 2px dotted #ccc; width: 200px; margin: 0 auto;"></div>
            </div>
        </div>

        <div
            style="margin-top: auto; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; padding-top: 15px;">
            صادر عن النظام الإلكتروني للجنة الزراعية والسمكية العليا - {{ date('Y') }} | تاريخ الطباعة: {{ $printDate }}
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // QR رمادي للغلاف
        new QRCode(document.getElementById("qrcode_cover"), {
            text: "REF-{{ $project->form_number }}",
            width: 85,
            height: 85,
            colorDark: "#666666",  // رمادي
            colorLight: "#ffffff"   // أبيض
        });

        // QR رمادي للرأس
        new QRCode(document.getElementById("qrcode_header"), {
            text: "REF-{{ $project->form_number }}",
            width: 60,
            height: 60,
            colorDark: "#666666",  // رمادي
            colorLight: "#ffffff"   // أبيض
        });

        // إضافة تأثير تحويلة للصورة
        document.addEventListener('DOMContentLoaded', function () {
            const logoImg = document.querySelector('.logo-wrapper img');
            if (logoImg) {
                logoImg.addEventListener('mouseenter', function () {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'transform 0.3s ease';
                });
                logoImg.addEventListener('mouseleave', function () {
                    this.style.transform = 'scale(1)';
                });
            }

            // إضافة تأثير للمس QR (اختياري)
            const qrElements = document.querySelectorAll('#qrcode_cover, #qrcode_header');
            qrElements.forEach(qr => {
                qr.addEventListener('mouseenter', function () {
                    this.style.filter = 'brightness(0.95)';
                    this.style.transition = 'filter 0.3s ease';
                });
                qr.addEventListener('mouseleave', function () {
                    this.style.filter = 'brightness(1)';
                });
            });
        });
    </script>
</body>

</html>