<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') - @endifنظام متابعة المشاريع</title>
    <link rel="icon" href="{{ asset('images/logo.ico') }}" type="image/x-icon" />

    <!-- All assets and fonts self-hosted locally via Vite -->

    <!-- Vendor Libraries (Bootstrap RTL, FontAwesome, Select2, Flasher) -->
    <link rel="stylesheet" href="{{ asset('css/libs/bootstrap.rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/libs/font-awesome.all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/libs/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/libs/select2-bootstrap-5-theme.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher-toastr.min.css') }}" />

    <!-- Unified Master Stylesheet & Application JS (Vite Pipeline) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- jQuery (loaded first - required by Select2, financing, and other components) -->
    <script src="{{ asset('js/libs/jquery-3.7.0.min.js') }}"></script>
    <!-- PHPFlasher & Toastr Scripts (Local) -->
    <script src="{{ asset('vendor/flasher/flasher.min.js') }}"></script>
    <script src="{{ asset('vendor/flasher/flasher-toastr.min.js') }}"></script>
    <script src="{{ asset('js/app-utils.js') }}"></script>
    @yield('styles')
    <style>
        :root {
            /* Color Palette */
            --primary: #1e3a5f;
            --primary-light: #2d5a8f;
            --primary-dark: #0f1f3d;
            --primary-color: #1e3a5f; /* alias for unified-arabic-design.css compatibility */
            --accent: #c9a961;
            --accent-light: #d4b876;
            --accent-dark: #b89850;
            --sky-blue: #3a8fc7; /* Sky Blue - secondary accent */
            --sky-blue-light: #5ba3d0;
            --gold-color: #c9a961; /* Gold alias */

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
            --sidebar-width: 240px;
            --header-height: 50px;
            --content-max-width: 1400px;

            /* Spacing */
            --space-xs: 0.25rem;
            --space-sm: 0.4rem;
            --space-md: 0.8rem;
            --space-lg: 1.2rem;
            --space-xl: 1.6rem;
            --space-2xl: 2.4rem;

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
            font-weight: 400;
        }

        b, strong, th, h1, h2, h3, h4, h5, h6, .fw-bold, .font-weight-bold {
            font-weight: 700 !important;
        }

        .fw-semibold, .font-weight-semibold, .card-header, .card-header h5, .form-label, .btn {
            font-weight: 600 !important;
        }

        html {
            scroll-behavior: smooth;
            font-size: 14px;
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
            /* Same subtle separator as sidebar's internal borders */
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
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
            width: 32px;
            height: 32px;
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
            padding: var(--space-md) var(--space-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            /* Exactly matches the navbar background so the two zones look like one band */
            background: var(--primary);
            min-height: 0;
        }

        .sidebar-header h5 {
            font-size: var(--font-size-sm);
            font-weight: 700;
            color: rgba(255, 255, 255, 0.75);
            margin: 0;
            letter-spacing: 0.03em;
            text-transform: uppercase;
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
            background: rgba(15, 31, 61, 0.75);
            display: flex;
            visibility: hidden;
            opacity: 0;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.15s ease, visibility 0.15s ease;
            pointer-events: none;
            will-change: opacity;
        }

        .loader-overlay.active {
            visibility: visible;
            opacity: 1;
            pointer-events: all;
        }

        .loader-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--space-xl) var(--space-2xl);
            text-align: center;
            max-width: 320px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .loader-overlay.active .loader-content {
            transform: none;
        }

        .loader-spinner {
            width: 44px;
            height: 44px;
            border: 3px solid var(--gray-100);
            border-top: 3px solid var(--accent);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin: 0 auto var(--space-md);
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

        /* ==========================================================================
   DYNAMIC PERMISSIONS VISIBILITY SYSTEM
   Automatically hiddes elements based on role permissions
   ========================================================================== */
        @auth

            @php 
                $roleId = auth()->user()->role_id;
                $isAdmin = auth()->user()->isAdmin() || (auth()->user()->role && auth()->user()->role->full_access);
                
                if ($isAdmin) {
                    $disabledPermissions = [];
                } else {
                    $version = \App\Models\User::getRolePermissionsVersion($roleId);
                    $matrixPath = base_path('resources/views/roles/partials/_permissions_matrix.blade.php');
                    $matrixMtime = file_exists($matrixPath) ? filemtime($matrixPath) : 0;

                    $disabledPermissions = \Illuminate\Support\Facades\Cache::remember('disabled_perms_css_' . $roleId . '_v' . $version . '_m' . $matrixMtime, now()->addHours(1), function () use ($roleId) {
                        $role = \App\Models\Role::find($roleId);
                        if (!$role) return [];

                        $allDbPerms = \App\Models\Permission::pluck('slug')->toArray();
                        $matrixPerms = \App\Services\PermissionResolver::getPermissions();
                        $matrixSlugs = array_column($matrixPerms, 'slug');
                        $allSlugs = array_unique(array_merge($allDbPerms, $matrixSlugs));
                        
                        // Role-based permissions only (SSOT intersection)
                        $rolePerms = \App\Models\Permission::whereHas('rolePermissions', function ($q) use ($roleId) {
                            $q->where('role_id', $roleId);
                        })->pluck('slug')->toArray();

                        $roleSlugs = array_intersect($rolePerms, $matrixSlugs);
                        return array_diff($allSlugs, $roleSlugs);
                    });

                    $cssString = \Illuminate\Support\Facades\Cache::remember('disabled_perms_css_str_' . $roleId . '_v' . $version . '_m' . $matrixMtime, now()->addHours(1), function () use ($disabledPermissions) {
                        if (empty($disabledPermissions)) return '';
                        $selectors = [];
                        foreach ($disabledPermissions as $perm) {
                            $selectors[] = '.auth-perm-' . str_replace('.', '-', $perm);
                            $selectors[] = '[data-perm="' . $perm . '"]';
                        }
                        return implode(",\n            ", $selectors) . " {\n                display: none !important;\n            }";
                    });
                }
            @endphp
            @if(!empty($cssString))
                {!! $cssString !!}
            @endif

        @endauth
    </style>
<script>
window.onerror = function(msg, url, line, col, error) { 
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    fetch('/log-js-error', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token.getAttribute('content') }, 
        body: JSON.stringify({ message: msg, url: url, line: line, col: col }) 
    }); 
}; 
window.addEventListener('unhandledrejection', function(event) { 
    var token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;
    fetch('/log-js-error', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token.getAttribute('content') }, 
        body: JSON.stringify({ message: event.reason ? event.reason.toString() : 'Unhandled Rejection', url: window.location.href, line: 0, col: 0 }) 
    }); 
});
</script>
</head>



