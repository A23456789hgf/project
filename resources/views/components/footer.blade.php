{{-- Toastr removed in favor of PHPFlasher --}}
<script src="{{ asset('js/libs/select2.min.js') }}"></script>

<!-- Global Select2 Interceptor: Ensures search is always enabled and formatting is preserved across ALL pages -->
<script>
    (function($) {
        if (!$.fn.select2) return;
        var originalSelect2 = $.fn.select2;
        $.fn.select2 = function() {
            var args = arguments;
            var options = args[0];
            
            if (typeof options === 'object' || typeof options === 'undefined') {
                return this.each(function() {
                    var $this = $(this);
                    // Clone options to avoid polluting a shared options object
                    var opts = $.extend(true, {}, options || {});
                    
                    // Force search box to appear by removing any minimum length restrictions
                    opts.minimumResultsForSearch = 0;
                    
                    // Always default to bootstrap-5 theme if none specified
                    if (!opts.theme) {
                        opts.theme = 'bootstrap-5';
                    }

                    // Preserve original formatting classes
                    if (!opts.selectionCssClass) {
                        var originalClasses = $this.attr('class') || '';
                        var classesArray = originalClasses.split(/\s+/);
                        var cssClassesToPreserve = classesArray.filter(function(c) {
                            return c !== 'form-select' && c !== 'form-control' && c !== 'select2-hidden-accessible' && c !== 'select2-search' && !c.startsWith('select2');
                        }).join(' ');
                        
                        var dropdownClassesToPreserve = classesArray.filter(function(c) {
                            return c !== 'form-select' && c !== 'form-control' && c !== 'select2-hidden-accessible' && c !== 'select2-search' && !c.startsWith('select2') && c !== 'form-select-sm' && c !== 'form-control-sm' && c !== 'form-select-lg' && c !== 'form-control-lg';
                        }).join(' ');
                        
                        if (cssClassesToPreserve) {
                            opts.selectionCssClass = cssClassesToPreserve;
                            opts.dropdownCssClass = dropdownClassesToPreserve;
                        }
                    }
                    
                    // Initialize with original function
                    originalSelect2.apply($this, [opts]);
                });
            } else {
                return originalSelect2.apply(this, arguments);
            }
        };
        // Preserve original properties (like .defaults)
        for (var key in originalSelect2) {
            $.fn.select2[key] = originalSelect2[key];
        }
    })(jQuery);
</script>

