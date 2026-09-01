<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-play-circle"></i>الأنشطة التمهيدية والإجراءات والتكاليف
    </div>

    <div class="project-table-wrapper">
        <table class="project-table" id="preliminaryActivitiesTable">
            <thead>
                <tr>
                    <th>النشاط</th>
                    <th>
                        الوزن (%)
                        <div class="mt-2 small d-flex align-items-center gap-2">
                            <span>المجموع:</span>
                            <span id="totalActivitiesWeightDisplay" class="badge bg-secondary">0.00%</span>
                            <span id="activitiesWeightStatus" class="badge bg-warning" style="display: none;">غير موازن</span>
                        </div>
                    </th>
                    <th>إجمالي التكاليف</th>
                    <th width="150">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="preliminary-activities-container">
                @if(isset($project) && $project->preliminaryActivities->count() > 0)
                    @foreach($project->preliminaryActivities as $index => $activity)
                        <!-- Activity Row -->
                        <tr class="project-animated-row activity-row" data-activity-index="{{ $index }}" data-activity-expanded="true">
                            <td>
                                <input type="hidden" 
                                       name="preliminary_activities[{{ $index }}][id]"
                                       value="{{ $activity->id }}">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link p-0 toggle-activity-btn" 
                                            data-activity-index="{{ $index }}"
                                            title="إظهار/إخفاء الإجراءات">
                                        <i class="fas fa-chevron-down activity-toggle-icon"></i>
                                    </button>
                                    <input type="text" 
                                           name="preliminary_activities[{{ $index }}][name]"
                                           class="form-control form-control-sm activity-name-input"
                                           value="{{ $activity->name }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="preliminary_activities[{{ $index }}][weight]"
                                           class="form-control form-control-sm activity-weight"
                                           value="{{ $activity->weight }}">
                                    <span class="text-muted" style="font-size: 0.9rem; min-width: 20px;">%</span>
                                </div>
                            </td>
                            <td>
                                <div class="text-success fw-bold activity-total-cost" data-activity-index="{{ $index }}">0.00</div>
                                <small class="text-muted">ر.س</small>
                            </td>
                            <td>
                                <div class="project-action-buttons d-flex gap-2">
                                    <button type="button" class="project-btn project-btn-info add-procedure-btn"
                                            data-activity-index="{{ $index }}"
                                            title="إضافة إجراء">
                                        <i class="fas fa-plus-circle"></i>إجراء
                                    </button>
                                    <button type="button" class="project-btn project-btn-danger remove-activity"
                                            title="حذف النشاط">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Procedures for this activity -->
                        @if($activity->procedures && $activity->procedures->count() > 0)
                            @foreach($activity->procedures as $procedureIndex => $procedure)
                                <tr class="project-animated-row procedure-row" data-activity-index="{{ $index }}" data-procedure-index="{{ $procedureIndex }}">
                                    <td colspan="4" class="p-0">
                                        <div class="project-procedure-container bg-light p-3 border-start border-success border-3">
                                            <input type="hidden" 
                                                   name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][id]"
                                                   value="{{ $procedure->id }}">
                                            <h6 class="border-bottom pb-2 mb-3 text-success d-flex align-items-center" style="cursor: pointer;">
                                                <button type="button" class="btn btn-sm btn-link p-0 me-2 toggle-procedure-details-btn" 
                                                        data-procedure-index="{{ $procedureIndex }}"
                                                        title="إظهار/إخفاء تفاصيل الإجراء">
                                                    <i class="fas fa-chevron-down procedure-toggle-icon"></i>
                                                </button>
                                                <i class="fas fa-arrow-down me-1"></i>الإجراء #<span class="procedure-number">{{ $procedureIndex + 1 }}</span> للنشاط: <span class="activity-name-display">{{ $activity->name ?? 'بدون اسم' }}</span>
                                            </h6>
                                            <div class="procedure-details-container" data-procedure-index="{{ $procedureIndex }}" data-details-expanded="true">
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold">اسم الإجراء </label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm procedure-name-input"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][procedure_name]"
                                                               value="{{ $procedure->procedure_name }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label small fw-bold">الوزن (%) </label>
                                                        <input type="number" 
                                                               class="form-control form-control-sm procedure-weight"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][weight]"
                                                               value="{{ $procedure->weight }}"
                                                               min="0" max="100" step="0.01">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold">وسائل التحقق</label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][verification_means]"
                                                               value="{{ $procedure->verification_means }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold">الإجراءات</label>
                                                        <div class="d-flex gap-1">
                                                            <button type="button" class="project-btn project-btn-info add-cost-btn" 
                                                                    data-activity-index="{{ $index }}"
                                                                    data-procedure-index="{{ $procedureIndex }}"
                                                                    title="إضافة تكلفة">
                                                                <i class="fas fa-plus-circle"></i>تكلفة
                                                            </button>
                                                            <button type="button" class="project-btn project-btn-danger remove-procedure">
                                                                <i class="fas fa-trash-alt"></i>حذف
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Dates Section -->
                                                <div class="row mb-3">
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold">تاريخ البداية (ميلادي)</label>
                                                        <input type="date"
                                                               class="form-control form-control-sm start-date"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][start_date]"
                                                               value="{{ $procedure->start_date }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold">تاريخ النهاية (ميلادي)</label>
                                                        <input type="date"
                                                               class="form-control form-control-sm end-date"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][end_date]"
                                                               value="{{ $procedure->end_date }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold">المدة (أيام)</label>
                                                        <input type="number"
                                                               class="form-control form-control-sm duration-days"
                                                               name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][duration_days]"
                                                               value="{{ $procedure->duration_days ?? 0 }}"
                                                               min="0" readonly>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Costs Section -->
                                            <div class="costs-section mt-3" data-procedure-index="{{ $procedureIndex }}" data-costs-expanded="true">
                                                <h6 class="border-bottom pb-2 mb-3 text-info d-flex align-items-center" style="cursor: pointer;">
                                                    <button type="button" class="btn btn-sm btn-link p-0 me-2 toggle-costs-btn" 
                                                            data-procedure-index="{{ $procedureIndex }}"
                                                            title="إظهار/إخفاء التكاليف">
                                                        <i class="fas fa-chevron-down costs-toggle-icon"></i>
                                                    </button>
                                                    <i class="fas fa-dollar-sign me-1"></i>تكاليف الإجراء: <span class="procedure-name-display">{{ $procedure->procedure_name ?? 'بدون اسم' }}</span>
                                                </h6>
                                                
                                                <div class="costs-container" data-costs-expanded="true">
                                                    <div class="costs-list">
                                                        @if($procedure->costs && $procedure->costs->count() > 0)
                                                            @foreach($procedure->costs as $costIndex => $cost)
                                                                <div class="project-cost-item bg-white p-3 mb-3 rounded border" data-activity-index="{{ $index }}" data-procedure-index="{{ $procedureIndex }}" data-cost-row>
                                                                    <input type="hidden" 
                                                                           name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][id]"
                                                                           value="{{ $cost->id }}">
                                                                    <div class="row align-items-end g-2">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label small fw-bold">البند المالي </label>
                                                                            <select class="form-select form-select-sm financial-item-select"
                                                                                    name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][financial_item_id]">
                                                                                <option value="">اختر البند المالي</option>
                                                                                @if(isset($financialItems) && $financialItems->count() > 0)
                                                                                    @foreach($financialItems as $financialItem)
                                                                                        <option value="{{ $financialItem->id }}" {{ $cost->financial_item_id == $financialItem->id ? 'selected' : '' }}>{{ $financialItem->name }}</option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-2">
                                                                            <label class="form-label small fw-bold">الوحدة </label>
                                                                            <select class="form-select form-select-sm unit-select"
                                                                                    name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][unit_id]">
                                                                                <option value="">اختر الوحدة</option>
                                                                                @if(isset($units) && $units->count() > 0)
                                                                                    @foreach($units as $unit)
                                                                                        <option value="{{ $unit->id }}" {{ $cost->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-2">
                                                                            <label class="form-label small fw-bold">المبلغ </label>
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="number" 
                                                                                       class="form-control amount-input"
                                                                                       name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][amount]"
                                                                                       value="{{ $cost->amount ?? old("preliminary_activities.{$index}.procedures.{$procedureIndex}.costs.{$costIndex}.amount", 0) }}"
                                                                                       min="0" 
                                                                                       step="0.01" 
                                                                                       placeholder="0.00">
                                                                                <span class="input-group-text">ر.س</span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-2">
                                                                            <label class="form-label small fw-bold">الكمية </label>
                                                                            <input type="number" 
                                                                                   class="form-control form-control-sm quantity-input"
                                                                                   name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][quantity]"
                                                                                   value="{{ $cost->quantity ?? old("preliminary_activities.{$index}.procedures.{$procedureIndex}.costs.{$costIndex}.quantity", 1) }}"
                                                                                   min="1" 
                                                                                   step="1"
                                                                                   placeholder="1">
                                                                        </div>
                                                                        
                                                                        <div class="col-md-2">
                                                                            <label class="form-label small fw-bold">الإجمالي</label>
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="number" 
                                                                                       class="form-control total-input bg-light"
                                                                                       name="preliminary_activities[{{ $index }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][total]"
                                                                                       value="{{ $cost->total ?? old("preliminary_activities.{$index}.procedures.{$procedureIndex}.costs.{$costIndex}.total", 0) }}"
                                                                                       readonly
                                                                                       tabindex="-1">
                                                                                <span class="input-group-text">ر.س</span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-1">
                                                                            <button type="button" 
                                                                                    class="btn btn-outline-danger btn-sm delete-cost-btn w-100"
                                                                                    title="حذف هذا البند">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="text-center text-muted py-3">
                                                                <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i><br>
                                                                لا توجد تكاليف مضافة بعد
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Procedure Total Cost -->
                                            <div class="row mt-3 pt-3 border-top">
                                                <div class="col-md-12">
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <small class="text-muted">إجمالي التكاليف:</small>
                                                        <strong class="procedure-total-cost text-success ms-2" 
                                                                data-activity-index="{{ $index }}"
                                                                data-procedure-index="{{ $procedureIndex }}">0.00</strong>
                                                        <span class="ms-1">ر.س</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="4" class="text-center">
                            <i class="fas fa-play-circle fa-2x mb-2"></i><br>
                            لا توجد أنشطة تمهيدية مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="add-activity-btn">
                <i class="fas fa-plus"></i>إضافة نشاط تمهيدي جديد
            </button>
        </div>
    </div>
