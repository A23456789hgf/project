<!-- Print Settings Modal -->
<div class="modal fade" id="printSettingsModal" tabindex="-1" aria-labelledby="printSettingsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-navy text-white py-3">
                <h5 class="modal-title d-flex align-items-center" id="printSettingsModalLabel">
                    <i class="fas fa-print me-2"></i> إعدادات طباعة المذكرة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Header Type -->
                    <div class="col-md-12">
                        <label class="fw-bold mb-3 d-block text-navy"><i class="fas fa-heading me-1 text-primary"></i>
                            اختيار الترويسة العليا:</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="header_type" id="header_ministry"
                                    value="ministry" checked>
                                <label
                                    class="btn btn-outline-navy w-100 py-3 rounded-3 d-flex flex-column align-items-center"
                                    for="header_ministry">
                                    <span class="fw-bold mb-1">وزارة الزراعة والثروة السمكية والموارد المائية </span>
                                    <span class="fw-bold mb-1">نائب الوزير </span>

                                    <small class="opacity-75 small">الترويسة الرسمية للوزارة</small>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="header_type" id="header_committee"
                                    value="committee">
                                <label
                                    class="btn btn-outline-navy w-100 py-3 rounded-3 d-flex flex-column align-items-center"
                                    for="header_committee">
                                    <span class="fw-bold mb-1">اللجنة الزراعية والسمكية العليا</span>
                                    <span class="fw-bold mb-1">رئيس اللجنة</span>
                                    <small class="opacity-75 small">ترويسة اللجنة العليا</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 opacity-25">

                    <!-- Signer Officer -->
                    <div class="col-md-12">
                        <label class="fw-bold mb-3 d-block text-navy"><i class="fas fa-user-tie me-1 text-primary"></i>
                            اختيار توقيع المسمى الوظيفي (Titles):</label>
                        <select class="form-select select2-officer" id="signer_officer_id" name="officer_ids[]" multiple="multiple">
                            <option value="">-- اختر المسؤول --</option>
                            @if(isset($officers))
                                @foreach($officers as $officer)
                                    <option value="{{ $officer->id }}">{{ $officer->admin_name }} - {{ $officer->job_title }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted mt-2 d-block">سيتم استخدام اسم ومسمى هذا المسؤول في أسفل المذكرة</small>
                    </div>

                    <hr class="my-4 opacity-25">

                    <!-- Signatures Selection -->
                    <div class="col-md-12">
                        <label class="fw-bold mb-3 d-block text-navy d-flex justify-content-between">
                            <span><i class="fas fa-file-signature me-1 text-primary"></i> اختيار صور التواقيع الإلكترونية (إن وجد):</span>
                            <small class="text-muted fw-normal" id="signatures-loading">جاري التحميل...</small>
                        </label>

                        <div id="signatures-list" class="row g-2 max-vh-40 overflow-auto border rounded p-3 bg-light">
                            <!-- Signatures will be loaded here via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary px-5 fw-bold shadow-sm" id="confirm-print-btn">
                    <i class="fas fa-print me-1"></i> تأكيد وطباعة
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-navy {
        background-color: #001f3f !important;
    }

    .text-navy {
        color: #001f3f !important;
    }

    .btn-outline-navy {
        color: #001f3f;
        border-color: #001f3f;
    }

    .btn-outline-navy:hover,
    .btn-check:checked+.btn-outline-navy {
        background-color: #001f3f;
        color: #fff;
        border-color: #001f3f;
    }

    .max-vh-40 {
        max-height: 40vh;
    }

    .signature-item {
        transition: all 0.2s;
    }

    .signature-item:hover {
        background-color: #e9ecef;
    }

    .order-input {
        width: 60px !important;
        text-align: center;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentMemoirId = null;
        const STORAGE_KEY = 'memoir_print_settings';

        // Global function to trigger modal
        window.openPrintSettings = function (memoirId) {
            currentMemoirId = memoirId;
            loadSignaturesAndInitialize();
            var myModal = new bootstrap.Modal(document.getElementById('printSettingsModal'));
            myModal.show();
        };

        function updateDefaultsBasedOnHeader(headerType) {
            const DEFAULTS = {
                'ministry': 'نائب وزير الزراعة والثروة السمكية والموارد المائية',
                'committee': 'رئيس اللجنة الزراعية والسمكية العليا'
            };

            const targetTitle = DEFAULTS[headerType];
            if (!targetTitle) return;

            const officerSelect = $('#signer_officer_id');
            let currentValues = officerSelect.val() || [];
            let matchedOfficerId = null;

            officerSelect.find('option').each(function() {
                const text = $(this).text();
                if (text.includes(targetTitle)) {
                    matchedOfficerId = $(this).val();
                    return false;
                }
            });

            if (matchedOfficerId && !currentValues.includes(matchedOfficerId)) {
                currentValues.unshift(matchedOfficerId);
                officerSelect.val(currentValues).trigger('change');
            }

            document.querySelectorAll('.sig-checkbox').forEach(cb => {
                const title = cb.getAttribute('data-title');
                if (title && (title === targetTitle || title.includes(targetTitle))) {
                    cb.checked = true;
                }
            });
        }

        function loadSignaturesAndInitialize() {
            const listContainer = document.getElementById('signatures-list');
            const loadingText = document.getElementById('signatures-loading');

            loadingText.classList.remove('d-none');
            listContainer.innerHTML = '';

            fetch('{{ route("signatures.active") }}')
                .then(response => response.json())
                .then(data => {
                    loadingText.classList.add('d-none');

                    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                    const savedHeader = saved.header_type || 'ministry';
                    const savedSigs = saved.signatures || [];

                    document.querySelectorAll('input[name="header_type"]').forEach(radio => {
                        if (radio.value === savedHeader) radio.checked = true;
                    });

                    // Initialize Officer Select2
                    $('#signer_officer_id').select2({
                        theme: 'bootstrap-5',
                        dropdownParent: $('#printSettingsModal'),
                        placeholder: '-- اختر المسؤول --',
                        allowClear: true,
                        width: '100%',
                        language: {
                            noResults: function() { return "لا توجد نتائج"; }
                        }
                    });

                    if (saved.officer_ids && Array.isArray(saved.officer_ids)) {
                        $('#signer_officer_id').val(saved.officer_ids).trigger('change');
                    }

                    data.forEach(sig => {
                        const savedSig = savedSigs.find(s => s.id == sig.id);
                        const isChecked = savedSig ? 'checked' : '';
                        const orderValue = savedSig ? savedSig.order : sig.display_order;

                        const col = document.createElement('div');
                        col.className = 'col-12 signature-item border-bottom pb-2 mb-2 px-2 rounded';
                        col.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="form-check m-0 d-flex align-items-center gap-2">
                                    <input class="form-check-input sig-checkbox" type="checkbox" value="${sig.id}" id="sig_${sig.id}" ${isChecked} data-title="${sig.job_title || ''}">
                                    <label class="form-check-label fw-bold small mb-0 cursor-pointer" for="sig_${sig.id}">
                                        ${sig.name} <br>
                                        <small class="text-muted fw-normal">${sig.job_title}</small>
                                    </label>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <small class="text-muted x-small">الترتيب:</small>
                                    <input type="number" class="form-control form-control-sm order-input sig-order" value="${orderValue}" data-id="${sig.id}">
                                </div>
                            </div>
                        `;
                        listContainer.appendChild(col);
                    });

                    document.querySelectorAll('input[name="header_type"]').forEach(radio => {
                        radio.addEventListener('change', function() {
                            updateDefaultsBasedOnHeader(this.value);
                        });
                    });

                    if (!saved.officer_ids && !saved.signatures) {
                        updateDefaultsBasedOnHeader(savedHeader);
                    }
                })
                .catch(error => {
                    loadingText.innerText = 'فشل التحميل';
                    console.error('Error fetching signatures:', error);
                });
        }

        document.getElementById('confirm-print-btn').addEventListener('click', function () {
            if (!currentMemoirId) return;

            const btn = this;
            const originalHtml = btn.innerHTML;
            const headerType = document.querySelector('input[name="header_type"]:checked').value;
            const officerIds = $('#signer_officer_id').val(); // Will be an array now
            const selectedSigs = [];

            document.querySelectorAll('.sig-checkbox:checked').forEach(cb => {
                const id = cb.value;
                const orderInput = document.querySelector(`.sig-order[data-id="${id}"]`);
                selectedSigs.push({
                    id: id,
                    order: orderInput ? parseInt(orderInput.value) : 0
                });
            });

            // Save preferences to localStorage
            localStorage.setItem(STORAGE_KEY, JSON.stringify({
                header_type: headerType,
                signatures: selectedSigs,
                officer_ids: officerIds
            }));

            // Sort by order before constructing ID string
            selectedSigs.sort((a, b) => a.order - b.order);
            const sigIds = selectedSigs.map(s => s.id).join(',');

            // 1. Show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري التجهيز...';

            // 2. POST to prepare-print to get a secure session token
            fetch(`{{ url('memoirs') }}/${currentMemoirId}/prepare-print`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    header_type: headerType,
                    sigs: sigIds,
                    officer_ids: officerIds
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.url) {
                        // 3. Open the secure URL (hides parameters from user)
                        window.open(data.url, '_blank');
                        bootstrap.Modal.getInstance(document.getElementById('printSettingsModal')).hide();
                    } else {
                        alert('فشل تجهيز رابط الطباعة. يرجى المحاولة مرة أخرى.');
                    }
                })
                .catch(error => {
                    console.error('Error preparing print:', error);
                    alert('حدث خطأ أثناء التواصل مع الخادم.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
        });
    });
</script>