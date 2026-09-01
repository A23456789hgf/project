/**
 * Data Operations Utility
 * Handles uniform export/import functionality across all pages
 */

const DataOperations = {
    /**
     * Initialize export functionality
     */
    initExport: function() {
        document.querySelectorAll('.export-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const entityName = this.getAttribute('data-entity') || 'البيانات';
                const originalContent = this.innerHTML;
                const originalClass = this.className;

                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التصدير...';
                this.className = originalClass + ' disabled';
                this.style.pointerEvents = 'none';
                this.style.opacity = '0.6';

                // Show toast notification
                if (typeof toastr !== 'undefined') {
                    toastr.info(`جاري تحضير ملف ${entityName}...`, 'تصدير البيانات');
                }

                // Simulate processing time then trigger download
                setTimeout(() => {
                    const href = this.getAttribute('href') || this.dataset.originalHref;
                    window.location.href = href;

                    // Reset button after a delay
                    setTimeout(() => {
                        this.innerHTML = originalContent;
                        this.className = originalClass;
                        this.style.pointerEvents = 'auto';
                        this.style.opacity = '1';

                        if (typeof toastr !== 'undefined') {
                            toastr.success(`تم تحضير ملف ${entityName} بنجاح!`, 'تصدير مكتمل');
                        }
                    }, 1000);
                }, 800);

                // Store original href in case we need it again
                this.setAttribute('data-original-href', this.getAttribute('href'));
            });
        });
    },

    /**
     * Initialize import modal/form handling
     */
    initImport: function() {
        // Handle import file input change
        document.querySelectorAll('input[name="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = this.files[0];
                if (file) {
                    const fileName = file.name;
                    const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB

                    // Validate file type
                    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
                    const isValidType = allowedTypes.includes(file.type);

                    if (!isValidType) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('نوع الملف غير مدعوم. استخدم Excel أو CSV', 'خطأ');
                        }
                        this.value = '';
                        return;
                    }

                    if (fileSize > 2) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('حجم الملف أكبر من 2MB', 'خطأ');
                        }
                        this.value = '';
                        return;
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.success(`تم اختيار الملف: ${fileName}`, 'نجح');
                    }
                }
            });
        });
    },

    /**
     * Show export loading animation
     */
    showExportLoading: function(element, entityName = 'البيانات') {
        const originalContent = element.innerHTML;
        element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التصدير...';
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.6';

        if (typeof toastr !== 'undefined') {
            toastr.info(`جاري تحضير ملف ${entityName}...`, 'تصدير البيانات');
        }

        setTimeout(() => {
            element.innerHTML = originalContent;
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';

            if (typeof toastr !== 'undefined') {
                toastr.success(`تم تحضير ملف ${entityName} بنجاح!`, 'تصدير مكتمل');
            }
        }, 3000);
    },

    /**
     * Handle import form submission
     */
    handleImportSubmit: function(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            form.addEventListener('submit', function(e) {
                const fileInput = this.querySelector('input[name="file"]');
                if (!fileInput || !fileInput.files.length) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('الرجاء اختيار ملف', 'تحذير');
                    }
                }
            });
        }
    },

    /**
     * Initialize all functionality
     */
    init: function() {
        this.initExport();
        this.initImport();
    }
};

// Auto-initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    DataOperations.init();
});