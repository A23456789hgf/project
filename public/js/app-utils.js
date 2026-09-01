/**
 * Application Utilities
 * Centralized JavaScript utilities to prevent conflicts
 */

(function(window) {
    'use strict';
    
    // Create global namespace
    window.AppUtils = window.AppUtils || {};
    
    /**
     * Common utilities
     */
    AppUtils.Utils = {
        showToast: function(message, type = 'info', options = {}) {
            const method = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
            
            // 1. Try Toastr explicitly if available (PHPFlasher Toastr adapter)
            if (typeof toastr !== 'undefined') {
                toastr[method](message);
            } 
            // 2. Try PHPFlasher global object
            else if (typeof flasher !== 'undefined') {
                try {
                    flasher[method](message);
                } catch(e) {
                    console.error('Flasher error:', e);
                }
            } 
            // 3. Fallback to SweetAlert2 Toast which is guaranteed to look like a popup
            else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: message,
                    toast: true,
                    position: 'top-start',
                    showConfirmButton: false,
                    timer: 5000
                });
            } 
            // 4. Absolute fallback
            else {
                console.log(`${type.toUpperCase()}: ${message}`);
            }
        },

        
        // Confirm dialog
        confirm: function(message, callback) {
            Swal.fire({
                title: 'تأكيد',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            });
            return true;
        },
        
        // Animate element
        animate: function(element, animation, duration = 300) {
            return new Promise((resolve) => {
                element.style.transition = `all ${duration}ms ease`;
                
                if (animation === 'fadeIn') {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }, 10);
                } else if (animation === 'fadeOut') {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(-20px)';
                } else if (animation === 'slideOut') {
                    element.style.opacity = '0';
                    element.style.transform = 'translateX(100px)';
                }
                
                setTimeout(resolve, duration);
            });
        },
        
        // Scroll to element
        scrollTo: function(element, options = {}) {
            const defaultOptions = {
                behavior: 'smooth',
                block: 'start'
            };
            
            element.scrollIntoView(Object.assign(defaultOptions, options));
        },
        
        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };
    
    /**
     * Form utilities
     */
    AppUtils.Form = {
        // Validate required fields
        validateRequired: function(container) {
            const requiredFields = container.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            return isValid;
        },
        
        // Clear validation errors
        clearValidation: function(container) {
            const invalidFields = container.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => {
                field.classList.remove('is-invalid');
            });
        },
        
        // Serialize form data
        serialize: function(form) {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            return data;
        }
    };
    
    /**
     * AJAX utilities
     */
    AppUtils.Ajax = {
        // GET request
        get: function(url, options = {}) {
            return this.request('GET', url, null, options);
        },
        
        // POST request
        post: function(url, data, options = {}) {
            return this.request('POST', url, data, options);
        },
        
        // Generic request
        request: function(method, url, data, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            // Add CSRF token if available
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            }
            
            const config = Object.assign(defaultOptions, options);
            config.method = method;
            
            if (data) {
                if (method === 'GET') {
                    const params = new URLSearchParams(data);
                    url += '?' + params.toString();
                } else {
                    config.body = JSON.stringify(data);
                }
            }
            
            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                });
        }
    };
    
    /**
     * Select utilities
     */
    AppUtils.Select = {
        // Reset select element
        reset: function(select, placeholder = 'اختر...') {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = true;
        },
        
        // Show loading in select
        showLoading: function(select) {
            select.innerHTML = '<option value="">جاري التحميل...</option>';
        },
        
        // Populate select with data
        populate: function(select, data, placeholder = 'اختر...') {
            let html = `<option value="">${placeholder}</option>`;
            data.forEach(item => {
                html += `<option value="${item.id}">${item.name}</option>`;
            });
            select.innerHTML = html;
            select.disabled = false;
        },
        
        // Initialize Select2
        initSelect2: function(selector, options = {}) {
            if (typeof $ !== 'undefined' && $.fn.select2) {
                const defaultOptions = {
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'اختر...',
                    allowClear: true
                };
                
                $(selector).select2(Object.assign(defaultOptions, options));
            }
        }
    };
    
    /**
     * Table utilities
     */
    AppUtils.Table = {
        // Add row to table
        addRow: function(tableBody, rowHtml) {
            const row = document.createElement('tr');
            row.innerHTML = rowHtml;
            tableBody.appendChild(row);
            return row;
        },
        
        // Remove row from table
        removeRow: function(row, requireConfirm = true) {
            if (!requireConfirm) {
                return AppUtils.Utils.animate(row, 'slideOut').then(() => {
                    row.remove();
                    return true;
                });
            }
            
            return Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من الحذف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    return AppUtils.Utils.animate(row, 'slideOut').then(() => {
                        row.remove();
                        return true;
                    });
                }
                return false;
            });
        },
        
        // Check if table is empty and show message
        checkEmpty: function(tableBody, emptyMessage = 'لا توجد بيانات') {
            if (tableBody.children.length === 0) {
                const colspan = tableBody.closest('table').querySelector('thead tr').children.length;
                tableBody.innerHTML = `
                    <tr class="empty-row">
                        <td colspan="${colspan}" class="text-center text-muted py-4">
                            ${emptyMessage}
                        </td>
                    </tr>
                `;
            }
        }
    };
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('✅ AppUtils Initialized');
        
        // Fix for dropdowns inside table-responsive getting clipped
        document.addEventListener('show.bs.dropdown', function(e) {
            const toggle = e.target;
            const menu = toggle.nextElementSibling || (toggle.parentElement && toggle.parentElement.querySelector('.dropdown-menu'));
            
            if (menu && toggle.closest('.table-responsive')) {
                // Store a reference to put it back later
                menu.dataset.bsOriginalParentId = toggle.parentElement ? (toggle.parentElement.id || '') : '';
                
                // If parent doesn't have an ID, give it a temporary one
                if (toggle.parentElement && !toggle.parentElement.id) {
                    const tempId = 'dropdown-parent-' + Math.random().toString(36).substr(2, 9);
                    toggle.parentElement.id = tempId;
                    menu.dataset.bsOriginalParentId = tempId;
                }
                
                toggle.dataset.bsMenuAppended = 'true';
                
                // Append to body to avoid clipping by overflow: hidden/auto
                document.body.appendChild(menu);
                
                // Force Popper.js to update the position immediately
                if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
                    const dp = bootstrap.Dropdown.getInstance(toggle);
                    if (dp && typeof dp.update === 'function') {
                        setTimeout(() => { dp.update(); }, 0);
                    }
                }
            }
        });
        
        document.addEventListener('hidden.bs.dropdown', function(e) {
            const toggle = e.target;
            if (toggle.dataset.bsMenuAppended === 'true') {
                const parentId = toggle.nextElementSibling ? null : (toggle.parentElement ? toggle.parentElement.id : '');
                
                // Find the menu in body and put it back
                const menus = document.querySelectorAll('body > .dropdown-menu');
                menus.forEach(menu => {
                    if (menu.getAttribute('aria-labelledby') === toggle.id || 
                        (menu.dataset.bsOriginalParentId && menu.dataset.bsOriginalParentId === parentId)) {
                        
                        const parentEl = document.getElementById(menu.dataset.bsOriginalParentId);
                        if (parentEl) {
                            parentEl.appendChild(menu);
                        } else if (toggle.parentElement) {
                            toggle.parentElement.appendChild(menu);
                        }
                    }
                });
                toggle.dataset.bsMenuAppended = 'false';
            }
        });
    });
    
})(window);