    <!-- Core JavaScript Libraries -->
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
    (function() {
        'use strict';
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            // Notification Toggle Logic
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            if (notificationToggle && notificationDropdown) {
                notificationToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('active');
                });
                
                document.addEventListener('click', function(e) {
                    if (!notificationDropdown.contains(e.target) && e.target !== notificationToggle) {
                        notificationDropdown.classList.remove('active');
                    }
                });
                
                // Prevent closing when clicking inside dropdown
                notificationDropdown.addEventListener('click', function(e) {
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
                window.addEventListener('resize', function() {
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
                toggleBtn.addEventListener('click', function() {
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
                    link.addEventListener('click', function(e) {
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
