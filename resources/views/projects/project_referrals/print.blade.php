<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>وثيقة سجل حركات وإحالات المشروع</title>
    <style>
        /* تنسيق عام للوثيقة مع هوامش محسنة */
        body {
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 12pt;
            width: 29.7cm; /* عرض A4 بالعرض */
            height: 21cm; /* ارتفاع A4 بالطول */
            box-sizing: border-box;
        }

        /* إعدادات الصفحة للطباعة مع هوامش احترافية */
        @page {
            size: A4 landscape;
            
            /* هوامش رئيسية للصفحة */
            margin-top: 2.5cm;    /* هامش علوي أكبر للرأس الرسمي */
            margin-bottom: 2cm;   /* هامش سفلي للتوقيعات */
            margin-left: 1.5cm;   /* هامش أيسر */
            margin-right: 1.5cm;  /* هامش أيمن */
            
            /* رأس الصفحة (Header) */
            @top-left {
                content: "الجمهورية اليمنية";
                font-size: 10pt;
                font-weight: bold;
                vertical-align: bottom;
            }
            
            @top-center {
                content: "وثيقة رسمية - سرية";
                font-size: 9pt;
                color: #666;
            }
            
            @top-right {
                content: "الصفحة " counter(page) " من " counter(pages);
                font-size: 10pt;
            }
            
            /* تذييل الصفحة (Footer) */
            @bottom-left {
                content: "وزارة الزراعة والثروة السمكية والموارد المائية";
                font-size: 9pt;
                color: #666;
            }
            
            @bottom-center {
                content: "النظام الإلكتروني لإدارة المشاريع (EMIS)";
                font-size: 9pt;
            }
            
            @bottom-right {
                content: "تاريخ الطباعة: " string(print-date);
                font-size: 9pt;
            }
        }

        /* تعريف المتغيرات للتاريخ */
        @page {
            string-set: print-date "{{ date('Y/m/d H:i') }}";
        }

        /* مساحة العمل الفعلية مع هوامش داخلية */
        .page {
            padding: 0;
            width: 100%;
            height: 100%;
            
            /* هوامش داخلية للمحتوى */
            margin: 0 auto;
            padding-top: 0.5cm;
            padding-bottom: 0.5cm;
            
            /* منطقة آمنة للطباعة */
            box-sizing: border-box;
        }

        /* منطقة المحتوى الرئيسي مع هوامش */
        .content-area {
            width: 100%;
            height: 100%;
            padding: 0.5cm;
            box-sizing: border-box;
            position: relative;
        }

        /* منطقة الرأس مع هامش سفلي */
        .header-section {
            margin-bottom: 1cm;
            padding-bottom: 0.5cm;
            border-bottom: 2px solid #000;
        }

        /* منطقة المحتوى الرئيسي */
        .main-content {
            margin: 0.5cm 0;
            padding: 0.3cm;
        }

        /* منطقة التوقيعات مع هامش علوي */
        .signature-section {
            margin-top: 1cm;
            padding-top: 0.5cm;
            border-top: 1px solid #000;
            position: relative;
        }

        /* تنسيق الشعار */
        .logo-container {
            display: inline-block;
            vertical-align: top;
            margin-left: 0.5cm;
        }

        .logo-img {
            max-height: 80px; /* ارتفاع مناسب للشعار */
            max-width: 120px; /* عرض مناسب للشعار */
            height: auto;
            width: auto;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 3px;
            background: white;
        }

        /* تنسيق معلومات الوزارة مع الشعار */
        .ministry-info {
            display: inline-block;
            vertical-align: top;
            text-align: right;
            margin-top: 0.5cm;
        }

        /* الجداول مع هوامش داخلية محسنة */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.8cm;
            table-layout: fixed;
            word-wrap: break-word;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 0.4cm 0.3cm; /* هوامش داخلية للخلايا */
            text-align: center;
            vertical-align: middle;
            font-size: 11pt;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            padding: 0.5cm 0.3cm; /* هامش أكبر للرؤوس */
        }

        /* تحسين عرض النصوص الطويلة */
        td.long-text {
            text-align: right;
            padding: 0.3cm;
            line-height: 1.4;
        }

        /* هوامش للجداول الخاصة */
        .header-table {
            border: none;
            margin-bottom: 0.5cm;
        }

        .header-table td {
            border: none;
            padding: 0.3cm;
            vertical-align: top;
        }

        .signature-table {
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
        }

        .signature-table td {
            height: 2cm;
            text-align: center;
            vertical-align: bottom;
            padding: 0.5cm;
        }

        /* العناوين مع هوامش */
        .main-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin: 0.8cm 0;
            text-decoration: underline;
            padding: 0.3cm 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .section-title {
            text-align: right;
            font-size: 14pt;
            font-weight: bold;
            margin: 0.6cm 0 0.3cm 0;
            padding-right: 0.3cm;
            border-right: 3px solid #000;
            padding-bottom: 0.2cm;
        }

        /* هوامش للصفوف المميزة */
        .highlight-row {
            background-color: #f9f9f9;
            margin: 0.2cm 0;
        }

        /* هوامش للفواصل */
        .separator {
            height: 0.5cm;
            clear: both;
        }

        /* منطقة الختم مع هامش محدد */
        .stamp-area {
            margin-top: 1cm;
            padding: 0.5cm;
            text-align: center;
            border: 1px dashed #666;
            background-color: #fafafa;
            min-height: 2cm;
        }

        /* تصحيح عرض الأعمدة مع مراعاة الهوامش */
        .col-1 { width: 5%; }
        .col-2 { width: 12%; }
        .col-3 { width: 20%; }
        .col-4 { width: 20%; }
        .col-5 { width: 33%; }
        .col-6 { width: 10%; }

        /* تحسينات الطباعة */
        @media print {
            /* إخفاء عناصر الطباعة */
            .no-print {
                display: none !important;
            }
            
            /* ضمان بقاء الهوامش */
            body {
                margin: 0;
                padding: 0;
            }
            
            /* منع انقطاع الجداول بين الصفحات */
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            /* هوامش للصفحات المتعددة */
            .page-break {
                page-break-before: always;
                margin-top: 2cm;
            }
            
            /* تحسين عرض الشعار في الطباعة */
            .logo-img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        /* زر الطباعة (للعرض فقط) */
        .print-button {
            position: fixed;
            top: 1cm;
            right: 1cm;
            padding: 0.5cm 1cm;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 0.3cm;
            cursor: pointer;
            font-size: 12pt;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #0052a3;
        }

        /* تحسينات للعرض على الشاشة */
        @media screen {
            body {
                border: 1px solid #ccc;
                margin: 1cm auto;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            
            .print-button {
                display: block;
            }
            
            /* خطوط إرشادية للهوامش */
            .margin-guide {
                position: absolute;
                border: 1px dashed rgba(255,0,0,0.3);
                pointer-events: none;
            }
            
            .top-margin {
                top: 2.5cm;
                left: 1.5cm;
                right: 1.5cm;
                height: 0;
            }
            
            .bottom-margin {
                bottom: 2cm;
                left: 1.5cm;
                right: 1.5cm;
                height: 0;
            }
            
            .left-margin {
                left: 1.5cm;
                top: 2.5cm;
                bottom: 2cm;
                width: 0;
            }
            
            .right-margin {
                right: 1.5cm;
                top: 2.5cm;
                bottom: 2cm;
                width: 0;
            }
        }

        /* إعدادات الخطوط العربية */
        .arabic-text {
            font-family: 'Traditional Arabic', 'Times New Roman', serif;
            line-height: 1.6;
        }

        /* هوامش للنصوص العربية */
        .arabic-content {
            margin: 0.3cm 0;
            padding: 0.2cm;
            text-align: justify;
        }

        /* تخصيصات للطباعة بالأبيض والأسود */
        @media print and (color: 0) {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            th {
                background-color: #f5f5f5 !important;
            }
            
            .highlight-row {
                background-color: #f9f9f9 !important;
            }
            
            .logo-img {
                filter: grayscale(100%) !important;
                -webkit-filter: grayscale(100%) !important;
            }
        }
    </style>
</head>
<body>
    <!-- خطوط إرشادية للهوامش (تظهر فقط على الشاشة) -->
    <div class="margin-guide top-margin"></div>
    <div class="margin-guide bottom-margin"></div>
    <div class="margin-guide left-margin"></div>
    <div class="margin-guide right-margin"></div>

    <!-- زر الطباعة (للعرض فقط) -->
    <button class="print-button no-print" onclick="window.print()">🖨️ طباعة الوثيقة</button>

    <div class="page">
        <div class="content-area">
            <!-- رأس الوثيقة -->
            <div class="header-section">
                <table class="header-table">
                    <tr>
                        <td style="width: 40%;" class="text-end">
                            <div class="logo-container">
                                <!-- إضافة الشعار -->
                                <img src="{{ asset('images/logo.png') }}" 
                                     alt="شعار وزارة الزراعة والثروة السمكية والموارد المائية" 
                                     class="logo-img"
                                     onerror="this.style.display='none'">
                            </div>
                            <div class="ministry-info">
                                <strong>الجمهورية اليمنية</strong><br>
                                <strong>وزارة الزراعة والثروة السمكية والموارد المائية</strong><br>
                                <span style="font-size: 10pt;">الإدارة العامة للمشاريع والاستثمار</span><br>
                                <span style="font-size: 9pt; color: #666;">الرقم المرجعي: 01/2024</span>
                            </div>
                        </td>
                        <td style="width: 30%;" class="text-center">
                            <div style="font-size: 16pt; font-weight: bold; margin-bottom: 0.3cm;">
                                النظام الإلكتروني لإدارة المشاريع
                            </div>
                            <div style="font-size: 12pt; margin-bottom: 0.2cm;">
                                EMIS - Electronic Management Information System
                            </div>
                            <div style="font-size: 10pt; color: #666;">
                                نظام معتمد بقرار وزاري رقم 2024/1
                            </div>
                        </td>
                        <td style="width: 30%;" class="text-start">
                            <div style="font-size: 11pt; border-right: 2px solid #eee; padding-right: 0.5cm;">
                                <div style="margin-bottom: 0.3cm;">
                                    <strong>📋 رقم الوثيقة:</strong><br>
                                    <span style="font-family: monospace; font-size: 12pt;">
                                        {{ $referral->project->form_number }}/{{ date('Y') }}
                                    </span>
                                </div>
                                <div style="margin-bottom: 0.3cm;">
                                    <strong>📅 تاريخ الإصدار:</strong><br>
                                    {{ date('Y/m/d') }}
                                </div>
                                <div>
                                    <strong>🔄 نسخة الوثيقة:</strong><br>
                                    1.0 - النسخة الرسمية
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- العنوان الرئيسي -->
                <div class="main-title">وثيقة سجل حركات وإحالات المشروع</div>
                
                <!-- معلومات المشروع المختصرة -->
                <div style="text-align: center; margin: 0.3cm 0; font-size: 11pt;">
                    <span style="background: #f0f0f0; padding: 0.2cm 0.5cm; border-radius: 0.3cm;">
                        المشروع: <strong>{{ $referral->project->project_name }}</strong> | 
                        الرقم: <strong>{{ $referral->project->form_number }}</strong> | 
                        التاريخ: <strong>{{ $referral->created_at->format('Y/m/d') }}</strong>
                    </span>
                </div>
            </div>

            <!-- بيانات المشروع -->
            <div class="main-content">
                <div class="section-title">أولاً: بيانات المشروع الأساسية</div>
                <table>
                    <tr>
                        <th style="width: 20%;">اسم المشروع</th>
                        <td style="width: 30%; font-weight: bold; font-size: 12pt;">
                            {{ $referral->project->project_name }}
                        </td>
                        <th style="width: 20%;">رقم المشروع</th>
                        <td style="width: 30%; font-family: monospace; font-size: 11pt;">
                            {{ $referral->project->form_number }}
                        </td>
                    </tr>
                    <tr>
                        <th>الجهة المقدمة</th>
                        <td>{{ $referral->project->createdBy->entity->name ?? 'غير محدد' }}</td>
                        <th>المرحلة الحالية</th>
                        <td>{{ $referral->project->currentApprovalStage->name ?? 'غير محدد' }}</td>
                    </tr>
                    <tr>
                        <th>نوع الإحالة</th>
                        <td>
                            @if($referral->drop == 'approval')
                                <span style="font-weight: bold; color: #198754;">اعتماد رسمي</span>
                            @elseif($referral->drop == 'review')
                                <span style="font-weight: bold; color: #ffc107;">مراجعة فنية</span>
                            @elseif($referral->drop == 'consultation')
                                <span style="font-weight: bold; color: #0dcaf0;">استشارة تخصصية</span>
                            @else
                                <span style="font-weight: bold;">{{ $referral->drop }}</span>
                            @endif
                        </td>
                        <th>مرحلة الإحالة</th>
                        <td>{{ $referral->stage->name ?? 'غير محدد' }}</td>
                    </tr>
                    <tr>
                        <th>تاريخ بدء المشروع</th>
                        <td>{{ $referral->project->created_at->format('Y/m/d') }}</td>
                        <th>آخر تحديث</th>
                        <td>{{ $referral->project->updated_at->format('Y/m/d') }}</td>
                    </tr>
                    <tr>
                        <th>حالة المشروع</th>
                        <td>
                            @if($referral->project->status == 'active')
                                <span style="background: #198754; color: white; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    نشط
                                </span>
                            @elseif($referral->project->status == 'pending')
                                <span style="background: #ffc107; color: black; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    قيد المراجعة
                                </span>
                            @else
                                <span style="background: #6c757d; color: white; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    {{ $referral->project->status }}
                                </span>
                            @endif
                        </td>
                        <th>مستوى الأولوية</th>
                        <td>
                            @if($referral->project->priority == 'high')
                                <span style="background: #dc3545; color: white; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    عالي
                                </span>
                            @elseif($referral->project->priority == 'medium')
                                <span style="background: #ffc107; color: black; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    متوسط
                                </span>
                            @elseif($referral->project->priority == 'low')
                                <span style="background: #198754; color: white; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    منخفض
                                </span>
                            @else
                                <span style="background: #6c757d; color: white; padding: 0.1cm 0.3cm; border-radius: 0.2cm; font-weight: bold;">
                                    غير محدد
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="separator"></div>

                <!-- سجل الإحالات -->
                <div class="section-title">ثانياً: سجل حركات الإحالة والردود</div>
                <table>
                    <thead>
                        <tr>
                            <th class="col-1">#</th>
                            <th class="col-2">التاريخ والوقت</th>
                            <th class="col-3">الجهة المُحيلة</th>
                            <th class="col-4">الجهة المُستقبلة</th>
                            <th class="col-5">بيان الإحالة / الرد</th>
                            <th class="col-6">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($allReferrals as $index => $ref)
                        <!-- صف الإحالة -->
                        <tr class="highlight-row">
                            <td style="font-weight: bold; font-size: 11pt;">{{ $counter }}</td>
                            <td>
                                <div style="font-weight: bold;">{{ $ref->created_at->format('Y/m/d') }}</div>
                                <div style="font-size: 9pt; color: #666;">{{ $ref->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: bold; margin-bottom: 0.2cm;">{{ $ref->referringEntity->name }}</div>
                                /* <div style="font-size: 10pt; color: #555;">
                                    <span style="background: #e9ecef; padding: 0.1cm 0.3cm; border-radius: 0.2cm;">
                                        {{ $ref->referringUser->name }}
                                    </span>
                                </div> */
                            </td>
                            <td>
                                <div style="font-weight: bold; margin-bottom: 0.2cm;">{{ $ref->referredEntity->name }}</div>
                                <div style="font-size: 10pt; color: #555;">
                              
                                </div>
                            </td>
                            <td class="long-text arabic-content">
                            
                                <div style="text-align: justify; line-height: 1.5;">
                                    {{ $ref->referral_text }}
                                </div>
                                
                                @php
                                    $attachments = [];
                                    if ($ref->referral_attachments) {
                                        $attachments = json_decode($ref->referral_attachments, true);
                                    } elseif ($ref->referral_attachment) {
                                        $attachments = [$ref->referral_attachment];
                                    }
                                @endphp
                                
                                @if(!empty($attachments) && is_array($attachments))
                                    <div style="margin-top: 0.3cm; padding-top: 0.2cm; border-top: 1px dashed #ddd; font-size: 10pt; color: #666;">
                                        <strong>📎 المرفقات:</strong> 
                                        {{ count($attachments) }} ملف/ملفات
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="background: #ffc107; color: black; padding: 0.2cm 0.4cm; border-radius: 0.3cm; font-weight: bold; display: inline-block; min-width: 60px;">
                                    إحالة
                                </span>
                            </td>
                        </tr>
                        
                        @php $counter++; @endphp
                        
                        <!-- صف الرد -->
                        @if($ref->status !== 'pending')
                        <tr>
                            <td style="font-weight: bold; font-size: 11pt;">{{ $counter }}</td>
                            <td>
                                <div style="font-weight: bold;">{{ $ref->responded_at?->format('Y/m/d') ?? 'N/A' }}</div>
                                @if($ref->responded_at)
                                <div style="font-size: 9pt; color: #666;">{{ $ref->responded_at->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: bold; margin-bottom: 0.2cm;">{{ $ref->referredEntity->name }}</div>
                                <div style="font-size: 10pt; color: #555;">
                                    <span style="background: #e9ecef; padding: 0.1cm 0.3cm; border-radius: 0.2cm;">
                                        {{ $ref->respondingUser->name ?? 'غير محدد' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: bold; margin-bottom: 0.2cm;">{{ $ref->referringEntity->name }}</div>
                                <div style="font-size: 10pt; color: #555;">
                                    <span style="background: #e9ecef; padding: 0.1cm 0.3cm; border-radius: 0.2cm;">
                                        جهة المرسل
                                    </span>
                                </div>
                            </td>
                            <td class="long-text arabic-content">
                                <div style="margin-bottom: 0.3cm; padding-bottom: 0.2cm; border-bottom: 1px dashed #ddd;">
                                    <strong>📤 نص الرد الرسمي:</strong>
                                </div>
                                <div style="text-align: justify; line-height: 1.5;">
                                    {{ $ref->response_text }}
                                </div>
                                
                                @php
                                    $responseAttachments = [];
                                    if ($ref->response_attachments) {
                                        $responseAttachments = json_decode($ref->response_attachments, true);
                                    } elseif ($ref->response_attachment) {
                                        $responseAttachments = [$ref->response_attachment];
                                    }
                                @endphp
                                
                                @if(!empty($responseAttachments) && is_array($responseAttachments))
                                    <div style="margin-top: 0.3cm; padding-top: 0.2cm; border-top: 1px dashed #ddd; font-size: 10pt; color: #666;">
                                        <strong>📎 مرفقات الرد:</strong> 
                                        {{ count($responseAttachments) }} ملف/ملفات
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($ref->status === 'responded')
                                    <span style="background: #198754; color: white; padding: 0.2cm 0.4cm; border-radius: 0.3cm; font-weight: bold; display: inline-block; min-width: 60px;">
                                        تم الرد
                                    </span>
                                @else
                                    <span style="background: #dc3545; color: white; padding: 0.2cm 0.4cm; border-radius: 0.3cm; font-weight: bold; display: inline-block; min-width: 60px;">
                                        تم الإرجاع
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @php $counter++; @endphp
                        @endif
                        
                        <!-- فاصل بين الإحالات (ليس للإحالة الأخيرة) -->
                        @if(!$loop->last)
                        <tr>
                            <td colspan="6" style="border: none; height: 0.3cm; background: linear-gradient(to right, transparent, #f0f0f0, transparent);"></td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>

                <!-- ملخص الإحالات -->
                <div style="margin-top: 0.5cm; page-break-inside: avoid;">
                    <table style="width: 60%; margin-left: auto; background: #f8f9fa; border: 2px solid #dee2e6;">
                        <tr>
                            <th colspan="2" style="text-align: center; background: #e9ecef; font-size: 12pt; padding: 0.4cm;">
                                📊 ملخص الإحالات
                            </th>
                        </tr>
                        <tr>
                            <td style="width: 70%; padding: 0.3cm;">إجمالي عدد الإحالات</td>
                            <td style="width: 30%; text-align: center; font-weight: bold; font-size: 11pt;">
                                {{ $allReferrals->count() }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.3cm;">الإحالات المعلقة</td>
                            <td style="text-align: center; font-weight: bold; font-size: 11pt;">
                                <span style="color: #ffc107;">{{ $allReferrals->where('status', 'pending')->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.3cm;">الإحالات المجاب عليها</td>
                            <td style="text-align: center; font-weight: bold; font-size: 11pt;">
                                <span style="color: #198754;">{{ $allReferrals->where('status', 'responded')->count() }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.3cm;">الإحالات المعادة</td>
                            <td style="text-align: center; font-weight: bold; font-size: 11pt;">
                                <span style="color: #dc3545;">{{ $allReferrals->where('status', 'returned')->count() }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- التوقيعات -->
            <div class="signature-section">
                <div class="section-title">ثالثاً: التوقيعات والإجازات</div>
                
                <table class="signature-table">
                    <tr>
                        <td style="width: 33%;">
                            <div style="height: 2cm; border-bottom: 1px solid #000; position: relative;">
                                <div style="position: absolute; bottom: -1.5cm; right: 0; left: 0; text-align: center; font-size: 11pt;">
                                    <strong>إعداد / مـقرر اللجنة</strong><br>
                                    الاسم: ____________________<br>
                                    التوقيع: ____________________<br>
                                    التاريخ: ___/___/______
                                </div>
                            </div>
                        </td>
                        <td style="width: 34%;">
                            <div style="height: 2cm; border-bottom: 1px solid #000; position: relative;">
                                <div style="position: absolute; bottom: -1.5cm; right: 0; left: 0; text-align: center; font-size: 11pt;">
                                    <strong>مراجعة / مدير الإدارة</strong><br>
                                    الاسم: ____________________<br>
                                    التوقيع: ____________________<br>
                                    التاريخ: ___/___/______
                                </div>
                            </div>
                        </td>
                        <td style="width: 33%;">
                            <div style="height: 2cm; border-bottom: 1px solid #000; position: relative;">
                                <div style="position: absolute; bottom: -1.5cm; right: 0; left: 0; text-align: center; font-size: 11pt;">
                                    <strong>اعتماد / وكيل القطاع</strong><br>
                                    الاسم: ____________________<br>
                                    التوقيع: ____________________<br>
                                    التاريخ: ___/___/______
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- منطقة الختم -->
                <div class="stamp-area">
                    <div style="margin-bottom: 0.3cm;">
                        <strong>🔴 مكان الختم الرسمي للجهة 🔴</strong>
                    </div>
                    <div style="font-size: 10pt; color: #666;">
                        (يتم الختم هنا بعد التوقيعات مباشرة)<br>
                        يجب أن يكون الختم واضحاً ويغطي جزءاً من التوقيعات والتاريخ
                    </div>
                </div>

                <!-- معلومات الطباعة -->
                <div style="margin-top: 0.5cm; font-size: 9pt; color: #666; text-align: center; border-top: 1px solid #eee; padding-top: 0.3cm;">
                    <div style="margin-bottom: 0.2cm;">
                        تم إنشاء هذه الوثيقة تلقائيًا من النظام الإلكتروني لإدارة المشاريع (EMIS)
                    </div>
                    <div>
                        <strong>رقم المرجع الفني:</strong> SYS-{{ $referral->project->form_number }}-{{ date('Ymd') }} |
                        <strong>وقت الإنشاء:</strong> {{ date('H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // إضافة دعم لترقيم الصفحات
        document.addEventListener('DOMContentLoaded', function() {
            // التحقق من وجود الشعار
            const logoImg = document.querySelector('.logo-img');
            if (logoImg) {
                logoImg.onerror = function() {
                    // إذا فشل تحميل الشعار، عرض نص بديل
                    const logoContainer = document.querySelector('.logo-container');
                    logoContainer.innerHTML = '<div style="width: 120px; height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 10pt; color: #999;">شعار الوزارة</div>';
                };
            }
            
            // إضافة ترقيم الصفحات إذا كان هناك محتوى طويل
            const contentHeight = document.querySelector('.content-area').scrollHeight;
            const pageHeight = 21 * 37.8; // تحويل cm إلى pixels (تقريبي)
            
            if (contentHeight > pageHeight) {
                console.log('المحتوى قد يتطلب صفحات متعددة');
            }
            
            // تحسين الطباعة
            window.addEventListener('beforeprint', function() {
                document.title = "وثيقة سجل إحالات المشروع - {{ $referral->project->form_number }}";
                
                // إضافة تأكيد قبل الطباعة
                console.log('جاري تحضير الوثيقة للطباعة...');
            });
            
            window.addEventListener('afterprint', function() {
                console.log('تمت الطباعة بنجاح');
            });
        });
        
        // وظيفة الطباعة مع معالجة الأخطاء
        function printDocument() {
            try {
                // إضافة رسالة تحميل
                const printBtn = document.querySelector('.print-button');
                if (printBtn) {
                    const originalText = printBtn.innerHTML;
                    printBtn.innerHTML = '🔄 جاري التحضير للطباعة...';
                    printBtn.disabled = true;
                    
                    setTimeout(() => {
                        window.print();
                        printBtn.innerHTML = originalText;
                        printBtn.disabled = false;
                    }, 500);
                } else {
                    window.print();
                }
            } catch (error) {
                console.error('خطأ في الطباعة:', error);
                alert('حدث خطأ أثناء الطباعة. يرجى المحاولة مرة أخرى.');
            }
        }
    </script>
</body>
</html>