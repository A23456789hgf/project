<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="نظام متابعة المشاريع - لوحة التحكم">
    <title>نظام متابعة المشاريع</title>
    <link rel="icon" href="{{ asset('images/logo.ico') }}" type="image/x-icon" />

    <!-- Performance Optimizations -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" as="style">

    {{-- @vite(['resources/css/activity.css', 'resources/js/activity.js']) --}}

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">

    <!-- Google Fonts - Optimized Loading -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet"
        media="print" onload="this.media='all'">

    <!-- Bootstrap 5.3.3 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />

    <!-- Font Awesome -->
    <script data-auto-replace-svg="nest" defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/unified-arabic-design.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/table-auto-fit.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/configuration-styles.css') }}" />

    @yield('styles')

    <style>
        :root {
            /* 🎨 Color Palette - Light Mode */
            --primary: #1e3a5f;
            --primary-light: #3b82f6;
            --primary-hover: #2563eb;
            --primary-dark: #1e40af;
            --accent: #c9a961;
            --accent-hover: #d4b876;

            /* Neutral Colors */
            --white: #ffffff;
            --background: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;

            /* Status Colors */
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;

            /* Layout */
            --sidebar-width: 72px;
            --sidebar-width-expanded: 240px;
            --header-height: 56px;
            --content-padding: 1.25rem;

            /* Typography */
            --font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            --text-xs: 0.75rem;
            --text-sm: 0.8125rem;
            --text-base: 0.875rem;
            --text-lg: 1rem;
            --text-xl: 1.125rem;
            --font-medium: 500;
            --font-semibold: 600;

            /* Spacing */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;

            /* Shadows - Subtle & Modern */
            --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            --shadow-soft: 0 2px 12px rgba(0, 0, 0, 0.06);

            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-full: 9999px;

            /* Transitions */
            --transition-fast: 150ms ease;
            --transition: 200ms ease;
            --transition-slow: 300ms ease;

            /* Z-Index Scale */
            --z-dropdown: 100;
            --z-sticky: 200;
            --z-fixed: 300;
            --z-modal: 400;
            --z-popover: 500;
            --z-tooltip: 600;
        }

        /* ===== Base Reset & Typography ===== */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: var(--font-family);
            font-size: var(--text-base);
            font-weight: 400;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--background);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-top: var(--header-height);
        }

        /* ===== Focus States - Accessibility ===== */
        :focus-visible {
            outline: 2px solid var(--primary-light);
            outline-offset: 2px;
        }

        a {
            color: inherit;
            text-decoration: none;
            transition: color var(--transition-fast);
        }

        /* ===== Navbar ===== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--surface);
            border-bottom: 1px solid var(--border-light);
            box-shadow: var(--shadow-xs);
            z-index: var(--z-fixed);
            padding: 0 var(--space-4);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: box-shadow var(--transition);
        }

        .navbar.scrolled {
            box-shadow: var(--shadow-sm);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-weight: var(--font-semibold);
            font-size: var(--text-lg);
            color: var(--primary);
        }

        .navbar-brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        .navbar-brand span {
            display: inline-block;
            transition: opacity var(--transition-fast);
        }

        .menu-toggle {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.125rem;
            padding: var(--space-2);
            border-radius: var(--radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            transition: all var(--transition-fast);
        }

        .menu-toggle:hover,
        .menu-toggle:focus {
            background: var(--border-light);
            color: var(--primary);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .nav-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1rem;
            padding: var(--space-2);
            border-radius: var(--radius);
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all var(--transition-fast);
        }

        .nav-btn:hover,
        .nav-btn:focus {
            background: var(--border-light);
            color: var(--primary);
        }

        .nav-btn .badge {
            position: absolute;
            top: 6px;
            left: 6px;
            background: var(--danger);
            color: white;
            font-size: 0.625rem;
            font-weight: 600;
            min-width: 16px;
            height: 16px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        /* ===== Sidebar - Modern Compact ===== */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            right: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: var(--surface);
            border-left: 1px solid var(--border-light);
            z-index: var(--z-sticky);
            display: flex;
            flex-direction: column;
            transition: width var(--transition-slow), box-shadow var(--transition);
            overflow-x: hidden;
        }

        .sidebar:hover,
        .sidebar.expanded {
            width: var(--sidebar-width-expanded);
            box-shadow: var(--shadow);
        }

        .sidebar-nav {
            flex: 1;
            padding: var(--space-3) 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: var(--radius-full);
        }

        .nav-item {
            margin: var(--space-1) var(--space-2);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            color: var(--text-secondary);
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            border-radius: var(--radius);
            transition: all var(--transition-fast);
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-link i {
            font-size: 1.125rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
            transition: color var(--transition-fast);
        }

        .nav-link .label {
            opacity: 0;
            transform: translateX(10px);
            transition: opacity var(--transition-fast), transform var(--transition-fast);
        }

        .sidebar:hover .nav-link .label,
        .sidebar.expanded .nav-link .label {
            opacity: 1;
            transform: translateX(0);
        }

        .nav-link:hover,
        .nav-link:focus {
            background: var(--border-light);
            color: var(--primary);
        }

        .nav-link:hover i,
        .nav-link:focus i {
            color: var(--primary-light);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: var(--primary);
            font-weight: var(--font-semibold);
            border-right: 3px solid var(--primary-light);
            padding-right: calc(var(--space-4) - 3px);
        }

        .nav-link.active i {
            color: var(--primary-light);
        }

        .nav-group {
            margin: var(--space-4) var(--space-2) var(--space-2);
        }

        .nav-group-title {
            padding: var(--space-2) var(--space-4);
            font-size: var(--text-xs);
            font-weight: var(--font-semibold);
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .sidebar:hover .nav-group-title,
        .sidebar.expanded .nav-group-title {
            opacity: 1;
        }

        .nav-collapse {
            padding-right: var(--space-6);
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-slow);
        }

        .nav-link[aria-expanded="true"]+.nav-collapse {
            max-height: 500px;
        }

        .nav-collapse .nav-link {
            padding: var(--space-2) var(--space-4);
            font-size: var(--text-xs);
            margin: 2px 0;
        }

        .nav-collapse .nav-link::before {
            content: "";
            position: absolute;
            right: calc(var(--space-4) + 10px);
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: var(--radius-full);
            background: var(--border);
            transition: background var(--transition-fast);
        }

        .nav-collapse .nav-link:hover::before,
        .nav-collapse .nav-link.active::before {
            background: var(--primary-light);
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: var(--content-padding);
            min-height: calc(100vh - var(--header-height));
            transition: margin-right var(--transition-slow);
        }

        .sidebar.expanded~.main-content {
            margin-right: var(--sidebar-width-expanded);
        }

        /* ===== Cards ===== */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
            transition: all var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow);
            border-color: var(--border-light);
        }

        .card-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--border-light);
            background: transparent;
            font-weight: var(--font-semibold);
            font-size: var(--text-lg);
            color: var(--text-primary);
        }

        .card-body {
            padding: var(--space-5);
        }

        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            padding: 0.5rem 1rem;
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            border-radius: var(--radius);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border-color: var(--border);
        }

        .btn-outline:hover {
            background: var(--border-light);
            border-color: var(--border);
        }

        .btn-sm {
            padding: 0.375rem 0.875rem;
            font-size: var(--text-xs);
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: var(--radius);
        }

        /* ===== Forms ===== */
        .form-control,
        .form-select {
            font-size: var(--text-sm);
            padding: 0.5rem 0.875rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--text-primary);
            transition: all var(--transition-fast);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }

        .form-label {
            font-size: var(--text-sm);
            font-weight: var(--font-medium);
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }

        /* ===== Tables ===== */
        .table {
            font-size: var(--text-sm);
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--border-light);
        }

        .table th {
            font-weight: var(--font-semibold);
            color: var(--text-secondary);
            padding: var(--space-3) var(--space-4);
            border-bottom-width: 1px;
        }

        .table td {
            padding: var(--space-3) var(--space-4);
            border-bottom-color: var(--border-light);
            color: var(--text-primary);
        }

        .table-hover tbody tr:hover {
            background: var(--border-light);
        }

        /* ===== Notifications Dropdown ===== */
        .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--space-2);
            min-width: 280px;
            margin-top: var(--space-2);
        }

        .notification-item {
            display: flex;
            gap: var(--space-3);
            padding: var(--space-3);
            border-radius: var(--radius);
            transition: background var(--transition-fast);
        }

        .notification-item:hover {
            background: var(--border-light);
        }

        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .notification-icon.success {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
        }

        .notification-icon.warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .notification-icon.info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info);
        }

        .notification-icon.danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: var(--font-medium);
            font-size: var(--text-sm);
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .notification-desc {
            font-size: var(--text-xs);
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .notification-time {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: var(--space-1);
        }

        /* ===== Loader ===== */
        .loader {
            position: fixed;
            inset: 0;
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: var(--z-modal);
            transition: opacity var(--transition-slow), visibility var(--transition-slow);
        }

        .loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            text-align: center;
            padding: var(--space-6);
        }

        .loader-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--border-light);
            border-top-color: var(--primary-light);
            border-radius: var(--radius-full);
            animation: spin 0.8s linear infinite;
            margin: 0 auto var(--space-4);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            font-weight: var(--font-medium);
            color: var(--text-primary);
            margin-bottom: var(--space-1);
        }

        .loader-subtext {
            font-size: var(--text-xs);
            color: var(--text-muted);
        }

        /* ===== Overlay for Mobile Sidebar ===== */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            z-index: calc(var(--z-sticky) - 1);
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition);
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* ===== Responsive Design ===== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
                box-shadow: var(--shadow-lg);
            }

            .sidebar.active {
                transform: translateX(0);
                width: var(--sidebar-width-expanded) !important;
            }

            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .main-content {
                margin-right: 0 !important;
            }

            .navbar-brand span {
                display: none;
            }
        }

        @media (max-width: 768px) {
            :root {
                --content-padding: 1rem;
            }

            .navbar {
                padding: 0 var(--space-3);
            }

            .main-content {
                padding: var(--space-4);
            }

            .card-body {
                padding: var(--space-4);
            }

            .dropdown-menu {
                min-width: 260px;
                right: 0 !important;
                left: auto !important;
            }
        }

        @media (max-width: 480px) {
            .navbar-actions {
                gap: 0;
            }

            .nav-btn {
                width: 32px;
                height: 32px;
                padding: var(--space-1);
            }

            .btn {
                padding: 0.45rem 0.9rem;
                font-size: var(--text-xs);
            }

            .table-responsive {
                font-size: var(--text-xs);
            }
        }

        /* ===== Utilities ===== */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .bg-surface {
            background: var(--surface);
        }

        .border-light {
            border-color: var(--border-light) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* ===== Select2 Customization ===== */
        .select2-container .select2-selection--single {
            height: 38px;
            border-color: var(--border);
            border-radius: var(--radius);
        }

        .select2-container--focus .select2-selection--single {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .select2-dropdown {
            border-color: var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .select2-search__field {
            font-family: var(--font-family);
            font-size: var(--text-sm);
        }

        /* ===== Print Styles ===== */
        @media print {

            .navbar,
            .sidebar,
            .sidebar-overlay,
            .loader {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Navbar -->
    @include('components.navbar')

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <div class="nav-group">
                <div class="nav-group-title">القائمة الرئيسية</div>
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span class="label">لوحة التحكم</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#projectsMenu">
                        <i class="fas fa-tasks"></i>
                        <span class="label">المشاريع</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <div class="nav-collapse collapse" id="projectsMenu">
                        <a href="{{ route('projects.index') }}" class="nav-link">جميع المشاريع</a>
                        <a href="{{ route('projects.create') }}" class="nav-link">مشروع جديد</a>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="{{ route('tasks.index') }}" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <span class="label">المهام</span>
                    </a>
                </div>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">الإعدادات</div>
                <div class="nav-item">
                    <a href="{{ route('profile.show') }}" class="nav-link">
                        <i class="fas fa-user-cog"></i>
                        <span class="label">الملف الشخصي</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="label">تسجيل الخروج</span>
                    </a>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid px-0">
            @yield('content')
        </div>
    </main>

    <!-- Page Loader -->
    <div class="loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-text">جاري التحميل...</div>
            <div class="loader-subtext">يرجى الانتظار قليلاً</div>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    @include('components.footer')
    @stack('modals')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Hide loader on page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('pageLoader')?.classList.add('hidden');
            }, 300);
        });

        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.querySelector('.menu-toggle');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                if (window.innerWidth < 992) {
                    sidebar?.classList.toggle('active');
                    overlay?.classList.toggle('active');
                } else {
                    sidebar?.classList.toggle('expanded');
                }
            }

            menuToggle?.addEventListener('click', toggleSidebar);
            overlay?.addEventListener('click', () => {
                sidebar?.classList.remove('active');
                overlay?.classList.remove('active');
            });

            // Close sidebar on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    sidebar?.classList.remove('active');
                    overlay?.classList.remove('active');
                }
            });

            // Navbar scroll effect
            window.addEventListener('scroll', () => {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 10) {
                    navbar?.classList.add('scrolled');
                } else {
                    navbar?.classList.remove('scrolled');
                }
            });

            // Initialize Select2 with Arabic support
            if ($.fn.select2) {
                $('select:not(.no-select2)').select2({
                    theme: 'bootstrap-5',
                    language: 'ar',
                    dir: 'rtl'
                });
            }

            // Toastr Configuration
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-left',
                    timeOut: 4000,
                    rtl: true
                };

                
                
                
                
                @if($errors->any())
                    @foreach($errors->all() as $error)
                        flasher.error("{{ $error }}");
                    @endforeach
                @endif
            }
        });
    </script>

    @stack('scripts')
</body>

</html>