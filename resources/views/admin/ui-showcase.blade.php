@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 bg-white p-4 rounded-lg shadow-sm">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="fas fa-palette text-primary me-2"></i>
                معرض مكونات نظام التصميم الموحد (UI Design System Showcase)
            </h2>
            <p class="text-muted mb-0 small">استعراض تفاعلي كامل لكافة المكونات الموحدة في بيئة RTL ومطابقة لجميع الشاشات.</p>
        </div>
        <div>
            <x-ui.badge variant="success" icon="fas fa-check-circle">الإصدار 1.0 - Phase 1</x-ui.badge>
        </div>
    </div>

    <!-- 1. Buttons Showcase -->
    <x-ui.card title="1. الأزرار (Buttons)" icon="fas fa-mouse-pointer" hover="true">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <x-ui.button variant="primary" icon="fas fa-save">حفظ البيانات</x-ui.button>
            <x-ui.button variant="secondary" icon="fas fa-filter">تصفية</x-ui.button>
            <x-ui.button variant="success" icon="fas fa-file-excel">تصدير Excel</x-ui.button>
            <x-ui.button variant="danger" icon="fas fa-trash">حذف</x-ui.button>
            <x-ui.button variant="warning" icon="fas fa-edit">تعديل</x-ui.button>
            <x-ui.button variant="outline-primary" icon="fas fa-sync">تحديث</x-ui.button>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <x-ui.button variant="primary" size="sm">زر صغير (Small)</x-ui.button>
            <x-ui.button variant="primary" size="md">زر متوسط (Medium)</x-ui.button>
            <x-ui.button variant="primary" size="lg">زر كبير (Large)</x-ui.button>
        </div>
    </x-ui.card>

    <!-- 2. Badges & Alerts Showcase -->
    <div class="row">
        <div class="col-md-6">
            <x-ui.card title="2. الشارات (Badges)" icon="fas fa-tags">
                <div class="d-flex flex-wrap gap-2">
                    <x-ui.badge variant="success" icon="fas fa-check">مكتمل</x-ui.badge>
                    <x-ui.badge variant="warning" icon="fas fa-clock">قيد الانتظار</x-ui.badge>
                    <x-ui.badge variant="danger" icon="fas fa-times">مرفوض</x-ui.badge>
                    <x-ui.badge variant="info" icon="fas fa-info-circle">نشط</x-ui.badge>
                </div>
            </x-ui.card>
        </div>
        <div class="col-md-6">
            <x-ui.card title="3. التنبيهات (Alerts)" icon="fas fa-bell">
                <x-ui.alert type="success" :dismissible="true">تمت العملية بنجاح وبسرعة استجابة فائقة.</x-ui.alert>
                <x-ui.alert type="warning" :dismissible="true">تنبيه: توجد 3 سجلات بانتظار الاعتماد الإداري.</x-ui.alert>
            </x-ui.card>
        </div>
    </div>

    <!-- 3. Form Inputs Showcase -->
    <x-ui.card title="4. حقول المدخلات (Forms & Inputs)" icon="fas fa-edit">
        <div class="row">
            <div class="col-md-4">
                <x-ui.input name="demo_text" label="اسم المشروع" placeholder="أدخل اسم المشروع..." required="true" />
            </div>
            <div class="col-md-4">
                <x-ui.input name="demo_number" label="المبلغ التقديري" type="number" placeholder="0.00" />
            </div>
            <div class="col-md-4">
                <x-ui.select name="demo_select" label="حالة المشروع" :options="['active' => 'نشط', 'pending' => 'قيد الانتظار', 'completed' => 'مكتمل']" />
            </div>
        </div>
    </x-ui.card>

    <!-- 4. Tables Showcase -->
    <x-ui.card title="5. الجداول الاستجابية (Data Table)" icon="fas fa-table">
        <x-ui.table :headers="['#', 'اسم المشروع', 'الجهة المبادرة', 'الميزانية (ر.ي)', 'الحالة', 'الإجراءات']">
            <tr>
                <td>1</td>
                <td class="fw-bold">مشروع إنتاج وتوزيع البذور والشتلات</td>
                <td>قطاع الثروة النباتية</td>
                <td>15,000,000</td>
                <td><x-ui.badge variant="success">نشط</x-ui.badge></td>
                <td>
                    <x-ui.button variant="outline-primary" size="sm" icon="fas fa-eye">عرض</x-ui.button>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td class="fw-bold">مشروع التحصين البيطري الوقائي</td>
                <td>قطاع الثروة الحيوانية</td>
                <td>8,500,000</td>
                <td><x-ui.badge variant="warning">قيد المراجعة</x-ui.badge></td>
                <td>
                    <x-ui.button variant="outline-primary" size="sm" icon="fas fa-eye">عرض</x-ui.button>
                </td>
            </tr>
        </x-ui.table>
    </x-ui.card>

    <!-- 5. Empty State & Loading Showcase -->
    <div class="row">
        <div class="col-md-6">
            <x-ui.card title="6. حالة الشاشة الفارغة (Empty State)" icon="fas fa-folder-open">
                <x-ui.empty-state title="لا توجد مشاريع مضافة" subtitle="قم بإضافة مشروع جديد أو تغيير خيارات الفلترة المطبقة.">
                    <x-slot name="action">
                        <x-ui.button variant="primary" icon="fas fa-plus">إضافة مشروع جديد</x-ui.button>
                    </x-slot>
                </x-ui.empty-state>
            </x-ui.card>
        </div>
        <div class="col-md-6">
            <x-ui.card title="7. حالة التحميل (Loading State)" icon="fas fa-spinner">
                <x-ui.loading text="جاري مزامنة بيانات القطاعات الميدانية..." />
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
