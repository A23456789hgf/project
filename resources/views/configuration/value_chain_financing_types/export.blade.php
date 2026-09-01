@extends('layouts.app')

@section('title', 'تصدير أنواع تمويل سلاسل القيمة')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-file-export text-primary me-2"></i>
                    تصدير أنواع التمويل
                </h2>
                <a href="{{ route('value-chain-financing-types.index') }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('value-chain-financing-types.export.download') }}" method="GET">
                        <!-- نطاق البيانات -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">نطاق البيانات</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="all_records" value="all" checked>
                                <label class="form-check-label" for="all_records">
                                    جميع الأنواع
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="active_only" value="active">
                                <label class="form-check-label" for="active_only">
                                    الأنواع النشطة فقط
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="inactive_only" value="inactive">
                                <label class="form-check-label" for="inactive_only">
                                    الأنواع غير النشطة فقط
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
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_id" value="id" checked disabled>
                                        <label class="form-check-label" for="field_id">
                                            معرف النوع
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_name" value="name" checked disabled>
                                        <label class="form-check-label" for="field_name">
                                            اسم النوع
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_is_active" value="is_active" checked>
                                        <label class="form-check-label" for="field_is_active">
                                            الحالة
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_created_at" value="created_at">
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
                                        <option value="name">اسم النوع</option>
                                        <option value="created_at">تاريخ الإنشاء</option>
                                        <option value="id">معرف النوع</option>
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
                            <a href="{{ route('value-chain-financing-types.index') }}" class="btn btn-secondary btn-lg">
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
                        إحصائيات أنواع التمويل
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-primary">{{ \App\Models\ValueChainFinancingType::count() }}</h4>
                                <small class="text-muted">إجمالي الأنواع</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ \App\Models\ValueChainFinancingType::where('is_active', true)->count() }}</h4>
                                <small class="text-muted">الأنواع النشطة</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <h4 class="text-warning">{{ \App\Models\ValueChainFinancingType::where('is_active', false)->count() }}</h4>
                                <small class="text-muted">الأنواع غير النشطة</small>
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
    const requiredFields = ['id', 'name'];
    const checkboxes = document.querySelectorAll('input[name="fields[]"]');

    checkboxes.forEach(function(checkbox) {
        if (requiredFields.includes(checkbox.value)) {
            checkbox.disabled = true;
            // Also add a hidden input since disabled inputs aren't submitted
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'fields[]';
            hidden.value = checkbox.value;
            checkbox.parentNode.appendChild(hidden);
        }
    });
});
</script>
@endsection
