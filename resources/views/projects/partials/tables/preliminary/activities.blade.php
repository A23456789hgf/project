@php
    $isFinancialReview = (isset($projectStatus) && $projectStatus === 'financial_review');
    $reviewerType = $reviewerType ?? 'general';
    $canModifyStructure = !$isFinancialReview || $reviewerType === 'technical' || $reviewerType === 'general';
    $canModifyCosts = !$isFinancialReview || $reviewerType === 'financial' || $reviewerType === 'general';
@endphp
<div class="preliminary-activities-container" id="preliminary-activities-container">
    <!-- Header Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary-gradient text-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>الأنشطة الأولية للمشروع
                </h5>
                <small class="text-white-50">إدارة الأنشطة والإجراءات والتكاليف الأولية</small>
            </div>
            <div>
                <span class="badge bg-light text-primary fs-6" id="total-weight-display">
                    الوزن الإجمالي: <span id="weight-total">0</span>%
                </span>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-soft-primary">
                        <div class="stat-icon">
                            <i class="fas fa-tasks text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h4 id="activities-count">0</h4>
                            <p>عدد الأنشطة</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-soft-success">
                        <div class="stat-icon">
                            <i class="fas fa-list-check text-success"></i>
                        </div>
                        <div class="stat-content">
                            <h4 id="procedures-count">0</h4>
                            <p>عدد الإجراءات</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-soft-warning">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave text-warning"></i>
                        </div>
                        <div class="stat-content">
                            <h4 id="costs-count">0</h4>
                            <p>عدد التكاليف</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-soft-info">
                        <div class="stat-icon">
                            <i class="fas fa-calculator text-info"></i>
                        </div>
                        <div class="stat-content">
                            <h4><span id="total-cost">0</span> ريال</h4>
                            <p>إجمالي التكلفة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100 project-table no-stack" id="preliminary-activities-table" style="min-width: 900px;">
                    <thead class="bg-primary-gradient text-white">
                        <tr>
                            <th class="w-5">#</th>
                            <th class="w-50 col-min-400">النشاط / الإجراءات</th>
                            <th class="w-25 text-center col-min-150">الوزن (%)</th>
                            <th class="w-20 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="activities-tbody">
                        @if(isset($project) && $project->preliminaryActivities->count() > 0)
                            @foreach($project->preliminaryActivities as $activityIndex => $activity)
                                @include('projects.partials.tables.preliminary.activity-row', [
                                    'activityIndex' => $activityIndex,
                                    'activity' => $activity,
                                    'financialItems' => $financialItems ?? []
                                ])
                            @endforeach
                        @else
                            <tr id="no-activities-row">
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد أنشطة مضافة</h5>
                                        <p class="text-muted">قم بإضافة أول نشاط للمشروع</p>
                                        <button type="button" class="btn btn-primary shadow-sm px-4" onclick="addNewActivity()">
                                            <i class="fas fa-plus me-1"></i>إضافة أول نشاط
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($canModifyStructure)
            <div class="text-center mt-4">
                <button type="button" class="btn btn-success btn-lg shadow-sm px-5" onclick="addNewActivity()">
                    <i class="fas fa-plus-circle me-2"></i>إضافة نشاط جديد للمشروع
                </button>
            </div>
            @endif
        </div>
    </div>


    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">معاينة البيانات</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="preview-content">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row Templates -->
<template id="activity-row-template">
    @include('projects.partials.tables.preliminary.activity-row', [
        'activityIndex' => 'INDEX_PLACEHOLDER', 
        'activity' => null,
        'financialItems' => $financialItems ?? [],
        'units' => $units ?? []
    ])
</template>

<template id="procedure-row-template">
    @include('projects.partials.tables.preliminary.procedure-row', [
        'activityIndex' => 'ACTIVITY_INDEX_PLACEHOLDER', 
        'procedureIndex' => 'PROCEDURE_INDEX_PLACEHOLDER', 
        'procedure' => null,
        'financialItems' => $financialItems ?? [],
        'units' => $units ?? []
    ])
</template>

<template id="cost-row-template">
    @include('projects.partials.tables.preliminary.cost-row', [
        'activityIndex' => 'ACTIVITY_INDEX_PLACEHOLDER', 
        'procedureIndex' => 'PROCEDURE_INDEX_PLACEHOLDER', 
        'costIndex' => 'COST_INDEX_PLACEHOLDER', 
        'cost' => null,
        'financialItems' => $financialItems ?? [],
        'units' => $units ?? []
    ])
