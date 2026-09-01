@extends('layouts.print')

@section('report_subject', 'تقرير مراجعة مصفوفة الصلاحيات')
@section('report_title', 'تقرير مراجعة مصفوفة الصلاحيات الشاملة')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي الصلاحيات</h6>
            <h4 class="fw-bold mb-0 text-primary-print">{{ $totalPermissions }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الثغرات المكتشفة</h6>
            <h4 class="fw-bold text-danger mb-0">{{ count($gaps) }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الأدوار المراجعة</h6>
            <h4 class="fw-bold text-success mb-0">{{ $totalRoles }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الوحدات النظامية</h6>
            <h4 class="fw-bold text-dark mb-0">{{ $moduleStats->count() }}</h4>
        </div>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3 text-primary-print">العمليات غير المحمية (صلاحيات مفقودة)</h5>
    @if(count($gaps) > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>المتحكم</th>
                <th>العملية</th>
                <th>الاسم المقترح للصلاحية</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gaps as $gap)
            <tr>
                <td class="text-start" dir="ltr"><small>{{ str_replace('App\\Http\\Controllers\\', '', $gap['controller']) }}</small></td>
                <td class="text-danger fw-bold" dir="ltr"><small>{{ $gap['method'] }}()</small></td>
                <td dir="ltr" class="text-info fw-bold">{{ $gap['suggested_name'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-center p-3 border rounded">
        <h6 class="text-success mb-0">لا توجد ثغرات مكتشفة. كود النظام محمي بالكامل.</h6>
    </div>
    @endif
</div>

<div class="mb-4">
    <h5 class="fw-bold mb-3 text-primary-print">توزيع الصلاحيات على الوحدات</h5>
    @if(count($moduleStats) > 0)
    <div class="row">
        @foreach($moduleStats as $module)
        <div class="col-3 mb-3">
            <div class="p-2 border rounded text-center">
                <div class="fw-bold text-secondary">{{ $module->module ?? 'بدون وحدة' }}</div>
                <div class="fw-bold text-primary-print fs-4">{{ $module->count }}</div>
                <small class="text-muted">صلاحية</small>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-center text-muted">لا توجد إحصائيات للوحدات</p>
    @endif
</div>

<div>
    <h5 class="fw-bold mb-3 text-primary-print">الأدوار المراجعة</h5>
    @if(count($roles) > 0)
    <table class="table-print text-center">
        <thead>
            <tr>
                <th>اسم الدور</th>
                <th>عدد الصلاحيات الممنوحة</th>
                <th>حالة الدور</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td class="text-start fw-bold">{{ $role->name }}</td>
                <td>{{ $role->permissions_count }}</td>
                <td>
                    @if($role->permissions_count === 0)
                        <span class="text-danger">فارغ</span>
                    @elseif($role->permissions_count > 50)
                        <span class="text-warning">صلاحيات واسعة</span>
                    @else
                        <span class="text-success">طبيعي</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-center text-muted">لا توجد أدوار مسجلة</p>
    @endif
</div>
@endsection
