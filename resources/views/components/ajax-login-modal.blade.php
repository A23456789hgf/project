<div class="modal fade" id="ajaxLoginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ajaxLoginModalLabel" aria-hidden="true" style="z-index: 10060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fs-6 fw-bold" id="ajaxLoginModalLabel">
                    <i class="fa-solid fa-user-lock me-2"></i> انتهت الجلسة - يرجى تسجيل الدخول للمتابعة
                </h5>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 shadow-sm mb-3 text-dark" style="font-size: 0.88rem; line-height: 1.6;">
                    <i class="fa-solid fa-triangle-exclamation text-warning me-2 fs-5 align-middle"></i>
                    انتهت صلاحية الجلسة بسبب عدم النشاط. للحفاظ على جميع بياناتك ومدخلاتك الحالية دون أي ضياع، يرجى إدخال كلمة المرور مجدداً لاستكمال العملية تلقائياً.
                </div>
                <form id="ajaxLoginForm" novalidate onsubmit="return false;">
                    <div id="ajaxLoginError" class="alert alert-danger d-none mb-3" style="font-size: 0.85rem;"></div>
                    <div class="mb-3">
                        <label for="ajax_login_username" class="form-label font-weight-bold">اسم المستخدم أو رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control dir-ltr" id="ajax_login_username" name="username" required autocomplete="username">
                    </div>
                    <div class="mb-3">
                        <label for="ajax_login_password" class="form-label font-weight-bold">كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control dir-ltr" id="ajax_login_password" name="password" required autocomplete="current-password">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0 px-4 py-3">
                <button type="button" class="btn btn-primary w-100 fw-bold py-2" id="btnAjaxLogin">
                    <span class="btn-text"><i class="fa-solid fa-right-to-bracket me-2"></i>تسجيل الدخول واستكمال العملية</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    let ajaxLoginSuccessCallbacks = [];
    let ajaxLoginModalInstance = null;
    let isShowingModal = false;

    // =========================================================================
    // 1. DYNAMIC LOGIN MODAL CONTROLLER
    // =========================================================================
    window.showAjaxLoginModal = function(onSuccessCallback) {
        if (typeof onSuccessCallback === 'function') {
            ajaxLoginSuccessCallbacks.push(onSuccessCallback);
        }

        if (isShowingModal) {
            return;
        }

        const modalEl = document.getElementById('ajaxLoginModal');
        if (!modalEl) return;

        isShowingModal = true;
        document.getElementById('ajaxLoginForm').reset();
        document.getElementById('ajaxLoginError').classList.add('d-none');

        if (!ajaxLoginModalInstance) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                ajaxLoginModalInstance = new bootstrap.Modal(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
            } else {
                console.error('[GlobalSessionGuard] Bootstrap JS is required.');
                isShowingModal = false;
                return;
            }
        }
        ajaxLoginModalInstance.show();
    };

    function notifyLoginSuccess(newCsrfToken) {
        isShowingModal = false;
        
        // 1. Update DOM CSRF Tokens
        if (newCsrfToken) {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) metaTag.setAttribute('content', newCsrfToken);
            
            document.querySelectorAll('input[name="_token"]').forEach(el => {
                el.value = newCsrfToken;
            });
        }

        // 2. Update jQuery default headers if jQuery exists
        if (typeof jQuery !== 'undefined') {
            jQuery.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': newCsrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '')
                }
            });
        }

        // 3. Hide Modal
        if (ajaxLoginModalInstance) {
            ajaxLoginModalInstance.hide();
        }

        // 4. Toast notification
        if (typeof AppUtils !== 'undefined' && AppUtils.Utils && AppUtils.Utils.showToast) {
            AppUtils.Utils.showToast('تم تجديد الجلسة بنجاح، جاري استكمال طلبك...', 'success');
        }

        // 5. Execute queued callbacks (retry paused requests)
        const callbacks = ajaxLoginSuccessCallbacks.slice();
        ajaxLoginSuccessCallbacks = [];
        callbacks.forEach(cb => {
            try { cb(newCsrfToken); } catch (e) { console.error('[GlobalSessionGuard] Callback error:', e); }
        });
    }

    // =========================================================================
    // 2. GLOBAL FETCH INTERCEPTOR
    // =========================================================================
    const rawFetch = window.fetch;
    window.fetch = function(...args) {
        const url = (typeof args[0] === 'string') ? args[0] : (args[0] && args[0].url ? args[0].url : '');

        // Exclude auth routes from interception to prevent infinite loops
        if (url.includes('/login') || url.includes('/sanctum/csrf-cookie')) {
            return rawFetch.apply(this, args);
        }

        return rawFetch.apply(this, args).then(response => {
            if (response.status === 419 || response.status === 401) {
                return new Promise((resolve, reject) => {
                    window.showAjaxLoginModal((newCsrfToken) => {
                        // Update CSRF token in request headers before retrying
                        let [resource, config] = args;
                        config = config || {};
                        
                        const token = newCsrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        
                        if (config.headers) {
                            if (config.headers instanceof Headers) {
                                config.headers.set('X-CSRF-TOKEN', token);
                            } else {
                                config.headers['X-CSRF-TOKEN'] = token;
                            }
                        } else {
                            config.headers = { 'X-CSRF-TOKEN': token };
                        }
                        
                        args[1] = config;

                        // Retry original fetch
                        rawFetch.apply(window, args).then(resolve).catch(reject);
                    });
                });
            }
            return response;
        });
    };

    // =========================================================================
    // 3. GLOBAL JQUERY AJAX INTERCEPTOR
    // =========================================================================
    if (typeof jQuery !== 'undefined') {
        const rawJQueryAjax = jQuery.ajax;
        jQuery.ajax = function(url, options) {
            if (typeof url === 'object') {
                options = url;
                url = undefined;
            }
            options = options || {};

            const requestUrl = url || options.url || '';
            if (requestUrl.includes('/login') || requestUrl.includes('/sanctum/csrf-cookie')) {
                return rawJQueryAjax.call(jQuery, url || options, url ? options : undefined);
            }

            const deferred = jQuery.Deferred();

            function executeAjaxRequest() {
                const jqXHR = rawJQueryAjax.call(jQuery, url || options, url ? options : undefined);
                
                jqXHR.done(function(data, textStatus, xhr) {
                    deferred.resolve(data, textStatus, xhr);
                }).fail(function(xhr, textStatus, errorThrown) {
                    if (xhr.status === 419 || xhr.status === 401) {
                        window.showAjaxLoginModal((newCsrfToken) => {
                            const token = newCsrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            if (!options.headers) options.headers = {};
                            options.headers['X-CSRF-TOKEN'] = token;
                            
                            // Retry request
                            executeAjaxRequest();
                        });
                    } else {
                        deferred.reject(xhr, textStatus, errorThrown);
                    }
                });
            }

            executeAjaxRequest();
            return deferred.promise(jqXHR_proxy(deferred));
        };

        function jqXHR_proxy(deferred) {
            return {
                abort: function() {},
                always: deferred.always,
                done: deferred.done,
                fail: deferred.fail,
                then: deferred.then
            };
        }
    }

    // =========================================================================
    // 4. GLOBAL STANDARD HTML FORM SUBMISSION INTERCEPTOR
    // =========================================================================
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method !== 'POST') return; // GET forms don't require CSRF or session verification

        // Ignore the ajax login form itself
        if (form.id === 'ajaxLoginForm') return;

        // Ignore forms marked to skip session check
        if (form.dataset.skipSessionCheck === 'true') return;

        e.preventDefault();
        e.stopPropagation();

        // Perform lightweight session check
        rawFetch('/check-session', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 200) {
                // Session is valid, submit natively
                HTMLFormElement.prototype.submit.call(form);
            } else {
                // Session expired, trigger modal
                window.showAjaxLoginModal(() => {
                    // Update hidden token input inside form
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    let tokenInput = form.querySelector('input[name="_token"]');
                    if (tokenInput && token) tokenInput.value = token;

                    // Submit natively
                    HTMLFormElement.prototype.submit.call(form);
                });
            }
        })
        .catch(() => {
            // Network error fallback: submit form natively
            HTMLFormElement.prototype.submit.call(form);
        });
    }, true);

    // =========================================================================
    // 5. LOGIN MODAL FORM SUBMISSION HANDLER
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const btnLogin = document.getElementById('btnAjaxLogin');
        const loginForm = document.getElementById('ajaxLoginForm');

        if (btnLogin && loginForm) {
            const submitLogin = function() {
                const usernameInput = document.getElementById('ajax_login_username').value.trim();
                const passwordInput = document.getElementById('ajax_login_password').value;
                const errorAlert = document.getElementById('ajaxLoginError');

                if (!usernameInput || !passwordInput) {
                    errorAlert.textContent = 'يرجى إدخال اسم المستخدم وكلمة المرور';
                    errorAlert.classList.remove('d-none');
                    return;
                }

                btnLogin.disabled = true;
                btnLogin.querySelector('.btn-text').classList.add('d-none');
                btnLogin.querySelector('.spinner-border').classList.remove('d-none');
                errorAlert.classList.add('d-none');

                const formData = new FormData();
                formData.append('username', usernameInput);
                formData.append('password', passwordInput);

                // Fetch CSRF cookie to ensure fresh CSRF token before login attempt
                rawFetch('/sanctum/csrf-cookie', { method: 'GET' })
                .then(() => {
                    return rawFetch('/login', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': getCookieValue('XSRF-TOKEN')
                        },
                        body: formData
                    });
                })
                .then(response => response.json().then(data => ({ status: response.status, data: data })))
                .then(({ status, data }) => {
                    btnLogin.disabled = false;
                    btnLogin.querySelector('.btn-text').classList.remove('d-none');
                    btnLogin.querySelector('.spinner-border').classList.add('d-none');

                    if (status === 200 && data.success) {
                        notifyLoginSuccess(data.csrf_token);
                    } else {
                        let errMsg = data.message || 'بيانات الدخول غير صحيحة';
                        if (data.errors) {
                            const firstKey = Object.keys(data.errors)[0];
                            errMsg = data.errors[firstKey][0];
                        }
                        errorAlert.textContent = errMsg;
                        errorAlert.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('[GlobalSessionGuard] Login request error:', error);
                    btnLogin.disabled = false;
                    btnLogin.querySelector('.btn-text').classList.remove('d-none');
                    btnLogin.querySelector('.spinner-border').classList.add('d-none');
                    errorAlert.textContent = 'حدث خطأ في الاتصال بالخادم، يرجى المحاولة مرة أخرى.';
                    errorAlert.classList.remove('d-none');
                });
            };

            btnLogin.addEventListener('click', submitLogin);
            loginForm.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitLogin();
                }
            });
        }
    });

    function getCookieValue(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) return decodeURIComponent(match[2]);
        return '';
    }

})();
</script>
