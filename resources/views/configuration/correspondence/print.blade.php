<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المراسلة | {{ $correspondence->correspondence_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0C5B47;
            --primary-light: #e6eeec;
            --border-dark: #222;
            --text-dark: #1a1a1a;
            --text-muted: #555;
        }

        /* --- إعدادات الطباعة والهوامش --- */
        @page {
            size: A4 portrait;
            margin: 0mm 10mm 0mm 10mm; /* top=0, right=10, bottom=0, left=10 */
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                width: 100%;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }

        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            direction: rtl;
            background: #f5f5f5;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .print-container {
            background: white;
            width: 210mm;
            margin: 0 auto;
            padding: 0mm 15mm;
            min-height: 297mm;
            position: relative;
            box-sizing: border-box;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* --- الترويسة (Header) --- */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-right, .header-left { width: 30%; }
        .header-center { width: 40%; text-align: center; }

        .header-right { font-weight: 700; font-size: 10pt; line-height: 1.6; }
        .header-left { display: flex; flex-direction: column; align-items: flex-end; }

        .qr-code-img { width: 70px; height: 70px; border: 1px solid #ddd; padding: 2px; background: #fff; }
        .logo { max-height: 60px; margin-bottom: 5px; }

        h1 {
            font-size: 14pt;
            color: var(--primary);
            margin: 5px 0;
            font-weight: 800;
        }

        .number-badge {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 5px;
            font-weight: 700;
            display: inline-block;
            margin-top: 5px;
            border: 1px solid var(--primary);
            font-size: 10pt;
        }

        /* --- شبكة المعلومات --- */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            border: 1px solid var(--border-dark);
            margin-bottom: 20px;
        }

        .info-item {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border-dark);
            border-left: 1px solid var(--border-dark);
            display: flex;
            justify-content: space-between;
        }

        .info-item:nth-child(even) { border-left: none; }
        .info-item:nth-last-child(-n+2) { border-bottom: none; }

        .info-label { font-size: 9pt; color: var(--text-muted); font-weight: 600; }
        .info-value { font-size: 10pt; font-weight: 700; }

        /* --- محتوى المراسلة --- */
        .message-box {
            border: 1px solid var(--border-dark);
            padding: 15px;
            min-height: 250px;
            margin-bottom: 20px;
        }

        .message-header {
            background: var(--primary-light);
            color: var(--primary);
            padding: 5px 10px;
            font-weight: 800;
            border-bottom: 1px solid var(--primary);
            margin: -15px -15px 15px -15px;
            font-size: 11pt;
        }

        /* --- سجل الحركة (Movement Table) --- */
        .movement-section { margin-bottom: 30px; }
        .section-title {
            background: var(--primary);
            color: white;
            padding: 5px 10px;
            font-weight: 800;
            font-size: 10pt;
            margin-bottom: 10px;
            text-align: center;
        }

        table { width: 100%; border-collapse: collapse; font-size: 9pt; }
        th {
            background-color: #f2f2f2 !important;
            color: var(--text-dark) !important;
            border: 1px solid var(--border-dark);
            padding: 8px;
            text-align: center;
            font-weight: 800;
        }
        td { border: 1px solid var(--border-dark); padding: 6px; text-align: center; }
        .text-end { text-align: right; }

        /* --- إخفاء التوقيعات عند الطباعة --- */
        .footer-signatures { display: flex; justify-content: space-between; margin-top: 40px; padding: 0 10px; }
        @media print { .footer-signatures { display: none !important; } }

        /* --- أزرار التحكم --- */
        .floating-actions {
            position: fixed;
            bottom: 25px;
            left: 25px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Cairo';
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: 0.3s;
        }

        .btn-print { background: var(--primary); color: white; }
        .btn-close { background: #555; color: white; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 50pt;
            color: rgba(0, 0, 0, 0.02);
            font-weight: 800;
            z-index: 0;
            pointer-events: none;
            white-space: nowrap;
        }
    </style>
</head>
<body>

<div class="print-container">
    <div class="watermark">اللجنة الزراعية والسمكية العليا</div>
    
    <div class="header-section">
        <div class="header-right">
            الجمهورية اليمنية<br>
            اللجنة الزراعية والسمكية العليا<br>
            @(isset($correspondence->senderEntity) ? $correspondence->senderEntity->name : 'مكتب رئيس اللجنة')
        </div>
        
        <div class="header-center">
            @if(isset($logoPath) && file_exists($logoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="logo"><br>
            @else
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo"><br>
            @endif
            <h1>تفاصيل المراسلة الرسمية</h1>
            <div class="number-badge">رقم المراسلة: {{ $correspondence->correspondence_number }}</div>
        </div>

        <div class="header-left">
            <img src="{{ $qrCodeData }}" alt="Verification QR" class="qr-code-img">
            <span style="font-size: 7pt; margin-top: 5px; font-weight: 600;">رمز التحقق الرقمي</span>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">من:</span>
            <span class="info-value">{{ $correspondence->senderEntity->name }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">إلى:</span>
            <span class="info-value">{{ $correspondence->recipientEntity->name }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">التاريخ:</span>
            <span class="info-value">{{ $correspondence->sent_at ? $correspondence->sent_at->format('Y/m/d') : $correspondence->created_at->format('Y/m/d') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">الأولوية:</span>
            <span class="info-value">
                {{ $correspondence->priority_label }} 
                @if($correspondence->confidential) - <span style="color:red">سري</span> @endif
            </span>
        </div>
    </div>

    <div class="message-box">
        <div class="message-header">الموضوع: {{ $correspondence->subject }}</div>
        <div style="white-space: pre-wrap; position: relative; z-index: 1; font-size: 11pt;">{!! nl2br(e($correspondence->message_body)) !!}</div>
        
        @if($correspondence->notes)
            <div style="margin-top: 20px; padding-top: 10px; border-top: 1px dashed var(--text-muted); font-size: 9pt; color: var(--text-muted);">
                <strong>ملاحظات:</strong> {{ $correspondence->notes }}
            </div>
        @endif
    </div>
    
    <div class="movement-section">
        <div class="section-title">سجل حركة المراسلة</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">م</th>
                    <th width="20%">التاريخ والوقت</th>
                    <th width="20%">المستخدم</th>
                    <th width="55%">الإجراء المتخذ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $index => $movement)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $movement->action_date ? $movement->action_date->format('Y/m/d H:i') : '' }}</td>
                    <td>{{ $movement->user->name ?? 'النظام' }}</td>
                    <td class="text-end">{{ $movement->action_description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- توقيعات مخفية أثناء الطباعة -->
    <div class="footer-signatures no-print">
        <div class="sig-box">
            <span>توقيع المرسل</span>
            <div class="sig-space">{{ $correspondence->senderUser->name }}</div>
        </div>
        <div class="sig-box">
            <span>توقيع المستلم</span>
            <div class="sig-space">......................</div>
        </div>
        <div class="sig-box">
            <span>اعتماد الختم</span>
            <div class="sig-space">رسمي</div>
        </div>
    </div>

    <div style="position: absolute; bottom: 10mm; left: 10mm; right: 10mm; text-align: center; font-size: 8pt; color: #777; border-top: 1px solid #eee; padding-top: 5px;">
        نظام المراسلات الإلكتروني - تاريخ الاستخراج: {{ $printDate ?? now()->format('Y-m-d H:i') }}
    </div>
</div>

<div class="floating-actions no-print">
    <button onclick="window.print()" class="btn btn-print">طباعة الوثيقة</button>
    <button onclick="window.close()" class="btn btn-close">إغلاق</button>
</div>

</body>
</html>