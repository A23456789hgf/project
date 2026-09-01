<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>نظام متابعة المشاريع - @yield('title', 'الرئيسية')</title>
    <link rel="icon" href="{{ asset('images/logo.ico') }}" type="image/x-icon" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">

    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/unified-arabic-design.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/table-auto-fit.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/configuration-styles.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/responsive-system.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* Professional Dashboard Palette */
            --primary: #0f172a;
            /* Slate 900 */
            --primary-soft: #1e293b;
            /* Slate 800 */
            --accent: #3b82f6;
            /* Modern Blue */
            --accent-gold: #c9a961;
            /* User's Gold */
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;

            /* Neutrals */
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;

            /* Layout constants */
            --header-h: 64px;
            --sidebar-w: 260px;
            --sidebar-collapsed-w: 80px;
            --radius: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);

            /* Typography */
            --font-family: 'Cairo', sans-serif;
            --font-body: 'Tajawal', sans-serif;
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-main);
            color: var(--text-main);
            font-size: 0.9rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar Styling (Modern Dark) */
        .sidebar-container {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--primary);
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            right: 0;
            top: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            border-left: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-container.collapsed {
            width: var(--sidebar-collapsed-w);
        }

        .sidebar-brand {
            height: var(--header-h);
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-brand img {
            width: 32px;
            height: 32px;
            margin-left: 12px;
            flex-shrink: 0;
        }

        .sidebar-brand span {
            font-weight: 700;
            font-size: 1.1rem;
            color: #f8fafc;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

        /* Nav Link Base - Clear Icons */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 14px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .nav-link i {
            width: 24px;
            font-size: 1.15rem;
            margin-left: 12px;
            text-align: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-link.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .sidebar-container.collapsed .nav-link span,
        .sidebar-container.collapsed .sidebar-brand span {
            display: none;
        }

        /* Navbar Styling */
        .navbar {
            height: var(--header-h);
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            right: var(--sidebar-w);
            z-index: 1030;
            transition: all 0.3s;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-container.collapsed~.navbar {
            right: var(--sidebar-collapsed-w);
        }

        /* Mobile Menu Toggle Button */
        .navbar-toggler {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-main);
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.2s;
            line-height: 1;
        }

        .navbar-toggler:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .navbar-toggler:focus {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }

        /* Main Content Styling */
        .main-content {
            margin-top: var(--header-h);
            margin-right: var(--sidebar-w);
            padding: 24px;
            flex: 1;
            transition: all 0.3s;
            width: 100%;
            box-sizing: border-box;
            min-height: calc(100vh - var(--header-h));
        }

        .sidebar-container.collapsed~.main-content {
            margin-right: var(--sidebar-collapsed-w);
        }

        /* Cards and UI Components */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s;
            margin-bottom: 1rem;
        }

        .card:hover {
            box-shadow: var(--shadow);
        }

        /* Buttons Styling */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        /* Loader Overlay */
        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .loader-overlay.active {
            display: flex;
        }

        .spinner-modern {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.2);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* High-DPI Display Optimization for Icons */
        @media (-webkit-min-device-pixel-ratio: 2),
        (min-resolution: 192dpi) {
            .nav-link i {
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
        }

        /* ========== RESPONSIVE BREAKPOINTS ========== */

        /* Large Desktops (1200px+) */
        @media (min-width: 1200px) {
            .main-content {
                max-width: calc(100% - var(--sidebar-w));
            }
        }

        /* Tablets & Small Desktops (992px - 1200px) */
        @media (min-width: 992px) and (max-width: 1200px) {
            .sidebar-container {
                width: var(--sidebar-collapsed-w);
            }

            .sidebar-container .nav-link span,
            .sidebar-brand span {
                display: none;
            }

            .navbar {
                right: var(--sidebar-collapsed-w);
            }

            .main-content {
                margin-right: var(--sidebar-collapsed-w);
                padding: 20px;
            }

            .nav-link {
                justify-content: center;
                padding: 12px 10px;
            }

            .nav-link i {
                margin-left: 0;
                width: 20px;
                font-size: 1.2rem;
            }
        }

        /* Mobile & Small Tablets (max 991.98px) */
        @media (max-width: 991.98px) {
            .sidebar-container {
                transform: translateX(100%);
                box-shadow: none;
            }

            .sidebar-container.show {
                transform: translateX(0);
                box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
            }

            .navbar {
                right: 0 !important;
                padding: 0 16px;
            }

            .main-content {
                margin-right: 0 !important;
                padding: 16px;
            }

            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1035;
                display: none;
            }

            .sidebar-overlay.active {
                display: block;
            }

            .navbar-toggler {
                display: block;
            }

            .nav-link i {
                font-size: 1.4rem;
                width: 28px;
                height: 28px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-left: 0;
            }

            .nav-link {
                padding: 12px 16px;
                justify-content: flex-end;
                gap: 10px;
            }

            .nav-link span {
                font-size: 1rem;
            }

            .sidebar-content {
                padding: 12px 8px;
            }

            .sidebar-brand {
                padding: 0 16px;
            }

            .sidebar-brand span {
                font-size: 1rem;
            }
        }

        /* Small Mobile (max 480px) */
        @media (max-width: 480px) {
            .navbar {
                padding: 0 12px;
                height: 56px;
            }

            :root {
                --header-h: 56px;
            }

            .main-content {
                padding: 12px;
                margin-top: 56px;
            }

            body {
                font-size: 0.85rem;
            }

            .card {
                border-radius: 10px;
            }

            .btn {
                padding: 7px 14px;
                font-size: 0.85rem;
            }
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        a,
        button,
        input,
        select,
        textarea {
            transition: all 0.2s ease;
        }
    </style>
    @yield('styles')
</head>

<body>
    <div class="wrapper" style="display: flex; flex-direction: column; width: 100%;">
        @include('components.navbar')

        <main class="main-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    <div class="loader-overlay" id="pageLoader">
        <div class="text-center">
            <div class="spinner-modern mb-3 mx-auto"></div>
            <div class="h5 font-weight-bold">جاري التحميل...</div>
        </div>
    </div>

    @include('components.footer')

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('js/app-utils.js') }}" defer></script>
    <script src="{{ asset('js/page-loader.js') }}"></script>
    <script src="{{ asset('js/data-operations.js') }}" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileMenuClose = document.getElementById('mobileMenuClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            // Toggle sidebar (desktop collapse/expand, mobile open/close)
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    const isMobile = window.innerWidth < 992;
                    if (isMobile) {
                        if (sidebar.classList.contains('show')) {
                            sidebar.classList.remove('show');
                            sidebarOverlay.classList.remove('active');
                        } else {
                            sidebar.classList.add('show');
                            sidebarOverlay.classList.add('active');
                        }
                    } else {
                        sidebar.classList.toggle('collapsed');
                        localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                    }
                });
            }

            // Helper to close mobile menu
            const closeMobileSidebar = () => {
                if (sidebar) sidebar.classList.remove('show');
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            };

            if (mobileMenuClose) {
                mobileMenuClose.addEventListener('click', closeMobileSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeMobileSidebar);
            }

            // Restore sidebar state on desktop
            if (window.innerWidth >= 992 && sidebar) {
                const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
                if (isCollapsed) sidebar.classList.add('collapsed');
            }

            // Close mobile menu on resize to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992 && sidebar) {
                    sidebar.classList.remove('show');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('active');
                }
            });

            // Toastr global config
            if (typeof toastr !== 'undefined') {
                toastr.options = { "positionClass": "toast-top-left", "closeButton": true, "progressBar": true };
            }

            // Toastr messages logic
            @if(session('success')) toastr.success("{{ session('success') }}"); @endif
            @if(session('error')) toastr.error("{{ session('error') }}"); @endif
            @if($errors->any())
                @foreach($errors->all() as $error) toastr.error("{{ $error }}"); @endforeach
            @endif
        });
    </script>

    @stack('modals')
    @yield('scripts')
    @stack('scripts')
</body>

</html>