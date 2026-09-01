<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $project->project_name }}</title>
    <style>
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 25px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
            color: #222;
        }

        /* جعل كل سطر يحتوي على عنصرين */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 30px; /* مسافات بين العناصر */
        }

        .info-item {
            width: calc(50% - 15px); /* عنصرين في كل صف */
            box-sizing: border-box;
        }

        .info-item p {
            margin: 4px 0;
        }

        /* تحسين شكل العناوين */
        h2 {
            border-right: 4px solid #007b83;
            padding-right: 8px;
            color: #007b83;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $project->project_name }}</h1>
        <p>رقم النموذج: {{ $project->form_number }}</p>
    </div>

    <div class="section">
        <h2>معلومات أساسية</h2>

        <p><span class="label">الحالة:</span> 
            @if($project->status === 'draft')
                مسودة
            @elseif($project->status === 'final')
                نهائي
            @else
                {{ $project->status }}
            @endif
        </p>

        <div class="info-grid">
            <div class="info-item">
                <p><span class="label">عدد المستفيدين:</span> {{ number_format($project->number_of_beneficiaries) }}</p>
            </div>
            <div class="info-item">
                <p><span class="label">فئات المستفيدين:</span> {{ $project->beneficiary_categories ?? 'غير محدد' }}</p>
            </div>

            <div class="info-item">
                <p><span class="label">تاريخ البدء:</span> {{ $project->start_date_gregorian ?? 'غير محدد' }}</p>
            </div>
            <div class="info-item">
                <p><span class="label">تاريخ الانتهاء:</span> {{ $project->end_date_gregorian ?? 'غير محدد' }}</p>
            </div>

            <div class="info-item">
                <p><span class="label">الأولوية:</span> 
                    {{ $project->priority === 'high' ? 'عالية' : ($project->priority === 'medium' ? 'متوسطة' : 'منخفضة') }}
                </p>
            </div>
            <div class="info-item">
                <p><span class="label">تاريخ الإنشاء:</span> {{ $project->created_at->format('Y-m-d') }}</p>
            </div>
        </div>
    </div>

    @if($project->detail)
    <div class="section">
        <h2>تفاصيل المشروع</h2>
        <p><span class="label">ملخص المشروع:</span> {{ $project->detail->project_summary ?? 'لم يتم إضافة ملخص بعد' }}</p>
        <p><span class="label">المشكلة والمبررات:</span> {{ $project->detail->problem_and_justification ?? 'لم يتم إضافة المشكلة والمبررات بعد' }}</p>
        <p><span class="label">النتائج المتوقعة:</span> {{ $project->detail->expected_results ?? 'لم يتم إضافة النتائج المتوقعة بعد' }}</p>
        <p><span class="label">المخرجات المتوقعة:</span> {{ $project->detail->expected_outputs ?? 'لم يتم إضافة المخرجات المتوقعة بعد' }}</p>
        <p><span class="label">مكونات المشروع:</span> {{ $project->detail->project_components ?? 'لم يتم إضافة مكونات المشروع بعد' }}</p>
        <p><span class="label">الأثر المتوقع:</span> {{ $project->detail->expected_impact ?? 'لم يتم إضافة الأثر المتوقع بعد' }}</p>
        <p><span class="label">جزء من خطة أكبر:</span> {{ $project->detail->is_part_of_plan ? 'نعم' : 'لا' }}</p>
    </div>
    @endif

    <div class="footer">
        تم إنشاء هذا التقرير في: {{ now()->format('Y-m-d H:i:s') }}<br>
        وزارة الزراعة والثروة السمكية والموارد المائية
    </div>
</body>
</html>
