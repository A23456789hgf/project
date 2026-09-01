<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('report_title', 'تقرير رسمي')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #ffffff;
            color: #1f2937;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .official-print-header {
            margin-bottom: 30px;
        }

        .table-print {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .table-print th,
        .table-print td {
            padding: 0.75rem;
            vertical-align: top;
            border: 1px solid #dee2e6;
            font-size: 13px;
        }

        .table-print thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
            background-color: #f8f9fa;
            font-weight: 700;
            color: #2c5f2d;
        }

        .text-primary-print {
            color: #2c5f2d !important;
        }
        
        .bg-primary-print {
            background-color: #2c5f2d !important;
            color: white !important;
        }

        /* Print Settings */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .avoid-break {
                page-break-inside: avoid;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body onload="window.print()">

    <div class="container-fluid py-4">
        
        <!-- Controls for non-print view -->
        <div class="d-flex justify-content-end mb-4 no-print gap-2">
            <button onclick="window.history.back()" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i> العودة
            </button>
            <button onclick="window.print()" class="btn btn-success" style="background-color: #2c5f2d; border-color: #2c5f2d;">
                <i class="fas fa-print me-1"></i> طباعة
            </button>
        </div>

        <!-- Official Print Header -->
        <div class="official-print-header">
            <table style="width: 100%; border-bottom: 2px solid #2c5f2d; padding-bottom: 10px; margin-bottom: 20px; direction: rtl; border-collapse: collapse;">
                <tr>
                    <td width="33%" style="text-align: right; vertical-align: top; border: none; padding: 0;">
                        <div style="font-size: 16px; font-weight: bold; color: #2c5f2d; font-family: 'Tajawal';">الجمهورية اليمنية</div>
                        <div style="font-size: 14px; color: #1f2937; font-family: 'Tajawal';">وزارة الزراعة والثروة السمكية والموارد المائية</div>
                    </td>
                    <td width="33%" style="text-align: center; vertical-align: middle; border: none; padding: 0;">
                        <img src="{{ asset('images/logo.png') }}" style="height: 90px;" alt="Logo">
                    </td>
                    <td width="33%" style="text-align: left; vertical-align: top; border: none; padding: 0;">
                        <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal';">التاريخ: {{ date('Y-m-d') }}</div>
                        <div style="font-size: 12px; color: #64748b; font-family: 'Tajawal'; mt-1">الموضوع: @yield('report_subject', 'تقرير رسمي')</div>
                    </td>
                </tr>
            </table>
            <div style="text-align: center; margin-bottom: 25px;">
                <h2 style="color: #2c5f2d; font-weight: 800; margin: 0; font-family: 'Tajawal';">@yield('report_title')</h2>
            </div>
        </div>

        <!-- Content -->
        @yield('content')
        
    </div>

    @stack('scripts')
</body>
</html>
