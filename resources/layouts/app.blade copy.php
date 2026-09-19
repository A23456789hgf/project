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
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />
    <!-- Bootstrap 5.3.3 RTL (single source) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />
    <script data-auto-replace-svg="nest" defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <!-- خط Cairo للعربية -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'" />
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

.sidebar-container.collapsed ~ .main-content {
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
    from { transform: rotate(0deg); }
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
a, button, input, select, textarea {
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
    
    @include('components.footer')

    @stack('modals')
    {{-- Toastr Notifications --}}
    <script>
        $(document).ready(function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}");
            @endif

            @if(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @endif

            @if(session('info'))
                toastr.info("{{ session('info') }}");
            @endif

            @if(count($errors) > 0)
                @foreach($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif
        });
    </script>
</body>
</html>