<!-- Execution Data Entry Form -->
<div class="card-body bg-light-subtle rounded mb-3" id="add-row-card-{{ $action->id }}">
    @php
        $totalAmountSpent = $allExecutions ? $allExecutions->sum('amount_spent') : 0;
        $totalCompletionPct = $allExecutions ? $allExecutions->sum('completion_percentage') : 0;
        $remainingCompletionPct = max(0, 100 - $totalCompletionPct);
    @endphp

    <!-- Budget Summary Section -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">المبلغ المنصرف حالياً</h6>
                        <i class="fas fa-coins text-success fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-success">{{ number_format($totalAmountSpent, 2) }}</div>
                    <small class="text-muted d-block mt-1">﷼ (ريال )</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">نسبة الإنجاز</h6>
                        <i class="fas fa-percentage text-primary fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-primary">{{ number_format(min($totalCompletionPct, 100), 1) }}%</div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                             role="progressbar" 
                             style="width: {{ min($totalCompletionPct, 100) }}%"
                             aria-valuenow="{{ min($totalCompletionPct, 100) }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">المتبقي للإنجاز</h6>
                        <i class="fas fa-hourglass-end text-warning fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-warning">{{ number_format($remainingCompletionPct, 1) }}%</div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             role="progressbar" 
                             style="width: {{ $remainingCompletionPct }}%"
                             aria-valuenow="{{ $remainingCompletionPct }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">عدد التنفيذات</h6>
                        <i class="fas fa-list-check text-info fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-info">{{ $allExecutions ? $allExecutions->count() : 0 }}</div>
                    <small class="text-muted d-block mt-1">عملية تنفيذية</small>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">تاريخ البداية الفعلي (ميلادي) *</label>
            <input type="date" 
                   class="form-control actual-start-gregorian"
                   name="actual_start_date_gregorian"
                   data-hijri-field="actual_start_hijri_display_{{ $action->id }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">تاريخ البداية الفعلي (هجري)</label>
            <input type="text" 
                   class="form-control"
                   id="actual_start_hijri_display_{{ $action->id }}"
                   readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">حالة الإجراء *</label>
            <select class="form-select" name="status">
                <option value="">-- اختر الحالة --</option>
                <option value="not_started">لم يبدأ</option>
                <option value="in_progress">قيد التنفيذ</option>
                <option value="delayed">متأخر</option>
                <option value="stalled">متوقف</option>
                <option value="completed">مكتمل</option>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">تاريخ النهاية الفعلي (ميلادي)</label>
            <input type="date" 
                   class="form-control actual-finish-gregorian"
                   name="actual_finish_date_gregorian"
                   data-hijri-field="actual_finish_hijri_display_{{ $action->id }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">تاريخ النهاية الفعلي (هجري)</label>
            <input type="text" 
                   class="form-control"
                   id="actual_finish_hijri_display_{{ $action->id }}"
                   readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">ملاحظات</label>
            <textarea class="form-control" 
                      name="notes" 
                      rows="2"
                      placeholder="أي ملاحظات أو تعليقات..."></textarea>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">المبلغ الفعلي (للإجراء) *</label>
            <div class="input-group">
                <input type="number" 
                       class="form-control actual-amount"
                       name="actual_amount" 
                       step="0.01" 
                       min="0">
                <span class="input-group-text">﷼</span>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">المبلغ المنفق *</label>
            <div class="input-group">
                <input type="number" 
                       class="form-control amount-spent"
                       name="amount_spent" 
                       step="0.01" 
                       min="0">
                <span class="input-group-text">﷼</span>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">المبلغ المتبقي (محسوب تلقائياً)</label>
            <div class="input-group">
                <input type="number" 
                       class="form-control remaining-amount"
                       readonly
                       step="0.01">
                <span class="input-group-text">﷼</span>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label class="form-label">نسبة الإنجاز (%) *</label>
            <div class="d-flex align-items-end gap-2">
                <div class="flex-grow-1">
                    <input type="number" 
                           class="form-control completion-percentage-input"
                           name="completion_percentage" 
                           step="0.1"
                           min="0"
                           max="100"
                           value="0">
                </div>
                <div style="width: 200px;">
                    <div class="progress" style="height: 32px;">
                        <div class="progress-bar completion-progress-preview bg-success" 
                             role="progressbar" 
                             style="width: 0%"
                             aria-valuenow="0" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <span class="completion-percentage-preview" style="font-size: 12px; font-weight: bold;">0%</span>
                        </div>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle me-1"></i>
                أدخل نسبة الإنجاز من 0% إلى 100%
            </small>
        </div>
    </div>

    <!-- Delay Explanation Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-warning" id="delay-section-executive">
                <div class="card-header bg-warning bg-opacity-10 py-2">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>شرح التأخير (سيظهر تلقائياً عند اكتشاف تأخير)
                    </h6>
                </div>
                <div class="card-body" style="display: none;" id="delay-content-executive">
                    <div class="mb-3">
                        <label class="form-label">شرح سبب التأخير *</label>
                        <textarea class="form-control delay-explanation-input"
                                  name="delay_explanation"
                                  rows="4"
                                  placeholder="أدخل شرح مفصل لسبب التأخير..."></textarea>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            يجب توفير شرح عند اكتشاف تأخير عن الموعد المخطط له
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">مرفقات التأخير (اختياري)</label>
                        <input type="file" 
                               class="form-control delay-attachments-input"
                               name="delay_attachments[]"
                               multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            صيغ مدعومة: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG (متعدد الاختيار)
                        </small>
                    </div>
                    <div id="delay-attachments-preview-executive" class="d-flex flex-wrap gap-2">
                        <!-- Preview will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Documents -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white py-2">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-pdf me-2"></i>المستندات التقنية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">إرفاق المستندات التقنية</label>
                        <input type="file" 
                               class="form-control technical-docs-input"
                               name="technical_documents[]"
                               multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png"
                               aria-label="رفع المستندات التقنية">
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            صيغ مدعومة: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG
                        </small>
                    </div>
                    <div id="technical-docs-preview-{{ $action->id }}" class="d-flex flex-wrap gap-2">
                        <!-- Preview will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Documents -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-receipt me-2"></i>المستندات المالية
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">إرفاق المستندات المالية</label>
                        <input type="file" 
                               class="form-control financial-docs-input"
                               name="financial_documents[]"
                               multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png"
                               aria-label="رفع المستندات المالية">
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            صيغ مدعومة: PDF, DOC, DOCX, XLS, XLSX, TXT, JPG, PNG
                        </small>
                    </div>
                    <div id="financial-docs-preview-{{ $action->id }}" class="d-flex flex-wrap gap-2">
                        <!-- Preview will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plannedEndDate = '{{ $action->end_date_gregorian ? $action->end_date_gregorian->format("Y-m-d") : "" }}';
    
    function checkForDelay() {
        const finishDateInput = document.querySelector('.actual-finish-gregorian');
        const delaySection = document.getElementById('delay-content-executive');
        const delayExplanation = document.querySelector('.delay-explanation-input');
        
        if (finishDateInput && finishDateInput.value && plannedEndDate) {
            const actualFinishDate = new Date(finishDateInput.value);
            const plannedDate = new Date(plannedEndDate);
            
            if (actualFinishDate > plannedDate) {
                if (delaySection) {
                    delaySection.style.display = 'block';
                    if (delayExplanation) {
                        delayExplanation.required = false;
                    }
                }
            } else {
                if (delaySection) {
                    delaySection.style.display = 'none';
                    if (delayExplanation) {
                        delayExplanation.required = false;
                        delayExplanation.value = '';
                    }
                }
            }
        }
    }
    
    const finishDateInput = document.querySelector('.actual-finish-gregorian');
    if (finishDateInput) {
        finishDateInput.addEventListener('change', checkForDelay);
        finishDateInput.addEventListener('input', checkForDelay);
        finishDateInput.addEventListener('blur', checkForDelay);
    }

    const techDocsInput = document.querySelector('[name="technical_documents[]"]');
    if (techDocsInput) {
        techDocsInput.addEventListener('change', function() {
            updateDocumentPreview(this, `technical-docs-preview-{{ $action->id }}`);
        });
    }

    const finDocsInput = document.querySelector('[name="financial_documents[]"]');
    if (finDocsInput) {
        finDocsInput.addEventListener('change', function() {
            updateDocumentPreview(this, `financial-docs-preview-{{ $action->id }}`);
        });
    }

    const delayAttachmentsInput = document.querySelector('.delay-attachments-input');
    if (delayAttachmentsInput) {
        delayAttachmentsInput.addEventListener('change', function() {
            updateDocumentPreview(this, 'delay-attachments-preview-executive');
        });
    }

    function updateDocumentPreview(input, previewContainerId) {
        const previewContainer = document.getElementById(previewContainerId);
        previewContainer.innerHTML = '';

        Array.from(input.files).forEach((file, index) => {
            const fileSize = (file.size / 1024).toFixed(2);
            const badge = document.createElement('div');
            badge.className = 'badge bg-info text-dark d-flex align-items-center gap-2 p-2';
            badge.style.maxWidth = '200px';
            badge.innerHTML = `
                <i class="fas fa-file"></i>
                <span title="${file.name}" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1;">
                    ${file.name}
                </span>
                <small>(${fileSize} KB)</small>
            `;
            previewContainer.appendChild(badge);
        });
    }

    const completionPercentageInput = document.querySelector('.completion-percentage-input');
    if (completionPercentageInput) {
        completionPercentageInput.addEventListener('change', function() {
            updateCompletionPercentagePreview(this);
        });
        completionPercentageInput.addEventListener('input', function() {
            updateCompletionPercentagePreview(this);
        });
    }

    function updateCompletionPercentagePreview(input) {
        const percentage = Math.min(100, Math.max(0, parseFloat(input.value) || 0));
        const progressBar = document.querySelector('.completion-progress-preview');
        const percentageText = document.querySelector('.completion-percentage-preview');
        
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage.toFixed(1));
        }
        if (percentageText) {
            percentageText.textContent = percentage.toFixed(1) + '%';
        }
    }
});
</script>
