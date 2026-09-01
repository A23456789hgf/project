<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تصدير السجلات إلى PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --info: #2980b9;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            direction: rtl;
            text-align: right;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            border-bottom: none;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            padding: 15px;
        }

        .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }

        .badge-success {
            background-color: var(--success);
            padding: 8px 12px;
            font-weight: normal;
        }

        .badge-primary {
            background-color: var(--secondary);
            padding: 8px 12px;
            font-weight: normal;
        }

        .badge-warning {
            background-color: var(--warning);
            padding: 8px 12px;
            font-weight: normal;
        }

        .btn-export {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            border: none;
            padding: 12px 30px;
            font-weight: bold;
            font-size: 18px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
            transition: all 0.3s;
        }

        .btn-export:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(231, 76, 60, 0.6);
        }

        .btn-filter {
            background: linear-gradient(135deg, var(--secondary), var(--info));
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 10px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card i {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }

        .footer {
            background: linear-gradient(135deg, var(--primary), #1a2530);
            color: white;
            padding: 20px 0;
            margin-top: 40px;
            border-radius: 15px;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .page-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 4px;
            background: var(--success);
            border-radius: 2px;
        }

        .pagination .page-item .page-link {
            border-radius: 10px;
            margin: 0 5px;
            border: none;
            color: var(--primary);
        }

        .pagination .page-item.active .page-link {
            background: var(--secondary);
            color: white;
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px;
            }

            .btn-export {
                width: 100%;
                margin-top: 15px;
            }

            .stat-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- العنوان الرئيسي -->
        <div class="header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-file-pdf me-2"></i>تصدير السجلات إلى PDF</h1>
                    <p class="mb-0">أداة متكاملة لتصدير سجلات البرامج بتنسيق PDF</p>
                </div>
                <div class="col-md-6 text-start">
                    <button class="btn btn-export float-start">
                        <i class="fas fa-file-pdf me-2"></i> تصدير إلى PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- الإحصائيات -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, var(--secondary), #2980b9);">
                    <i class="fas fa-project-diagram"></i>
                    <div class="number">42</div>
                    <p>البرامج الكلية</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, var(--success), #219653);">
                    <i class="fas fa-check-circle"></i>
                    <div class="number">36</div>
                    <p>البرامج النشطة</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, var(--warning), #e67e22);">
                    <i class="fas fa-users"></i>
                    <div class="number">128</div>
                    <p>المشاريع المرتبطة</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, var(--danger), #c0392b);">
                    <i class="fas fa-file-pdf"></i>
                    <div class="number">24</div>
                    <p>تقارير PDF مسبقة</p>
                </div>
            </div>
        </div>

        <!-- منطقة التصفية -->
        <div class="filter-section">
            <h3 class="page-title"><i class="fas fa-filter me-2"></i>تصفية السجلات</h3>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">بحث بالاسم</label>
                    <input type="text" class="form-control" placeholder="ابحث باسم البرنامج...">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">الحالة</label>
                    <select class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" class="form-control">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-12 text-center">
                    <button class="btn btn-filter me-2">
                        <i class="fas fa-filter me-2"></i> تطبيق الفلتر
                    </button>
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i> إعادة تعيين
                    </button>
                </div>
            </div>
        </div>

        <!-- جدول السجلات -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-list me-2"></i>سجلات البرامج</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>اسم البرنامج</th>
                                <th>الحالة</th>
                                <th>تاريخ البدء</th>
                                <th>تاريخ الانتهاء</th>
                                <th>عدد المشاريع</th>
                                <th>خيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>برنامج تطوير المهارات</td>
                                <td><span class="badge badge-success">نشط</span></td>
                                <td>2023-01-15</td>
                                <td>2024-06-30</td>
                                <td>12</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>مبادرة الشباب</td>
                                <td><span class="badge badge-success">نشط</span></td>
                                <td>2023-02-10</td>
                                <td>2023-12-31</td>
                                <td>8</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>دعم المشاريع الصغيرة</td>
                                <td><span class="badge badge-warning">قيد التنفيذ</span></td>
                                <td>2023-03-22</td>
                                <td>2024-03-21</td>
                                <td>15</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>برنامج التدريب المهني</td>
                                <td><span class="badge badge-success">نشط</span></td>
                                <td>2023-04-05</td>
                                <td>2023-12-15</td>
                                <td>6</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>مشروع الابتكار الاجتماعي</td>
                                <td><span class="badge badge-warning">قيد التنفيذ</span></td>
                                <td>2023-05-18</td>
                                <td>2024-05-17</td>
                                <td>10</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>برنامج التمكين الاقتصادي</td>
                                <td><span class="badge badge-success">نشط</span></td>
                                <td>2023-06-30</td>
                                <td>2024-06-29</td>
                                <td>14</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-file-pdf"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- التصفح -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">السابق</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">التالي</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- خيارات التصدير المتقدمة -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-cogs me-2"></i>خيارات التصدير المتقدمة</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">تقرير أساسي</h5>
                                <p class="card-text">تصدير الجدول الأساسي مع البيانات الأساسية</p>
                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-download me-2"></i> تصدير
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-bar fa-3x text-success mb-3"></i>
                                <h5 class="card-title">تقرير مفصل</h5>
                                <p class="card-text">تصدير مع الرسوم البيانية والتحليلات</p>
                                <button class="btn btn-outline-success">
                                    <i class="fas fa-download me-2"></i> تصدير
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-customize fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">تخصيص التقرير</h5>
                                <p class="card-text">اختر الحقول والتصميم الذي تفضله</p>
                                <button class="btn btn-outline-warning">
                                    <i class="fas fa-sliders me-2"></i> تخصيص
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- تذييل الصفحة -->
        <div class="footer text-center">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-file-pdf me-2"></i> نظام التصدير إلى PDF</h5>
                    <p class="mb-0">أداة متكاملة لتصدير سجلات البرامج</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>اتصل بنا</h5>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i> info@example.com</p>
                    <p class="mb-0"><i class="fas fa-phone me-2"></i> +966 123 456 789</p>
                </div>
                <div class="col-md-4">
                    <h5>تابعنا</h5>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4 bg-light">
            <p class="mb-0">© 2023 نظام إدارة البرامج. جميع الحقوق محفوظة.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تفعيل أداة التصدير
        document.querySelector('.btn-export').addEventListener('click', function() {
            const button = this;
            const originalText = button.innerHTML;

            // عرض مؤشر التحميل
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جارٍ التصدير...';
            button.disabled = true;

            // محاكاة عملية التصدير
            setTimeout(() => {
                // عرض رسالة نجاح
                button.innerHTML = '<i class="fas fa-check-circle me-2"></i> تم التصدير بنجاح!';
                button.classList.remove('btn-danger');
                button.classList.add('btn-success');

                // إظهار رسالة تنزيل
                const downloadMsg = document.createElement('div');
                downloadMsg.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-download me-2"></i>
                        تم إنشاء ملف PDF بنجاح. <a href="#" class="alert-link">انقر هنا لتنزيل الملف</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                button.parentElement.appendChild(downloadMsg);

                // إعادة الزر إلى وضعه الأصلي بعد 5 ثوانٍ
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-danger');
                }, 5000);
            }, 2000);
        });

        // تفعيل أزرار التصدير الفردي
        document.querySelectorAll('.btn-sm.btn-outline-danger').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const programName = row.children[1].textContent;

                // عرض رسالة نجاح
                const alert = document.createElement('div');
                alert.innerHTML = `
                    <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-file-pdf me-2"></i>
                        جاري تصدير السجل: <strong>${programName}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.querySelector('.card-body').prepend(alert);

                // إغلاق الرسالة بعد 3 ثوانٍ
                setTimeout(() => {
                    alert.querySelector('.btn-close').click();
                }, 3000);
            });
        });
    </script>
</body>
</html>
