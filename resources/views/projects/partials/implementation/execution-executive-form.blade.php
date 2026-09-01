<!-- Execution Data Entry Form -->
<div class="card-body bg-light-subtle rounded mb-3" id="add-row-card-{{ $action->id }}">
    @php
        $totalAmountSpent = $allExecutions ? $allExecutions->sum('amount_spent') : 0;
        $totalCompletionPct = $allExecutions ? $allExecutions->sum('completion_percentage') : 0;
        $remainingCompletionPct = max(0, 100 - $totalCompletionPct);
        $actionBudget = $action->costs->sum('total') ?? 0;
        $remainingBudget = max(0, $actionBudget - $totalAmountSpent);
    @endphp

    <!-- Budget Summary Section -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">الميزانية الكلية</h6>
                        <i class="fas fa-money-bill-wave text-info fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-info"
                         id="summary-budget-exec-{{ $action->id }}"
                         data-base-value="{{ $actionBudget }}">{{ number_format($actionBudget, 2) }}</div>
                    <small class="text-muted d-block mt-1">﷼ (ريال سعودي)</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">المبلغ المنصرف حالياً</h6>
                        <i class="fas fa-coins text-success fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-success"
                         id="summary-spent-exec-{{ $action->id }}"
                         data-base-value="{{ $totalAmountSpent }}">{{ number_format($totalAmountSpent, 2) }}</div>
                    <small class="text-muted d-block mt-1">﷼ (ريال سعودي)</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 bg-gradient-light shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">المتبقي من الميزانية</h6>
                        <i class="fas fa-wallet text-primary fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-primary"
                         id="summary-remaining-exec-{{ $action->id }}"
                         data-base-value="{{ $remainingBudget }}">{{ number_format($remainingBudget, 2) }}</div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                             id="summary-remaining-bar-exec-{{ $action->id }}"
                             role="progressbar" 
                             style="width: {{ $actionBudget > 0 ? ($remainingBudget / $actionBudget * 100) : 0 }}%"
                             aria-valuenow="{{ $actionBudget > 0 ? ($remainingBudget / $actionBudget * 100) : 0 }}" 
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
                        <h6 class="text-muted text-uppercase small fw-bold mb-0">نسبة الإنجاز الكلية</h6>
                        <i class="fas fa-percentage text-warning fa-lg opacity-75"></i>
                    </div>
                    <div class="display-6 fw-bold text-warning">
                        <span id="summary-completion-exec-{{ $action->id }}" data-base-value="{{ $totalCompletionPct }}">{{ number_format(min($totalCompletionPct, 100), 1) }}</span>%
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             id="summary-completion-bar-exec-{{ $action->id }}"
                             role="progressbar" 
                             style="width: {{ min($totalCompletionPct, 100) }}%"
                             aria-valuenow="{{ min($totalCompletionPct, 100) }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">تاريخ البداية الفعلي (ميلادي) * <small class="text-muted">(مخطط: {{ $action->start_date_gregorian?->format('d/m/Y') ?? '-' }})</small></label>
            <input type="date" 
                   class="form-control actual-start-gregorian"
                   name="actual_start_date_gregorian"
                   data-hijri-field="actual_start_hijri_display_{{ $action->id }}"
                   value="{{ $action->start_date_gregorian ? $action->start_date_gregorian->format('Y-m-d') : '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">تاريخ البداية الفعلي (هجري)</label>
            <input type="text" 
                   class="form-control actual-start-hijri"
                   name="actual_start_date_hijri"
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
            <label class="form-label">تاريخ النهاية الفعلي (ميلادي) <small class="text-muted">(مخطط: {{ $action->end_date_gregorian?->format('d/m/Y') ?? '-' }})</small></label>
            <input type="date" 
                   class="form-control actual-finish-gregorian"
                   name="actual_finish_date_gregorian"
                   data-hijri-field="actual_finish_hijri_display_{{ $action->id }}"
                   value="{{ $action->end_date_gregorian ? $action->end_date_gregorian->format('Y-m-d') : '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">تاريخ النهاية الفعلي (هجري)</label>
            <input type="text" 
                   class="form-control actual-finish-hijri"
                   name="actual_finish_date_hijri"
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
                       min="0" 
                       value="{{ number_format($actionBudget, 2, '.', '') }}">
                <span class="input-group-text">﷼</span>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle me-1"></i>
                هذا هو المبلغ الكلي المعتمد لهذا الإجراء
            </small>
        </div>
        <div class="col-md-4">
            <label class="form-label">المبلغ المنفذ (التعميد/الصرف) *</label>
            <div class="input-group">
                <input type="number" 
                       class="form-control amount-spent"
                       name="amount_spent" 
                       step="0.01" 
                       min="0" 
                       max="{{ $remainingBudget + 0.01 }}"
                       data-remaining-budget="{{ $remainingBudget }}">
                <span class="input-group-text">﷼</span>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-info-circle me-1"></i>
                أقصى مبلغ متاح بدون تجاوز: <span class="fw-bold">{{ number_format($remainingBudget, 2) }}</span> ﷼
            </small>
        </div>
        <div class="col-md-4">
            <label class="form-label">الرصيد المتبقي (بعد هذا الإدخال)</label>
            <div class="input-group">
                <input type="number" 
                       class="form-control remaining-amount-display"
                       id="remaining-amount-display-exec-{{ $action->id }}"
                       readonly
                       value="{{ number_format($remainingBudget, 2, '.', '') }}"
                       step="0.01">
                <span class="input-group-text">﷼</span>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="fas fa-calculator me-1"></i>
                محسوب تلقائياً: (المبلغ الفعلي - المنفق)
            </small>
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
                           max="{{ $remainingCompletionPct }}"
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
                المتبقي للإكمال: <span class="fw-bold">{{ number_format($remainingCompletionPct, 1) }}%</span>
            </small>
        </div>
    </div>

    <!-- Budget Warning -->
    <div class="alert alert-warning alert-dismissible fade show mb-3" id="budget-warning-exec-{{ $action->id }}" style="display: none;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>تنبيه:</strong> المبلغ المنفق يتجاوز المبلغ المتبقي من الميزانية!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <input type="hidden" name="planned_amount" value="{{ $actionBudget }}">
    <input type="hidden" name="executive_activity_action_id" value="{{ $action->id }}">

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