</div>

<style>
.toggle-activity-btn,
.toggle-procedure-details-btn,
.toggle-costs-btn {
    transition: transform 0.3s ease;
    color: inherit;
    text-decoration: none;
}

.toggle-activity-btn:hover,
.toggle-procedure-details-btn:hover,
.toggle-costs-btn:hover {
    opacity: 0.7;
}

.activity-row[data-activity-expanded="false"] ~ .procedure-row,
.procedure-details-container[data-details-expanded="false"],
.costs-container[data-costs-expanded="false"] {
    display: none;
}

.activity-toggle-icon.rotated,
.procedure-toggle-icon.rotated,
.costs-toggle-icon.rotated {
    transform: rotate(-90deg);
}
</style>

<script>
(function() {
    'use strict';

    function initializeExpandCollapse() {
        document.addEventListener('click', function(e) {
            if (e.target.closest('.toggle-activity-btn')) {
                const btn = e.target.closest('.toggle-activity-btn');
                const activityIndex = btn.dataset.activityIndex;
                const activityRow = document.querySelector(`.activity-row[data-activity-index="${activityIndex}"]`);
                const icon = btn.querySelector('.activity-toggle-icon');
                
                const isExpanded = activityRow.dataset.activityExpanded === 'true';
                activityRow.dataset.activityExpanded = isExpanded ? 'false' : 'true';
                
                if (isExpanded) {
                    icon.classList.add('rotated');
                } else {
                    icon.classList.remove('rotated');
                }
            }

            if (e.target.closest('.toggle-procedure-details-btn')) {
                const btn = e.target.closest('.toggle-procedure-details-btn');
                const procedureIndex = btn.dataset.procedureIndex;
                const detailsContainer = btn.closest('.project-procedure-container').querySelector(`.procedure-details-container[data-procedure-index="${procedureIndex}"]`);
                const icon = btn.querySelector('.procedure-toggle-icon');
                
                if (detailsContainer) {
                    const isExpanded = detailsContainer.dataset.detailsExpanded === 'true';
                    detailsContainer.dataset.detailsExpanded = isExpanded ? 'false' : 'true';
                    
                    if (isExpanded) {
                        icon.classList.add('rotated');
                    } else {
                        icon.classList.remove('rotated');
                    }
                }
            }

            if (e.target.closest('.toggle-costs-btn')) {
                const btn = e.target.closest('.toggle-costs-btn');
                const procedureIndex = btn.dataset.procedureIndex;
                const container = btn.closest('.project-procedure-container');
                const costsContainer = container.querySelector(`.costs-container[data-costs-expanded]`);
                const icon = btn.querySelector('.costs-toggle-icon');
                
                if (costsContainer) {
                    const isExpanded = costsContainer.dataset.costsExpanded === 'true';
                    costsContainer.dataset.costsExpanded = isExpanded ? 'false' : 'true';
                    
                    if (isExpanded) {
                        icon.classList.add('rotated');
                    } else {
                        icon.classList.remove('rotated');
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeExpandCollapse);
    } else {
        initializeExpandCollapse();
    }

    function calculateActivityWeights() {
        let totalWeight = 0;
        document.querySelectorAll('input.activity-weight').forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });
        return parseFloat(totalWeight.toFixed(2));
    }

    function updateActivityWeightDisplay() {
        const totalWeight = calculateActivityWeights();
        const totalWeightDisplay = document.getElementById('totalActivitiesWeightDisplay');
        const weightStatus = document.getElementById('activitiesWeightStatus');

        if (totalWeightDisplay) {
            totalWeightDisplay.textContent = totalWeight.toFixed(2) + '%';

            if (totalWeight === 100) {
                totalWeightDisplay.className = 'badge bg-success';
                if (weightStatus) {
                    weightStatus.style.display = 'none';
                }
            } else if (totalWeight > 100) {
                totalWeightDisplay.className = 'badge bg-danger';
                if (weightStatus) {
                    weightStatus.textContent = 'تجاوز 100%';
                    weightStatus.className = 'badge bg-danger';
                    weightStatus.style.display = 'inline-block';
                }
            } else if (totalWeight < 100) {
                totalWeightDisplay.className = 'badge bg-warning';
                if (weightStatus) {
                    weightStatus.textContent = 'أقل من 100%';
                    weightStatus.className = 'badge bg-warning';
                    weightStatus.style.display = 'inline-block';
                }
            }
        }
    }

    function calculateProcedureWeights(activityIndex) {
        let totalWeight = 0;
        const procedures = document.querySelectorAll(`.procedure-row[data-activity-index="${activityIndex}"] .procedure-weight`);
        procedures.forEach(input => {
            totalWeight += parseFloat(input.value) || 0;
        });
        return parseFloat(totalWeight.toFixed(2));
    }

    function updateProcedureWeightDisplay(activityIndex) {
        const totalWeight = calculateProcedureWeights(activityIndex);
        const activityRow = document.querySelector(`.activity-row[data-activity-index="${activityIndex}"]`);
        
        if (activityRow) {
            let procedureWeightDisplay = activityRow.querySelector('.procedure-weight-total');
            if (!procedureWeightDisplay) {
                const weightTd = activityRow.querySelector('td:nth-child(2)');
                if (weightTd) {
                    procedureWeightDisplay = document.createElement('div');
                    procedureWeightDisplay.className = 'small text-muted mt-2 procedure-weight-total';
                    weightTd.appendChild(procedureWeightDisplay);
                }
            }
            
            if (procedureWeightDisplay) {
                procedureWeightDisplay.innerHTML = `<span class="badge bg-info" title="مجموع أوزان الإجراءات">إجراءات: ${totalWeight.toFixed(2)}%</span>`;
            }
        }
    }

    document.addEventListener('input', (e) => {
        if (e.target.closest('.activity-weight')) {
            updateActivityWeightDisplay();
        }
        if (e.target.closest('.procedure-weight')) {
            const procedureInput = e.target.closest('input.procedure-weight');
            const procedureRow = procedureInput.closest('.procedure-row');
            if (procedureRow) {
                const activityIndex = procedureRow.dataset.activityIndex;
                updateProcedureWeightDisplay(activityIndex);
            }
        }
    });

    document.addEventListener('change', (e) => {
        if (e.target.closest('.start-date, .end-date')) {
            const dateInput = e.target;
            const procedureContainer = dateInput.closest('.procedure-row, .project-procedure-container');
            
            if (procedureContainer && typeof HijriConverter !== 'undefined') {
                const startDateInput = procedureContainer.querySelector('.start-date');
                const endDateInput = procedureContainer.querySelector('.end-date');
                const durationInput = procedureContainer.querySelector('.duration-days');
                const startHijriDisplay = procedureContainer.querySelector('.start-date-hijri-display');
                const endHijriDisplay = procedureContainer.querySelector('.end-date-hijri-display');
                const startHijriHidden = procedureContainer.querySelector('input.start-date-hijri');
                const endHijriHidden = procedureContainer.querySelector('input.end-date-hijri');
                
                if (startDateInput && startDateInput.value) {
                    const hijri = HijriConverter.gregorianToHijri(startDateInput.value);
                    const hijriValue = HijriConverter.formatHijri(hijri);
                    if (startHijriDisplay) startHijriDisplay.value = hijriValue;
                    if (startHijriHidden) startHijriHidden.value = hijriValue;
                }
                
                if (endDateInput && endDateInput.value) {
                    const hijri = HijriConverter.gregorianToHijri(endDateInput.value);
                    const hijriValue = HijriConverter.formatHijri(hijri);
                    if (endHijriDisplay) endHijriDisplay.value = hijriValue;
                    if (endHijriHidden) endHijriHidden.value = hijriValue;
                }
                
                if (startDateInput && endDateInput && durationInput && startDateInput.value && endDateInput.value) {
                    const start = new Date(startDateInput.value + 'T00:00:00Z');
                    const end = new Date(endDateInput.value + 'T00:00:00Z');
                    const diffTime = end.getTime() - start.getTime();
                    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays >= 0) {
                        durationInput.value = diffDays;
                    }
                }
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        updateActivityWeightDisplay();
        
        document.querySelectorAll('.activity-row').forEach(row => {
            const activityIndex = row.dataset.activityIndex;
            updateProcedureWeightDisplay(activityIndex);
            
            const startDateInput = row.closest('.project-table-container').querySelector(`.procedure-row[data-activity-index="${activityIndex}"] .start-date`);
            if (startDateInput && startDateInput.value) {
                const event = new Event('change', { bubbles: true });
                startDateInput.dispatchEvent(event);
            }
        });
    });
})();
</script>
