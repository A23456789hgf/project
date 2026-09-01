<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملاحظات وحركة المشروع | {{ $project->project_name ?? 'اعتماد رسمي' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --main-color: #2c5f2d;
            --sec-color: #97bc62;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --border-color: #e2e8f0;
            --bg-color: #ffffff;
        }

        @page {
            size: A4;
            margin: 0;
        }

        html {
            direction: rtl;
        }

        body {
            font-family: 'Tajawal', 'Cairo', 'Segoe UI', Tahoma, Arial, sans-serif;
            background: #e5e7eb;
            margin: 0;
            padding: 20px 0;
            direction: rtl;
            text-align: right;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 10mm auto;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            page-break-after: always;
            page-break-inside: avoid;
            direction: rtl;
            overflow: hidden;
        }

        .internal-padding {
            padding: 40px 40px 60px 40px;
            flex: 1;
            display: flex;
            flex-direction: column;
            direction: rtl;
        }

        /* ===== الأنماط الداخلية ===== */
        .internal-title {
            border-bottom: 3px solid var(--main-color);
            padding-bottom: 12px;
            margin-bottom: 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            direction: rtl;
        }

        .internal-title h1 {
            font-size: 24px;
            color: var(--main-color);
            font-weight: 800;
            margin: 0;
            text-align: right;
        }

        .project-code-light {
            font-size: 15px;
            color: #8ba888;
            font-weight: 600;
        }

        .section-header {
            margin-top: 30px;
            margin-bottom: 20px;
            border-right: 5px solid var(--main-color);
            padding-right: 15px;
        }

        .section-header h2 {
            font-size: 20px;
            color: #1f2937;
        }

        /* ===== تفاصيل الملاحظات ===== */
        .detail-block {
            background-color: #f9fbf8;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-right: 4px solid var(--main-color);
        }

        .detail-block h6 {
            font-size: 16px;
            color: var(--main-color);
            font-weight: 700;
            margin-bottom: 12px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 8px;
        }

        .review-notes {
            margin-bottom: 15px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid var(--border-color);
        }

        .review-notes.financial {
            border-right: 4px solid #17a2b8;
        }

        .review-notes.technical {
            border-right: 4px solid #e83e8c;
        }

        .review-notes h6 {
            border: none;
            padding: 0;
            margin-bottom: 8px;
            font-size: 15px;
        }

        .financial h6 { color: #17a2b8; }
        .technical h6 { color: #e83e8c; }

        .review-notes p {
            font-size: 14.5px;
            color: #2d3748;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        .review-notes small {
            color: #64748b;
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            color: #64748b;
            padding: 30px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            margin-top: 20px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .page {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }
            .detail-block {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="page internal-padding">
        
        <!-- عنوان الصفحة الرئيسي -->
        <div class="internal-title">
            <h1>المراجعات وحركة المشروع</h1>
            <span class="project-code-light">{{ $project->form_number ?? 'الرقم المرجعي' }}</span>
        </div>

        <!-- المراجعات المالية والفنية -->
        <div class="section-header">
            <h2>أولاً: المراجعات المالية والفنية</h2>
        </div>

        @if($project->projectApprovals && $project->projectApprovals->whereIn('status', ['financial_review', 'approved', 'rejected'])->count() > 0)
            @foreach($project->projectApprovals->whereIn('status', ['financial_review', 'approved', 'rejected']) as $approval)
                @if($approval->financial_review_notes || $approval->technical_review_notes)
                    <div class="detail-block">
                        <h6>
                            مرحلة: {{ $approval->stage->name_ar ?? 'مرحلة غير محددة' }}
                        </h6>
                        <div style="margin-bottom: 15px; font-size: 15px;">
                            <strong>جهة المراجعة:</strong> {{ $approval->entity->name ?? 'الجهة غير محددة' }}
                        </div>

                        @if($approval->financial_review_notes)
                            <div class="review-notes financial">
                                <h6>الملاحظات المالية:</h6>
                                <p>{{ $approval->financial_review_notes }}</p>
                                @if($approval->financialReviewer)
                                    <small>بواسطة: {{ $approval->financialReviewer->name }}</small>
                                @endif
                            </div>
                        @endif

                        @if($approval->technical_review_notes)
                            <div class="review-notes technical">
                                <h6>الملاحظات الفنية:</h6>
                                <p>{{ $approval->technical_review_notes }}</p>
                                @if($approval->technicalReviewer)
                                    <small>بواسطة: {{ $approval->technicalReviewer->name }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        @else
            <div class="empty-state">لا توجد ملاحظات مالية أو فنية مسجلة حتى الآن.</div>
        @endif

        <!-- سجل حركة المشروع -->
        <div class="section-header" style="margin-top: 40px;">
            <h2>ثانياً: سجل حركة المشروع تفصيلياً</h2>
        </div>

        @include('projects.partials.activity-history-display', ['is_print' => true])
    </div>
</body>

</html>