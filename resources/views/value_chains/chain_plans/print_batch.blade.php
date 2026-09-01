<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة شاملة - خطط السلاسل</title>
    <style>
        body { font-family: 'Tajawal', sans-serif; direction: rtl; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .footer { text-align: left; margin-top: 30px; font-size: 12px; color: #555; }
        @media print {
            button { display: none; }
            @page { size: landscape; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px;">طباعة الصفحة</button>

    <div class="header">
        <h2>طباعة شاملة - خطط السلاسل</h2>
        <p>تاريخ الطباعة: {{ $printDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>المحافظة</th>
                <th>المديرية</th>
                <th>السلسلة</th>
                <th>المجال</th>
                <th>المؤشر</th>
                <th>العدد</th>
                <th>التمويل</th>
                <th>مصدر التمويل</th>
                <th>الجهة المنفذة</th>
            </tr>
        </thead>
        <tbody>
            @php
                $formatAuthorities = function($value) {
                    if (empty($value) || $value === '-') return '-';
                    $ids = is_array($value) ? $value : (json_decode($value, true) ?: [$value]);
                    if (empty($ids)) return '-';
                    $names = \App\Models\Authority::whereIn('id', (array)$ids)->pluck('agency_name')->toArray();
                    return !empty($names) ? implode('، ', $names) : (is_array($value) ? implode('، ', $value) : $value);
                };
            @endphp
            @foreach($chainPlans as $plan)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $plan->governorate->name ?? '-' }}</td>
                <td>{{ $plan->directorate->name ?? '-' }}</td>
                <td>{{ $plan->valueChain->name ?? '-' }}</td>
                <td>{{ $plan->domain->name ?? '-' }}</td>
                <td>{{ $plan->indicator }}</td>
                <td>{{ $plan->number }}</td>
                <td>{{ $plan->financingType->name ?? '-' }}</td>
                <td>{{ $formatAuthorities($plan->funding_source_id) }}</td>
                <td>{{ $formatAuthorities($plan->authority_id ?: $plan->implementing_entity_id) }}</td>
            </tr>
            @endforeach
            @if($chainPlans->isEmpty())
            <tr>
                <td colspan="10" style="padding: 20px;">لا توجد بيانات متاحة</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        طُبع بواسطة نظام متابعة المشاريع
    </div>
</body>
</html>
