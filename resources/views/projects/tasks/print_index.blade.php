<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة مهام المشروع | {{ $project->project_name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0C5B47;
            --primary-light: #e6eeec;
            --border-dark: #222;
            --text-dark: #1a1a1a;
            --text-muted: #555;
        }

        @page {
            size: A4 landscape;
            margin: 20mm 15mm 20mm 15mm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background-color: white;
            }

            .no-print {
                display: none !important;
            }

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
        }

        body {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            direction: rtl;
            background: #f5f5f5;
            color: var(--text-dark);
            line-height: 1.5;
        }

        .print-container {
            background: white;
            width: 277mm;
            margin: 0 auto;
            padding: 5px;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-right,
        .header-left {
            width: 25%;
        }

        .header-center {
            width: 50%;
            text-align: center;
        }

        .header-right {
            font-weight: 700;
            font-size: 11pt;
            line-height: 1.6;
            text-align: center;
        }

        .header-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .logo {
            max-height: 80px;
            margin-bottom: 5px;
        }

        h1, h3 {
            font-size: 18pt;
            color: var(--primary);
            margin: 5px 0;
            font-weight: 800;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border: 1px solid var(--border-dark);
            margin-bottom: 15px;
        }

        .info-item {
            padding: 8px;
            border-left: 1px solid var(--border-dark);
            text-align: center;
        }

        .info-item:last-child {
            border-left: none;
        }

        .info-label {
            display: block;
            font-size: 9pt;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .info-value {
            display: block;
            font-size: 10.5pt;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        th {
            background-color: var(--primary) !important;
            color: white !important;
            border: 1px solid var(--border-dark);
            padding: 10px 4px;
            font-size: 9.5pt;
            text-align: center;
        }

        td {
            border: 1px solid var(--border-dark);
            padding: 7px 4px;
            font-size: 9pt;
            text-align: center;
            word-wrap: break-word;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .floating-actions {
            position: fixed;
            bottom: 25px;
            left: 25px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Cairo';
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: 0.3s;
        }

        .btn-print {
            background: var(--primary);
            color: white;
        }

        .btn-close {
            background: #555;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <div class="print-container">
        <div class="header-section">
            <div class="header-right">
                الجمهورية اليمنية<br>
                وزارة الزراعة والثروة السمكية والموارد المائية<br>
            </div>
            <div class="header-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo"><br>
                <h3>قائمة مهام المشروع</h3>
            </div>
            <div class="header-left">
                <span style="font-size: 10pt; margin-top: 5px; font-weight: 600;">تاريخ الطباعة: {{ now()->format('Y-m-d') }}</span>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">المشروع</span>
                <span class="info-value">{{ $project->project_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">الجهة المنفذة</span>
                <span class="info-value">{{ $project->implementingEntity->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">إجمالي المهام</span>
                <span class="info-value">{{ count($tasks) }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="5%">م</th>
                    <th width="25%">المهمة</th>
                    <th width="10%">الحالة</th>
                    <th width="10%">الأولوية</th>
                    <th width="15%">الجهة المرتبطة</th>
                    <th width="20%">المسند إليه</th>
                    <th width="15%">تاريخ الاستحقاق</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $index => $task)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: right;">
                            <div style="font-weight:bold; margin-bottom: 5px;">{{ $task->title }}</div>
                            <div style="font-size: 8pt; color: #555;">{{ $task->description }}</div>
                        </td>
                        <td>
                            @php
                                $statuses = [
                                    'todo' => 'قيد الانتظار',
                                    'in_progress' => 'قيد التنفيذ',
                                    'completed' => 'مكتملة',
                                    'cancelled' => 'ملغاة'
                                ];
                            @endphp
                            {{ $statuses[$task->status] ?? $task->status }}
                        </td>
                        <td>
                            @php
                                $priorities = [
                                    'low' => 'منخفضة',
                                    'medium' => 'متوسطة',
                                    'high' => 'عالية',
                                    'urgent' => 'عاجلة'
                                ];
                            @endphp
                            {{ $priorities[$task->priority] ?? $task->priority }}
                        </td>
                        <td>{{ $task->projectEntity->entity_name ?? '-' }}</td>
                        <td>
                            @if($task->assignees->isNotEmpty())
                                {{ $task->assignees->pluck('name')->join('، ') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">لا توجد مهام مطابقة للبحث</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 40px; text-align: center; font-size: 8pt; color: #777; border-top: 1px solid #eee; padding-top: 10px;">
            هذه الوثيقة صادرة عن النظام الإلكتروني لإدارة المشاريع والمهام - تاريخ الاستخراج: {{ now()->format('Y-m-d H:i') }}
        </div>
    </div>

    <div class="floating-actions no-print">
        <button onclick="window.print()" class="btn btn-print">طباعة الوثيقة</button>
        <button onclick="window.close()" class="btn btn-close">إغلاق</button>
    </div>

</body>
</html>