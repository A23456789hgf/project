<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نظام متابعة المشاريع</title>
    <link rel="icon" href="{{ asset('images/logo.ico') }}" type="image/x-icon" />

    <!-- Resource Hints for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://code.jquery.com">

    {{-- @vite(['resources/css/activity.css', 'resources/js/activity.js']) --}}
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet"
        media="print" onload="this.media='all'" />
    <!-- Bootstrap 5.3.3 RTL (single source) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />
    <script data-auto-replace-svg="nest" defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <!-- خط Cairo للعربية -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet"
        media="print" onload="this.media='all'" />
    <!-- نظام التصميم الموحد للعربية - Unified Arabic Design System -->
    <link rel="stylesheet" href="{{ asset('css/unified-arabic-design.css') }}" />
    <!-- Auto-fit Table Font Sizing -->
    <link rel="stylesheet" href="{{ asset('css/table-auto-fit.css') }}" />
    <!-- Unified Design System - Beautiful Page Formatting -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}" />
    <!-- Configuration Pages Styles -->
    <link rel="stylesheet" href="{{ asset('css/configuration-styles.css') }}" />
    @yield('styles')
    <style>
        :root {
            /* Color Palette */
            --primary: #1e3a5f;
            --primary-light: #2d5a8f;
            --primary-dark: #0f1f3d;
            --accent: #c9a961;
            --accent-light: #d4b876;
            --accent-dark: #b89850;

            /* Neutrals */
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;

            /* Layout */
            --sidebar-width: 260px;
            --header-height: 60px;
            --content-max-width: 1400px;

            /* Spacing */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;

            /* Typography */
            --font-sans: 'Cairo', 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

            /* Transitions */
            --transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, opacity 0.15s ease, transform 0.15s ease;
            --transition-slow: background 0.25s ease, color 0.25s ease, border-color 0.25s ease, opacity 0.25s ease, transform 0.25s ease;

            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: var(--font-sans);
            font-size: var(--font-size-sm);
            line-height: 1.6;
            color: var(--gray-800);
            background: var(--gray-50);
            padding-top: var(--header-height);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ========== NAVBAR ========== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--primary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-sm);
            z-index: 1000;
            transition: var(--transition);
        }

        .navbar .container-fluid {
            height: 100%;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
        }

        .navbar-brand:hover {
            color: var(--accent);
        }

        .navbar-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .menu-toggle {
            display: block;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.25rem;
            cursor: pointer;
            padding: var(--space-sm);
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent);
        }

        /* ========== NOTIFICATION BUTTONS ========== */
        .btn-link {
            background: none;
            border: none;
            color: var(--accent) !important;
            font-size: 1.125rem;
            cursor: pointer;
            padding: var(--space-sm);
            border-radius: var(--radius);
            transition: var(--transition);
        }

        .btn-link:hover {
            background: rgba(201, 169, 97, 0.1);
            color: var(--accent-light) !important;
        }

        /* ========== NOTIFICATION SYSTEM ========== */
        .notification-wrapper {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            left: 10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 320px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1050;
            display: none;
            margin-top: 0.5rem;
        }

        .notification-dropdown.active {
            display: block;
        }

        .notification-header {
            padding: var(--space-md);
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-50);
        }

        .notification-header h6 {
            margin: 0;
            font-size: var(--font-size-base);
            font-weight: 600;
            color: var(--gray-800);
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            padding: var(--space-md);
            border-bottom: 1px solid var(--gray-100);
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .notification-item:hover {
            background: var(--gray-50);
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            background: var(--primary-light);
            color: white;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: var(--space-sm);
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 2px;
            color: var(--primary);
        }

        .notification-desc {
            font-size: var(--font-size-xs);
            color: var(--gray-600);
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 4px;
        }

        .notification-time i {
            margin-left: 4px;
        }

        .notification-footer {
            padding: var(--space-sm) var(--space-md);
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }

        .notification-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: var(--font-size-xs);
            font-weight: 500;
        }

        .notification-footer a:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }

        /* ========== SIDEBAR ========== */
        .sidebar-container {
            position: fixed;
            top: var(--header-height);
            right: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: var(--primary);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s ease;
            z-index: 900;
            box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar-container.collapsed {
            transform: translateX(100%);
        }

        /* Custom Scrollbar */
        .sidebar-container::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-container::-webkit-scrollbar-thumb {
            background: rgba(201, 169, 97, 0.5);
            border-radius: 3px;
        }

        .sidebar-container::-webkit-scrollbar-thumb:hover {
            background: var(--accent);
        }

        .sidebar-header {
            padding: var(--space-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h5 {
            font-size: var(--font-size-base);
            font-weight: 600;
            color: var(--accent);
            margin: 0;
        }

        .sidebar-content {
            padding: var(--space-md) 0;
        }

        /* Navigation Links */
        .sidebar-container .nav-link {
            display: flex;
            align-items: center;
            padding: 0.625rem var(--space-lg);
            margin: 0.125rem var(--space-md);
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            border-radius: var(--radius);
            font-size: var(--font-size-sm);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .sidebar-container .nav-link i {
            width: 20px;
            margin-left: var(--space-sm);
            font-size: 0.9375rem;
            color: inherit;
            opacity: 0.85;
            transition: var(--transition);
        }

        .sidebar-container .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .sidebar-container .nav-link:hover i {
            color: var(--accent);
        }

        .sidebar-container .nav-link.active {
            background: linear-gradient(to left, rgba(201, 169, 97, 0.2), transparent);
            color: var(--white);
            font-weight: 600;
        }

        .sidebar-container .nav-link.active::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 70%;
            background: var(--accent);
            border-radius: 3px 0 0 3px;
        }

        .sidebar-container .nav-link.active i {
            color: var(--accent);
        }

        /* Collapsible Menus */
        .sidebar-container .nav-link[data-bs-toggle="collapse"] {
            justify-content: space-between;
        }

        .sidebar-container .nav-link .fa-chevron-down {
            font-size: 0.75rem;
            margin-left: 0;
            margin-right: auto;
            transition: transform 0.2s ease;
            order: -1;
        }

        .sidebar-container .nav-link[aria-expanded="true"] .fa-chevron-down {
            transform: rotate(180deg);
        }

        .sidebar-container .nav-link[data-bs-toggle="collapse"] span {
            order: 1;
            flex: 1;
            text-align: right;
        }

        .sidebar-container .collapse {
            padding-right: var(--space-md);
        }

        .sidebar-container .collapse .nav-link {
            padding: 0.5rem var(--space-lg) 0.5rem 2.5rem;
            font-size: var(--font-size-xs);
            margin: 0.125rem var(--space-sm);
        }

        .sidebar-container .collapse .nav-link::before {
            content: '•';
            position: absolute;
            right: 1.75rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
        }

        .sidebar-container .collapse .nav-link.active::before {
            color: var(--accent);
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: var(--space-xl);
            min-height: calc(100vh - var(--header-height));
            transition: margin-right 0.3s ease;
        }

        .sidebar-container.collapsed~.main-content {
            margin-right: 0;
        }

        /* ========== CARDS ========== */
        .custom-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .custom-card:hover {
            box-shadow: var(--shadow);
            border-color: var(--gray-300);
        }

        /* ========== BUTTONS ========== */
        .btn-primary {
            background: var(--primary);
            color: var(--white);
            border: 1px solid var(--primary);
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius);
            font-size: var(--font-size-sm);
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* ========== OVERLAY ========== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--header-height);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 850;
            backdrop-filter: blur(2px);
        }

        /* ========== LOADER ========== */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 31, 61, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .loader-overlay.active {
            display: flex;
            pointer-events: all;
        }

        .loader-content {
            background: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-xl);
            padding: var(--space-2xl);
            text-align: center;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            transform: translateY(20px);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .loader-overlay.active .loader-content {
            transform: translateY(0);
        }

        .loader-spinner {
            width: 64px;
            height: 64px;
            border: 4px solid var(--gray-100);
            border-top: 4px solid var(--accent);
            border-radius: 50%;
            animation: spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            margin: 0 auto var(--space-lg);
            box-shadow: var(--shadow-sm);
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            font-size: var(--font-size-xl);
            font-weight: 700;
            color: var(--primary);
            margin-bottom: var(--space-sm);
            letter-spacing: -0.01em;
        }

        .loader-subtext {
            font-size: var(--font-size-base);
            color: var(--gray-600);
            opacity: 0.9;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .menu-toggle {
                display: block;
            }

            .sidebar-container {
                transform: translateX(100%);
                box-shadow: var(--shadow-lg);
            }

            .sidebar-container.show {
                transform: translateX(0);
            }

            .sidebar-overlay.active {
                display: block;
            }

            .main-content {
                margin-right: 0;
            }

            .notification-dropdown {
                left: auto;
                right: 0;
                width: 300px;
            }
        }

        @media (max-width: 768px) {
            .navbar .container-fluid {
                padding: 0 var(--space-md);
            }

            .navbar-brand {
                font-size: var(--font-size-xs);
                gap: var(--space-sm);
            }

            .navbar-brand img {
                width: 32px;
                height: 32px;
            }

            .sidebar-container {
                width: 280px;
            }

            .main-content {
                padding: var(--space-lg);
            }

            .notification-dropdown {
                width: 280px;
            }
        }

        @media (max-width: 480px) {
            .navbar .container-fluid {
                padding: 0 var(--space-sm);
            }

            .navbar-brand span {
                display: none;
            }

            .sidebar-container {
                width: 100%;
                max-width: 320px;
            }

            .main-content {
                padding: var(--space-md);
            }

            .notification-dropdown {
                width: calc(100vw - 2rem);
                right: 0.5rem;
                left: auto;
            }
        }

        /* ========== UTILITIES ========== */
        .hover-effect {
            transition: var(--transition);
            cursor: pointer;
        }

        .hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Focus States */
        *:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        /* Smooth Transitions */
        a,
        button,
        input,
        select,
        textarea {
            transition: var(--transition);
        }

        /* Select2 Bootstrap 5 Theme Fixes */
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
            padding-right: 0;
            padding-left: 0;
            color: #212529;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            top: 1px;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            min-height: 38px;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .select2-search__field {
            font-family: inherit;
        }

        .select2-dropdown {
            border-color: #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
    @yield('styles')

<body>
    <!-- الشريط العلوي -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <button class="menu-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand mb-0 h1" href="{{ route('dashboard') }}">
                    <img src="{{ asset('/images/logo.png') }}" alt="الشعار" width="40" />
                    وزارة الزراعة والثروة السمكية والموارد المائية
                </a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification System -->
                @can('notifications.view')
                    @include('components.notifications')
                @endcan

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button
                        class="btn btn-link dropdown-toggle d-flex align-items-center gap-2 p-1 text-decoration-none border-0"
                        style="color:#fff !important; background: rgba(255,255,255,0.08); border-radius: 999px;"
                        type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-white text-primary d-flex align-items-center justify-content-center"
                            style="width: 32px; height: 32px; border-radius: 50%; font-size: 0.85rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="d-none d-md-inline small" style="color:#fff;">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                                href="{{ route('profile.show') }}">
                                <i class="fas fa-user-circle text-primary"></i>
                                <span>الملف الشخصي</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger"
                                href="javascript:void(0)" onclick="confirmLogout()">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>تسجيل الخروج</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                    @csrf
                </form>

                <script>
                    function confirmLogout() {
                        Swal.fire({
                            title: 'هل تود تسجيل الخروج؟',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'نعم، خروج',
                            cancelButtonText: 'لا',
                            customClass: {
                                confirmButton: 'btn btn-danger mx-2',
                                cancelButton: 'btn btn-secondary mx-2'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('logout-form').submit();
                            }
                        });
                    }
                </script>

            </div>
        </div>
    </nav>

    <!-- طبقة التعتيم للجوال -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- القائمة الجانبية -->
    <div class="sidebar-container" id="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">القائمة الجانبية</h5>
        </div>

        <div class="sidebar-content">
            <ul class="nav flex-column">
                @php
                    $user = auth()->user();
                @endphp

                @can('dashboard.sidebar')
                    <li class="nav-item auth-perm-dashboard-sidebar" data-sidebar-module="dashboard">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> الصفحة الرئيسية
                        </a>
                    </li>
                @endcan

                @canany(['projects.sidebar', 'projects-implementation.sidebar', 'quality.sidebar', 'execution.sidebar'])
                    <li class="nav-item auth-perm-projects-sidebar auth-perm-execution-sidebar auth-perm-quality-sidebar"
                        data-sidebar-module="projects">
                        @php
                            $isProjectsActive = request()->routeIs('projects.*')
                                || request()->routeIs('execution.*')
                                || request()->routeIs('projects.quality.*');
                        @endphp

                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isProjectsActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-projects" role="button"
                            aria-expanded="{{ $isProjectsActive ? 'true' : 'false' }}" aria-controls="submenu-projects">

                            <span>
                                <i class="fas fa-project-diagram"></i> مشروع
                            </span>

                            <i class="fas fa-chevron-down small"></i>
                        </a>

                        <div class="collapse ps-3 {{ $isProjectsActive ? 'show' : '' }}" id="submenu-projects">
                            <ul class="nav flex-column">
                                @can('projects.sidebar')
                                    <li class="nav-item auth-perm-projects-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}"
                                            href="{{ route('projects.index') }}">
                                            قائمة المشاريع
                                        </a>
                                    </li>
                                @endcan

                                @can('projects-implementation.sidebar')
                                    <li class="nav-item auth-perm-projects-implementation-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.implementation.index') ? 'active' : '' }}"
                                            href="{{ route('projects.implementation.index') }}">
                                            المشاريع المنفذة
                                        </a>
                                    </li>
                                @endcan

                                @can('quality.sidebar')
                                    <li class="nav-item auth-perm-quality-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.quality.*') ? 'active' : '' }}"
                                            href="javascript:void(0)">
                                            <i class="fas fa-chart-line me-2"></i>جودة المشاريع
                                        </a>
                                    </li>
                                @endcan

                                @can('execution.sidebar')
                                    <li class="nav-item auth-perm-execution-sidebar">
                                        <a class="nav-link {{ request()->routeIs('execution.tracking') ? 'active' : '' }}"
                                            href="javascript:void(0)">
                                            <i class="fas fa-tasks me-2"></i>متابعة التنفيذ
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                @canany(['referrals.sidebar', 'correspondence.sidebar', 'memoirs.sidebar'])
                    <li class="nav-item auth-perm-referrals-sidebar auth-perm-correspondence-sidebar auth-perm-memoirs-sidebar"
                        data-sidebar-module="correspondence">
                        @php
                            $isReferralsActive = request()->routeIs('project-referrals.*')
                                || request()->routeIs('referrals.*')
                                || request()->routeIs('correspondence.*')
                                || request()->routeIs('memoirs.*');
                        @endphp

                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isReferralsActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-referrals" role="button"
                            aria-expanded="{{ $isReferralsActive ? 'true' : 'false' }}" aria-controls="submenu-referrals">

                            <span>
                                <i class="fas fa-exchange-alt"></i> إدارة الإحالات
                            </span>

                            <i class="fas fa-chevron-down small"></i>
                        </a>

                        <div class="collapse ps-3 {{ $isReferralsActive ? 'show' : '' }}" id="submenu-referrals">
                            <ul class="nav flex-column">
                                @can('referrals.sidebar')
                                    <li class="nav-item auth-perm-referrals-sidebar">
                                        <a class="nav-link {{ request()->routeIs('project-referrals.index') ? 'active' : '' }}"
                                            href="{{ route('project-referrals.index') }}">
                                            إحالات المشاريع
                                        </a>
                                    </li>
                                @endcan

                                @can('correspondence.sidebar')
                                    <li class="nav-item auth-perm-correspondence-sidebar">
                                        <a class="nav-link {{ request()->routeIs('correspondence.index') ? 'active' : '' }}"
                                            href="{{ route('correspondence.index') }}">
                                            لوحة متابعة الإحالات والمراسلات
                                        </a>
                                    </li>
                                @endcan

                                @can('memoirs.sidebar')
                                    <li class="nav-item auth-perm-memoirs-sidebar">
                                        <a class="nav-link {{ request()->routeIs('memoirs.index') ? 'active' : '' }}"
                                            href="{{ route('memoirs.index') }}">
                                            <i class="fas fa-file-signature me-1"></i> المذكرات الإدارية
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                @can('plans.sidebar')
                    <li class="nav-item auth-perm-plans-sidebar" data-sidebar-module="planning">
                        @php
                            $isPlanningActive = request()->routeIs('plans.*');
                        @endphp

                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isPlanningActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-planning" role="button"
                            aria-expanded="{{ $isPlanningActive ? 'true' : 'false' }}" aria-controls="submenu-planning">
                            <span>
                                <i class="fas fa-paste"></i> إدارة التخطيط
                            </span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>

                        <div class="collapse ps-3 {{ $isPlanningActive ? 'show' : '' }}" id="submenu-planning">
                            <ul class="nav flex-column">
                                @can('plans.index')
                                    <li class="nav-item auth-perm-plans-sidebar">
                                        <a class="nav-link {{ request()->routeIs('plans.index') ? 'active' : '' }}"
                                            href="{{ route('plans.index') }}">
                                            الخطط والمشاريع
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('reports.sidebar')
                    <li class="nav-item auth-perm-reports-sidebar" data-sidebar-module="reports">
                        @php
                            $isReportsActive = request()->routeIs('projects.reports.*');
                        @endphp

                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isReportsActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-reports" role="button"
                            aria-expanded="{{ $isReportsActive ? 'true' : 'false' }}" aria-controls="submenu-reports">
                            <span>
                                <i class="fas fa-chart-pie"></i> إدارة التقارير
                            </span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>

                        <div class="collapse ps-3 {{ $isReportsActive ? 'show' : '' }}" id="submenu-reports">
                            <ul class="nav flex-column">
                                @can('reports.index')
                                    <li class="nav-item auth-perm-reports-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.reports.index') ? 'active' : '' }}"
                                            href="{{ route('projects.reports.index') }}">
                                            لوحة معلومات التقارير
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('empowerment.sidebar')
                    <li class="nav-item auth-perm-empowerment-sidebar" data-sidebar-module="empowerment">
                        @php
                            $isEmpowermentActive = request()->routeIs('projects.empowerment*');
                        @endphp
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isEmpowermentActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-empowerment" role="button"
                            aria-expanded="{{ $isEmpowermentActive ? 'true' : 'false' }}"
                            aria-controls="submenu-empowerment">
                            <span>
                                <i class="fas fa-hand-holding-usd"></i> إدارة التمكين
                            </span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>

                        <div class="collapse ps-3 {{ $isEmpowermentActive ? 'show' : '' }}" id="submenu-empowerment">
                            <ul class="nav flex-column">
                                @can('empowerment.loans.view')
                                    <li class="nav-item auth-perm-empowerment-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.empowerment') ? 'active' : '' }}"
                                            href="{{ route('projects.empowerment') }}">
                                            القروض والحسابات
                                        </a>
                                    </li>
                                @endcan

                                @can('empowerment.beneficiaries.view')
                                    <li class="nav-item auth-perm-empowerment-sidebar">
                                        <a class="nav-link {{ request()->routeIs('projects.empowerment.beneficiaries.all') ? 'active' : '' }}"
                                            href="{{ route('projects.empowerment.beneficiaries.all') }}">
                                            مستفيدو القروض
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @php
                    $encodingPermissions = [
                        'programs.sidebar',
                        'domains.sidebar',
                        'governorates.sidebar',
                        'villages.sidebar',
                        'financing-types.sidebar',
                        'financing-forms.sidebar',
                        'authorities.sidebar',
                        'priorities.sidebar',
                        'beneficiaries.sidebar',
                        'configuration.sidebar',
                        'donors.sidebar'
                    ];
                @endphp

                @canany($encodingPermissions)
                    <li class="nav-item auth-perm-configuration-sidebar" data-sidebar-module="encoding">
                        @php
                            $encodingRoutes = ['programs.*', 'domains.*', 'subdomains.*', 'interventions.*', 'governorates.*', 'directorates.*', 'sub-areas.*', 'villages.*', 'financial-items.*', 'funding-sources.*', 'financing-types.*', 'formfinancing.*', 'subfinancing-forms.*', 'authorities.*', 'main-routers.*', 'sub-routers.*', 'priorities.*', 'units.*', 'beneficiary-groups.*'];
                            $isEncodingActive = request()->routeIs(...$encodingRoutes);
                        @endphp
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isEncodingActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-encoding" role="button"
                            aria-expanded="{{ $isEncodingActive ? 'true' : 'false' }}" aria-controls="submenu-encoding">
                            <span><i class="fas fa-cogs"></i> الترميزات</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                        <div class="collapse ps-3 {{ $isEncodingActive ? 'show' : '' }}" id="submenu-encoding">
                            <ul class="nav flex-column">
                                @can('programs.sidebar')
                                    <li class="nav-item auth-perm-programs-sidebar">
                                        <a class="nav-link {{ request()->routeIs('programs.*') ? 'active' : '' }}"
                                            href="{{ route('programs.index') }}">البرامج</a>
                                    </li>
                                @endcan

                                @can('domains.sidebar')
                                    <li class="nav-item auth-perm-domains-sidebar">
                                        <a class="nav-link {{ request()->routeIs('domains.*') ? 'active' : '' }}"
                                            href="{{ route('domains.index') }}">المجالات</a>
                                    </li>
                                    <li class="nav-item auth-perm-domains-sidebar">
                                        <a class="nav-link {{ request()->routeIs('subdomains.*') ? 'active' : '' }}"
                                            href="{{ route('subdomains.index') }}">المجالات الفرعية</a>
                                    </li>
                                @endcan

                                @can('configuration.sidebar')
                                    <li class="nav-item auth-perm-configuration-sidebar">
                                        <a class="nav-link {{ request()->routeIs('interventions.*') ? 'active' : '' }}"
                                            href="{{ route('interventions.index') }}">أنواع التدخل</a>
                                    </li>
                                @endcan

                                @can('governorates.sidebar')
                                    <li class="nav-item auth-perm-governorates-sidebar">
                                        <a class="nav-link {{ request()->routeIs('governorates.*') ? 'active' : '' }}"
                                            href="{{ route('governorates.index') }}">المحافظة</a>
                                    </li>
                                    <li class="nav-item auth-perm-governorates-sidebar">
                                        <a class="nav-link {{ request()->routeIs('directorates.*') ? 'active' : '' }}"
                                            href="{{ route('directorates.index') }}">المديرية</a>
                                    </li>
                                    <li class="nav-item auth-perm-governorates-sidebar">
                                        <a class="nav-link {{ request()->routeIs('sub-areas.*') ? 'active' : '' }}"
                                            href="{{ route('sub-areas.index') }}">العزلة / المنطقة</a>
                                    </li>
                                @endcan

                                @can('villages.sidebar')
                                    <li class="nav-item auth-perm-villages-sidebar">
                                        <a class="nav-link {{ request()->routeIs('villages.*') ? 'active' : '' }}"
                                            href="{{ route('villages.index') }}">القرية / الحارة</a>
                                    </li>
                                @endcan

                                @can('configuration.sidebar')
                                    <li class="nav-item auth-perm-configuration-sidebar">
                                        <a class="nav-link {{ request()->routeIs('financial-items.*') ? 'active' : '' }}"
                                            href="{{ route('financial-items.index') }}">البنود المالية</a>
                                    </li>
                                @endcan

                                @can('donors.sidebar')
                                    <li class="nav-item auth-perm-donors-sidebar">
                                        <a class="nav-link {{ request()->routeIs('funding-sources.*') ? 'active' : '' }}"
                                            href="{{ route('funding-sources.index') }}">مصدر التمويل</a>
                                    </li>
                                @endcan

                                @can('financing-types.sidebar')
                                    <li class="nav-item auth-perm-financing-types-sidebar">
                                        <a class="nav-link {{ request()->routeIs('financing-types.*') ? 'active' : '' }}"
                                            href="{{ route('financing-types.index') }}">نوع التمويل </a>
                                    </li>
                                @endcan

                                @can('financing-forms.sidebar')
                                    <li class="nav-item auth-perm-financing-forms-sidebar">
                                        <a class="nav-link {{ request()->routeIs('formfinancing.*') ? 'active' : '' }}"
                                            href="{{ route('formfinancing.index') }}">اشكال التمويل </a>
                                    </li>
                                    <li class="nav-item auth-perm-financing-forms-sidebar">
                                        <a class="nav-link {{ request()->routeIs('subfinancing-forms.*') ? 'active' : '' }}"
                                            href="{{ route('subfinancing-forms.index') }}">اشكال التمويل الفرعية </a>
                                    </li>
                                @endcan

                                @can('authorities.sidebar')
                                    <li class="nav-item auth-perm-authorities-sidebar">
                                        <a class="nav-link {{ request()->routeIs('authorities.*') ? 'active' : '' }}"
                                            href="{{ route('authorities.index') }}"> الجهة </a>
                                    </li>
                                @endcan

                                @can('configuration.sidebar')
                                    <li class="nav-item auth-perm-configuration-sidebar">
                                        <a class="nav-link {{ request()->routeIs('internal-entities.*') ? 'active' : '' }}"
                                            href="{{ route('internal-entities.index') }}">
                                            الجهات الداخلية المنظمة
                                        </a>
                                    </li>
                                @endcan

                                @can('entity_officers.view')
                                    <li class="nav-item auth-perm-entity-officers-view">
                                        <a class="nav-link {{ request()->routeIs('entity-officers.*') ? 'active' : '' }}"
                                            href="{{ route('entity-officers.index') }}">
                                            مسؤولي الجهات
                                        </a>
                                    </li>
                                @endcan

                                @can('entity-authorities.view')
                                    <li class="nav-item auth-perm-entity-authorities-view">
                                        <a class="nav-link {{ request()->routeIs('entity-authorities.*') ? 'active' : '' }}"
                                            href="{{ route('entity-authorities.index') }}">
                                            صلاحيات الجهات
                                        </a>
                                    </li>
                                @endcan

                                @can('priorities.sidebar')
                                    <li class="nav-item auth-perm-priorities-sidebar">
                                        <a class="nav-link {{ request()->routeIs('priorities.*') ? 'active' : '' }}"
                                            href="{{ route('priorities.index') }}"> الأولويات </a>
                                    </li>
                                @endcan

                                @can('configuration.sidebar')
                                    <li class="nav-item auth-perm-configuration-sidebar">
                                        <a class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}"
                                            href="{{ route('units.index') }}"> الوحدة </a>
                                    </li>
                                @endcan

                                @can('beneficiaries.sidebar')
                                    <li class="nav-item auth-perm-beneficiaries-sidebar">
                                        <a class="nav-link {{ request()->routeIs('beneficiary-groups.*') ? 'active' : '' }}"
                                            href="{{ route('beneficiary-groups.index') }}"> الفئات المستفيدة </a>
                                    </li>
                                @endcan

                                @can('signatures.sidebar')
                                    <li class="nav-item auth-perm-signatures-sidebar">
                                        <a class="nav-link {{ request()->routeIs('signatures.*') ? 'active' : '' }}"
                                            href="{{ route('signatures.index') }}">
                                            إدارة التواقيع
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

                @canany(['users.sidebar', 'roles-permissions.sidebar', 'audit-logs.sidebar'])
                    <li class="nav-item auth-perm-users-sidebar auth-perm-roles-permissions-sidebar auth-perm-audit-logs-sidebar"
                        data-sidebar-module="users">
                        @php
                            $userManagementRoutes = ['users.*', 'roles.*', 'roles-permissions.*', 'admin.audit-logs.*'];
                            $isUserManagementActive = request()->routeIs(...$userManagementRoutes);
                        @endphp
                        <a class="nav-link d-flex justify-content-between align-items-center {{ $isUserManagementActive ? 'active' : '' }}"
                            data-bs-toggle="collapse" href="#submenu-users" role="button"
                            aria-expanded="{{ $isUserManagementActive ? 'true' : 'false' }}" aria-controls="submenu-users">
                            <span><i class="fas fa-users-cog"></i> إدارة المستخدمين</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                        <div class="collapse ps-3 {{ $isUserManagementActive ? 'show' : '' }}" id="submenu-users">
                            <ul class="nav flex-column">
                                @can('users.sidebar')
                                    <li class="nav-item auth-perm-users-sidebar">
                                        <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}"
                                            href="{{ route('users.index') }}">قائمة المستخدمين</a>
                                    </li>
                                @endcan

                                @can('roles-permissions.sidebar')
                                    <li class="nav-item auth-perm-roles-permissions-sidebar">
                                        <a class="nav-link {{ request()->routeIs('roles.index*') ? 'active' : '' }}"
                                            href="{{ route('roles.index') }}">إدارة الأدوار والصلاحيات </a>
                                    </li>
                                @endcan

                                @can('audit-logs.sidebar')
                                    <li class="nav-item auth-perm-audit-logs-sidebar">
                                        <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                                            href="{{ route('admin.audit-logs.index') }}">سجل الأنشطة</a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany
            </ul>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <main class="main-content">
        <div class="container-fluid">
            @yield('content')
        </div>
    </main>


    <!-- Page Loader -->
    <div class="loader-overlay" id="pageLoader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">جاري تحميل الصفحة...</div>
            <div class="loader-subtext">يرجى الانتظار</div>
        </div>
    </div>

    @include('components.footer')

    @stack('modals')
    {{-- Toastr Notifications --}}
    <script>
        $(document).ready(function () {
            

            

            

            

            @if(count($errors) > 0)
                @foreach($errors->all() as $error)
                    flasher.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS aligned to CSS version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Application Utilities -->
    <script src="{{ asset('js/app-utils.js') }}" defer></script>
    <!-- Project Forms Fix -->
    <script src="{{ asset('js/project-forms-fix.js') }}" defer></script>
    <!-- Page Loader -->
    <script src="{{ asset('js/page-loader.js') }}"></script>

    <!-- Layout JavaScript -->
    <script>
        (function () {
            'use strict';
            // Wait for DOM to be ready
            document.addEventListener('DOMContentLoaded', function () {
                // Notification Toggle Logic
                const notificationToggle = document.getElementById('notificationToggle');
                const notificationDropdown = document.getElementById('notificationDropdown');

                if (notificationToggle && notificationDropdown) {
                    notificationToggle.addEventListener('click', function (e) {
                        e.stopPropagation();
                        notificationDropdown.classList.toggle('active');
                    });

                    document.addEventListener('click', function (e) {
                        if (!notificationDropdown.contains(e.target) && e.target !== notificationToggle) {
                            notificationDropdown.classList.remove('active');
                        }
                    });

                    // Prevent closing when clicking inside dropdown
                    notificationDropdown.addEventListener('click', function (e) {
                        e.stopPropagation();
                    });
                }

                // Sidebar management
                const sidebar = document.getElementById('sidebar');
                const toggleBtn = document.getElementById('sidebarToggle');
                const overlay = document.getElementById('sidebarOverlay');
                const icon = toggleBtn ? toggleBtn.querySelector('i') : null;

                // دالة لتحديث ارتفاع القائمة ديناميكياً
                function updateSidebarHeight() {
                    if (sidebar) {
                        const headerHeight = document.querySelector('.navbar').offsetHeight;
                        const viewportHeight = window.innerHeight;
                        sidebar.style.height = `calc(${viewportHeight}px - ${headerHeight}px)`;
                    }
                }

                // دالة لإغلاق القائمة على الجوال
                function closeSidebarOnMobile() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.add('collapsed');
                        if (overlay) overlay.classList.remove('active');
                        if (icon) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                        localStorage.setItem('sidebar-collapsed', 'true');
                    }
                }

                if (sidebar && toggleBtn && icon) {
                    // تحميل الحالة المحفوظة
                    const savedState = localStorage.getItem('sidebar-collapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    } else {
                        sidebar.classList.remove('collapsed');
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');

                        // على الجوال، إظهار طبقة التعتيم
                        if (window.innerWidth <= 992 && overlay) {
                            overlay.classList.add('active');
                        }
                    }

                    // تحديث الارتفاع عند التحميل وعند تغيير حجم النافذة
                    updateSidebarHeight();
                    window.addEventListener('resize', function () {
                        updateSidebarHeight();

                        // على الشاشات الكبيرة، إزالة طبقة التعتيم
                        if (window.innerWidth > 992) {
                            if (overlay) overlay.classList.remove('active');
                        } else {
                            // على الجوال، إظهار طبقة التعتيم إذا كانت القائمة مفتوحة
                            if (!sidebar.classList.contains('collapsed')) {
                                if (overlay) overlay.classList.add('active');
                            }
                        }
                    });

                    // تبديل القائمة الجانبية
                    toggleBtn.addEventListener('click', function () {
                        sidebar.classList.toggle('collapsed');
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        localStorage.setItem('sidebar-collapsed', isCollapsed);

                        // تغيير الأيقونة
                        if (isCollapsed) {
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                            if (overlay) overlay.classList.remove('active');
                        } else {
                            icon.classList.remove('fa-bars');
                            icon.classList.add('fa-times');

                            // على الجوال، إظهار طبقة التعتيم
                            if (window.innerWidth <= 992) {
                                if (overlay) overlay.classList.add('active');
                            }
                        }
                    });

                    // إغلاق القائمة عند النقر على طبقة التعتيم (للجوال)
                    if (overlay) {
                        overlay.addEventListener('click', closeSidebarOnMobile);
                    }

                    // إغلاق القائمة عند النقر على رابط (للجوال)
                    const navLinks = sidebar.querySelectorAll('.nav-link');
                    navLinks.forEach(link => {
                        link.addEventListener('click', function (e) {
                            // إذا كان الرابط ليس لتبديل القائمة الفرعية
                            if (!this.getAttribute('data-bs-toggle') || this.getAttribute('data-bs-toggle') !== 'collapse') {
                                closeSidebarOnMobile();
                            }
                        });
                    });
                }

                // تهيئة toastr
                if (typeof toastr !== 'undefined') {
                    toastr.options = {
                        "positionClass": "toast-bottom-left",
                        "closeButton": true,
                        "progressBar": true,
                        "timeOut": 5000,
                        "extendedTimeOut": 1000
                    };
                }

                console.log('✅ Layout JavaScript Initialized');
            });
        })();
    </script>

    <!-- إصلاح مشكلة التواريخ القديمة في جميع الصفحات -->
    <script src="{{ asset('js/date-fix.js') }}" defer></script>
    <!-- Hijri Date Converter - تحويل التواريخ الهجرية -->
    <script src="{{ asset('js/hijri-converter.js') }}" defer></script>
    <!-- Data Operations Utility - عمليات البيانات الموحدة -->
    <script src="{{ asset('js/data-operations.js') }}" defer></script>
    <!-- Date Converter Utility - تحويل التواريخ -->
    <script src="{{ asset('js/date-converter.js') }}" defer></script>
    <!-- Comprehensive Form Fixes - إصلاحات شاملة للنماذج -->
    <script src="{{ asset('js/comprehensive-form-fixes.js') }}" defer></script>
    <!-- Preliminary Activities Manager - إدارة الأنشطة التمهيدية -->
    <script src="{{ asset('js/preliminary-activities-manager.js') }}" defer></script>

    <!-- Global Searchable Dropdowns -->
    <script src="{{ asset('js/global-search-dropdowns.js') }}" defer></script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>