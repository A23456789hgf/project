<div class="modal fade" id="activityActionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="fas fa-bolt me-2"></i> إجراءات النشاط: <span id="displayActivityName"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="ActivityActionsManager.addAction()">
                            <i class="fas fa-plus me-1"></i> إضافة إجراء
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold small text-muted">إجمالي الأوزان:</span>
                        <span id="actionTotalWeightDisplay" class="fw-bold text-primary fs-5">0%</span>
                        <i id="actionWeightStatusIcon" class="fas fa-check-circle text-success d-none"></i>
                    </div>
                </div>
                <div class="p-3">
                    <table class="table-clean w-100">
                        <thead>
                            <tr>
                                <th>اسم الإجراء</th>
                                <th style="width: 100px;" class="text-center">الوزن %</th>
                                <th style="width: 150px;">البدء</th>
                                <th style="width: 150px;">الانتهاء</th>
                                <th style="width: 60px;" class="text-center">المدة</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="actionsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-light px-4 border rounded-pill" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success px-4 fw-bold rounded-pill" onclick="ActivityActionsManager.saveActions()">
                    <i class="fas fa-check me-1"></i> اعتماد الإجراءات
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- إدارة الإجراءات (Modal) ---
    window.ActivityActionsManager = {
        currentActivityIndex: null,
        actions: [],
        bsModal: null,

        init: function() {
            const el = document.getElementById('activityActionsModal');
            if (el) {
                this.bsModal = new bootstrap.Modal(el);
            }
        },

        open: function(activityIndex, activityName, existingActions) {
            this.currentActivityIndex = activityIndex;
            const displayEl = document.getElementById('displayActivityName');
            if (displayEl) displayEl.textContent = activityName || 'نشاط';
            
            // Deep copy existing actions
            this.actions = JSON.parse(JSON.stringify(existingActions || []));
            this.render();
            if (this.bsModal) this.bsModal.show();
        },

        render: function() {
            const container = document.getElementById('actionsTableBody');
            if (!container) return;
            container.innerHTML = '';
            
            this.actions.forEach((action, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <input type="text" class="form-control form-control-sm rounded-pill" 
                            value="${action.name || ''}" 
                            oninput="ActivityActionsManager.actions[${index}].name = this.value"
                            placeholder="اسم الإجراء..." required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-center fw-bold rounded-pill" 
                            value="${action.weight || ''}" step="0.01" min="0" max="100" 
                            oninput="ActivityActionsManager.actions[${index}].weight = parseFloat(this.value)||0; ActivityActionsManager.calculateTotal()"
                            placeholder="0">
                    </td>
                    <td>
                        <input type="date" class="form-control form-control-sm rounded-pill" 
                            value="${action.start_date_g || ''}" 
                            onchange="ActivityActionsManager.updateDate(${index}, 'start_date_g', this.value)">
                    </td>
                    <td>
                        <input type="date" class="form-control form-control-sm rounded-pill" 
                            value="${action.end_date_g || ''}" 
                            onchange="ActivityActionsManager.updateDate(${index}, 'end_date_g', this.value)">
                    </td>
                    <td class="text-center fw-bold text-primary">
                        <span class="badge bg-primary-subtle text-primary border rounded-pill px-2">${action.duration || 0}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="ActivityActionsManager.removeAction(${index})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                `;
                container.appendChild(tr);
            });
            this.calculateTotal();
        },

        updateDate: function(index, field, value) {
            this.actions[index][field] = value;
            this.calculateDuration(index);
        },

        calculateDuration: function(index) {
            const action = this.actions[index];
            if (action.start_date_g && action.end_date_g) {
                const start = new Date(action.start_date_g);
                const end = new Date(action.end_date_g);
                const diff = (end - start) / (1000 * 60 * 60 * 24) + 1;
                action.duration = diff > 0 ? Math.ceil(diff) : 0;
            } else {
                action.duration = 0;
            }
            this.render();
        },

        addAction: function() {
            this.actions.push({ name: '', weight: 0, start_date_g: '', end_date_g: '', duration: 0 });
            this.render();
        },

        removeAction: function(index) {
            this.actions.splice(index, 1);
            this.render();
        },

        calculateTotal: function() {
            const total = this.actions.reduce((sum, act) => sum + (parseFloat(act.weight) || 0), 0);
            const display = document.getElementById('actionTotalWeightDisplay');
            const icon = document.getElementById('actionWeightStatusIcon');
            if (!display) return;

            display.textContent = total.toFixed(1) + '%';
            
            const isComplete = Math.abs(total - 100) < 0.1;
            const isOver = total > 100.1;

            display.className = 'fw-bold fs-5';
            icon?.classList.add('d-none');

            if (isComplete) {
                display.classList.add('text-success');
                icon?.classList.remove('d-none');
            } else if (isOver) {
                display.classList.add('text-danger');
            } else {
                display.classList.add('text-warning');
            }
        },

        saveActions: function() {
            const total = this.actions.reduce((sum, act) => sum + (parseFloat(act.weight) || 0), 0);
            
            if (this.actions.length > 0 && Math.abs(total - 100) > 0.1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'يجب أن يكون مجموع أوزان الإجراءات 100% (المجموع الحالي: ' + total.toFixed(1) + '%)',
                    confirmButtonText: 'حسناً'
                });
                return;
            }

            // Optional: Check for empty names
            if (this.actions.some(a => !a.name.trim())) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى إدخال أسماء لجميع الإجراءات', confirmButtonText: 'حسناً' });
                return;
            }

            if (window.ActivitiesManager) {
                window.ActivitiesManager.updateActivityActions(this.currentActivityIndex, this.actions);
            }
            if (this.bsModal) this.bsModal.hide();
        }
    };

    // --- التهيئة ---
    document.addEventListener('DOMContentLoaded', function() {
        ActivityActionsManager.init();
    });
</script>