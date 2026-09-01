<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="utf-8">
    <title>سجل العمليات</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>سجل العمليات</h1>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>المستخدم</th>
                <th>الجهة</th>
                <th>الإجراء</th>
                <th>القسم</th>
                <th>الوصف</th>
                <th>التاريخ والوقت</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->user_name ?? ($log->user->name ?? 'النظام') }}</td>
                <td>{{ $log->entity_name ?? '-' }}</td>
                <td>{{ $log->translated_action }}</td>
                <td>{{ $log->translated_module }}</td>
                <td>{{ $log->translated_description }}</td>
                <td>{{ $log->arabic_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        تم استخراج هذا التقرير من نظام إدارة المشاريع - {{ now()->year }}
    </div>
</body>
</html>
