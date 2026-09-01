{{--
==========================================================================
Premium Application Layout Fragment (إطار التصميم المحترف والأنيق)
Developed with Creativity & Precision
==========================================================================
--}}

<!-- المحتوى الرئيسي للنظام -->
<main class="main-content" style="transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);">
    <div class="container-fluid py-4 animate-fade-in">
        @yield('content')
    </div>
</main>

{{-- نهاية مغلف الصفحة الرئيسي في حال تم فتحه في جزء سابق --}}
</div>

<!-- ==========================================================================
     شاشة التحميل الذكية والمبتكرة (Ultra-Premium Creative Loader Overlay)
     ========================================================================== -->
<div class="loader-overlay" id="pageLoader">
    <div class="loader-container">
        <div class="brand-loader-glowing">
            <div class="double-ring-inner"></div>
            <div class="double-ring-outer"></div>
            <div class="loader-brand-icon">
                <i class="fas fa-seedling animate-pulse-slow"></i>
            </div>
        </div>
        <div class="loader-content-text">
            <h5 class="loader-title">جاري تحضير النظام</h5>
            <p class="loader-subtitle">يرجى الانتظار لحين تحميل البيانات الإحصائية والتقارير...</p>
        </div>
        <div class="loader-progress-bar">
            <div class="loader-progress-fill"></div>
        </div>
    </div>
</div>

{{-- استدعاء المكون السفلي --}}
@include('components.footer')

{{-- تضمين jQuery/Bootstrap/Select2/app-utils/page-loader محلياً عبر المكون السفلي --}}

<!-- ==========================================================================
     التحكم البرمجي بالتفاعل الفائق للقائمة الجانبية وشاشة العرض
     ========================================================================== -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        // دالة تحكم التبديل السلس للقائمة الجانبية
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function (e) {
                e.preventDefault();
                const isMobile = window.innerWidth < 992;

                if (isMobile) {
                    if (sidebar) {
                        sidebar.classList.toggle('show');
                        const isShown = sidebar.classList.contains('show');
                        if (sidebarOverlay) {
                            if (isShown) {
                                sidebarOverlay.classList.add('active');
                            } else {
                                sidebarOverlay.classList.remove('active');
                            }
                        }
                    }
                } else {
                    if (sidebar) {
                        sidebar.classList.toggle('collapsed');
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        localStorage.setItem('sidebar-collapsed', isCollapsed);

                        // إرسال حدث مخصص للمكونات الأخرى لتحديث الرسوم البيانية أو الجداول
                        window.dispatchEvent(new Event('sidebar-toggled'));
                    }
                }
            });
        }

        // إغلاق القائمة الجانبية في بيئة الهواتف الذكية
        const closeMobileSidebar = function () {
            if (sidebar) sidebar.classList.remove('show');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        };

        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', closeMobileSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        // استعادة الحالة المفضلة للمستخدم عند التحميل الأولي
        if (window.innerWidth >= 992 && sidebar) {
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }

        // معالجة تغيير حجم الشاشة بذكاء وتجنب التداخل البصري
        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (window.innerWidth >= 992 && sidebar) {
                    sidebar.classList.remove('show');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            }, 100);
        });
    });
</script>

@stack('modals')
@yield('scripts')
@stack('scripts')
</body>
{{-- استدعاء إشعارات النظام الراقية PHPFlasher --}}
@flasher_render
@flasher_render_assets