</template>

@push('styles')
<style>
    .preliminary-activities-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-card {
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border: 1px solid transparent;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 24px;
    }

    .stat-content h4 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #718096;
        font-size: 14px;
    }

    .bg-soft-primary { background-color: rgba(102, 126, 234, 0.1); }
    .bg-soft-success { background-color: rgba(72, 187, 120, 0.1); }
    .bg-soft-warning { background-color: rgba(237, 137, 54, 0.1); }
    .bg-soft-info { background-color: rgba(66, 153, 225, 0.1); }

    .empty-state {
        padding: 40px;
        color: #6c757d;
    }

    .table th {
        font-weight: 600;
        color: #4a5568;
        border-bottom: 2px solid #e2e8f0;
    }

    .table > :not(:first-child) {
        border-top: none;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }

    .activity-row {
        transition: all 0.3s ease;
    }

    .activity-row:hover {
        background-color: #f8fafc !important;
    }

    .collapse-icon i, .procedure-collapse-btn i {
        transition: transform 0.2s ease;
    }

    .total-cost-badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
    }

    .btn-group-xs > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.2rem;
    }
</style>
@endpush

@push('scripts')
@include('projects.partials.unit-fetch-script')
<script>
    // Persistent counters for unique indices
    let activityCounter = 0;
    let procedureCounter = 0;
    let costCounter = 0;

    // Helper to find the next available index based on existing elements
    function initializeCounters() {
        document.querySelectorAll('.activity-row').forEach(row => {
            const idx = parseInt(row.dataset.activityIndex) || 0;
            if (idx >= activityCounter) activityCounter = idx + 1;
        });
        
        document.querySelectorAll('.procedure-row').forEach(row => {
            const idx = parseInt(row.dataset.procedureIndex) || 0;
            if (idx >= procedureCounter) procedureCounter = idx + 1;
        });

        document.querySelectorAll('.cost-row').forEach(row => {
            const idx = parseInt(row.dataset.costIndex) || 0;
            if (idx >= costCounter) costCounter = idx + 1;
        });
    }

    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeCounters();
        
        // Initialize Hijri dates for existing rows
        document.querySelectorAll('.procedure-row').forEach(row => {
            const startVal = row.querySelector('.procedure-start-date')?.value;
            const endVal = row.querySelector('.procedure-end-date')?.value;
            const startHijriInput = row.querySelector('.procedure-start-date-hijri');
            const endHijriInput = row.querySelector('.procedure-end-date-hijri');
            const durationInput = row.querySelector('.procedure-duration');

            if (startVal && startHijriInput && typeof HijriConverter !== 'undefined') {
                const hijri = HijriConverter.gregorianToHijri(startVal);
                startHijriInput.value = HijriConverter.formatHijri(hijri);
            }
            if (endVal && endHijriInput && typeof HijriConverter !== 'undefined') {
                const hijri = HijriConverter.gregorianToHijri(endVal);
                endHijriInput.value = HijriConverter.formatHijri(hijri);
            }
            if (durationInput && startVal && endVal && typeof HijriConverter !== 'undefined') {
                 durationInput.value = HijriConverter.calculateDuration(startVal, endVal);
            }
        });

        // Initialize activity weights
        document.querySelectorAll('.activity-row').forEach(activityRow => {
             calculateActivityProceduresWeight(activityRow);
        });

        if (typeof calculateTotals === 'function') {
            calculateTotals();
        }
    });

    function addNewActivity() {
        const tbody = document.getElementById('activities-tbody');
        const template = document.getElementById('activity-row-template').innerHTML;
        
        const noRow = document.getElementById('no-activities-row');
        if (noRow) noRow.remove();

        const activityIndex = activityCounter++;
        const newHtml = template.replace(/INDEX_PLACEHOLDER/g, activityIndex);

        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = newHtml;
        
        while (tempDiv.firstChild) {
            tbody.appendChild(tempDiv.firstChild);
        }
        
        updateActivityNumbers();
        calculateTotals();
    }


    function removeActivity(button) {
        const row = button.closest('.activity-row');
        // Check if activity has procedures
        const proceduresCount = row.nextElementSibling.querySelectorAll('.procedure-row').length;
        
        if (proceduresCount > 0) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: `هذا النشاط يحتوي على ${proceduresCount} إجراءات. هل تريد حذفه مع جميع الإجراءات؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    const detailsRow = row.nextElementSibling;
                    if (detailsRow && detailsRow.classList.contains('activity-details-row')) {
                        detailsRow.remove();
                    }
                    row.remove();
                    updateActivityNumbers();
                    calculateTotals();
                    checkEmptyState();
                }
            });
        } else {
            const detailsRow = row.nextElementSibling;
            if (detailsRow && detailsRow.classList.contains('activity-details-row')) {
                detailsRow.remove();
            }
            row.remove();
            updateActivityNumbers();
            calculateTotals();
            checkEmptyState();
        }
    }

    function updateActivityNumbers() {
        document.querySelectorAll('.activity-row').forEach((row, index) => {
            row.querySelector('.activity-number').textContent = index + 1;
        });
    }

    function checkEmptyState() {
        const activitiesCount = document.querySelectorAll('.activity-row').length;
        if (activitiesCount === 0) {
            // Re-create the empty state row if simpler, or just show it if hidden
            // Assuming it might have been removed, we should probably check if it exists or recreate it
            // For now, simpler to reload page or just leave it empty
            // If the element exists but is hidden:
             const noRow = document.getElementById('no-activities-row');
             // If we removed it, we can't unhide it. We should ideally toggle display none
             // But existing code removes it.
             if(!noRow) {
                // Ideally append logic here, but for now let's hope it wasn't destroyed if removed
                // Actually `addNewActivity` removes it. So we need to recreate.
                const tbody = document.getElementById('activities-tbody');
                const html = `<tr id="no-activities-row">
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد أنشطة مضافة</h5>
                                        <p class="text-muted">قم بإضافة أول نشاط للمشروع</p>
                                        <button type="button" class="btn btn-primary" onclick="addNewActivity()">
                                            <i class="fas fa-plus me-1"></i>إضافة نشاط جديد
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                tbody.insertAdjacentHTML('beforeend', html);
             }
        }
    }

    /* --- Moved Functions from Partials --- */

    function toggleProcedures(button) {
        const activityRow = button.closest('.activity-row');
        const collapseBtn = button.classList.contains('collapse-icon') ? button : activityRow.querySelector('.collapse-icon');
        const collapseIcon = collapseBtn.querySelector('i');
        const detailsRow = activityRow.nextElementSibling;
        const collapseDiv = detailsRow.querySelector('.collapse');
        
        if (collapseDiv.classList.contains('show')) {
            collapseDiv.classList.remove('show');
            setTimeout(() => {
                if (!collapseDiv.classList.contains('show')) {
                    detailsRow.classList.add('d-none');
                }
            }, 300);
            collapseIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            collapseBtn.setAttribute('aria-expanded', 'false');
        } else {
            detailsRow.classList.remove('d-none');
            setTimeout(() => collapseDiv.classList.add('show'), 10);
            collapseIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            collapseBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function addProcedureToActivity(button) {
        const activityRow = button.closest('.activity-details-row').previousElementSibling;
        const detailsRow = activityRow.nextElementSibling;
        const proceduresTbody = detailsRow.querySelector('.procedures-tbody');
        
        const noProceduresRow = proceduresTbody.querySelector('.no-procedures-row');
        if (noProceduresRow) {
            noProceduresRow.remove();
        }
        
        // Get activity index from the activity row
        const activityIndex = activityRow.dataset.activityIndex;
        const procedureIndex = procedureCounter++;
        
        // Get procedure template
        let templateHtml = document.getElementById('procedure-row-template').innerHTML;
        templateHtml = templateHtml.replace(/ACTIVITY_INDEX_PLACEHOLDER/g, activityIndex)
                                   .replace(/PROCEDURE_INDEX_PLACEHOLDER/g, procedureIndex);
        
        const tempTbody = document.createElement('tbody');
        tempTbody.innerHTML = templateHtml;
        
        while (tempTbody.firstChild) {
            proceduresTbody.appendChild(tempTbody.firstChild);
        }
        
        updateProcedureNumbers(activityRow);
        calculateActivityProceduresWeight(activityRow);
        calculateTotals();
    }


    function updateProcedureNumbers(activityRow) {
        const proceduresTbody = activityRow.closest('tr').nextElementSibling
            .querySelector('.procedures-tbody');
        
        proceduresTbody.querySelectorAll('.procedure-row').forEach((row, index) => {
            row.querySelector('.procedure-number').textContent = index + 1;
        });
    }

    function calculateActivityProceduresWeight(activityRow) {
        const proceduresTbody = activityRow.closest('tr').nextElementSibling
            .querySelector('.procedures-tbody');
        
        let totalWeight = 0;
        proceduresTbody.querySelectorAll('.procedure-row').forEach(row => {
            const weight = parseFloat(row.querySelector('[name*="[weight]"]').value) || 0;
            totalWeight += weight;
        });
        
        const weightTotalSpan = activityRow.nextElementSibling
            .querySelector('.activity-procedures-weight-total');
        
        if (weightTotalSpan) {
            weightTotalSpan.textContent = totalWeight.toFixed(2);
            
            // Update color based on weight
            if (Math.abs(totalWeight - 100) < 0.01) {
                weightTotalSpan.classList.remove('text-danger');
                weightTotalSpan.classList.add('text-success');
            } else {
                weightTotalSpan.classList.remove('text-success');
                weightTotalSpan.classList.add('text-danger');
            }
        }
    }

    function removeProcedure(button) {
        const procedureRow = button.closest('.procedure-row');
        const detailsRow = procedureRow.nextElementSibling;
        const activityRow = procedureRow.closest('.activity-details-row').previousElementSibling;
        
        // Check if procedure has costs
        const costsBadge = procedureRow.querySelector('.costs-count-badge');
        const costsCount = costsBadge ? parseInt(costsBadge.textContent) : 0;
        
        if (costsCount > 0) {
            Swal.fire({
                title: 'تأكيد الحذف',
                text: `هذا الإجراء يحتوي على ${costsCount} تكاليف. هل تريد حذفه مع جميع التكاليف؟`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (detailsRow && detailsRow.classList.contains('procedure-details-row')) {
                        detailsRow.remove();
                    }
                    procedureRow.remove();
                    updateProcedureNumbers(activityRow);
                    calculateActivityProceduresWeight(activityRow);
                    calculateTotals();
                }
            });
        } else {
            if (detailsRow && detailsRow.classList.contains('procedure-details-row')) {
                detailsRow.remove();
            }
            procedureRow.remove();
            updateProcedureNumbers(activityRow);
            calculateActivityProceduresWeight(activityRow);
            calculateTotals();
        }
    }

    function toggleCosts(button) {
        const procedureRow = button.closest('.procedure-row');
        const collapseBtn = button.classList.contains('procedure-collapse-btn') ? button : procedureRow.querySelector('.procedure-collapse-btn');
        const collapseIcon = collapseBtn.querySelector('i');
        const detailsRow = procedureRow.nextElementSibling;
        const collapseDiv = detailsRow.querySelector('.collapse');
        
        if (collapseDiv.classList.contains('show')) {
            collapseDiv.classList.remove('show');
            setTimeout(() => {
                if (!collapseDiv.classList.contains('show')) {
                    detailsRow.classList.add('d-none');
                }
            }, 300);
            collapseIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            collapseBtn.setAttribute('aria-expanded', 'false');
        } else {
            detailsRow.classList.remove('d-none');
            setTimeout(() => collapseDiv.classList.add('show'), 10);
            collapseIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            collapseBtn.setAttribute('aria-expanded', 'true');
        }
    }

    // Ensure aria-expanded reflects current DOM state on initial load
    document.addEventListener('DOMContentLoaded', function() {
        // Activities
        document.querySelectorAll('.activity-row').forEach(activityRow => {
            const detailsRow = activityRow.nextElementSibling;
            if (!detailsRow) return;
            const collapseDiv = detailsRow.querySelector('.collapse');
            const btn = activityRow.querySelector('.collapse-icon');
            if (btn && collapseDiv) {
                const expanded = collapseDiv.classList.contains('show') && !detailsRow.classList.contains('d-none');
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'fa-chevron-down');
                    icon.classList.add(expanded ? 'fa-chevron-up' : 'fa-chevron-down');
                }
            }
        });

        // Procedures
        document.querySelectorAll('.procedure-row').forEach(procRow => {
            const detailsRow = procRow.nextElementSibling;
            if (!detailsRow) return;
            const collapseDiv = detailsRow.querySelector('.collapse');
            const btn = procRow.querySelector('.procedure-collapse-btn');
            if (btn && collapseDiv) {
                const expanded = collapseDiv.classList.contains('show') && !detailsRow.classList.contains('d-none');
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-chevron-up', 'fa-chevron-down');
                    icon.classList.add(expanded ? 'fa-chevron-up' : 'fa-chevron-down');
                }
            }
        });
    });

    function addCostToProcedure(button) {
        const detailsRow = button.closest('.procedure-details-row');
        const procedureRow = detailsRow.previousElementSibling;
        
        // Find indices from data attributes
        const activityIndex = procedureRow.dataset.activityIndex;
        const procedureIndex = procedureRow.dataset.procedureIndex;

        const costsTbody = detailsRow.querySelector('.costs-tbody');
        
        const noCostsRow = costsTbody.querySelector('.no-costs-row');
        if (noCostsRow) {
            noCostsRow.remove();
        }
        
        const costIndex = costCounter++;
        
        // Get cost template
        let templateHtml = document.getElementById('cost-row-template').innerHTML;
        templateHtml = templateHtml.replace(/ACTIVITY_INDEX_PLACEHOLDER/g, activityIndex)
                                   .replace(/PROCEDURE_INDEX_PLACEHOLDER/g, procedureIndex)
                                   .replace(/COST_INDEX_PLACEHOLDER/g, costIndex);
        
        const tempTbody = document.createElement('tbody');
        tempTbody.innerHTML = templateHtml;
        
        while (tempTbody.firstChild) {
            costsTbody.appendChild(tempTbody.firstChild);
        }

        if (window.initializeApiUnitSelects) {
            window.initializeApiUnitSelects(costsTbody);
        }

        if (window.initExecutiveSelect2) {
            window.initExecutiveSelect2(costsTbody);
        }

        // ── تعبئة البنود المالية من ERPNext (إن كانت محمّلة مسبقاً) ──────────
        if (window.erpNextFinancialItems && window.erpNextFinancialItems.length > 0) {
            const newCostRow = costsTbody.lastElementChild;
            if (newCostRow) {
                const financialSelect = newCostRow.querySelector('.financial-item-select');
                if (financialSelect && !financialSelect.disabled) {
                    window.erpNextFinancialItems.forEach(function(item) {
                        const alreadyExists = Array.from(financialSelect.options)
                            .some(function(opt) { return opt.value === String(item.id); });
                        if (alreadyExists) return;

                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.text || item.name || item.id;
                        option.dataset.fromErpnext = 'true';
                        financialSelect.appendChild(option);
                    });
                    // حدّث Select2 إن كان مفعّلاً
                    if (typeof $ !== 'undefined' && $(financialSelect).data('select2')) {
                        $(financialSelect).trigger('change.select2');
                    }
                }
            }
        }
        
        updateCostNumbers(procedureRow);
        calculateProcedureTotal(procedureRow);
        calculateTotals();
    }

    function updateCostNumbers(procedureRow) {
        const costsTbody = procedureRow.nextElementSibling
            .querySelector('.costs-tbody');
        
        costsTbody.querySelectorAll('.cost-row').forEach((row, index) => {
            row.querySelector('.cost-number').textContent = index + 1;
        });
    }

    function calculateProcedureTotal(procedureRow) {
        const costsTbody = procedureRow.nextElementSibling
            .querySelector('.costs-tbody');
        
        let total = 0;
        costsTbody.querySelectorAll('.cost-row').forEach(row => {
            const amount = parseFloat(row.querySelector('[name*="[amount]"]').value) || 0;
            const quantity = parseFloat(row.querySelector('[name*="[quantity]"]').value) || 1;
            total += amount * quantity;
        });
        
        // Update procedure total display
        const procedureCostDisplay = procedureRow.querySelector('.procedure-cost-display');
        const procedureTotalSpan = procedureRow.nextElementSibling
            .querySelector('.procedure-total-cost');
        
        if (procedureCostDisplay) {
            procedureCostDisplay.textContent = total.toLocaleString('ar-SA');
        }
        
        if (procedureTotalSpan) {
            procedureTotalSpan.textContent = total.toLocaleString('ar-SA');
        }
        
        // Update costs count badge
        const costsCount = costsTbody.querySelectorAll('.cost-row').length;
        const costsBadge = procedureRow.querySelector('.costs-count-badge');
        if (costsBadge) {
            costsBadge.textContent = costsCount;
        }
    }

    // Unified Date Change Listener for Preliminary using HijriConverter
    document.addEventListener('change', function(e) {
        // 1. Flat preliminary procedures
        if (e.target.matches('.procedure-start-date, .procedure-end-date')) {
            const row = e.target.closest('.procedure-row');
            if (!row) return;

            const startVal = row.querySelector('.procedure-start-date')?.value;
            const endVal = row.querySelector('.procedure-end-date')?.value;
            const startHijriInput = row.querySelector('.procedure-start-date-hijri');
            const endHijriInput = row.querySelector('.procedure-end-date-hijri');
            const durationInput = row.querySelector('.procedure-duration');

            if (e.target.matches('.procedure-start-date') && startHijriInput && startVal) {
                const hijri = HijriConverter.gregorianToHijri(startVal);
                startHijriInput.value = HijriConverter.formatHijri(hijri);
                
                // Update min attribute of end date
                const endInput = row.querySelector('.procedure-end-date');
                if (endInput) endInput.setAttribute('min', startVal);
            } else if (e.target.matches('.procedure-end-date') && endHijriInput && endVal) {
                const hijri = HijriConverter.gregorianToHijri(endVal);
                endHijriInput.value = HijriConverter.formatHijri(hijri);
                
                // Update max attribute of start date
                const startInput = row.querySelector('.procedure-start-date');
                if (startInput) startInput.setAttribute('max', endVal);
            }

            // Always recalculate Duration if both exist, but don't touch dates
            if (durationInput && startVal && endVal) {
                // Validation: End date must not be before start date
                if (new Date(endVal) < new Date(startVal)) {
                    if (e.target.matches('.procedure-end-date')) {
                        e.target.value = '';
                        if (endHijriInput) endHijriInput.value = '';
                    } else {
                        row.querySelector('.procedure-end-date').value = '';
                        if (endHijriInput) endHijriInput.value = '';
                    }
                    durationInput.value = '';
                    return;
                }
                durationInput.value = HijriConverter.calculateDuration(startVal, endVal);
            }
        }

        // 2. Hierarchical preliminary procedures
        if (e.target.matches('.start-date, .end-date')) {
            const container = e.target.closest('.row'); 
            if (!container) return;

            const startVal = container.querySelector('.start-date')?.value;
            const endVal = container.querySelector('.end-date')?.value;
            const startHijriDisp = container.querySelector('.start-date-hijri');
            const startHijriHidden = container.querySelector('.start-date-hijri-input');
            const endHijriDisp = container.querySelector('.end-date-hijri');
            const endHijriHidden = container.querySelector('.end-date-hijri-input');
            const durationInput = container.querySelector('.duration-days');

            if (e.target.matches('.start-date') && startVal) {
                const hijri = HijriConverter.gregorianToHijri(startVal);
                const hijriStr = HijriConverter.formatHijri(hijri);
                if (startHijriDisp) startHijriDisp.value = hijriStr;
                if (startHijriHidden) startHijriHidden.value = hijriStr;
                
                // Update min attribute of end date
                const endInput = container.querySelector('.end-date');
                if (endInput) endInput.setAttribute('min', startVal);
            } else if (e.target.matches('.end-date') && endVal) {
                const hijri = HijriConverter.gregorianToHijri(endVal);
                const hijriStr = HijriConverter.formatHijri(hijri);
                if (endHijriDisp) endHijriDisp.value = hijriStr;
                if (endHijriHidden) endHijriHidden.value = hijriStr;
                
                // Update max attribute of start date
                const startInput = container.querySelector('.start-date');
                if (startInput) startInput.setAttribute('max', endVal);
            }

            if (durationInput && startVal && endVal) {
                // Validation: End date must not be before start date
                if (new Date(endVal) < new Date(startVal)) {
                    if (e.target.matches('.end-date')) {
                        e.target.value = '';
                        if (endHijriDisp) endHijriDisp.value = '';
                        if (endHijriHidden) endHijriHidden.value = '';
                    } else {
                        const endInput = container.querySelector('.end-date');
                        if (endInput) endInput.value = '';
                        if (endHijriDisp) endHijriDisp.value = '';
                        if (endHijriHidden) endHijriHidden.value = '';
                    }
                    durationInput.value = '';
                    return;
                }
                durationInput.value = HijriConverter.calculateDuration(startVal, endVal);
            }
        }
    });

    // Update weight calculation on input
    document.addEventListener('input', function(e) {
        // Procedure weights
        if (e.target.matches('[name*="procedures"][name*="[weight]"]')) {
            const procedureRow = e.target.closest('.procedure-row');
            if (procedureRow) {
                const activityRow = procedureRow.closest('.activity-details-row')?.previousElementSibling;
                if (activityRow) {
                    calculateActivityProceduresWeight(activityRow);
                }
                calculateTotals();
            }
        }
        
        // Activity weights
        if (e.target.matches('[name^="preliminary_activities"][name$="[weight]"]')) {
            calculateTotals();
        }
    });

    // Instant Date conversion for Hijri
    document.addEventListener('input', function(e) {
        if (e.target.matches('.procedure-start-date, .procedure-end-date, .start-date, .end-date')) {
            // Trigger change logic on input for instant update if date picker allows or manual entry
            e.target.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });



    function calculateTotals() {
        let totalWeight = 0;
        let activitiesCount = 0;
        let proceduresCount = 0;
        let costsCount = 0;
        let totalCost = 0;
        
        // Calculate from activities
        document.querySelectorAll('.activity-row').forEach(activityRow => {
            activitiesCount++;
            const weightInput = activityRow.querySelector('[name*="[weight]"]');
            const weight = weightInput ? (parseFloat(weightInput.value) || 0) : 0;
            totalWeight += weight;
            
            // Get the details row which contains procedures
            const activityDetailsRow = activityRow.nextElementSibling;
            if (activityDetailsRow && activityDetailsRow.classList.contains('activity-details-row')) {
                // Count procedures in this activity
                const procedureRows = activityDetailsRow.querySelectorAll('.procedure-row');
                proceduresCount += procedureRows.length;
                
                // Calculate costs in procedures
                procedureRows.forEach(procedureRow => {
                    let procedureTotalCost = 0;
                    let procedureCostsCount = 0;

                    const procedureDetailsRow = procedureRow.nextElementSibling;
                    if (procedureDetailsRow && procedureDetailsRow.classList.contains('procedure-details-row')) {
                        const costRows = procedureDetailsRow.querySelectorAll('.cost-row');
                        procedureCostsCount = costRows.length;
                        costsCount += procedureCostsCount;
                        
                        costRows.forEach(costRow => {
                            const amountInput = costRow.querySelector('input[name*="[amount]"]');
                            const quantityInput = costRow.querySelector('input[name*="[quantity]"]');
                            const amount = amountInput ? (parseFloat(amountInput.value) || 0) : 0;
                            const quantity = quantityInput ? (parseFloat(quantityInput.value) || 1) : 1;
                            procedureTotalCost += amount * quantity;
                        });
                        totalCost += procedureTotalCost;
                    }

                    // Update procedure indicators
                    const costsBadge = procedureRow.querySelector('.costs-count-badge');
                    if (costsBadge) {
                        costsBadge.textContent = procedureCostsCount;
                        costsBadge.style.display = procedureCostsCount > 0 ? 'inline' : 'none';
                    }
                    const procedureCostDisplay = procedureRow.querySelector('.procedure-cost-display');
                    if (procedureCostDisplay) {
                        procedureCostDisplay.textContent = procedureTotalCost.toLocaleString('ar-SA');
                    }
                });

                // Force weight update for this activity's procedures
                if (window.WeightCalculator && typeof window.WeightCalculator.updateProcedureWeight === 'function') {
                    window.WeightCalculator.updateProcedureWeight(activityRow.dataset.activityIndex);
                }
            }
        });

        // Force global weight update
        if (window.WeightCalculator && typeof window.WeightCalculator.updateActivityWeight === 'function') {
            window.WeightCalculator.updateActivityWeight();
        }
        
        // Update display
        const weightTotalElem = document.getElementById('weight-total');
        if (weightTotalElem) weightTotalElem.textContent = totalWeight.toFixed(2);
        
        const activitiesCountElem = document.getElementById('activities-count');
        if (activitiesCountElem) activitiesCountElem.textContent = activitiesCount;
        
        const proceduresCountElem = document.getElementById('procedures-count');
        if (proceduresCountElem) proceduresCountElem.textContent = proceduresCount;
        
        const costsCountElem = document.getElementById('costs-count');
        if (costsCountElem) costsCountElem.textContent = costsCount;
        
        const totalCostElem = document.getElementById('total-cost');
        if (totalCostElem) {
            totalCostElem.textContent = totalCost.toLocaleString('ar-SA');
            totalCostElem.dataset.rawValue = totalCost;
        }
        
        // Update weight badge color
        const weightBadge = document.getElementById('total-weight-display');
        if (weightBadge) {
            if (Math.abs(totalWeight - 100) < 0.01) {
                weightBadge.classList.remove('bg-warning');
                weightBadge.classList.add('bg-success');
            } else {
                weightBadge.classList.remove('bg-success');
                weightBadge.classList.add('bg-warning');
            }
        }
        
        // Update financial summary table
        if (typeof updateFinancialSummary === 'function') {
            updateFinancialSummary();
        }
    }

    function previewAllData() {
        // Collect all data and show in modal
        const activitiesData = [];
        
        document.querySelectorAll('.activity-row').forEach(activityRow => {
            const activity = {
                name: activityRow.querySelector('[name*="[name]"]').value,
                weight: activityRow.querySelector('[name*="[weight]"]').value,
                procedures: []
            };
            
            const activityDetailsRow = activityRow.nextElementSibling;
            if (activityDetailsRow && activityDetailsRow.classList.contains('activity-details-row')) {
                activityDetailsRow.querySelectorAll('.procedure-row').forEach(procedureRow => {
                    const procedure = {
                        name: procedureRow.querySelector('[name*="[procedure_name]"]').value,
                        weight: procedureRow.querySelector('[name*="[weight]"]').value,
                        costs: []
                    };
                    
                    const procedureDetailsRow = procedureRow.nextElementSibling;
                    if (procedureDetailsRow && procedureDetailsRow.classList.contains('procedure-details-row')) {
                        procedureDetailsRow.querySelectorAll('.cost-row').forEach(costRow => {
                            const cost = {
                                financial_item: costRow.querySelector('[name*="[financial_item_id]"] option:checked').text,
                                amount: costRow.querySelector('[name*="[amount]"]').value,
                                quantity: costRow.querySelector('[name*="[quantity]"]').value,
                                total: (parseFloat(costRow.querySelector('[name*="[amount]"]').value) || 0) * 
                                       (parseFloat(costRow.querySelector('[name*="[quantity]"]').value) || 1)
                            };
                            procedure.costs.push(cost);
                        });
                    }
                    
                    activity.procedures.push(procedure);
                });
            }
            
            activitiesData.push(activity);
        });
        
        // Show in modal
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        const previewContent = document.getElementById('preview-content');
        
        let html = `
            <div class="preview-container">
                <h4 class="mb-4">معاينة الأنشطة الأولية</h4>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    إجمالي الوزن: <strong>${document.getElementById('weight-total').textContent}%</strong> | 
                    إجمالي التكلفة: <strong>${document.getElementById('total-cost').textContent} ريال</strong>
                </div>
        `;
        
        activitiesData.forEach((activity, index) => {
            html += `
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">النشاط ${index + 1}: ${activity.name}</h5>
                        <small>الوزن: ${activity.weight}%</small>
                    </div>
                    <div class="card-body">
            `;
            
            if (activity.procedures.length > 0) {
                activity.procedures.forEach((procedure, pIndex) => {
                    html += `
                        <div class="mb-4">
                            <h6><i class="fas fa-list me-2"></i>الإجراء ${pIndex + 1}: ${procedure.name}</h6>
                            <small class="text-muted">الوزن: ${procedure.weight}%</small>
                            
                            <div class="table-responsive mt-2">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>البند المالي</th>
                                            <th>المبلغ</th>
                                            <th>الكمية</th>
                                            <th>الإجمالي</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    procedure.costs.forEach(cost => {
                        html += `
                            <tr>
                                <td>${cost.financial_item}</td>
                                <td>${cost.amount}</td>
                                <td>${cost.quantity}</td>
                                <td>${cost.total.toLocaleString('ar-SA')}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                });
            } else {
                html += `<p class="text-muted">لا توجد إجراءات مضافة</p>`;
            }
            
            html += `</div></div>`;
        });
        
        html += `</div>`;
        previewContent.innerHTML = html;
        modal.show();
    }

    // Auto-calculate on input changes
    document.addEventListener('input', function(e) {
        if (e.target.matches('[name*="[weight]"], [name*="[amount]"], [name*="[quantity]"]')) {
            // Auto-calculate cost total
            if (e.target.matches('[name*="[amount]"], [name*="[quantity]"]')) {
                const costRow = e.target.closest('.cost-row');
                if (costRow) {
                    calculateCostTotal(costRow);
                }
            }
            calculateTotals();
        }
    });

    function calculateCostTotal(costRow) {
        const amount = parseFloat(costRow.querySelector('[name*="[amount]"]').value) || 0;
        const quantity = parseFloat(costRow.querySelector('[name*="[quantity]"]').value) || 1;
        const total = amount * quantity;
        
        const totalSpan = costRow.querySelector('.cost-total-display');
        if (totalSpan) {
            totalSpan.textContent = total.toLocaleString('ar-SA');
        }
    }
</script>
@endpush