@extends('layouts.app')

@section('title', 'تصدير المديريات')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-file-export text-primary me-2"></i>
                    تصدير المديريات
                </h2>
                <a href="{{ route('directorates.index') }}" class="btn btn-outline-secondary">
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
                    <form action="{{ route('directorates.export-excel') }}" method="GET">
                        <!-- نطاق البيانات -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">فلترة البيانات</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="governorate_id" class="form-label">المحافظة</label>
                                    <select name="governorate_id" id="governorate_id" class="form-select">
                                        <option value="">جميع المحافظات</option>
                                        @foreach(\App\Models\Governorate::all() as $gov)
                                            <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                            <h6 class="text-primary mb-3">الحقول المضمنة</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_id" value="id" checked disabled>
                                        <label class="form-check-label" for="field_id">
                                            معرف المديرية
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_gov_name" value="governorate_name" checked disabled>
                                        <label class="form-check-label" for="field_gov_name">
                                            اسم المحافظة
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_name" value="name" checked disabled>
                                        <label class="form-check-label" for="field_name">
                                            اسم المديرية
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" id="field_is_active" value="is_active" checked>
                                        <label class="form-check-label" for="field_is_active">
                                            الحالة
                                        </label>
                                    </div>
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
                            <a href="{{ route('directorates.index') }}" class="btn btn-secondary btn-lg">
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
                        إحصائيات المديريات
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary">{{ \App\Models\Directorate::count() }}</h4>
                                <small class="text-muted">إجمالي المديريات</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h4 class="text-success">{{ \App\Models\Directorate::where('is_active', true)->count() }}</h4>
                                <small class="text-muted">المديريات النشطة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
