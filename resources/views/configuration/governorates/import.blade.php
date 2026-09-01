@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/excel-drag-drop.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                    استيراد المحافظات
                </h2>
                <a href="{{ route('governorates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>
                    العودة للقائمة
                </a>
            </div>

            <!-- Drag and Drop Zone -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        استيراد البيانات من Excel
                    </h5>
                </div>
                <div class="card-body">
                    <div class="excel-drop-zone">
                        <div class="drop-zone-content">
                            <i class="fas fa-cloud-upload-alt drop-icon"></i>
                            <p class="drop-message">اسحب ملف CSV أو Excel هنا أو انقر للاختيار</p>
                            <p class="drop-info">
                                <small class="text-muted">
                                    الصيغ المدعومة: CSV (.csv) أو Excel (.xlsx, .xls)<br>
                                    الحد الأقصى للحجم: 2 ميجابايت
                                </small>
                            </p>
                            <input type="file" class="excel-file-input" accept=".csv,.xlsx,.xls" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <div class="btn-group" role="group">
                            <a href="{{ route('governorates.download-template', ['format' => 'csv']) }}" class="btn btn-outline-success">
                                <i class="fas fa-download me-1"></i>
                                تحميل قالب CSV
                            </a>
                            <a href="{{ route('governorates.download-template', ['format' => 'xlsx']) }}" class="btn btn-outline-primary">
                                <i class="fas fa-download me-1"></i>
                                تحميل قالب Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        تعليمات الاستيراد
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">خطوات الاستيراد:</h6>
                            <ol class="mb-3">
                                <li>قم بتحميل قالب Excel</li>
                                <li>املأ البيانات في القالب</li>
                                <li>اسحب الملف إلى المنطقة أعلاه أو انقر للاختيار</li>
                                <li>راجع البيانات قبل الاستيراد</li>
                                <li>اختر نوع العملية (إضافة/تحديث)</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">الحقول المطلوبة:</h6>
                            <ul class="mb-3">
                                <li><strong>اسم المحافظة:</strong> مطلوب وفريد</li>
                            </ul>
                            
                            <h6 class="text-warning">ملاحظات مهمة:</h6>
                            <ul class="text-muted small">
                                <li>تأكد من عدم وجود أسماء محافظات مكررة</li>
                                <li>استخدم الأسماء العربية الصحيحة</li>
                                <li>تجنب المسافات الزائدة في بداية أو نهاية الأسماء</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/excel-drag-drop.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    ExcelDragDrop.init({
        dropZoneSelector: '.excel-drop-zone',
        fileInputSelector: '.excel-file-input',
        previewUrl: '{{ route("governorates.preview-import") }}',
        templateUrl: '{{ route("governorates.download-template") }}',
        messages: {
            dragEnter: 'اسحب ملف CSV أو Excel هنا أو انقر للاختيار',
            dragOver: 'اتركه هنا لرفع الملف',
            invalidType: 'نوع الملف غير مدعوم. يرجى اختيار ملف CSV أو Excel',
            fileTooLarge: 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت',
            uploadError: 'حدث خطأ أثناء رفع الملف'
        }
    });
});
</script>
@endsection