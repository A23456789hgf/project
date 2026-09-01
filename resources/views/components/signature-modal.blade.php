<style>
    #signatureSetupModal .modal-dialog {
        max-width: 520px;
        margin: 1rem auto;
    }

    #signatureSetupModal .modal-content {
        max-height: calc(100vh - 2rem);
        display: flex;
        flex-direction: column;
    }

    #signatureSetupModal .modal-body {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        /* إتاحة التمرير السلس والمرن بالإصبع في شاشات الجوال */
        overscroll-behavior: contain;
    }

    #signatureSetupModal .signature-wrapper {
        border: 2px dashed #0d6efd;
        border-radius: 10px;
        background: #f8f9fa;
        position: relative;
        width: 100%;
        margin: 12px 0;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    #signatureSetupModal .signature-pad {
        display: block;
        width: 100%;
        touch-action: none;
        /* يخصص اللمس داخل المربع للرسم فقط */
        cursor: crosshair;
    }

    @media (max-width: 576px) {
        #signatureSetupModal .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }

        #signatureSetupModal .modal-body {
            padding: 1rem 0.75rem;
            max-height: calc(100vh - 130px);
        }

        #signatureSetupModal p.text-muted {
            font-size: 0.88rem;
        }
    }
</style>
<div class="modal fade" id="signatureSetupModal" tabindex="-1" aria-labelledby="signatureSetupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered mx-2 mx-sm-auto">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light py-3 flex-shrink-0">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2"
                    id="signatureSetupModalLabel">
                    <i class="fas fa-file-signature"></i> إعداد التوقيع الإلكتروني
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="signatureCloseBtn"></button>
            </div>
            <div class="modal-body p-3 p-sm-4 text-center">
                <p class="text-muted mb-1">يرجى رسم توقيعك الإلكتروني أدناه في الإطار المخصص لاعتماد العمليات.</p>
                <small class="text-secondary d-block mb-2 d-sm-none" style="font-size: 0.8rem;">
                    <i class="fas fa-hand-point-up"></i> للتمرير في الشاشة، اسحب بإصبعك من النصوص خارج مربع التوقيع
                </small>
                <div class="signature-wrapper">
                    <canvas id="signature-pad" class="signature-pad"></canvas>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill"
                        id="clearSignatureBtn">
                        <i class="fas fa-eraser"></i> مسح وإعادة رسم
                    </button>
                    <span id="signatureError" class="text-danger fw-medium"
                        style="display: none; font-size: 0.85rem;">يرجى رسم التوقيع أولاً.</span>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3 flex-shrink-0 d-flex flex-wrap gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal"
                    id="signatureDeclineBtn">تخطي</button>
                <button type="button" class="btn btn-primary px-4 rounded-3 fw-bold shadow-sm" id="saveSignatureBtn">حفظ
                    واعتماد</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/signature_pad.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const saveBtn = document.getElementById('saveSignatureBtn');
        const errorSpan = document.getElementById('signatureError');
        const modalEl = document.getElementById('signatureSetupModal');
        const declineBtn = document.getElementById('signatureDeclineBtn');
        const closeBtn = document.getElementById('signatureCloseBtn');

        if (!canvas) return;

        // Initialize SignaturePad
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: 'rgb(0, 0, 100)'
        });

        // Function to resize canvas for high DPI displays and responsive layout
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const wrapper = canvas.parentElement;
            const parentWidth = wrapper ? wrapper.offsetWidth : 0;
            if (parentWidth === 0) return;

            // تحديد الارتفاع بشكل ديناميكي متناسب مع عرض الشاشة والجوالات
            const isMobile = window.innerWidth <= 576;
            const targetHeight = isMobile ? 160 : 200;

            // حفظ الرسم قبل تغيير الأبعاد لكي لا يضيع التوقيع عند تدوير شاشة الهاتف
            let savedDrawing = null;
            if (signaturePad && !signaturePad.isEmpty()) {
                savedDrawing = signaturePad.toDataURL();
            }

            canvas.width = parentWidth * ratio;
            canvas.height = targetHeight * ratio;
            canvas.style.width = parentWidth + "px";
            canvas.style.height = targetHeight + "px";

            const ctx = canvas.getContext("2d");
            ctx.scale(ratio, ratio);

            if (signaturePad) {
                signaturePad.clear();
                if (savedDrawing) {
                    signaturePad.fromDataURL(savedDrawing);
                }
            }
        }

        window.addEventListener("resize", resizeCanvas);

        let signatureModalInstance = null;
        if (typeof bootstrap !== 'undefined') {
            signatureModalInstance = new bootstrap.Modal(modalEl);
        }

        modalEl.addEventListener('shown.bs.modal', function () {
            resizeCanvas();
        });

        clearBtn.addEventListener('click', function () {
            signaturePad.clear();
            errorSpan.style.display = 'none';
        });

        // Decline logic - just close the modal. The user can set it up later.
        declineBtn.addEventListener('click', function () {
            sessionStorage.setItem('signature_prompt_declined', 'true');
        });
        closeBtn.addEventListener('click', function () {
            sessionStorage.setItem('signature_prompt_declined', 'true');
        });

        saveBtn.addEventListener('click', function () {
            if (signaturePad.isEmpty()) {
                errorSpan.style.display = 'block';
                return;
            }

            errorSpan.style.display = 'none';
            const dataURL = signaturePad.toDataURL(); // PNG
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';

            fetch('{{ route("profile.signature.setup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ signature_data: dataURL })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.userHasSignature = true;
                        if (signatureModalInstance) {
                            signatureModalInstance.hide();
                        }

                        if (typeof flasher !== 'undefined') {
                            flasher.success(data.message || 'تم حفظ التوقيع بنجاح.');
                        }

                        // If this was triggered from an intercepted form, resubmit it
                        if (window.pendingSignatureForm) {
                            // Create a hidden input for the signature data so the intercepted form gets the signature
                            let sigInput = window.pendingSignatureForm.querySelector('input[name="signature"]');
                            if (!sigInput) {
                                sigInput = document.createElement('input');
                                sigInput.type = 'hidden';
                                sigInput.name = 'signature';
                                window.pendingSignatureForm.appendChild(sigInput);
                            }
                            sigInput.value = dataURL;

                            // Use requestSubmit to trigger any native or JS onsubmit handlers, fallback to submit
                            if (typeof window.pendingSignatureForm.requestSubmit === 'function') {
                                window.pendingSignatureForm.requestSubmit();
                            } else {
                                window.pendingSignatureForm.submit();
                            }
                            window.pendingSignatureForm = null;
                        } else {
                            // Reload the page to show the session()->flash() bootstrap alert
                            window.location.reload();
                        }

                    } else {
                        if (typeof flasher !== 'undefined') {
                            flasher.error(data.message || 'حدث خطأ أثناء حفظ التوقيع.');
                        } else {
                            alert(data.message || 'حدث خطأ أثناء حفظ التوقيع.');
                        }
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'حفظ واعتماد';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof flasher !== 'undefined') {
                        flasher.error('حدث خطأ في الاتصال بالخادم.');
                    } else {
                        alert('حدث خطأ في الاتصال بالخادم.');
                    }
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'حفظ واعتماد';
                });
        });

        // Global functions for other scripts to interact with the modal
        window.openSignatureModal = function (formToSubmit = null) {
            window.pendingSignatureForm = formToSubmit;
            signaturePad.clear();
            errorSpan.style.display = 'none';
            if (signatureModalInstance) {
                signatureModalInstance.show();
            }
        };

        window.userHasSignature = {{ auth()->user() && auth()->user()->hasSignature() ? 'true' : 'false' }};

        // Setup initial prompt
        const urlParams = new URLSearchParams(window.location.search);
        const justChangedPassword = urlParams.has('password_changed') || document.referrer.includes('force-change-password');

        // Show modal if user doesn't have signature AND hasn't declined in this session
        if (!window.userHasSignature && sessionStorage.getItem('signature_prompt_declined') !== 'true') {
            // Either they just changed password (initial login), or we can just prompt them once per session
            // The logic: "After the initial login...". 
            // We can just show it on the dashboard if they are on the dashboard.
            if (window.location.pathname === '/dashboard' || window.location.pathname === '/home') {
                setTimeout(() => {
                    window.openSignatureModal();
                }, 1000);
            }
        }

        // Global interceptor for forms/buttons that require signature
        document.body.addEventListener('click', function (e) {
            const trigger = e.target.closest('.requires-signature');
            if (trigger) {
                if (!window.userHasSignature) {
                    e.preventDefault();
                    e.stopPropagation();

                    let formToSubmit = null;
                    if (trigger.tagName === 'BUTTON' && trigger.type === 'submit') {
                        formToSubmit = trigger.closest('form');
                    }

                    window.openSignatureModal(formToSubmit);
                    return false;
                }
            }
        }, true); // Use capture phase to intercept before form submission
    });
</script>