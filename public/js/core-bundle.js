/**
 * Project Management System - Core Bundle
 * Consolidated JavaScript for performance and organization
 * Includes: AppUtils, DateFix, HijriConverter, DataOperations, SearchableDropdowns, FormFixes
 */

(function(window, $) {
    'use strict';

    // 1. Sidebar & Layout Management
    const initLayout = function() {
        // Notification Toggle
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        if (notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!notificationDropdown.contains(e.target) && e.target !== notificationToggle) {
                    notificationDropdown.classList.remove('active');
                }
            });
        }

        // Sidebar
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        const icon = toggleBtn ? toggleBtn.querySelector('i') : null;
        
        function updateSidebarHeight() {
            if (sidebar) {
                const headerHeight = document.querySelector('.navbar').offsetHeight;
                sidebar.style.height = `calc(${window.innerHeight}px - ${headerHeight}px)`;
            }
        }
        
        function closeSidebarOnMobile() {
            if (window.innerWidth <= 992 && sidebar) {
                sidebar.classList.add('collapsed');
                if (overlay) overlay.classList.remove('active');
                if (icon) { icon.classList.replace('fa-times', 'fa-bars'); }
                localStorage.setItem('sidebar-collapsed', 'true');
            }
        }
        
        if (sidebar && toggleBtn) {
            const savedState = localStorage.getItem('sidebar-collapsed');
            if (savedState === 'true') {
                sidebar.classList.add('collapsed');
                if (icon) icon.classList.replace('fa-times', 'fa-bars');
            } else if (window.innerWidth <= 992) {
                sidebar.classList.add('collapsed');
            }

            updateSidebarHeight();
            window.addEventListener('resize', updateSidebarHeight);
            
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebar-collapsed', isCollapsed);
                if (icon) {
                    if (isCollapsed) icon.classList.replace('fa-times', 'fa-bars');
                    else icon.classList.replace('fa-bars', 'fa-times');
                }
                if (window.innerWidth <= 992 && overlay) {
                    overlay.classList.toggle('active', !isCollapsed);
                }
            });

            if (overlay) overlay.addEventListener('click', closeSidebarOnMobile);
            sidebar.querySelectorAll('.nav-link:not([data-bs-toggle="collapse"])')
                   .forEach(link => link.addEventListener('click', closeSidebarOnMobile));
        }

        if (typeof toastr !== 'undefined') {
            toastr.options = { "positionClass": "toast-bottom-left", "closeButton": true, "progressBar": true };
        }
    };

    // 2. Hijri Converter Class
    class HijriConverter {
        static gregorianToJD(y, m, d) {
            if (m <= 2) { y -= 1; m += 12; }
            let a = Math.floor(y / 100);
            let b = 2 - a + Math.floor(a / 4);
            return Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + d + b - 1524.5;
        }
        static jdToHijri(jd) {
            jd += 0.5;
            let i = Math.floor(jd);
            let l = i - 1948440 + 10632;
            let n = Math.floor((l - 1) / 10631);
            l = l - 10631 * n + 354;
            let j = (Math.floor((10985 - l) / 5316)) * (Math.floor((50 * l) / 17719)) + (Math.floor(l / 5670)) * (Math.floor((43 * l) / 15238));
            l = l - (Math.floor((30 - j) / 15)) * (Math.floor((17719 * j) / 50)) - (Math.floor(j / 16)) * (Math.floor((15238 * j) / 43)) + 29;
            let m = Math.floor((24 * l) / 709);
            let d = l - Math.floor((709 * m) / 24);
            let y = 30 * n + j - 30;
            return { year: y, month: m, day: d };
        }
        static gregorianToHijri(dateStr) {
            if (!dateStr) return null;
            let y, m, d;
            if (dateStr instanceof Date) {
                y = dateStr.getFullYear(); m = dateStr.getMonth() + 1; d = dateStr.getDate();
            } else {
                const parts = dateStr.split('-');
                if (parts.length !== 3) return null;
                y = parseInt(parts[0]); m = parseInt(parts[1]); d = parseInt(parts[2]);
            }
            return this.jdToHijri(this.gregorianToJD(y, m, d));
        }
        static formatHijri(h) {
            if (!h) return '';
            if (typeof h === 'string') return h;
            return `${String(h.day).padStart(2, '0')}/${String(h.month).padStart(2, '0')}/${h.year}`;
        }
    }
    window.HijriConverter = HijriConverter;

    // 3. Date Utilities
    const initDateFix = function() {
        const setupDateInputs = () => {
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.setAttribute('min', '1900-01-01');
                input.setAttribute('max', '2100-12-31');
            });
        };
        setupDateInputs();
        
        // Hijri Auto-binding
        document.addEventListener('input', (e) => {
            if (e.target.type === 'date' && (e.target.dataset.hijriTarget || e.target.dataset.hijriField)) {
                const targetId = e.target.dataset.hijriTarget || e.target.dataset.hijriField;
                const target = document.querySelector(targetId) || document.getElementById(targetId);
                if (target) {
                    const h = HijriConverter.gregorianToHijri(e.target.value);
                    target.value = HijriConverter.formatHijri(h);
                }
            }
        });
    };

    // 4. Data Operations
    window.DataOperations = {
        init: function() {
            $(document).on('click', '.export-link', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const entity = $btn.data('entity') || 'البيانات';
                const originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin me-2"></i>جاري التصدير...').addClass('disabled');
                setTimeout(() => {
                    window.location.href = $btn.attr('href');
                    setTimeout(() => $btn.html(originalHtml).removeClass('disabled'), 1000);
                }, 500);
            });
        }
    };

    // 5. Global Searchable Dropdowns (Select2)
    const initSelect2 = function() {
        if (!$.fn.select2) return;
        
        window.getGlobalSelect2Config = function($el) {
            var originalClasses = $el ? ($el.attr('class') || '') : '';
            var classesArray = originalClasses.split(/\s+/);
            var cssClassesToPreserve = classesArray.filter(function(c) {
                return c !== 'form-select' && c !== 'form-control' && c !== 'select2-hidden-accessible';
            }).join(' ');

            const config = {
                theme: 'bootstrap-5',
                dir: "rtl", width: '100%',
                selectionCssClass: cssClassesToPreserve,
                dropdownCssClass: cssClassesToPreserve,
                language: { noResults: () => "لا توجد نتائج", searching: () => "جاري البحث..." }
            };
            const $modal = $el.closest('.modal');
            if ($modal.length) config.dropdownParent = $modal;
            if ($el.attr('placeholder')) { config.placeholder = $el.attr('placeholder'); config.allowClear = true; }
            
            if ($el.data('ajax-url')) {
                config.ajax = {
                    url: $el.data('ajax-url'), dataType: 'json', delay: 250,
                    data: (params) => ({ q: params.term, type: $el.attr('data-ajax-type') || $el.data('ajax-type'), page: params.page || 1 }),
                    processResults: (data) => ({ results: data.results, pagination: { more: data.pagination?.more } }),
                    cache: true
                };
                config.minimumInputLength = 0;
            }
            return config;
        };

        window.initGlobalSelect2 = function(container, force) {
            const $scope = container ? $(container) : $('body');
            
            if ($scope.is('select')) {
                if (force && $scope.hasClass('select2-hidden-accessible')) {
                    $scope.select2('destroy');
                }
                if (force || !$scope.hasClass('select2-hidden-accessible')) {
                    $scope.select2(window.getGlobalSelect2Config($scope));
                }
                return;
            }

            const selector = force ? 'select:not(.no-search)' : 'select:not(.no-search):not(.select2-hidden-accessible)';
            
            $scope.find(selector).each(function() {
                var $this = $(this);
                if (force && $this.hasClass('select2-hidden-accessible')) {
                    $this.select2('destroy');
                }
                $this.select2(window.getGlobalSelect2Config($this));
            });
        };

        window.initGlobalSelect2();
        $(document).on('shown.bs.modal', function() { window.initGlobalSelect2(this); });
    };

    // 6. Global Initialization
    document.addEventListener('DOMContentLoaded', () => {
        initLayout();
        initDateFix();
        initSelect2();
        DataOperations.init();
        console.log('🚀 Core Bundle Initialized');
    });

})(window, jQuery);
