(function () {
    'use strict';

    let isInitialized = false;

    function initPreliminaryActivitiesManager() {
        if (isInitialized) return;
        isInitialized = true;

        if (typeof AppUtils === 'undefined') {
            console.error('AppUtils not found! Make sure app-utils.js is loaded.');
            return;
        }

        const PreliminaryActivitiesManager = {
            activityCounter: document.querySelectorAll('.activity-row').length,
            procedureCounters: {},
            costCounters: {},

            init: function () {
                this.bindEvents();
                this.initializeExistingActivities();
                console.log('✅ Preliminary Activities Manager Initialized');
            },

            bindEvents: function () {
                const addBtn = document.getElementById('add-activity-btn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => this.addActivity());
                }

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-activity')) {
                        this.removeActivity(e.target.closest('tr'));
                    }
                });

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.add-procedure-btn')) {
                        const btn = e.target.closest('.add-procedure-btn');
                        const activityIndex = btn.dataset.activityIndex;
                        this.addProcedure(activityIndex);
                    }
                });

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-procedure')) {
                        this.removeProcedure(e.target.closest('tr'));
                    }
                });

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.add-cost-btn')) {
                        const btn = e.target.closest('.add-cost-btn');
                        const activityIndex = btn.dataset.activityIndex;
                        const procedureIndex = btn.dataset.procedureIndex;
                        this.addCost(activityIndex, procedureIndex);
                    }
                });

                document.addEventListener('click', (e) => {
                    if (e.target.closest('.delete-cost-btn')) {
                        this.removeCost(e.target.closest('.project-cost-item'));
                    }
                });

                document.addEventListener('input', (e) => {
                    if (e.target.classList.contains('activity-name-input')) {
                        const activityRow = e.target.closest('.activity-row');
                        if (activityRow) {
                            const activityIndex = activityRow.dataset.activityIndex;
                            const activityName = e.target.value || 'بدون اسم';

                            const procedureRows = document.querySelectorAll(`.procedure-row[data-activity-index="${activityIndex}"]`);
                            procedureRows.forEach(row => {
                                const displays = row.querySelectorAll('.activity-name-display');
                                displays.forEach(display => {
                                    display.textContent = activityName;
                                });
                            });
                        }
                    }

                    if (e.target.classList.contains('procedure-name-input')) {
                        const procedureRow = e.target.closest('.procedure-row');
                        if (procedureRow) {
                            const procedureName = e.target.value || 'بدون اسم';

                            const costItems = procedureRow.querySelectorAll('.procedure-name-display');
                            costItems.forEach(item => {
                                item.textContent = procedureName;
                            });
                        }
                    }
                });

                document.addEventListener('input', (e) => {
                    if (e.target.classList.contains('amount-input') || e.target.classList.contains('quantity-input')) {
                        const costItem = e.target.closest('.project-cost-item');
                        if (costItem) {
                            this.calculateCostTotal(costItem);

                            const activityIndex = costItem.dataset.activityIndex;
                            const procedureIndex = costItem.dataset.procedureIndex;
                            this.updateProcedureTotalCost(activityIndex, procedureIndex);
                            this.updateActivityTotalCost(activityIndex);
                        }
                    }
                });
            },

            initializeExistingActivities: function () {
                const activityRows = document.querySelectorAll('.activity-row');
                activityRows.forEach(activityRow => {
                    const activityIndex = activityRow.dataset.activityIndex;

                    const procedureRows = document.querySelectorAll(`.procedure-row[data-activity-index="${activityIndex}"]`);
                    this.procedureCounters[activityIndex] = procedureRows.length;

                    procedureRows.forEach(procedureRow => {
                        const procedureIndex = procedureRow.dataset.procedureIndex;
                        if (!this.costCounters[activityIndex]) {
                            this.costCounters[activityIndex] = {};
                        }

                        const costItems = procedureRow.querySelectorAll('.project-cost-item');
                        this.costCounters[activityIndex][procedureIndex] = costItems.length;

                        costItems.forEach(costItem => {
                            this.calculateCostTotal(costItem);
                        });

                        this.updateProcedureTotalCost(activityIndex, procedureIndex);
                    });

                    this.updateActivityTotalCost(activityIndex);
                });
            },

            addActivity: function () {
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }

                const tbody = document.querySelector('#preliminaryActivitiesTable tbody');
                const row = this.createActivityRow();
                tbody.appendChild(row);

                AppUtils.Utils.animate(row, 'fadeIn');
                this.activityCounter++;
            },

            createActivityRow: function () {
                const row = document.createElement('tr');
                row.className = 'project-animated-row activity-row';
                row.dataset.activityIndex = this.activityCounter;

                row.innerHTML = `
                    <td>
                        <input type="text" 
                               name="preliminary_activities[${this.activityCounter}][name]"
                               class="form-control form-control-sm activity-name-input"
                               placeholder="أدخل اسم النشاط"
                               required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               name="preliminary_activities[${this.activityCounter}][weight]"
                               class="form-control form-control-sm activity-weight"
                               placeholder="0.00"
                               required>
                    </td>
                    <td>
                        <div class="text-success fw-bold activity-total-cost" data-activity-index="${this.activityCounter}">0.00</div>
                        <small class="text-muted">ر.س</small>
                    </td>
                    <td>
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-info add-procedure-btn"
                                    data-activity-index="${this.activityCounter}"
                                    title="إضافة إجراء">
                                <i class="fas fa-plus-circle"></i>إجراء
                            </button>
                            <button type="button" class="project-btn project-btn-danger remove-activity"
                                    title="حذف النشاط">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                return row;
            },

            addProcedure: function (activityIndex) {
                const tbody = document.querySelector('#preliminaryActivitiesTable tbody');

                if (!this.procedureCounters[activityIndex]) {
                    this.procedureCounters[activityIndex] = 0;
                }

                const procedureIndex = this.procedureCounters[activityIndex];
                const row = this.createProcedureRow(activityIndex, procedureIndex);

                const activityRow = document.querySelector(`.activity-row[data-activity-index="${activityIndex}"]`);
                let insertAfter = activityRow;

                const lastProcedureRow = tbody.querySelector(`.procedure-row[data-activity-index="${activityIndex}"]:last-of-type`);
                if (lastProcedureRow) {
                    insertAfter = lastProcedureRow;
                }

                insertAfter.insertAdjacentElement('afterend', row);
                AppUtils.Utils.animate(row, 'fadeIn');

                this.procedureCounters[activityIndex]++;
            },

            createProcedureRow: function (activityIndex, procedureIndex) {
                const row = document.createElement('tr');
                row.className = 'project-animated-row procedure-row';
                row.dataset.activityIndex = activityIndex;
                row.dataset.procedureIndex = procedureIndex;

                const activityRow = document.querySelector(`.activity-row[data-activity-index="${activityIndex}"]`);
                const activityName = activityRow ? activityRow.querySelector('input[name*="name"]').value || 'بدون اسم' : 'بدون اسم';

                row.innerHTML = `
                    <td colspan="4" class="p-0">
                        <div class="project-procedure-container bg-light p-3 border-start border-success border-3">
                            <h6 class="border-bottom pb-2 mb-3 text-success">
                                <i class="fas fa-arrow-down me-1"></i>الإجراء #<span class="procedure-number">${procedureIndex + 1}</span> للنشاط: <span class="activity-name-display">${activityName}</span>
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">اسم الإجراء <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control form-control-sm procedure-name-input"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][procedure_name]"
                                           placeholder="أدخل اسم الإجراء"
                                           required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">الوزن (%) <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control form-control-sm procedure-weight"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][weight]"
                                           value=""
                                           min="0" max="100" step="0.01"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">وسائل التحقق</label>
                                    <input type="text" 
                                           class="form-control form-control-sm"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][verification_means]">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">الإجراءات</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="project-btn project-btn-info add-cost-btn" 
                                                data-activity-index="${activityIndex}"
                                                data-procedure-index="${procedureIndex}"
                                                title="إضافة تكلفة">
                                            <i class="fas fa-plus-circle"></i>تكلفة
                                        </button>
                                        <button type="button" class="project-btn project-btn-danger remove-procedure">
                                            <i class="fas fa-trash-alt"></i>حذف
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">تاريخ البداية (ميلادي)</label>
                                    <input type="date"
                                           class="form-control form-control-sm start-date"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][start_date]">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">تاريخ النهاية (ميلادي)</label>
                                    <input type="date"
                                           class="form-control form-control-sm end-date"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][end_date]">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">المدة (أيام)</label>
                                    <input type="number"
                                           class="form-control form-control-sm duration-days"
                                           name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][duration_days]"
                                           value="0"
                                           min="0" readonly>
                                </div>
                            </div>

                            <div class="costs-section mt-3">
                                <h6 class="border-bottom pb-2 mb-3 text-info">
                                    <i class="fas fa-dollar-sign me-1"></i>تكاليف الإجراء: <span class="procedure-name-display">بدون اسم</span>
                                </h6>
                                
                                <div class="costs-list">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i><br>
                                        لا توجد تكاليف مضافة بعد
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 pt-3 border-top">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <small class="text-muted">إجمالي التكاليف:</small>
                                        <strong class="procedure-total-cost text-success ms-2" 
                                                data-activity-index="${activityIndex}"
                                                data-procedure-index="${procedureIndex}">0.00</strong>
                                        <span class="ms-1">ر.س</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                `;

                return row;
            },

            addCost: function (activityIndex, procedureIndex) {
                const procedureRow = document.querySelector(`.procedure-row[data-activity-index="${activityIndex}"][data-procedure-index="${procedureIndex}"]`);
                const costsList = procedureRow.querySelector('.costs-list');

                const emptyMsg = costsList.querySelector('.text-center');
                if (emptyMsg) {
                    emptyMsg.remove();
                }

                if (!this.costCounters[activityIndex]) {
                    this.costCounters[activityIndex] = {};
                }
                if (!this.costCounters[activityIndex][procedureIndex]) {
                    this.costCounters[activityIndex][procedureIndex] = 0;
                }

                const costIndex = this.costCounters[activityIndex][procedureIndex];
                const costItem = this.createCostItem(activityIndex, procedureIndex, costIndex);
                costsList.appendChild(costItem);
                AppUtils.Utils.animate(costItem, 'fadeIn');

                this.costCounters[activityIndex][procedureIndex]++;
            },

            createCostItem: function (activityIndex, procedureIndex, costIndex) {
                const item = document.createElement('div');
                item.className = 'project-cost-item bg-white p-3 mb-3 rounded border';
                item.dataset.activityIndex = activityIndex;
                item.dataset.procedureIndex = procedureIndex;
                item.setAttribute('data-cost-row', '');

                item.innerHTML = `
                    <div class="row align-items-end g-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">البند المالي <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm financial-item-select"
                                    name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][costs][${costIndex}][financial_item_id]"
                                    required>
                                <option value="">اختر البند المالي</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الوحدة <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm unit-select"
                                    name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][costs][${costIndex}][unit_id]"
                                    required>
                                <option value="">اختر الوحدة</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">المبلغ <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" 
                                       class="form-control amount-input"
                                       name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][costs][${costIndex}][amount]"
                                       value="0"
                                       min="0" step="0.01"
                                       required>
                                <span class="input-group-text">ر.س</span>
                            </div>
                        </div>
                        
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">الكمية <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control form-control-sm quantity-input"
                                   name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][costs][${costIndex}][quantity]"
                                   value="1"
                                   min="1" step="1"
                                   required>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الإجمالي</label>
                            <div class="input-group input-group-sm">
                                <input type="number" 
                                       class="form-control total-input bg-light"
                                       name="preliminary_activities[${activityIndex}][procedures][${procedureIndex}][costs][${costIndex}][total]"
                                       value="0"
                                       readonly
                                       tabindex="-1">
                                <span class="input-group-text">ر.س</span>
                            </div>
                        </div>
                        
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">&nbsp;</label>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm w-100 delete-cost-btn"
                                    title="حذف هذا البند">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;

                return item;
            },

            calculateCostTotal: function (costItem) {
                const amountInput = costItem.querySelector('.amount-input');
                const quantityInput = costItem.querySelector('.quantity-input');
                const totalInput = costItem.querySelector('.total-input');

                if (amountInput && quantityInput && totalInput) {
                    const amount = parseFloat(amountInput.value) || 0;
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const total = amount * quantity;
                    totalInput.value = total.toFixed(2);
                }
            },

            updateProcedureTotalCost: function (activityIndex, procedureIndex) {
                const procedureRow = document.querySelector(`.procedure-row[data-activity-index="${activityIndex}"][data-procedure-index="${procedureIndex}"]`);
                if (!procedureRow) return;

                let total = 0;
                const costItems = procedureRow.querySelectorAll('.project-cost-item');
                costItems.forEach(item => {
                    const totalInput = item.querySelector('.total-input');
                    if (totalInput) {
                        total += parseFloat(totalInput.value) || 0;
                    }
                });

                const totalDisplay = procedureRow.querySelector('.procedure-total-cost');
                if (totalDisplay) {
                    totalDisplay.textContent = total.toFixed(2);
                }
            },

            updateActivityTotalCost: function (activityIndex) {
                let total = 0;
                const procedureRows = document.querySelectorAll(`.procedure-row[data-activity-index="${activityIndex}"]`);
                procedureRows.forEach(procedureRow => {
                    const costItems = procedureRow.querySelectorAll('.project-cost-item');
                    costItems.forEach(item => {
                        const totalInput = item.querySelector('.total-input');
                        if (totalInput) {
                            total += parseFloat(totalInput.value) || 0;
                        }
                    });
                });

                const totalDisplay = document.querySelector(`.activity-total-cost[data-activity-index="${activityIndex}"]`);
                if (totalDisplay) {
                    totalDisplay.textContent = total.toFixed(2);
                }
            },

            removeActivity: function (row) {
                AppUtils.Table.removeRow(row).then((removed) => {
                    if (removed) {
                        this.checkEmptyTable();
                    }
                });
            },

            removeProcedure: function (row) {
                AppUtils.Table.removeRow(row).then((removed) => {
                    if (removed) {
                        const activityIndex = row.dataset.activityIndex;
                        this.updateActivityTotalCost(activityIndex);
                        this.checkEmptyTable();
                    }
                });
            },

            removeCost: function (costItem) {
                AppUtils.Utils.animate(costItem, 'fadeOut', () => {
                    costItem.remove();

                    const activityIndex = costItem.dataset.activityIndex;
                    const procedureIndex = costItem.dataset.procedureIndex;

                    const procedureRow = document.querySelector(`.procedure-row[data-activity-index="${activityIndex}"][data-procedure-index="${procedureIndex}"]`);
                    if (procedureRow) {
                        const costsList = procedureRow.querySelector('.costs-list');
                        const costItems = costsList.querySelectorAll('.project-cost-item');

                        if (costItems.length === 0) {
                            costsList.innerHTML = `
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-file-invoice-dollar fa-2x mb-2"></i><br>
                                    لا توجد تكاليف مضافة بعد
                                </div>
                            `;
                        }

                        this.updateProcedureTotalCost(activityIndex, procedureIndex);
                        this.updateActivityTotalCost(activityIndex);
                    }
                });
            },

            checkEmptyTable: function () {
                const tbody = document.querySelector('#preliminaryActivitiesTable tbody');
                const activityRows = tbody.querySelectorAll('.activity-row');

                if (activityRows.length === 0) {
                    tbody.innerHTML = `
                        <tr class="project-empty-row">
                            <td colspan="4" class="text-center">
                                <i class="fas fa-play-circle fa-2x mb-2"></i><br>
                                لا توجد أنشطة تمهيدية مضافة بعد
                            </td>
                        </tr>
                    `;
                }
            }
        };

        PreliminaryActivitiesManager.init();
        window.PreliminaryActivitiesManager = PreliminaryActivitiesManager;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPreliminaryActivitiesManager);
    } else {
        initPreliminaryActivitiesManager();
    }
})();
