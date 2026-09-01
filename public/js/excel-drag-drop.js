/**
 * Excel Drag & Drop Import Utility
 * Provides drag-and-drop functionality for Excel file imports
 */

(function (window) {
    'use strict';

    // Create namespace
    window.ExcelDragDrop = window.ExcelDragDrop || {};

    /**
     * Initialize drag and drop functionality
     * @param {Object} options Configuration options
     */
    ExcelDragDrop.init = function (options) {
        const config = Object.assign({
            dropZoneSelector: '.excel-drop-zone',
            fileInputSelector: '.excel-file-input',
            previewUrl: '',
            templateUrl: '',
            allowedTypes: [
                'text/csv',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/csv',
                'text/plain'
            ],
            maxFileSize: 2 * 1024 * 1024, // 2MB
            messages: {
                dragEnter: 'اسحب ملف Excel هنا أو انقر للاختيار',
                dragOver: 'اتركه هنا لرفع الملف',
                invalidType: 'نوع الملف غير مدعوم. يرجى اختيار ملف Excel (.xlsx) أو CSV',
                fileTooLarge: 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت',
                uploadError: 'حدث خطأ أثناء رفع الملف'
            }
        }, options);

        const dropZone = document.querySelector(config.dropZoneSelector);
        const fileInput = document.querySelector(config.fileInputSelector);

        if (!dropZone || !fileInput) {
            console.error('Drop zone or file input not found');
            return;
        }

        // Initialize drop zone
        initDropZone(dropZone, fileInput, config);

        // Initialize file input
        initFileInput(fileInput, config);

        // Add template download functionality
        initTemplateDownload(config);
    };

    /**
     * Initialize drop zone functionality
     */
    function initDropZone(dropZone, fileInput, config) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => highlight(dropZone, config), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => unhighlight(dropZone, config), false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', (e) => handleDrop(e, config), false);

        // Handle click to open file dialog
        dropZone.addEventListener('click', () => fileInput.click());
    }

    /**
     * Initialize file input functionality
     */
    function initFileInput(fileInput, config) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFiles(e.target.files, config);
            }
        });
    }

    /**
     * Initialize template download
     */
    function initTemplateDownload(config) {
        const templateBtn = document.querySelector('.download-template-btn');
        if (templateBtn && config.templateUrl) {
            templateBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = config.templateUrl;
            });
        }
    }

    /**
     * Prevent default drag behaviors
     */
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Highlight drop zone
     */
    function highlight(dropZone, config) {
        dropZone.classList.add('drag-over');
        const message = dropZone.querySelector('.drop-message');
        if (message) {
            message.textContent = config.messages.dragOver;
        }
    }

    /**
     * Remove highlight from drop zone
     */
    function unhighlight(dropZone, config) {
        dropZone.classList.remove('drag-over');
        const message = dropZone.querySelector('.drop-message');
        if (message) {
            message.textContent = config.messages.dragEnter;
        }
    }

    /**
     * Handle dropped files
     */
    function handleDrop(e, config) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files, config);
    }

    /**
     * Handle selected files
     */
    function handleFiles(files, config) {
        if (files.length === 0) return;

        const file = files[0];

        // Validate file type by extension (more reliable than MIME type)
        const fileName = file.name.toLowerCase();
        const allowedExtensions = ['.csv', '.xlsx', '.xls'];
        const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));

        // Also check MIME type as backup
        const isValidMimeType = config.allowedTypes.includes(file.type);

        if (!isValidExtension && !isValidMimeType) {
            showError(config.messages.invalidType);
            return;
        }

        // Validate file size
        if (file.size > config.maxFileSize) {
            showError(config.messages.fileTooLarge);
            return;
        }

        // Show loading state
        if (typeof showLoader === 'function') {
            showLoader('جاري رفع ومعالجة الملف، يرجى الانتظار...');
        } else {
            showLoading();
        }

        // Upload file
        uploadFile(file, config);
    }

    /**
     * Upload file to server
     */
    function uploadFile(file, config) {
        const formData = new FormData();
        formData.append('file', file);

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            formData.append('_token', csrfToken.getAttribute('content'));
        }

        fetch(config.previewUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                // Replace current page content with preview
                document.body.innerHTML = html;

                // Re-initialize any necessary scripts
                if (typeof bootstrap !== 'undefined') {
                    // Re-initialize Bootstrap components if needed
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showError(config.messages.uploadError);
            })
            .finally(() => {
                hideLoading();
            });
    }

    /**
     * Show error message — uses PHPFlasher JS API
     */
    function showError(message) {
        if (typeof flasher !== 'undefined') {
            flasher.error(message);
        } else if (typeof AppUtils !== 'undefined' && AppUtils.Utils) {
            AppUtils.Utils.showToast(message, 'error');
        } else {
            alert(message);
        }
    }

    /**
     * Show loading state (Internal fallback)
     */
    function showLoading() {
        const dropZone = document.querySelector('.excel-drop-zone');
        if (dropZone) {
            dropZone.classList.add('loading');
            const message = dropZone.querySelector('.drop-message');
            if (message) {
                message.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري رفع الملف...';
            }
        }
    }

    /**
     * Hide loading state (Internal fallback)
     */
    function hideLoading() {
        if (typeof hideLoader === 'function') {
            hideLoader();
        }
        const dropZone = document.querySelector('.excel-drop-zone');
        if (dropZone) {
            dropZone.classList.remove('loading');
        }
    }

    /**
     * Create drag and drop HTML structure
     */
    ExcelDragDrop.createDropZone = function (options = {}) {
        const config = Object.assign({
            title: 'استيراد البيانات من Excel',
            description: 'اسحب ملف Excel هنا أو انقر للاختيار',
            acceptedFormats: 'Excel (.xlsx) أو CSV',
            maxSize: '2 ميجابايت',
            templateText: 'تحميل القالب',
            templateUrl: '#'
        }, options);

        return `
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        ${config.title}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="excel-drop-zone">
                        <div class="drop-zone-content">
                            <i class="fas fa-cloud-upload-alt drop-icon"></i>
                            <p class="drop-message">${config.description}</p>
                            <p class="drop-info">
                                <small class="text-muted">
                                    الصيغ المدعومة: ${config.acceptedFormats}<br>
                                    الحد الأقصى للحجم: ${config.maxSize}
                                </small>
                            </p>
                            <input type="file" class="excel-file-input" accept=".xlsx,.csv" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <a href="${config.templateUrl}" class="btn btn-outline-success download-template-btn">
                            <i class="fas fa-download me-1"></i>
                            ${config.templateText}
                        </a>
                    </div>
                </div>
            </div>
        `;
    };

})(window);