<script src="{{ asset('js/app-utils.js?v=' . time()) }}" defer></script>
<script src="{{ asset('js/project-forms-fix.js?v=' . time()) }}" defer></script>
<script src="{{ asset('js/page-loader.js?v=' . time()) }}"></script>
<script src="{{ asset('js/project-tables.js?v=' . time()) }}" defer></script>
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
            const mobileMenuClose = document.getElementById('mobileMenuClose');

            // SVG Icons for the Menu and Close states
            const svgBars = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20" fill="currentColor"><path d="M0 96C0 78.3 14.3 64 32 64H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 128 0 113.7 0 96zM0 256c0-17.7 14.3-32 32-32H416c17.7 0 32 14.3 32 32s-14.3 32-32 32H32c-17.7 0-32-14.3-32-32zM448 416c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H416c17.7 0 32 14.3 32 32z"/></svg>`;
            const svgTimes = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="20" height="20" fill="currentColor"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>`;

            // Function to update the toggle icon
            function updateToggleIcon(isCollapsed, isMobile) {
                if (!toggleBtn) return;
                if (isMobile) {
                    if (sidebar && sidebar.classList.contains('show')) {
                        toggleBtn.innerHTML = svgTimes;
                    } else {
                        toggleBtn.innerHTML = svgBars;
                    }
                } else {
                    if (!isCollapsed) {
                        toggleBtn.innerHTML = svgTimes;
                    } else {
                        toggleBtn.innerHTML = svgBars;
                    }
                }
            }

            // Function to update sidebar height dynamically
            function updateSidebarHeight() {
                if (sidebar) {
                    const navbarElement = document.querySelector('.navbar');
                    const headerHeight = navbarElement ? navbarElement.offsetHeight : 60;
                    const viewportHeight = window.innerHeight;
                    sidebar.style.height = `calc(${viewportHeight}px - ${headerHeight}px)`;
                }
            }

            // Function to close the sidebar on mobile
            function closeSidebarOnMobile() {
                if (window.innerWidth <= 992) {
                    if (sidebar) sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('active');
                    updateToggleIcon(true, true);
                }
            }

            // Function to open the sidebar on mobile
            function openSidebarOnMobile() {
                if (sidebar) sidebar.classList.add('show');
                if (overlay) overlay.classList.add('active');
                updateToggleIcon(false, true);
            }

            if (sidebar && toggleBtn) {
                const isMobile = window.innerWidth <= 992;

                // Load saved state (only applicable for desktop)
                if (!isMobile) {
                    const savedState = localStorage.getItem('sidebar-collapsed');
                    if (savedState === 'true') {
                        sidebar.classList.add('collapsed');
                        updateToggleIcon(true, false);
                    } else {
                        sidebar.classList.remove('collapsed');
                        updateToggleIcon(false, false);
                    }
                } else {
                    // Mobile sidebar always starts hidden on initial load
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('active');
                    updateToggleIcon(true, true);
                }

                // Update height and resize behaviors
                updateSidebarHeight();
                window.addEventListener('resize', function () {
                    updateSidebarHeight();
                    const currentIsMobile = window.innerWidth <= 992;

                    if (!currentIsMobile) {
                        if (overlay) overlay.classList.remove('active');
                        sidebar.classList.remove('show');
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        updateToggleIcon(isCollapsed, false);
                    } else {
                        const isShowing = sidebar.classList.contains('show');
                        updateToggleIcon(!isShowing, true);
                        if (isShowing && overlay) {
                            overlay.classList.add('active');
                        }
                    }
                });

                // Toggle Sidebar button click event
                toggleBtn.addEventListener('click', function () {
                    const currentIsMobile = window.innerWidth <= 992;
                    if (currentIsMobile) {
                        if (sidebar.classList.contains('show')) {
                            closeSidebarOnMobile();
                        } else {
                            openSidebarOnMobile();
                        }
                    } else {
                        sidebar.classList.toggle('collapsed');
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        localStorage.setItem('sidebar-collapsed', isCollapsed);
                        updateToggleIcon(isCollapsed, false);
                    }
                });

                // Close button (X) inside sidebar header (for mobile)
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', closeSidebarOnMobile);
                }

                // Close menu when clicking the overlay (mobile backdrop)
                if (overlay) {
                    overlay.addEventListener('click', closeSidebarOnMobile);
                }

                // Close menu when clicking navigation links (except for collapse submenu toggles)
                const navLinks = sidebar.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        if (!this.getAttribute('data-bs-toggle') || this.getAttribute('data-bs-toggle') !== 'collapse') {
                            closeSidebarOnMobile();
                        }
                    });
                });
            }

            // توضيح: إعداد toastr يتم من خلال PHPFlasher (config/flasher.php) — لا حاجة للإعداد هنا

            console.log('✅ Layout JavaScript Initialized');
        });
    })();
</script>

<script src="{{ asset('js/date-fix.js') }}" defer></script>
<script src="{{ asset('js/hijri-converter.js') }}" defer></script>
<script src="{{ asset('js/data-operations.js') }}" defer></script>
<script src="{{ asset('js/date-converter.js') }}" defer></script>
<script src="{{ asset('js/comprehensive-form-fixes.js') }}" defer></script>
<script src="{{ asset('js/preliminary-activities-manager.js') }}" defer></script>
<script src="{{ asset('js/global-search-dropdowns.js') }}" defer></script>

<!-- SweetAlert2 (محلي - بدلاً من CDN لتجنب طلب الشبكة في كل صفحة) -->
<script src="{{ asset('js/libs/sweetalert2.all.min.js') }}"></script>
<script>
    window.confirmAction = function(elementOrAction, messageOrForm, ev) {
        // Stop any event bubbling or default actions explicitly
        try {
            var e = ev || window.event;
            if (e && typeof e.preventDefault === 'function') {
                e.preventDefault();
                e.stopPropagation();
            }
        } catch (err) {}

        // Immediately hide any loader that might have been shown
        if (typeof window.hidePageLoader === 'function') {
            window.hidePageLoader();
        }

        var targetElement = null;
        var message = '';

        // Handle signature: confirmAction(formOrButton, 'message', event)
        if (typeof elementOrAction === 'object' && elementOrAction !== null) {
            targetElement = elementOrAction;
            message = typeof messageOrForm === 'string' ? messageOrForm : (targetElement.getAttribute('data-confirm-message') || 'هل أنت متأكد من تنفيذ هذا الإجراء؟');
        } 
        // Handle signature: confirmAction('actionName', form)
        else if (typeof elementOrAction === 'string') {
            message = elementOrAction;
            if (typeof messageOrForm === 'object' && messageOrForm !== null) {
                targetElement = messageOrForm;
            }
        }

        // Determine action context for tailored styling
        var msgLower = (message || '').toLowerCase();
        var isDelete = /حذف|إلغاء نهائي|مسح|delete|destroy/i.test(msgLower);
        var isRevert = /رجوع|تراجع|تحويل.*مسودة|إلغاء الاعتماد|rollback|revert/i.test(msgLower);
        var isDisable = /تعطيل|إيقاف|disable/i.test(msgLower);
        var isEnable = /تفعيل|تمكين|enable/i.test(msgLower);
        var isApprove = /موافق|اعتماد|تأكيد|إرسال|إغلاق المسودة|قفل|approve|finalize|submit/i.test(msgLower);

        var title = 'تأكيد العملية';
        var icon = 'warning';
        var confirmBtnClass = 'btn-primary';
        var confirmBtnIcon = 'fa-check';
        var confirmBtnText = 'نعم، استمرار';

        if (isDelete) {
            title = 'تأكيد عملية الحذف';
            icon = 'warning';
            confirmBtnClass = 'btn-danger';
            confirmBtnIcon = 'fa-trash-alt';
            confirmBtnText = 'نعم، حذف';
        } else if (isDisable) {
            title = 'تأكيد عملية التعطيل';
            icon = 'warning';
            confirmBtnClass = 'btn-warning';
            confirmBtnIcon = 'fa-pause-circle';
            confirmBtnText = 'نعم، تعطيل';
        } else if (isEnable) {
            title = 'تأكيد عملية التفعيل';
            icon = 'question';
            confirmBtnClass = 'btn-success';
            confirmBtnIcon = 'fa-check-circle';
            confirmBtnText = 'نعم، تفعيل';
        } else if (isRevert) {
            title = 'تأكيد الرجوع إلى مسودة';
            icon = 'warning';
            confirmBtnClass = 'btn-warning';
            confirmBtnIcon = 'fa-undo-alt';
            confirmBtnText = 'نعم، تراجع';
        } else if (isApprove) {
            title = 'تأكيد العملية';
            icon = 'question';
            confirmBtnClass = 'btn-primary';
            confirmBtnIcon = 'fa-check-double';
            confirmBtnText = 'نعم، تأكيد';
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: message || 'هل أنت متأكد؟',
                icon: icon,
                showCancelButton: true,
                confirmButtonText: `<i class="fas ${confirmBtnIcon} me-1"></i> ${confirmBtnText}`,
                cancelButtonText: '<i class="fas fa-times me-1"></i> إلغاء',
                customClass: {
                    confirmButton: `btn ${confirmBtnClass} px-4 fw-bold`,
                    cancelButton: 'btn btn-light text-dark px-4 border'
                },
                buttonsStyling: true,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed && targetElement) {
                    if (targetElement.tagName === 'FORM') {
                        targetElement.classList.add('no-loader');
                        targetElement.onsubmit = null;
                        targetElement.submit();
                    } else if ((targetElement.tagName === 'BUTTON' || targetElement.tagName === 'INPUT') && targetElement.form) {
                        targetElement.form.classList.add('no-loader');
                        targetElement.form.onsubmit = null;
                        targetElement.form.submit();
                    } else if (targetElement.tagName === 'A' && targetElement.href && !targetElement.href.startsWith('javascript:')) {
                        targetElement.classList.add('no-loader');
                        window.location.href = targetElement.href;
                    } else if (typeof targetElement.submit === 'function') {
                        targetElement.submit();
                    }
                }
            });
            return false;
        } else {
            if (confirm(message)) {
                if (targetElement) {
                    if (targetElement.tagName === 'FORM') targetElement.submit();
                    else if (targetElement.form) targetElement.form.submit();
                    else if (targetElement.tagName === 'A' && targetElement.href) window.location.href = targetElement.href;
                }
                return true;
            }
            return false;
        }
    };
</script>
