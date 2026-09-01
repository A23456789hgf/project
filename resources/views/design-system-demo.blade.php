@extends('layouts.app')

@section('title', 'نظام التصميم الموحد - عرض توضيحي')

@section('content')
<div class="container-fluid">
    
    <!-- ========================================
         العنوان الرئيسي مع الأزرار
         ======================================== -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-palette"></i>
                نظام التصميم الموحد
            </h1>
            <p class="text-muted">عرض توضيحي لجميع مكونات التصميم الموحد</p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group" role="group">
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة جديد
                </button>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-file-import"></i> استيراد بيانات
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-file-export"></i> تصدير بيانات
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">
                        <i class="fas fa-download"></i> تحميل نموذج
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- ========================================
         بطاقات الإحصائيات
         ======================================== -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي السجلات
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,234</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                السجلات النشطة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">987</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                قيد المراجعة
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">156</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                معدل النمو
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">24%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
         بطاقة البحث والتصفية
         ======================================== -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0">
                <i class="fas fa-filter"></i>
                فلترة وترتيب البيانات
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <!-- البحث -->
                <div class="col-md-4">
                    <label for="search" class="form-label">
                        <i class="fas fa-search"></i>
                        بحث
                    </label>
                    <input type="text" name="search" id="search" class="form-control"
                           placeholder="ابحث هنا...">
                </div>

                <!-- الحالة -->
                <div class="col-md-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-toggle-on"></i>
                        الحالة
                    </label>
                    <select name="status" id="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="1">نشط</option>
                        <option value="0">غير نشط</option>
                    </select>
                </div>

                <!-- الترتيب -->
                <div class="col-md-3">
                    <label for="sort" class="form-label">
                        <i class="fas fa-sort"></i>
                        ترتيب حسب
                    </label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="name">الاسم</option>
                        <option value="created_at">تاريخ الإنشاء</option>
                        <option value="updated_at">تاريخ التحديث</option>
                    </select>
                </div>

                <!-- الأزرار -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================
         التنبيهات
         ======================================== -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                تم حفظ البيانات بنجاح!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                يرجى مراجعة البيانات المدخلة
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-times-circle"></i>
                حدث خطأ أثناء معالجة الطلب
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle"></i>
                معلومة مهمة: يرجى الانتباه لهذا الأمر
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>

    <!-- ========================================
         الجدول الرئيسي
         ======================================== -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0">
                <i class="fas fa-table"></i>
                قائمة البيانات
            </h6>
            <span class="badge bg-primary">150 سجل</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">الاسم</th>
                            <th width="20%">الفئة</th>
                            <th width="15%">الحالة</th>
                            <th width="15%">تاريخ الإنشاء</th>
                            <th width="20%">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 1; $i <= 10; $i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-alt text-primary me-2"></i>
                                    <strong>عنصر رقم {{ $i }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-tag me-1"></i>
                                    الفئة {{ $i }}
                                </span>
                            </td>
                            <td>
                                @if($i % 2 == 0)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> نشط
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-pause"></i> معلق
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    2024-01-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- الترقيم -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- ========================================
         نموذج إدخال البيانات
         ======================================== -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0">
                <i class="fas fa-edit"></i>
                نموذج إدخال البيانات
            </h6>
        </div>
        <div class="card-body">
            <form>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">
                            <i class="fas fa-user"></i>
                            الاسم
                        </label>
                        <input type="text" class="form-control" id="name" placeholder="أدخل الاسم">
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" class="form-control" id="email" placeholder="example@domain.com">
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">
                            <i class="fas fa-list"></i>
                            الفئة
                        </label>
                        <select class="form-select" id="category">
                            <option value="">اختر الفئة</option>
                            <option value="1">الفئة الأولى</option>
                            <option value="2">الفئة الثانية</option>
                            <option value="3">الفئة الثالثة</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on"></i>
                            الحالة
                        </label>
                        <select class="form-select" id="status">
                            <option value="1">نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-right"></i>
                            الوصف
                        </label>
                        <textarea class="form-control" id="description" rows="4" 
                                  placeholder="أدخل الوصف هنا..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> إلغاء
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> حفظ
            </button>
        </div>
    </div>

    <!-- ========================================
         أنواع الأزرار
         ======================================== -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0">
                <i class="fas fa-mouse-pointer"></i>
                أنواع الأزرار
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <h6>الأزرار الأساسية</h6>
                    <button class="btn btn-primary me-2">
                        <i class="fas fa-check"></i> أساسي
                    </button>
                    <button class="btn btn-secondary me-2">
                        <i class="fas fa-info"></i> ثانوي
                    </button>
                    <button class="btn btn-success me-2">
                        <i class="fas fa-check-circle"></i> نجاح
                    </button>
                    <button class="btn btn-warning me-2">
                        <i class="fas fa-exclamation-triangle"></i> تحذير
                    </button>
                    <button class="btn btn-danger me-2">
                        <i class="fas fa-times-circle"></i> خطر
                    </button>
                    <button class="btn btn-info me-2">
                        <i class="fas fa-info-circle"></i> معلومات
                    </button>
                </div>

                <div class="col-12">
                    <h6>الأزرار المحددة</h6>
                    <button class="btn btn-outline-primary me-2">
                        <i class="fas fa-check"></i> أساسي
                    </button>
                    <button class="btn btn-outline-secondary me-2">
                        <i class="fas fa-info"></i> ثانوي
                    </button>
                    <button class="btn btn-outline-success me-2">
                        <i class="fas fa-check-circle"></i> نجاح
                    </button>
                    <button class="btn btn-outline-warning me-2">
                        <i class="fas fa-exclamation-triangle"></i> تحذير
                    </button>
                    <button class="btn btn-outline-danger me-2">
                        <i class="fas fa-times-circle"></i> خطر
                    </button>
                </div>

                <div class="col-12">
                    <h6>أحجام الأزرار</h6>
                    <button class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-plus"></i> كبير
                    </button>
                    <button class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> عادي
                    </button>
                    <button class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-plus"></i> صغير
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection