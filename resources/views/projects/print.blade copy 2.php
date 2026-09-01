<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>وثيقة المشروع | {{ $project->project_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-color: #2c5aa0; 
            --sec-color: #4a9eff;  
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --qr-color: #666666; 
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
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
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-after: always;
        }

        /* --- تعديل مساحة اللون الأخضر في الغلاف --- */
        /* --- Removed cover-header-bg for cleaner look --- */
        .cover-header-bg {
            display: none;
        }

        .cover-content {
            position: relative;
            z-index: 1;
            padding: 40mm 20mm;
            height: 297mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-sizing: border-box;
            background: white;
        }

        .report-header-border {
            border-bottom: 4px solid var(--main-color);
            padding-bottom: 30px;
            margin-bottom: 50px;
            width: 100%;
        }

        .gov-title { color: var(--main-color); font-size: 20px; opacity: 0.8; margin-bottom: 10px; font-weight: 500; }
        .org-title { color: var(--main-color); font-size: 32px; font-weight: 800; margin: 0; letter-spacing: 0.5px; }

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
            width: 380px; /* حجم الحاوية */
            height: 380px; /* نفس العرض لتصبح دائرية */
            background-color: white;
            border-radius: 50%; /* تجعلها دائرية */
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
            background: radial-gradient(circle, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 50%, rgba(255,255,255,0.4) 100%);
            border-radius: 50%;
            z-index: 1;
        }
        
        .logo-wrapper img {
            width: 320px; /* حجم الشعار داخل الحاوية الدائرية */
            height: auto;
            display: block;
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.15));
            position: relative;
            z-index: 2;
        }
        
        /* تأثير عند الطباعة */
        @media print {
            .logo-wrapper {
                background: white;
                border: 2px solid #ddd;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
            font-size: 34px;
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 25px 40px;
            border-radius: 4px;
            width: 85%;
            text-align: right;
            border: 1px solid #eee;
            border-right-width: 12px;
            position: relative;
            z-index: 2;
        }
        .app-label { font-size: 14px; color: var(--light-text); display: block; margin-bottom: 5px;}
        .app-value { font-size: 24px; color: var(--main-color); font-weight: 800; }

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
        .internal-padding { padding: 40px; }

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
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .section-title {
            font-size: 18px;
            color: var(--main-color);
            font-weight: 800;
            margin: 35px 0 20px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 12px 20px;
            border-radius: 8px;
            display: block;
            border-right: 5px solid var(--main-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .modern-table th {
            background: #2c5aa0;
            color: white;
            padding: 12px 10px;
            text-align: right;
            border: 1px solid #dee2e6;
            font-size: 14px;
            font-weight: 700;
        }
        .modern-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .modern-table td {
            padding: 12px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            color: var(--dark-text);
        }

        .location-highlight { font-weight: 700; color: var(--main-color); }
        
        /* QR cover style */
        #qrcode_cover {
            padding: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        .page-break { page-break-before: always; }
        
        .nested-row { background-color: #fafafa; }
        .result-row { background-color: #f8fafc; padding-right: 30px !important; }
        .output-row { background-color: #ffffff; padding-right: 60px !important; }
        
        .hierarchy-label {
            font-weight: 700;
            color: var(--main-color);
            margin-bottom: 4px;
            display: block;
        }

        .section-header {
            margin-top: 45px;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px 25px;
            border-radius: 8px;
            border-right: 6px solid var(--main-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        }

        .section-header h2 {
            margin: 0;
            font-size: 20px;
            color: var(--main-color);
            font-weight: 800;
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
            font-size: 14px;
            color: var(--main-color);
            display: block;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .data-value {
            font-size: 15px;
            color: var(--dark-text);
            font-weight: 500;
        }

        .summary-card {
            background-color: #f7fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .metrics-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 35px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 40px;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
        }

        .metric-item {
            min-width: 140px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 800;
            display: block;
        }

        .metric-label {
            font-size: 16px;
            opacity: 0.95;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .status-final { background-color: #c6f6d5; color: #22543d; }
        .status-draft { background-color: #feebc8; color: #744210; }

        @media print {
            body { background: white; }
            .page { 
                margin: 0; 
                box-shadow: none; 
                height: auto;
                min-height: 297mm;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="page">
        <div class="cover-header-bg"></div>
        
        <div class="cover-content">
            <div class="report-header-border">
                <div class="gov-title">الجمهورية اليمنية</div>
                <h1 class="org-title">اللجنة الزراعية والسمكية العليا</h1>
            </div>
            
            <div class="logo-container">
                <div class="logo-wrapper" style="box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #eee;">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار اللجنة الزراعية والسمكية العليا">
                </div>
            </div>

            <h2 style="color: var(--sec-color); font-size: 26px; margin: 20px 0; font-weight: 800;">وثيقة اعتماد مشروع</h2>

            <div class="project-details-bottom">
                <div class="project-name-main">{{ $project->project_name }}</div>

                <div class="applicant-info-card">
                    <span class="app-label">الجهة المقدمة للمشروع</span>
                    <span class="app-value">{{ $project->created_by_entity ?? optional($project->createdBy)->department ?? '-' }}</span>
                </div>

                <div class="cover-footer">
                    <div style="text-align: right;">
                        <div style="font-size: 13px; color: #777; font-weight: bold;">الرقم المرجعي</div>
                        <div style="font-weight: 800; font-size: 22px; color: var(--main-color);">{{ $project->form_number }}</div>
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
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}</div>
                <div style="font-size: 12px; color: #666;">كود المشروع الموحد: {{ $project->form_number }}</div>
            </div>
            <div class="header-qr-box">
                <div id="qrcode_header"></div>
            </div>
        </div>

        <div class="section-header">
            <h2>أولاً: السياق المؤسسي والبيانات الأساسية</h2>
        </div>

        <div class="metrics-grid">
            <div class="metric-item">
                <span class="metric-value">{{ number_format($project->cost->total_cost ?? 0) }}</span>
                <span class="metric-label">إجمالي التكلفة (ريال)</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">{{ $project->project_duration }}</span>
                <span class="metric-label">مدة المشروع (يوم)</span>
            </div>
            <div class="metric-item">
                <span class="metric-value">{{ number_format($project->number_of_beneficiaries) }}</span>
                <span class="metric-label">عدد المستفيدين</span>
            </div>
        </div>

        <div class="summary-card">
            <div class="data-grid">
                <div class="data-item">
                    <span class="data-label">البرنامج</span>
                    <span class="data-value">{{ $project->program->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">المجال / المجال الفرعي</span>
                    <span class="data-value">{{ $project->domain->name ?? '-' }} / {{ $project->subdomain->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">نوع التدخل</span>
                    <span class="data-value">{{ $project->intervention->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">الأولوية</span>
                    <span class="data-value">{{ $project->priority->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">حالة المشروع</span>
                    <span class="status-badge {{ $project->status === 'completed' ? 'status-final' : 'status-draft' }}">
                        {{ $project->status === 'completed' ? 'معتمد' : 'تحت المراجعة' }}
                    </span>
                </div>
                <div class="data-item">
                    <span class="data-label">تاريخ البداية (ميلادي)</span>
                    <span class="data-value">{{ $project->start_date_gregorian ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">تاريخ النهاية (ميلادي)</span>
                    <span class="data-value">{{ $project->end_date_gregorian ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">الجهة المستفيدة الرئيسية</span>
                    <span class="data-value">{{ $project->beneficiary_entity_name ?? '-' }}</span>
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
                    <td class="location-highlight">{{ $loc->governorate->name }}</td>
                    <td>{{ $loc->directorate->name }}</td>
                    <td>{{ $loc->subArea->name ?? '-' }} / {{ $loc->village->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($project->detail)
        <div class="section-header">
            <h2>ثالثاً: تفاصيل المشروع (المقدمة، المبررات، المكونات، الأثر)</h2>
        </div>
        <div class="summary-card">
            @if($project->detail->project_introduction)
            <div style="margin-bottom: 15px;">
                <span class="data-label">مقدمة المشروع</span>
                <p style="font-size: 14px; line-height: 1.6; text-align: justify;">{{ $project->detail->project_introduction }}</p>
            </div>
            @endif
            @if($project->detail->problem_and_justification)
            <div style="margin-bottom: 15px;">
                <span class="data-label">المشكلة والمبررات والاحتياج</span>
                <p style="font-size: 14px; line-height: 1.6; text-align: justify;">{{ $project->detail->problem_and_justification }}</p>
            </div>
            @endif
            @if($project->detail->project_summary)
            <div style="margin-bottom: 15px;">
                <span class="data-label">ملخص المشروع</span>
                <p style="font-size: 14px; line-height: 1.6; text-align: justify;">{{ $project->detail->project_summary }}</p>
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

            <div style="margin-top: 15px;">
                <span class="data-label">هل المشروع جزء من الخطة؟</span>
                <span class="data-value">{{ $project->detail->is_part_of_plan ? 'نعم' : 'لا' }}</span>
            </div>
        </div>
        @endif
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}</div>
                <div style="font-size: 12px; color: #666;">الأهداف الاستراتيجية وسلسلة النتائج</div>
            </div>
        </div>

        <div class="section-header">
            <h2>رابعاً: الأهداف العامة والخاصة وسلسلة النتائج</h2>
        </div>

        @if($project->mainObjectives->count() > 0)
        <div style="margin-bottom: 25px; background-color: #fef2f2; padding: 15px; border-right: 4px solid #b91c1c;">
            <span class="hierarchy-label" style="display: block; margin-bottom: 5px;">الهدف العام (Overall Objective):</span>
            @foreach($project->mainObjectives as $mainObj)
                <div style="font-size: 16px; font-weight: 700; color: #b91c1c;">- {{ $mainObj->objective }}</div>
            @endforeach
        </div>
        @endif

        @foreach($project->specialObjectives as $objIndex => $objective)
            <div style="margin-bottom: 30px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; page-break-inside: avoid;">
                <div style="background-color: var(--main-color); color: white; padding: 12px 20px;">
                    <span style="font-weight: 800; font-size: 16px;">الهدف الخاص {{ $objIndex + 1 }}:</span>
                    <span style="font-size: 16px;">{{ $objective->objective }}</span>
                </div>
                
                @foreach($objective->results as $resIndex => $result)
                    <div style="padding: 15px 20px; border-bottom: 1px solid #edf2f7; background-color: #f8fafc;">
                        <span class="hierarchy-label">النتيجة المتوقعة {{ $objIndex + 1 }}.{{ $resIndex + 1 }}:</span>
                        <div style="font-size: 14px; font-weight: 600;">{{ $result->result_text }}</div>
                        
                        <div style="margin-top: 10px; padding-right: 20px;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <thead>
                                    <tr style="text-align: right; color: var(--light-text); border-bottom: 1px solid #e2e8f0;">
                                        <th style="padding: 5px;">المخرجات (Outputs)</th>
                                        <th style="padding: 5px; width: 150px;">مؤشر القياس</th>
                                        <th style="padding: 5px; width: 100px;">المستهدف</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result->outputs as $output)
                                    <tr style="border-bottom: 1px dotted #e2e8f0;">
                                        <td style="padding: 8px 5px;">{{ $output->output_text }}</td>
                                        <td style="padding: 8px 5px;">{{ $output->indicator ?? '-' }}</td>
                                        <td style="padding: 8px 5px; font-weight: bold;">{{ $output->target ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}</div>
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
                    <th>وصف الخطر</th>
                    <th width="120">مستوى الخطر (0-10)</th>
                    <th>إجراءات الحد من المخاطر</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->risks as $index => $risk)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="font-weight: 700;">{{ $risk->risk }}</td>
                    <td style="text-align: center;">{{ $risk->risk_rate }}</td>
                    <td>{{ $risk->proposed_solution }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align: center; color: #999;">لا توجد مخاطر مسجلة</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-header">
            <h2>سادساً: الجهات المشرفة</h2>
        </div>
        <table class="modern-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>الجهة</th>
                    <th>الجهة الأب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->supervisingAuthorities as $entity)
                <tr>
                    <td>{{ $entity->authority->agency_name ?? ($entity->authority->name ?? '-') }}</td>
                    <td>{{ $entity->parent->agency_name ?? ($entity->parent->name ?? '-') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align: center; color: #light-text;">لا توجد جهات مشرفة</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-header">
            <h2>سابعاً: الجهات المشاركة</h2>
        </div>
        <table class="modern-table" style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>الجهة</th>
                    <th>الجهة الأب</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->participatingEntities as $entity)
                <tr>
                    <td>{{ $entity->authority->agency_name ?? ($entity->authority->name ?? '-') }}</td>
                    <td>{{ $entity->parent->agency_name ?? ($entity->parent->name ?? '-') }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align: center; color: #light-text;">لا توجد جهات مشاركة</td></tr>
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
                @if($entity->entity_type == 'internal')
                    <span class="badge bg-primary">داخلية</span>
                @else
                    <span class="badge bg-secondary">خارجية</span>
                @endif
            </td>
            <td>
                {{ $entity->entity_name }}
            </td>
            <td>
                @if($entity->entity_type == 'internal')
                    @php
                        // البحث عن الجهة الداخلية في المصفوفة
                        $internalEntity = null;
                        foreach($internalEntities ?? [] as $intEnt) {
                            if($intEnt['entity_name'] == $entity->authority_id) {
                                $internalEntity = $intEnt;
                                break;
                            }
                        }
                    @endphp
                    {{ $internalEntity['father_name'] ?? 'لا توجد جهة أب' }}
                @else
                    {{ $entity->parent_name }}
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
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}</div>
                <div style="font-size: 12px; color: #666;">الأنشطة المبدئية والتمهيدية</div>
            </div>
        </div>

        <div class="section-header">
            <h2>تاسعاً: الأنشطة المبدئية والتمهيدية</h2>
        </div>
        @foreach($project->preliminaryActivities as $activity)
            <div style="margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                <div style="background-color: #f1f5f1; padding: 10px 15px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">
                    {{ $activity->name }}
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead style="background-color: #fafafa;">
                        <tr style="text-align: right; color: #555;">
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">الإجراء</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">البند المالي</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0;">الوحدة</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">الكمية</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">السعر</th>
                            <th style="padding: 8px; border-bottom: 2px solid #e2e8f0; text-align: center;">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activity->procedures as $procedure)
                            @foreach($procedure->costs as $cost)
                            <tr style="border-bottom: 1px dotted #eee;">
                                <td style="padding: 8px;">{{ $procedure->procedure_name }}</td>
                                <td style="padding: 8px;">{{ $cost->financialItem->name ?? '-' }}</td>
                                <td style="padding: 8px;">{{ $cost->unit->name ?? '-' }}</td>
                                <td style="padding: 8px; text-align: center;">{{ $cost->quantity }}</td>
                                <td style="padding: 8px; text-align: center;">{{ number_format($cost->amount) }}</td>
                                <td style="padding: 8px; text-align: center; font-weight: 700; color: var(--main-color);">{{ number_format($cost->total) }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot style="background-color: #f8fafc;">
                        <tr>
                            <td colspan="5" style="padding: 10px; text-align: left; font-weight: 800;">إجمالي النشاط:</td>
                            <td style="padding: 10px; text-align: center; font-weight: 800; color: #b91c1c; font-size: 14px;">{{ number_format($activity->procedures->flatMap->costs->sum('total')) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

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
                <div style="background-color: #f1f5f1; padding: 10px 15px; font-weight: 700; border-bottom: 1px solid #e2e8f0;">
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
                                <td style="padding: 8px;" rowspan="{{ $costCount }}">{{ $action->action_name }}</td>
                                <td style="padding: 8px;" rowspan="{{ $costCount }}">
                                    @foreach($action->assignedEntities as $entity)
                                        <div style="font-weight: 600;">- {{ $entity->agency_name ?? ($entity->name ?? '-') }}</div>
                                        <div style="font-size: 10px; color: #666; margin-right: 10px;">(المهمة: {{ $entity->task ?? 'غير محددة' }})</div>
                                    @endforeach
                                </td>
                                @endif
                                <td style="padding: 8px;">{{ $cost->financialItem->name ?? '-' }}</td>
                                <td style="padding: 8px;">{{ $cost->unit->name ?? '-' }}</td>
                                <td style="padding: 8px; text-align: center;">{{ $cost->quantity }}</td>
                                <td style="padding: 8px; text-align: center;">{{ number_format($cost->amount) }}</td>
                                <td style="padding: 8px; text-align: center; font-weight: 700; color: var(--main-color);">{{ number_format($cost->total) }}</td>
                            </tr>
                            @endforeach
                            @if($costCount == 0)
                            <tr style="border-bottom: 1px dotted #eee;">
                                <td style="padding: 8px;">{{ $action->action_name }}</td>
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
                            <td colspan="6" style="padding: 10px; text-align: left; font-weight: 800;">إجمالي النشاط التنفيذي:</td>
                            <td style="padding: 10px; text-align: center; font-weight: 800; color: #b91c1c; font-size: 14px;">{{ number_format($activity->actions->flatMap->costs->sum('total')) }}</td>
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
                <h3 style="font-size: 16px; color: var(--main-color); border-bottom: 2px solid #eee; padding-bottom: 8px;">المجموعات المستهدفة</h3>
                <ul style="list-style: none; padding: 0;">
                    @foreach($project->beneficiaryGroups as $group)
                        <li style="padding: 10px; border-bottom: 1px dotted #eee; font-size: 14px;">- {{ $group->group_name }}</li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h3 style="font-size: 16px; color: var(--main-color); border-bottom: 2px solid #eee; padding-bottom: 8px;">الجهات المستفيدة</h3>
                <table class="modern-table" style="font-size: 12px;">
                    <thead>
                        <tr>
                            <th>الجهة</th>
                            <th>الجهة الأب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->beneficiaryEntities as $entity)
                        <tr>
                            <td>{{ $entity->authority->agency_name ?? ($entity->authority->name ?? '-') }}</td>
                            <td>{{ $entity->parent->agency_name ?? ($entity->parent->name ?? '-') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="page internal-padding">
        <div class="page-header">
            <div>
                <div style="font-weight: 800; font-size: 18px; color: var(--main-color);">{{ $project->project_name }}</div>
                <div style="font-size: 12px; color: #666;">تكلفة المشروع ومصادر التمويل</div>
            </div>
        </div>

        <div class="section-header">
            <h2>الثاني عشر: طبيعة التمويل</h2>
        </div>
        <div class="summary-card">
            <div class="data-grid">
                <div class="data-item">
                    <span class="data-label">نوع التمويل الغالب</span>
                    <span class="data-value">{{ $project->financings->first()->financingType->name ?? '-' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">مصدر التمويل الرئيسي</span>
                    <span class="data-value">{{ $project->financings->first()->fundingSource->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section-header">
            <h2>الثالث عشر: تكاليف المشروع وبيانات الاعتماد</h2>
        </div>

        @if($project->cost)
        <div class="summary-card" style="margin-bottom: 20px;">
            <div class="data-grid">
                <div class="data-item">
                    <span class="data-label">نوع السنة</span>
                    <span class="data-value">{{ $project->cost->year_type == 'hijri' ? 'هجري' : 'ميلادي' }}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">سنة الاعتماد</span>
                    <span class="data-value">{{ $project->cost->approval_year_gregorian ?? '-' }}</span>
                </div>
                @if($project->cost->approval_date_hijri)
                <div class="data-item">
                    <span class="data-label">تاريخ الاعتماد (هجري)</span>
                    <span class="data-value">{{ $project->cost->approval_date_hijri }}</span>
                </div>
                @endif
                <div class="data-item">
                    <span class="data-label">إجمالي تكلفة المشروع المعتمدة</span>
                    <span class="data-value" style="color: #b91c1c;">{{ number_format($project->cost->total_cost) }} ريال</span>
                </div>
            </div>
        </div>
        @endif

        <table class="modern-table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>المصدر</th>
                    <th>جهة التمويل</th>
                    <th>نوع التمويل</th>
                    <th>شكل التمويل / الفرعي</th>
                    <th>المبلغ (ريال)</th>
                    <th>النسبة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project->financings as $finance)
                <tr>
                    <td>{{ $finance->fundingSource->name ?? '-' }}</td>
                    <td>{{ $finance->authority->agency_name ?? ($finance->authority->name ?? '-') }}</td>
                    <td>{{ $finance->financingType->name ?? '-' }}</td>
                    <td>{{ $finance->financingForm->name ?? '-' }} / {{ $finance->subFinancingForm->name ?? '-' }}</td>
                    <td style="font-weight: 800; color: #b91c1c;">{{ number_format($finance->financing_amount) }}</td>
                    <td>{{ $finance->financing_percentage }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f1;">
                    <td colspan="4" style="text-align: left; font-weight: 800; font-size: 16px; padding: 15px;">إجمالي ميزانية المشروع المعلنة:</td>
                    <td colspan="2" style="font-weight: 800; color: #b91c1c; font-size: 20px; padding: 15px;">{{ number_format($totalProjectCost) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="section-header">
            <h2>الرابع عشر: الوثائق والمرفقات</h2>
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
        <div style="padding: 20px; text-align: center; color: #666; font-style: italic;">لا توجد وثائق مرفقة بهذا المشروع</div>
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

        <div style="margin-top: auto; text-align: center; font-size: 11px; color: #aaa; border-top: 1px solid #eee; padding-top: 15px;">
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
            colorDark : "#666666",  // رمادي
            colorLight : "#ffffff"   // أبيض
        });

        // QR رمادي للرأس
        new QRCode(document.getElementById("qrcode_header"), {
            text: "REF-{{ $project->form_number }}",
            width: 60,
            height: 60,
            colorDark : "#666666",  // رمادي
            colorLight : "#ffffff"   // أبيض
        });
        
        // إضافة تأثير تحويلة للصورة
        document.addEventListener('DOMContentLoaded', function() {
            const logoImg = document.querySelector('.logo-wrapper img');
            if (logoImg) {
                logoImg.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.transition = 'transform 0.3s ease';
                });
                logoImg.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
            
            // إضافة تأثير للمس QR (اختياري)
            const qrElements = document.querySelectorAll('#qrcode_cover, #qrcode_header');
            qrElements.forEach(qr => {
                qr.addEventListener('mouseenter', function() {
                    this.style.filter = 'brightness(0.95)';
                    this.style.transition = 'filter 0.3s ease';
                });
                qr.addEventListener('mouseleave', function() {
                    this.style.filter = 'brightness(1)';
                });
            });
        });
    </script>
</body>
</html>