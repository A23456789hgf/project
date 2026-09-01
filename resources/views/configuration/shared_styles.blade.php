<link rel="stylesheet" href="{{ asset('css/design-system/master.css') }}">
<style>
    /* ============================================================
       المتغيرات الأساسية - نظام حكومي احترافي
       ============================================================ */
    :root {
        --brand-navy: #001f3f;
        --brand-navy-light: #002d5b;
        --brand-gold: #D4AF37;
        --brand-gold-light: #e8c547;
        --text-primary: #1e293b;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --bg-body: #f1f5f9;
        --bg-card: #ffffff;
        --bg-table-head: #001f3f;
        --border-color: #e2e8f0;
        --border-light: #f1f5f9;
        --radius-sm: 6px;
        --radius-md: 10px;
        --radius-lg: 14px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.1);
        --transition: 0.2s ease;
    }

    /* ============================================================
       الإعدادات العامة
       ============================================================ */
    body {
        background-color: var(--bg-body);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        direction: rtl;
        color: var(--text-primary);
    }

    /* ============================================================
       البطاقة الرئيسية
       ============================================================ */
    .main-card {
        background: var(--bg-card);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        padding: 28px 32px;
        margin-top: 20px;
        border: 1px solid var(--border-light);
    }

    /* ============================================================
       رأس الصفحة والعناوين
       ============================================================ */
    .page-header {
        border-bottom: 2px solid var(--border-light);
        padding-bottom: 20px;
        margin-bottom: 24px;
    }

    .page-header h2 {
        color: var(--brand-navy);
        font-weight: 800;
        font-size: 1.5rem;
        margin: 0;
    }

    .title-line {
        height: 3px;
        width: 50px;
        background: var(--brand-gold);
        border-radius: 2px;
        margin-top: 8px;
    }

    /* ============================================================
       الأزرار - Navy & Gold
       ============================================================ */
    .btn-navy-gold {
        background: var(--brand-navy);
        color: var(--brand-gold);
        border: 1px solid var(--brand-gold);
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: 0.85rem;
        height: 40px;
        padding: 0 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-navy-gold:hover {
        background: var(--brand-navy-light);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .btn-cancel-custom {
        background-color: #f1f5f9;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        height: 40px;
        display: inline-flex;
        align-items: center;
        padding: 0 20px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-cancel-custom:hover {
        background-color: #e2e8f0;
        color: var(--text-primary);
    }

    /* ============================================================
       الجدول الرئيسي
       ============================================================ */
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        margin-top: 0;
    }

    .custom-table thead th {
        background: var(--bg-table-head);
        color: #ffffff;
        padding: 12px 16px;
        font-weight: 600;
        font-size: 0.8rem;
        text-align: center;
        white-space: nowrap;
        border: none;
    }

    .custom-table thead th:first-child {
        border-top-right-radius: var(--radius-sm);
    }

    .custom-table thead th:last-child {
        border-top-left-radius: var(--radius-sm);
    }

    .custom-table tbody tr {
        background-color: #fff;
        border-bottom: 1px solid var(--border-light);
        transition: all var(--transition);
    }

    .custom-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .custom-table tbody tr:last-child {
        border-bottom: none;
    }

    .custom-table td {
        padding: 12px 16px;
        color: var(--text-secondary);
        vertical-align: middle;
        text-align: center;
    }

    .custom-table td.text-name,
    .text-name {
        color: var(--text-primary);
        font-weight: 600;
        text-align: right;
    }

    .badge-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* ============================================================
       أزرار الإجراءات داخل الجدول
       ============================================================ */
    .btn-action {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        border: none;
        margin: 0 2px;
        padding: 0;
        text-decoration: none;
        transition: all var(--transition);
        cursor: pointer;
    }

    .btn-action i {
        font-size: 13px;
        color: #fff;
    }

    .btn-action.btn-outline-warning,
    .btn-action.btn-warning,
    .btn-edit {
        background: #f59e0b;
    }

    .btn-action.btn-outline-warning:hover,
    .btn-action.btn-warning:hover,
    .btn-edit:hover {
        background: #d97706;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(245, 158, 11, 0.3);
    }

    .btn-action.btn-outline-danger,
    .btn-action.btn-danger,
    .btn-delete {
        background: #ef4444;
    }

    .btn-action.btn-outline-danger:hover,
    .btn-action.btn-danger:hover,
    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-action.btn-outline-info,
    .btn-action.btn-info,
    .btn-action.btn-outline-primary,
    .btn-action.btn-primary,
    .btn-view {
        background: #3b82f6;
    }

    .btn-action.btn-outline-info:hover,
    .btn-action.btn-info:hover,
    .btn-action.btn-outline-primary:hover,
    .btn-action.btn-primary:hover,
    .btn-view:hover {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(59, 130, 246, 0.3);
    }

    /* ============================================================
       شارات الحالة (Badges)
       ============================================================ */
    .custom-table .badge,
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        gap: 4px;
        border: none;
    }

    .custom-table .badge.bg-success,
    .status-active {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .custom-table .badge.bg-danger,
    .status-inactive {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .custom-table .badge.bg-secondary {
        background-color: #f1f5f9;
        color: var(--text-secondary);
    }

    .custom-table .badge.bg-info {
        background-color: #dbeafe;
        color: #2563eb;
    }

    /* ============================================================
       عناصر النماذج (Forms)
       ============================================================ */
    .field-label,
    .form-label {
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 6px;
        font-size: 0.82rem;
    }

    .custom-field,
    .form-control,
    .form-select {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 8px 12px;
        font-size: 0.85rem;
        text-align: right;
        transition: all var(--transition);
    }

    .custom-field:focus,
    .form-control:focus,
    .form-select:focus {
        border-color: var(--brand-navy);
        box-shadow: 0 0 0 3px rgba(0, 31, 63, 0.08);
        outline: none;
    }

    .form-control-sm,
    .form-select-sm {
        height: 36px;
        font-size: 0.82rem;
        padding: 6px 10px;
    }

    /* ============================================================
       التنقل بين الصفحات (Pagination) - نظيف وبسيط
       ============================================================ */
    .pagination-wrapper {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-light);
    }

    .pagination {
        margin: 0;
        gap: 4px;
        direction: ltr;
        /* الأرقام تكون بالترتيب الصحيح */
    }

    .pagination .page-item .page-link {
        font-size: 0.8rem;
        padding: 6px 12px;
        min-width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        background-color: #fff;
        transition: all var(--transition);
        text-decoration: none;
        line-height: 1;
    }

    .pagination .page-item .page-link:hover {
        background-color: #f1f5f9;
        border-color: var(--brand-navy);
        color: var(--brand-navy);
    }

    .pagination .page-item.active .page-link {
        background: var(--brand-navy);
        border-color: var(--brand-navy);
        color: var(--brand-gold);
        font-weight: 700;
    }

    .pagination .page-item.disabled .page-link {
        color: var(--text-muted);
        background-color: #f8fafc;
        border-color: var(--border-light);
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* أسهم السابق/التالي - بسيطة وواضحة */
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        font-weight: 600;
        padding: 6px 14px;
    }

    .pagination .page-link i,
    .pagination .page-link svg {
        font-size: 11px;
        width: 11px;
        height: 11px;
    }

    /* إخفاء أزرار "الأول/الأخير" المكررة إن وجدت */
    .pagination .page-item:first-child:first-of-type .page-link,
    .pagination .page-item:last-child:last-of-type .page-link {
        /* السماح فقط بأسهم السابق/التالي */
    }

    /* ============================================================
       التنبيهات
       ============================================================ */
    .alert {
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        padding: 14px 20px;
        border: none;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* ============================================================
       قائمة منسدلة
       ============================================================ */
    .dropdown-menu {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-lg);
        padding: 8px 0;
        min-width: 180px;
        z-index: 1050 !important;
    }

    .dropdown-item {
        font-size: 0.85rem;
        padding: 10px 16px;
        transition: all var(--transition);
    }

    .dropdown-item:hover {
        background-color: #f8fafc;
        color: var(--brand-navy);
    }

    /* ============================================================
       المودال
       ============================================================ */
    .modal-content {
        border-radius: var(--radius-lg);
        border: none;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background: var(--brand-navy);
        color: #fff;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        padding: 16px 24px;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-title {
        font-weight: 700;
        font-size: 1rem;
    }

    /* ============================================================
       حالة عدم وجود بيانات
       ============================================================ */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    /* ============================================================
       تحسينات إضافية
       ============================================================ */
    .text-muted {
        color: var(--text-muted) !important;
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    /* أيقونة السهم في الـ select */
    .form-select {
        background-position: left 0.75rem center;
        background-size: 12px;
    }

    /* إصلاح جذري لمشكلة اختفاء خيارات Select2 في جميع الصفحات بسبب الكلاسات الموروثة */
    .select2-container .select2-dropdown {
        height: auto !important;
        min-height: 100px !important;
        padding: 0 !important;
        transition: none !important;
    }

    .select2-container .select2-dropdown .select2-results__options {
        max-height: 250px !important;
        overflow-y: auto !important;
    }

    /* شريط التمرير */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>