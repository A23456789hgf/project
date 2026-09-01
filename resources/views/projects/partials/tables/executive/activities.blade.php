@php
    $isFinancialReview = (isset($projectStatus) && $projectStatus === 'financial_review');
    $reviewerType = $reviewerType ?? 'general';
    $canModifyStructure = !$isFinancialReview || $reviewerType === 'technical' || $reviewerType === 'general';
    $canModifyCosts = !$isFinancialReview || $reviewerType === 'financial' || $reviewerType === 'general';
@endphp
<div class="executive-activities-container" id="executive-activities-container">
    <!-- Header Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary-gradient text-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-tasks me-2"></i>الأنشطة التنفيذية للمشروع
                </h5>
                <small class="text-white-50">إدارة الأنشطة، الإجراءات، والمخاطر</small>
            </div>
            <div>
                <span class="badge bg-light text-primary fs-6" id="executive-total-weight-display">
                    الوزن الإجمالي: <span id="executive-weight-total">0</span>%
                </span>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Stats -->
            <div class="row mb-4 text-center">
                <div class="col-md-3">
                    <div class="stat-card bg-soft-primary">
                        <div class="stat-icon">
                            <i class="fas fa-tasks text-primary"></i>
                        </div>
                        <div class="stat-content">
                            <h4 id="executive-activities-count">0</h4>
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
                            <h4 id="executive-actions-count">0</h4>
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
                            <h4 id="executive-costs-count">0</h4>
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
                            <h4><span id="executive-total-cost">0</span> ريال</h4>
                            <p>إجمالي التكلفة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100 project-table" id="executive-activities-table">
                    <thead class="bg-primary-gradient text-white">
                        <tr>
                            <th class="w-5">#</th>
                            <th class="w-35 col-min-300">النشاط / الإجراءات</th>
                            <th class="w-15 text-center col-min-150">الوزن (%)</th>
                            <th class="w-20 col-min-200">المخرج</th>
                            <th class="w-20 col-min-200">المخاطر</th>
                            <th class="w-5 text-center">الخيارات</th>
                        </tr>
                    </thead>
                    <tbody id="executive-activities-tbody">
                        @if(isset($project) && $project->executiveActivities->count() > 0)
                            @foreach($project->executiveActivities as $activityIndex => $activity)
                                @include('projects.partials.tables.executive.activity-row', [
                                    'activityIndex' => $activityIndex,
                                    'activity' => $activity,
                                    'projectOutputs' => $project->resultOutputs,
                                    'projectRisks' => $project->risks
                                ])
                            @endforeach
                        @else
                            <tr id="no-executive-activities-row">
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">لا توجد أنشطة تنفيذية مضافة</h5>
                                        <p class="text-muted">قم بإضافة أول نشاط تنفيذي للمشروع</p>
                                        @if($canModifyStructure)
                                        <button type="button" class="btn btn-primary" onclick="addNewExecutiveActivity()">
                                            <i class="fas fa-plus me-1"></i>إضافة نشاط تنفيذي جديد
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($canModifyStructure)
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary btn-lg" onclick="addNewExecutiveActivity()">
                    <i class="fas fa-plus-circle me-2"></i>إضافة نشاط تنفيذي جديد
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .executive-activities-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .bg-primary-gradient {
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    }

    .stat-card {
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
        border: 1px solid transparent;
        margin-bottom: 1rem;
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

    /* Table Responsiveness & Layout Improvements */
    .executive-activities-container .table-responsive {
        border-radius: 8px;
        overflow-x: auto;
    }

    #executive-activities-table {
        min-width: 1000px;
    }

    .executive-actions-table-container .table-responsive {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-top: 10px;
    }

    .executive-actions-table-container table {
        min-width: 1200px; /* Force scroll for the dense actions table */
    }

    /* Cleaner input styling */
    .executive-activities-container .form-control-sm, 
    .executive-activities-container .form-select-sm {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 0.4rem 0.6rem;
    }

    .executive-activities-container .form-control:focus,
    .executive-activities-container .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    /* Date field grouping */
    .date-group-vertical {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .date-label-small {
        font-size: 10px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: -2px;
    }

    /* Badge & Count refinement */
    .assigned-count-badge, .costs-count-badge {
        font-size: 10px;
        padding: 4px 6px;
        border-radius: 20px;
    }

    .executive-activity-action-row .btn-group {
        white-space: nowrap;
    }

    /* Resize Weight and Duration fields */
    .action-weight-input {
        min-width: 80px !important;
    }

    .executive-duration {
        min-width: 70px !important;
        background-color: #f8fafc !important;
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

    .bg-soft-primary { background-color: rgba(79, 70, 229, 0.1); }
    .bg-soft-success { background-color: rgba(72, 187, 120, 0.1); }
    .bg-soft-warning { background-color: rgba(237, 137, 54, 0.1); }
    .bg-soft-info { background-color: rgba(66, 153, 225, 0.1); }

    .empty-state {
        padding: 40px;
        color: #6c757d;
    }
</style>

<!-- قالب النشاط التنفيذي -->
<template id="executive-activity-template">
    @include('projects.partials.tables.executive.activity-row', [
        'activityIndex' => '__INDEX__',
        'activity' => null,
        'projectOutputs' => isset($project) ? $project->resultOutputs : collect(),
        'projectRisks' => isset($project) ? $project->risks : collect()
    ])
</template>

<!-- قالب إجراء النشاط التنفيذي -->
<template id="executive-activity-action-template">
    @include('projects.partials.tables.executive.activity-action-row', [
        'activityIndex' => '__ACTIVITY_INDEX__',
        'actionIndex' => '__ACTION_INDEX__',
        'action' => null,
        'projectOutputs' => isset($project) ? $project->resultOutputs : collect(),
        'projectRisks' => isset($project) ? $project->risks : collect(),
        'financialItems' => $financialItems ?? [],
        'units' => $units ?? []
    ])
</template>

<!-- قالب المكلفين بالإجراء -->
<template id="executive-action-assigned-template">
    @include('projects.partials.tables.executive.action-assigned-row', [
        'activityIndex' => '__ACTIVITY_INDEX__',
        'actionIndex' => '__ACTION_INDEX__',
        'assignedIndex' => '__ASSIGNED_INDEX__',
        'assigned' => null
    ])
</template>

<!-- قالب تكاليف الإجراء -->
<template id="executive-action-cost-template">
    @include('projects.partials.tables.executive.action-cost-row', [
        'activityIndex' => '__ACTIVITY_INDEX__',
        'actionIndex' => '__ACTION_INDEX__',
        'costIndex' => '__COST_INDEX__',
        'cost' => null,
        'financialItems' => $financialItems ?? [],
        'units' => $units ?? []
    ])
</template>

@include('projects.partials.unit-fetch-script')

@push('scripts')
<script>
    let executiveActivityCounter = 0;
    let executiveActionCounter = 0;
    let executiveAssignedCounter = 0;
    let executiveCostCounter = 0;

    function initializeExecutiveCounters() {
        document.querySelectorAll('.executive-activity-row').forEach(row => {
            const idx = parseInt(row.dataset.activityIndex) || 0;
            if (idx >= executiveActivityCounter) executiveActivityCounter = idx + 1;
        });
        
        document.querySelectorAll('.executive-activity-action-row').forEach(row => {
            const idx = parseInt(row.dataset.actionIndex) || 0;
            if (idx >= executiveActionCounter) executiveActionCounter = idx + 1;
        });

        document.querySelectorAll('.executive-assigned-row').forEach(row => {
            const idx = parseInt(row.dataset.assignedIndex) || 0;
            if (idx >= executiveAssignedCounter) executiveAssignedCounter = idx + 1;
        });

        document.querySelectorAll('.executive-cost-row').forEach(row => {
            const idx = parseInt(row.dataset.costIndex) || 0;
            if (idx >= executiveCostCounter) executiveCostCounter = idx + 1;
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeExecutiveCounters();
        // Auto-weight on load
        document.querySelectorAll('.executive-activity-row').forEach(activityRow => {
            // autoWeightExecutiveActions(activityRow); // User wants manual entry only
        });
        // autoWeightExecutiveActivities(); // User wants manual entry only
        calculateExecutiveTotals();
    });

    /* --- Conslidated Scripts from Partials --- */

    if (typeof toggleExecutiveActions !== 'function') {
        function toggleExecutiveActions(button) {
            const row = button.closest('.executive-activity-row');
            const detailsRow = row.nextElementSibling;
            const collapseDiv = detailsRow.querySelector('.collapse');
            const icon = row.querySelector('.executive-collapse-icon i');
            
            if (collapseDiv.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapseDiv).hide();
                setTimeout(() => detailsRow.classList.add('d-none'), 350);
                icon.classList.remove('fa-square-minus');
                icon.classList.add('fa-square-plus');
            } else {
                detailsRow.classList.remove('d-none');
                bootstrap.Collapse.getOrCreateInstance(collapseDiv).show();
                icon.classList.remove('fa-square-plus');
                icon.classList.add('fa-square-minus');
            }
        }
    }

    function addNewExecutiveAction(button) {
        const activityRow = button.closest('.activity-details-row').previousElementSibling;
        const activityIndex = activityRow.dataset.activityIndex;
        const container = document.getElementById(`executive-actions-container-${activityIndex}`);
        const noRow = container.querySelector('.no-actions-row');
        if (noRow) noRow.remove();

        const actionIndex = executiveActionCounter++;
        const template = document.getElementById('executive-activity-action-template').innerHTML;
        const newHtml = template
            .replace(/__ACTIVITY_INDEX__/g, activityIndex)
            .replace(/__ACTION_INDEX__/g, actionIndex);

        // Template might contain multiple rows (action-row and action-details-row)
        const tempTbody = document.createElement('tbody');
        tempTbody.innerHTML = newHtml;
        
        while (tempTbody.firstChild) {
            container.appendChild(tempTbody.firstChild);
        }
        
        updateActionNumbers(container);
        // autoWeightExecutiveActions(activityRow); // User wants manual entry only
        calculateExecutiveTotals();
    }

    function removeExecutiveAction(button) {
        const row = button.closest('.executive-activity-action-row');
        const detailsRow = row.nextElementSibling;
        const container = row.parentElement;
        
        Swal.fire({
            title: 'تأكيد العملية',
            text: 'هل أنت متأكد من حذف هذا الإجراء؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'لا',
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                if (detailsRow && detailsRow.classList.contains('action-details-row')) {
                    detailsRow.remove();
                }
                const activityRow = container.closest('.activity-details-row').previousElementSibling;
                row.remove();
                updateActionNumbers(container);
                // autoWeightExecutiveActions(activityRow); // User wants manual entry only
                calculateExecutiveTotals();
                if (container.querySelectorAll('.executive-activity-action-row').length === 0) {
                    container.innerHTML = `
                        <tr class="no-actions-row">
                            <td colspan="8" class="text-center py-3 text-muted">
                                <i class="fas fa-inbox fa-lg me-2"></i>
                                لا توجد إجراءات مضافة
                            </td>
                        </tr>
                    `;
                }
            }
        });
    }

    function updateActionNumbers(container) {
        container.querySelectorAll('.executive-activity-action-row').forEach((row, index) => {
            const numSpan = row.querySelector('.action-number');
            if (numSpan) numSpan.textContent = index + 1;
        });
    }

    // New functions for toggling sub-sections
    function toggleActionAssignees(button) {
        const actionRow = button.closest('.executive-activity-action-row');
        const detailsRow = actionRow.nextElementSibling;
        const assigneesCollapse = detailsRow.querySelector('.action-assignees-collapse');
        const costsCollapse = detailsRow.querySelector('.action-costs-collapse');
        
        detailsRow.classList.remove('d-none');
        
        // Hide costs if shown
        if (costsCollapse.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(costsCollapse).hide();
        }
        
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(assigneesCollapse);
        bsCollapse.toggle();
        
        // Hide details row if both are hidden after toggle
        assigneesCollapse.addEventListener('hidden.bs.collapse', function () {
            if (!costsCollapse.classList.contains('show')) {
                detailsRow.classList.add('d-none');
            }
        }, { once: true });
    }

    function toggleActionCosts(button) {
        const actionRow = button.closest('.executive-activity-action-row');
        const detailsRow = actionRow.nextElementSibling;
        const assigneesCollapse = detailsRow.querySelector('.action-assignees-collapse');
        const costsCollapse = detailsRow.querySelector('.action-costs-collapse');
        
        detailsRow.classList.remove('d-none');
        
        // Hide assignees if shown
        if (assigneesCollapse.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(assigneesCollapse).hide();
        }
        
        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(costsCollapse);
        bsCollapse.toggle();

        costsCollapse.addEventListener('hidden.bs.collapse', function () {
            if (!assigneesCollapse.classList.contains('show')) {
                detailsRow.classList.add('d-none');
            }
        }, { once: true });
    }

    /* --- End Consolidated Scripts --- */


    function addNewExecutiveActivity() {
        const tbody = document.getElementById('executive-activities-tbody');
        const template = document.getElementById('executive-activity-template').innerHTML;
        const activityIndex = executiveActivityCounter++;
        const newHtml = template.replace(/__INDEX__/g, activityIndex);
        
        const noRow = document.getElementById('no-executive-activities-row');
        if (noRow) noRow.remove();

        const tr = document.createElement('tr');
        tr.innerHTML = newHtml; // This is a bit problematic since template includes multiple <tr>
        // Actually, activity-row is usually a <tr> then a details <tr>
        
        const tempDiv = document.createElement('tbody');
        tempDiv.innerHTML = newHtml;
        
        while (tempDiv.firstChild) {
            tbody.appendChild(tempDiv.firstChild);
        }

        updateExecutiveNumbers();
        // autoWeightExecutiveActivities(); // User wants manual entry only
        calculateExecutiveTotals();

        // Refresh dropdowns to ensure new row has latest risks and outputs
        if (window.ProjectForm && typeof window.ProjectForm.refreshRisks === 'function') {
            window.ProjectForm.refreshRisks();
            window.ProjectForm.refreshOutputs();
        } else {
            if (typeof window.updateRiskDropdowns === 'function') window.updateRiskDropdowns();
            if (typeof window.updateOutputDropdowns === 'function') window.updateOutputDropdowns();
        }
    }

    function removeExecutiveActivity(button) {
        const row = button.closest('.executive-activity-row');
        const detailsRow = row.nextElementSibling;
        
        Swal.fire({
            title: 'تأكيد العملية',
            text: 'هل أنت متأكد من حذف هذا النشاط؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'لا',
            reverseButtons: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                if (detailsRow && detailsRow.classList.contains('activity-details-row')) {
                    detailsRow.remove();
                }
                row.remove();
                updateExecutiveNumbers();
                // autoWeightExecutiveActivities(); // User wants manual entry only
                calculateExecutiveTotals();
                checkExecutiveEmptyState();
            }
        });
    }

    function autoWeightExecutive() {
        const activityRows = document.querySelectorAll('.executive-activity-row');
        if (activityRows.length === 0) return;

        let totalProjectCost = 0;
        const activityData = [];
        let totalActionsInProject = 0;

        activityRows.forEach(actRow => {
            let activityCost = 0;
            const actions = [];
            const detailsRow = actRow.nextElementSibling;
            if (detailsRow && detailsRow.classList.contains('activity-details-row')) {
                detailsRow.querySelectorAll('.executive-activity-action-row').forEach(actionRow => {
                    totalActionsInProject++;
                    let actionCost = 0;
                    const actionDetails = actionRow.nextElementSibling;
                    if (actionDetails && actionDetails.classList.contains('action-details-row')) {
                        actionDetails.querySelectorAll('.executive-cost-row').forEach(costRow => {
                            const amount = parseFloat(costRow.querySelector('.executive-cost-amount')?.value) || 0;
                            const qty = parseFloat(costRow.querySelector('.executive-cost-quantity')?.value) || 1;
                            actionCost += amount * qty;
                        });
                    }
                    actions.push({ row: actionRow, cost: actionCost });
                    activityCost += actionCost;
                });
            }
            activityData.push({ row: actRow, cost: activityCost, actions: actions });
            totalProjectCost += activityCost;
        });

        let actSum = 0;
        activityData.forEach((data, index) => {
            const input = data.row.querySelector('.executive-activity-weight');
            if (input) {
                let val;
                if (index === activityData.length - 1) {
                    val = (100 - actSum).toFixed(2);
                } else {
                    if (totalProjectCost > 0) {
                        val = (data.cost / totalProjectCost * 100).toFixed(2);
                    } else if (totalActionsInProject > 0) {
                        val = (data.actions.length / totalActionsInProject * 100).toFixed(2);
                    } else {
                        val = (100 / activityData.length).toFixed(2);
                    }
                    actSum += parseFloat(val);
                }
                // Values are no longer automatically assigned to allow manual entry
                /*
                input.value = val;
                input.readOnly = true;
                */
            }

            let actionSum = 0;
            data.actions.forEach((aData, aIndex) => {
                const aInput = aData.row.querySelector('.action-weight-input');
                if (aInput) {
                    let aVal;
                    if (aIndex === data.actions.length - 1) {
                        aVal = (100 - actionSum).toFixed(2);
                    } else {
                        if (data.cost > 0) {
                            aVal = (aData.cost / data.cost * 100).toFixed(2);
                        } else {
                            aVal = (100 / data.actions.length).toFixed(2);
                        }
                        actionSum += parseFloat(aVal);
                    }
                    // Values are no longer automatically assigned to allow manual entry
                    /*
                    aInput.value = aVal;
                    aInput.readOnly = true;
                    */
                }
            });
        });
    }

    // Overwrite old functions
    function autoWeightExecutiveActivities() { autoWeightExecutive(); }
    function autoWeightExecutiveActions() { autoWeightExecutive(); }

    function updateExecutiveNumbers() {
        document.querySelectorAll('.executive-activity-row').forEach((row, index) => {
            const numSpan = row.querySelector('.activity-number');
            if (numSpan) numSpan.textContent = index + 1;
        });
    }

    function checkExecutiveEmptyState() {
        const tbody = document.getElementById('executive-activities-tbody');
        if (tbody.querySelectorAll('.executive-activity-row').length === 0) {
            tbody.innerHTML = `
                <tr id="no-executive-activities-row">
                    <td colspan="6" class="text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">لا توجد أنشطة تنفيذية مضافة</h5>
                            <p class="text-muted">قم بإضافة أول نشاط تنفيذي للمشروع</p>
                            <button type="button" class="btn btn-primary" onclick="addNewExecutiveActivity()">
                                <i class="fas fa-plus me-1"></i>إضافة نشاط تنفيذي جديد
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    function addNewActionAssignee(button) {
        const detailsRow = button.closest('.action-details-row');
        const container = detailsRow.querySelector('.action-assignees-container');
        const actionRow = detailsRow.previousElementSibling;
        const activityIndex = actionRow.dataset.activityIndex;
        const actionIndex = actionRow.dataset.actionIndex;
        
        const noMsg = container.querySelector('.no-assignees-msg');
        if (noMsg) noMsg.remove();

        const assignedIndex = executiveAssignedCounter++;
        const template = document.getElementById('executive-action-assigned-template').innerHTML;
        const html = template
            .replace(/__ACTIVITY_INDEX__/g, activityIndex)
            .replace(/__ACTION_INDEX__/g, actionIndex)
            .replace(/__ASSIGNED_INDEX__/g, assignedIndex);

        const div = document.createElement('div');
        div.innerHTML = html;
        container.appendChild(div.firstElementChild);
        
        // Update badge
        const badge = actionRow.querySelector('.assigned-count-badge');
        if (badge) badge.textContent = container.querySelectorAll('.executive-assigned-row').length;
    }

    function addNewActionCost(button) {
        const detailsRow = button.closest('.action-details-row');
        const container = detailsRow.querySelector('.action-costs-container');
        const actionRow = detailsRow.previousElementSibling;
        const activityIndex = actionRow.dataset.activityIndex;
        const actionIndex = actionRow.dataset.actionIndex;
        
        const noMsg = container.querySelector('.no-costs-msg');
        if (noMsg) noMsg.remove();

        const costIndex = executiveCostCounter++;
        const template = document.getElementById('executive-action-cost-template').innerHTML;
        const html = template
            .replace(/__ACTIVITY_INDEX__/g, activityIndex)
            .replace(/__ACTION_INDEX__/g, actionIndex)
            .replace(/__COST_INDEX__/g, costIndex);

        const div = document.createElement('div');
        div.innerHTML = html;
        const newRow = div.firstElementChild;
        container.appendChild(newRow);
        
        if (window.initializeApiUnitSelects) {
            window.initializeApiUnitSelects(newRow);
        }
        
        if (window.initExecutiveSelect2) {
            window.initExecutiveSelect2(newRow);
        }

        // Initialize calculation for the new row
        if (window.initExecutiveCostRowCalculation) {
            window.initExecutiveCostRowCalculation(newRow);
        }

        // ── تعبئة البنود المالية من ERPNext (إن كانت محمّلة مسبقاً) ──────────
        if (window.erpNextFinancialItems && window.erpNextFinancialItems.length > 0) {
            const financialSelect = newRow.querySelector('.financial-item-select');
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

        // Update badge
        const badge = actionRow.querySelector('.costs-count-badge');
        if (badge) badge.textContent = container.querySelectorAll('.executive-cost-row').length;
        
        calculateExecutiveTotals();
    }

    function calculateCostRowTotal(row) {
        const amountInput = row.querySelector('.executive-cost-amount');
        const quantityInput = row.querySelector('.executive-cost-quantity');
        const totalInput = row.querySelector('.executive-cost-total');
        
        if (amountInput && quantityInput && totalInput) {
            const amount = parseFloat(amountInput.value) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            const total = amount * quantity;
            totalInput.value = total.toFixed(2);
        }
    }

    document.addEventListener('input', function(e) {
        // Handle executive cost calculations
        if (e.target.matches('.executive-cost-amount, .executive-cost-quantity')) {
            const row = e.target.closest('.executive-cost-row');
            calculateCostRowTotal(row);
            calculateExecutiveTotals();
            if (typeof calculateExecutiveFinancialSummary === 'function') {
                clearTimeout(window.executiveSummaryTimer);
                window.executiveSummaryTimer = setTimeout(calculateExecutiveFinancialSummary, 500);
            }
        }
        
        // Handle other recalculations
        if (e.target.matches('.executive-activity-weight, .action-weight-input')) {
            calculateExecutiveTotals();
        }
    });
    
    // Also listen for changes (e.g., if value is pasted or changed by script)
    document.addEventListener('change', function(e) {
         if (e.target.matches('.executive-cost-amount, .executive-cost-quantity')) {
            const row = e.target.closest('.executive-cost-row');
            calculateCostRowTotal(row);
            calculateExecutiveTotals();
            if (typeof calculateExecutiveFinancialSummary === 'function') {
                 calculateExecutiveFinancialSummary();
            }
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-executive-assigned-btn')) {
            const row = e.target.closest('.executive-assigned-row');
            const container = row.parentElement;
            const actionRow = container.closest('.action-details-row').previousElementSibling;
            
            row.remove();
            
            const badge = actionRow.querySelector('.assigned-count-badge');
            const count = container.querySelectorAll('.executive-assigned-row').length;
            if (badge) badge.textContent = count;
            
            if (count === 0) {
                container.innerHTML = '<div class="text-center py-2 text-muted no-assignees-msg"><small>لا يوجد مكلفون لهذا الإجراء</small></div>';
            }
        }
        
        if (e.target.closest('.delete-executive-cost-btn')) {
            const row = e.target.closest('.executive-cost-row');
            const container = row.parentElement;
            const actionRow = container.closest('.action-details-row').previousElementSibling;
            
            row.remove();
            
            const badge = actionRow.querySelector('.costs-count-badge');
            const count = container.querySelectorAll('.executive-cost-row').length;
            if (badge) badge.textContent = count;
            
            if (count === 0) {
                container.innerHTML = '<div class="text-center py-2 text-muted no-costs-msg"><small>لا توجد تكاليف لهذا الإجراء</small></div>';
            }
            
            calculateExecutiveTotals();
            if (typeof calculateExecutiveFinancialSummary === 'function') {
                 calculateExecutiveFinancialSummary();
            }
        }
    });

    function calculateExecutiveTotals() {
        // autoWeightExecutive(); // Trigger weighting - disabled for manual entry
        
        let totalWeight = 0;
        let activitiesCount = 0;
        let actionsCount = 0;
        let costsCount = 0;
        let totalCost = 0;

        document.querySelectorAll('.executive-activity-row').forEach(row => {
            activitiesCount++;
            const weightInput = row.querySelector('.executive-activity-weight');
            if (weightInput) totalWeight += parseFloat(weightInput.value) || 0;

            const activityDetailsRow = row.nextElementSibling;
            if (activityDetailsRow && activityDetailsRow.classList.contains('activity-details-row')) {
                const actionRows = activityDetailsRow.querySelectorAll('.executive-activity-action-row');
                actionsCount += actionRows.length;

                actionRows.forEach(actionRow => {
                    const actionDetailsRow = actionRow.nextElementSibling;
                    if (actionDetailsRow && actionDetailsRow.classList.contains('action-details-row')) {
                        const costRows = actionDetailsRow.querySelectorAll('.executive-cost-row');
                        costsCount += costRows.length;
                        
                        let actionTotal = 0;
                        costRows.forEach(costRow => {
                            const amount = parseFloat(costRow.querySelector('.executive-cost-amount').value) || 0;
                            const quantity = parseFloat(costRow.querySelector('.executive-cost-quantity').value) || 0;
                            const lineTotal = amount * quantity;
                            actionTotal += lineTotal;
                            totalCost += lineTotal;
                        });
                        
                        const actionTotalDisplay = actionDetailsRow.querySelector('.action-total-cost-display');
                        if (actionTotalDisplay) actionTotalDisplay.textContent = actionTotal.toFixed(2);
                    }
                });
            }
        });

        document.getElementById('executive-weight-total').textContent = totalWeight.toFixed(2);
        document.getElementById('executive-activities-count').textContent = activitiesCount;
        document.getElementById('executive-actions-count').textContent = actionsCount;
        document.getElementById('executive-costs-count').textContent = costsCount;
        
        const totalCostElem = document.getElementById('executive-total-cost');
        if (totalCostElem) {
            totalCostElem.textContent = totalCost.toLocaleString('ar-SA');
            totalCostElem.dataset.rawValue = totalCost;
        }

        const weightDisplay = document.getElementById('executive-total-weight-display');
        const weightValue = document.getElementById('executive-weight-total');
        if (Math.abs(totalWeight - 100) < 0.01) {
            weightDisplay.classList.replace('bg-warning', 'bg-success');
            weightDisplay.classList.replace('bg-danger', 'bg-success');
            if (weightValue) weightValue.classList.remove('text-danger');
        } else {
            weightDisplay.classList.replace('bg-success', 'bg-danger');
            if (weightValue) weightValue.classList.add('text-danger');
        }
        
        // Update executive financial summary table
        if (typeof calculateExecutiveFinancialSummary === 'function') {
            calculateExecutiveFinancialSummary();
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.matches('.executive-activity-weight, .executive-cost-amount, .executive-cost-quantity, .action-weight-input')) {
            calculateExecutiveTotals();
        }
    });

    // Unified Date Change Listener for Executive using HijriConverter
    document.addEventListener('change', function(e) {
        const isGregorian = e.target.matches('.primitive-start-date, .executive-start-date, .executive-end-date');
        const isHijri = e.target.matches('.executive-start-date-hijri, .start-date-hijri, .executive-end-date-hijri, .end-date-hijri');
        
        if (!isGregorian && !isHijri) return;

        const container = e.target.closest('.executive-activity-action-row') || e.target.closest('.row');
        if (!container) return;

        const startGregInput = container.querySelector('.primitive-start-date') || container.querySelector('.executive-start-date');
        const endGregInput = container.querySelector('.executive-end-date');
        const startHijriDisp = container.querySelector('.executive-start-date-hijri') || container.querySelector('.start-date-hijri');
        const startHijriHidden = container.querySelector('.start-date-hijri-input');
        const endHijriDisp = container.querySelector('.executive-end-date-hijri') || container.querySelector('.end-date-hijri');
        const endHijriHidden = container.querySelector('.end-date-hijri-input');
        const durationInput = container.querySelector('.executive-duration') || container.querySelector('.duration-days');

        if (isGregorian) {
            const val = e.target.value;
            if (val) {
                const hijri = HijriConverter.gregorianToHijri(val);
                const hijriStr = HijriConverter.formatHijri(hijri);

                if (e.target.matches('.primitive-start-date, .executive-start-date')) {
                    if (startHijriDisp) startHijriDisp.value = hijriStr;
                    if (startHijriHidden) startHijriHidden.value = hijriStr;
                    
                    // Silent optimization: update min of end date
                    if (endGregInput) endGregInput.setAttribute('min', val);
                } else if (e.target.matches('.executive-end-date')) {
                    if (endHijriDisp) endHijriDisp.value = hijriStr;
                    if (endHijriHidden) endHijriHidden.value = hijriStr;
                    
                    // Silent optimization: update max of start date
                    if (startGregInput) startGregInput.setAttribute('max', val);
                }
            }
        } else if (isHijri) {
            const val = e.target.value;
            if (val && val.split('/').length === 3) {
                const greg = HijriConverter.hijriToGregorian(val);
                if (greg) {
                    const gregStr = `${greg.year}-${String(greg.month).padStart(2, '0')}-${String(greg.day).padStart(2, '0')}`;
                    if (e.target.matches('.executive-start-date-hijri, .start-date-hijri')) {
                        if (startGregInput) startGregInput.value = gregStr;
                        if (startHijriHidden) startHijriHidden.value = val;
                    } else if (e.target.matches('.executive-end-date-hijri, .end-date-hijri')) {
                        if (endGregInput) endGregInput.value = gregStr;
                        if (endHijriHidden) endHijriHidden.value = val;
                    }
                }
            }
        }

        // Recalculate Duration if both dates are present
        if (durationInput && startGregInput?.value && endGregInput?.value) {
            // Validation: End date must not be before start date
            if (new Date(endGregInput.value) < new Date(startGregInput.value)) {
                // Clear the end date field(s)
                if (endGregInput) endGregInput.value = '';
                if (endHijriDisp) endHijriDisp.value = '';
                if (endHijriHidden) endHijriHidden.value = '';
                
                durationInput.value = '';
                return;
            }
            durationInput.value = HijriConverter.calculateDuration(startGregInput.value, endGregInput.value);
        }
    });
</script>
@endpush