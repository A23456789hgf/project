@extends('layouts.app')

@section('title', 'تصدير الجهات')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-file-export text-primary me-2"></i>
                    تصدير الجهات
                </h2>
                <a href="{{ route('authorities.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        خيارات التصدير
                    </h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>يرجى تصحيح الأخطاء التالية:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('authorities.download-export') }}" method="GET">
                        <!-- نطاق البيانات -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">نطاق البيانات</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="all_records" value="all" checked>
                                <label class="form-check-label" for="all_records">
                                    جميع الجهات
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="active_only" value="active">
                                <label class="form-check-label" for="active_only">
                                    الجهات النشطة فقط
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="inactive_only" value="inactive">
                                <label class="form-check-label" for="inactive_only">
                                    الجهات غير النشطة فقط
                                </label>
                            </div>
                        </div>

                        <!-- تنسيق الملف -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">تنسيق الملف</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="xlsx_format" value="xlsx" checked>
                                <label class="form-check-label" for="xlsx_format">
                                    Excel (.xlsx) - موصى به
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format" id="csv_format" value="csv">
                                <label class="form-check-label" for="csv_format">
                                    CSV (.csv)
                                </label>
                            </div>
                        </div>

                        <!-- الحقول المطلوبة -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">الحقول المطلوبة</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="hidden" name="fields[]" value="id">
                                    <input type="hidden" name="fields[]" value="agency_name">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="field_id" checked disabled>
                                        <label class="form-check-label" for="field_id">
                                            معرف الجهة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="field_agency_name" checked disabled>
                                        <label class="form-check-label" for="field_agency_name">
                                            اسم الجهة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_parent" value="parent_id" checked>
                                        <label class="form-check-label" for="field_parent">
                                            الجهة الأم
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_governorate" value="governorate_id" checked>
                                        <label class="form-check-label" for="field_governorate">
                                            المحافظة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_entity_scope" value="entity_scope" checked>
                                        <label class="form-check-label" for="field_entity_scope">
                                            نطاق الجهة
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_directorate" value="directorate_id" checked>
                                        <label class="form-check-label" for="field_directorate">
                                            المديرية
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_type_entity" value="type_entity_id" checked>
                                        <label class="form-check-label" for="field_type_entity">
                                            نوع الجهة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_financing_type" value="financing_type_id" checked>
                                        <label class="form-check-label" for="field_financing_type">
                                            نوع التمويل
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_is_active" value="is_active" checked>
                                        <label class="form-check-label" for="field_is_active">
                                            الحالة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_is_funded" value="is_funded" checked>
                                        <label class="form-check-label" for="field_is_funded">
                                            جهة ممولة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_created_at" value="created_at" checked>
                                        <label class="form-check-label" for="field_created_at">
                                            تاريخ الإنشاء
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ترتيب البيانات -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">ترتيب البيانات</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="sort_by" class="form-label">ترتيب حسب</label>
                                    <select name="sort_by" id="sort_by" class="form-select">
                                        <option value="agency_name">اسم الجهة</option>
                                        <option value="father_name">اسم الأب</option>
                                        <option value="created_at">تاريخ الإنشاء</option>
                                        <option value="id">معرف الجهة</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="sort_order" class="form-label">الترتيب</label>
                                    <select name="sort_order" id="sort_order" class="form-select">
                                        <option value="asc">تصاعدي (أ-ي)</option>
                                        <option value="desc">تنازلي (ي-أ)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- معلومات إضافية -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>ملاحظة:</strong> سيتم تصدير البيانات باللغة العربية مع الحفاظ على ترميز UTF-8 لضمان عرض النصوص بشكل صحيح.
                        </div>

                        <!-- أزرار التحكم -->
                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i>
                                تصدير البيانات
                            </button>
                            <a href="{{ route('authorities.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>
                                إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- إحصائيات سريعة -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar text-info me-2"></i>
                        إحصائيات الجهات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary">{{ \App\Models\Authority::count() }}</h4>
                                <small class="text-muted">إجمالي الجهات</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ \App\Models\Authority::where('is_active', true)->count() }}</h4>
                                <small class="text-muted">الجهات النشطة</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning">{{ \App\Models\Authority::where('is_active', false)->count() }}</h4>
                                <small class="text-muted">الجهات غير النشطة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تم إصلاح المشكلة التي كانت تقوم بتعطيل الحقول المخفية (hidden) 
    // مما كان يمنع إرسال `id` و `agency_name` عند التصدير.
    // الحقول المطلوبة معطلة بالفعل كـ checkboxes في الـ HTML لمنع إزالتها
});
</script>
@endsection