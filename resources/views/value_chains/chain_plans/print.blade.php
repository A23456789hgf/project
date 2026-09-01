<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة خطة السلسلة</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; direction: rtl; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table th, .info-table td { border: 1px solid #ddd; padding: 12px; text-align: right; }
        .info-table th { background-color: #f5f5f5; width: 30%; font-weight: bold; }
        .footer { text-align: left; margin-top: 30px; font-size: 12px; color: #555; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px;">طباعة</button>

    <div class="header">
        <h2>تفاصيل خطة السلسلة</h2>
        <p>تاريخ الطباعة: {{ $printDate }}</p>
    </div>

    <table class="info-table">
        <tr>
            <th>المحافظة</th>
            <td>{{ $chainPlan->governorate->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>المديرية</th>
            <td>{{ $chainPlan->directorate->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>السلسلة</th>
            <td>{{ $chainPlan->valueChain->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>المجال</th>
            <td>{{ $chainPlan->domain->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>المؤشر</th>
            <td>{{ $chainPlan->indicator }}</td>
        </tr>
        <tr>
            <th>العدد</th>
            <td>{{ $chainPlan->number }}</td>
        </tr>
        @php
            $formatAuthorities = function($value) {
                if (empty($value) || $value === '-') return '-';
                $ids = is_array($value) ? $value : (json_decode($value, true) ?: [$value]);
                if (empty($ids)) return '-';
                $names = \App\Models\Authority::whereIn('id', (array)$ids)->pluck('agency_name')->toArray();
                return !empty($names) ? implode('، ', $names) : (is_array($value) ? implode('، ', $value) : $value);
            };
        @endphp
        <tr>
            <th>نوع التمويل</th>
            <td>{{ $chainPlan->financingType->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>مصدر التمويل</th>
            <td>{{ $formatAuthorities($chainPlan->funding_source_id) }}</td>
        </tr>
        <tr>
            <th>الجهة المنفذة</th>
            <td>{{ $formatAuthorities($chainPlan->authority_id) }}</td>
        </tr>
        <tr>
            <th>الجهة المشرفة</th>
            <td>{{ $formatAuthorities($chainPlan->implementing_entity_id) }}</td>
        </tr>
    </table>

    <div class="footer">
        طُبع بواسطة نظام متابعة المشاريع
    </div>
</body>
</html>