<body>
    @include('components.navbar')


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

    @auth
        @if(auth()->user()->must_change_password)
            @include('components.force-password-change-modal')
        @else
            @include('components.signature-modal')
            @can('chat.view')
                @include('components.chat-widget')
                <script src="{{ asset('js/chat.js') }}"></script>
            @endcan
        @endif
    @endauth

    @include('components.footer')

    {{-- Bootstrap 5 JS Bundle (required for collapse, modal, dropdown, etc.) --}}
    <script src="{{ asset('js/libs/bootstrap.bundle.min.js') }}"></script>

    @yield('scripts')
    @stack('scripts')
    @flasher_render

    {{-- ════════════════════════════════════════════════════════════════════
         GLOBAL "غير ذلك" HANDLER
         Works on EVERY select with data-ajax-type anywhere in the project.
         When Select2 picks "other", a custom text input slides in underneath.
         The input name follows the convention:  custom_[field_name]
         so ProjectDataNormalizer can pick it up on any step save / draft.
         ════════════════════════════════════════════════════════════════════ --}}
    <script>
    (function ($) {
        'use strict';

        /* ─── Utility: derive a custom-field name from a select's name attr ─── */
        function customNameFor(selectName) {
            // e.g. "beneficiary_groups[]" → "custom_beneficiary_group_name"
            if (selectName.indexOf('beneficiary_groups') !== -1) {
                return 'custom_beneficiary_group_name';
            }
            // e.g. "program_id"  → "custom_program_name"
            // e.g. "financings[0][financing_type_id]" → "financings[0][custom_financing_type_name]"
            // e.g. "preliminary_activities[0][procedures][0][costs][0][financial_item_id]" → …[custom_financial_item_name]
            return selectName.replace(/([a-z_]+)_id(\]?)$/, 'custom_$1_name$2');
        }

        /* ─── Insert or retrieve the custom-input wrapper after the select ─── */
        function getOrCreateCustomInput($select) {
            // 1) If a static Blade wrapper is declared via data-custom-wrapper-id, use it
            var staticWrapperId = $select.data('custom-wrapper-id');
            if (staticWrapperId) {
                var $static = $('#' + staticWrapperId);
                if ($static.length) return $static;
            }

            // 2) Otherwise use/create a dynamic wrapper keyed to this element
            var wrapperId = 'other-wrapper-' + $select.attr('id');
            if (!wrapperId || wrapperId === 'other-wrapper-undefined') {
                wrapperId = $select.data('other-wrapper-id');
                if (!wrapperId) {
                    wrapperId = 'other-wrapper-' + Math.random().toString(36).slice(2, 9);
                    $select.data('other-wrapper-id', wrapperId);
                }
            }

            var $existing = $('#' + wrapperId);
            if ($existing.length) return $existing;

            var selectName = $select.attr('name') || '';
            var customFieldName = customNameFor(selectName);
            var ajaxType = $select.data('ajax-type') || '';

            // Human-readable label map
            var labelMap = {
                program: 'اسم البرنامج الجديد',
                priority: 'اسم الأولوية الجديدة',
                domain: 'اسم المجال الجديد',
                subdomain: 'اسم المجال الفرعي الجديد',
                intervention: 'اسم التدخل الجديد',
                beneficiary_group: 'اسم الفئة المستفيدة الجديدة',
                authority: 'اسم الجهة الجديدة',
                financial_item: 'اسم البند المالي الجديد',
                unit: 'اسم الوحدة الجديدة',
                financing_type: 'اسم نوع التمويل الجديد',
                financing_form: 'اسم شكل التمويل الجديد',
                sub_financing_form: 'اسم الشكل الفرعي الجديد',
                funding_source: 'اسم مصدر التمويل الجديد',
                internal_entity: 'اسم الجهة الداخلية الجديدة',
                directorate: 'اسم المديرية الجديدة',
                sub_area: 'اسم المنطقة الفرعية الجديدة',
            };
            var label = labelMap[ajaxType] || 'القيمة الجديدة';

            var $wrapper = $('<div>', {
                id: wrapperId,
                class: 'custom-other-wrapper mt-2',
                style: 'display:none'
            }).html(
                '<label class="form-label small text-primary fw-semibold">' +
                '<i class="fas fa-plus-circle me-1"></i>' + label +
                ' <span class="text-danger">*</span></label>' +
                '<input type="text" name="' + customFieldName + '"' +
                '       class="form-control form-control-sm custom-other-input"' +
                '       placeholder="أدخل ' + label + '...">' +
                '<small class="text-muted" style="font-size:0.72rem;">' +
                '  <i class="fas fa-clock me-1"></i>سيُحفظ بانتظار المراجعة والاعتماد.' +
                '</small>'
            );

            // Insert right after the Select2 container (or the original select if no S2)
            var $s2Container = $select.next('.select2-container');
            if ($s2Container.length) {
                $s2Container.after($wrapper);
            } else {
                $select.after($wrapper);
            }

            return $wrapper;
        }

        /* ─── Show / hide the custom input for a given select ─── */
        function updateOtherField($select) {
            var val = $select.val();
            var text = $select.find('option:selected').text().trim();
            var isOther = (val === 'other' || text === 'غير ذلك');

            var $wrapper = getOrCreateCustomInput($select);
            var $input = $wrapper.find('.custom-other-input');

            if (isOther) {
                $wrapper.slideDown(180);
                $input.prop('required', true);
            } else {
                $wrapper.slideUp(180);
                $input.prop('required', false).val('');
            }
        }

        /* ─── Listen on every Select2 change (covers dynamic rows too) ─── */
        $(document).on('select2:select select2:unselect change', 'select[data-ajax-type]', function () {
            updateOtherField($(this));
        });

        /* ─── On page ready: initialise for selects already set to "other" ─── */
        $(document).ready(function () {
            $('select[data-ajax-type]').each(function () {
                var $sel = $(this);
                var val = $sel.val();
                var text = $sel.find('option:selected').text().trim();
                if (val === 'other' || text === 'غير ذلك') {
                    updateOtherField($sel);
                }
            });
        });

        /* ─── Re-scan when new rows are added dynamically (financing, cost rows) ─── */
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (m) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) return;
                    $(node).find('select[data-ajax-type]').addBack('select[data-ajax-type]').each(function () {
                        var $sel = $(this);
                        // Small delay so Select2 has time to init on the new row
                        setTimeout(function () {
                            var val = $sel.val();
                            var text = $sel.find('option:selected').text().trim();
                            if (val === 'other' || text === 'غير ذلك') {
                                updateOtherField($sel);
                            }
                        }, 400);
                    });
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

    })(jQuery);
    </script>
    
    @include('components.ajax-login-modal')
</body>

</html>
