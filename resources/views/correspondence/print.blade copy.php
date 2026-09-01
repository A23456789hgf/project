<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            direction: rtl;
            line-height: 1.6;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        
        /* Decorative Container */
        .page-container {
            position: relative;
            width: 210mm;
            height: 297mm;
            padding: 25mm 20mm;
            box-sizing: border-box;
        }

        /* Border */
        .page-border {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 2px double #2c3e50;
            pointer-events: none;
            z-index: 100;
        }

        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0, 0, 0, 0.03);
            white-space: nowrap;
            z-index: -1;
            font-weight: bold;
            pointer-events: none;
        }

        /* Header Section */
        .header-section {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header-column {
            display: table-cell;
            vertical-align: middle;
        }
        .header-right {
            text-align: right;
            width: 35%;
        }
        .header-center {
            text-align: center;
            width: 30%;
        }
        .header-left {
            text-align: left;
            width: 35%;
            font-size: 10pt;
            color: #34495e;
        }
        .logo {
            max-width: 100px;
            height: auto;
        }
        .institution-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .sub-institution {
            font-size: 11pt;
            color: #7f8c8d;
        }

        /* Correspondence Metadata */
        .metadata-line {
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
            margin-left: 5px;
        }

        /* Addressing */
        .addressing-section {
            margin-top: 30px;
            margin-bottom: 30px;
            font-size: 12pt;
        }
        .recipient-name {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        .greeting {
            margin-top: 15px;
            font-style: italic;
        }

        /* Subject */
        .subject-box {
            background-color: #f8f9fa;
            border-right: 5px solid #2c3e50;
            padding: 10px 15px;
            margin: 25px 0;
            font-weight: bold;
            font-size: 12pt;
            text-align: center;
        }

        /* Main Content */
        .content-area {
            font-size: 12pt;
            text-align: justify;
            min-height: 400px;
            line-height: 1.8;
            color: #2c3e50;
        }

        /* Signature Section */
        .signature-container {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sig-placeholder {
            margin-top: 20px;
            border-bottom: 1px dotted #95a5a6;
            width: 200px;
            display: inline-block;
        }

        /* QR Code */
        .qr-box {
            display: table-cell;
            width: 50%;
            text-align: left;
            vertical-align: bottom;
        }
        .qr-image {
            width: 80px;
            height: 80px;
            border: 1px solid #ecf0f1;
            padding: 5px;
        }
        .qr-text {
            font-size: 8pt;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* Footer */
        .footer-strip {
            position: absolute;
            bottom: 25mm;
            left: 20mm;
            right: 20mm;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
            font-size: 9pt;
            color: #95a5a6;
            text-align: center;
        }
        .page-number {
            float: left;
        }
        .system-info {
            float: right;
        }
        .clear { clear: both; }

        /* Notes Section */
        .notes-section {
            margin-top: 40px;
            padding: 10px;
            background-color: #fff9e6;
            border: 1px dashed #f1c40f;
            font-size: 10pt;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="page-border"></div>
    <div class="page-container">
        <div class="watermark">اللجنة الزراعية</div>

        <!-- Header -->
        <div class="header-section">
            <div class="header-column header-right">
                <div class="institution-name">الجمهورية اليمنية</div>
                <div class="institution-name">اللجنة الزراعية والسمكية العليا</div>
                <div class="sub-institution">مكتب رئيس اللجنة</div>
            </div>
            <div class="header-column header-center">
                @if(file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="logo">
                @else
                    <div style="font-weight: bold; color: #2c3e50;">LOGO</div>
                @endif
            </div>
            <div class="header-column header-left">
                <div><span class="label">الرقم المرجعي:</span> {{ $correspondence->correspondence_number }}</div>
                <div><span class="label">التاريخ:</span> {{ now()->format('Y/m/d') }}مـ</div>
                <div><span class="label">المرفقات:</span> ...........</div>
            </div>
        </div>

        <!-- Addressing -->
        <div class="addressing-section">
            <div class="recipient-name">الأخ/ {{ $correspondence->recipientEntity->name }} <span style="float: left;">المحترم</span></div>
            <div class="greeting">بعد التحية والتقدير،،،</div>
        </div>

        <!-- Subject -->
        <div class="subject-box">
            الموضوع: {{ $correspondence->subject }}
        </div>

        <!-- Content -->
        <div class="content-area">
            {!! nl2br(e($correspondence->message_body)) !!}
        </div>

        <!-- Signature & QR -->
        <div class="signature-container">
            <div class="qr-box">
                <img src="{{ $qrCodeData }}" class="qr-image">
                <div class="qr-text">رقم التحقق الأصلي: {{ $correspondence->id }}</div>
            </div>
            <div class="signature-box">
                <div style="font-weight: bold; margin-bottom: 10px;">يعتمد،،،</div>
                <div style="font-weight: bold; color: #34495e;">ختم وتوقيع الجهة المصدرة</div>
                <div class="sig-placeholder"></div>
            </div>
        </div>

        <!-- Final Greeting -->
        <div style="text-align: center; margin-top: 30px; font-weight: bold;">
            وتقبلوا خالص التحية؛؛؛
        </div>

        <!-- Optional Notes -->
        @if($correspondence->notes)
            <div class="notes-section">
                <strong>ملاحظات إضافية:</strong> {{ $correspondence->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer-strip">
            <div class="system-info">نظام الأتمتة الموحد - الجهة المصدرة: {{ $correspondence->senderEntity->name }}</div>
            <div class="page-number">صفحة 1 من 1</div>
            <div class="clear"></div>
            <div style="margin-top: 5px; font-size: 8pt;">تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }} | المستخدم: {{ auth()->user()->name }}</div>
        </div>
    </div>
</body>
</html>