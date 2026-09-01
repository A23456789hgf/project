<style>
    /* ========================================================================= */
    /* التنسيقات العامة للشريط الجانبي (الأهداف)                                 */
    /* ========================================================================= */
    .goals-offcanvas {
        width: 800px !important;
        border-left: 6px solid #0284c7; /* أزرق */
        box-shadow: -20px 0 40px rgba(0, 0, 0, 0.15);
        background-color: #f5f7fb;
    }

    .goals-offcanvas .offcanvas-header {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        padding: 1.5rem 1.5rem;
        color: white;
        border-bottom: 4px solid #0284c7;
    }

    /* ========================================================================= */
    /* تنسيقات الجدول المتداخل (Master-Details)                                  */
    /* ========================================================================= */
    .goals-wrapper {
        padding: 1rem;
        font-family: 'Inter', sans-serif;
    }

    .goal-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .goal-card-header {
        background: linear-gradient(145deg, #f8fafc, #f1f5f9);
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .result-card {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        margin: 1rem;
        overflow: hidden;
    }

    .result-card-header {
        background: #e2e8f0;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #cbd5e1;
    }

    .output-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.75rem;
        margin: 0.5rem 1rem;
    }

    /* حقول الإدخال */
    .clean-input {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 0.4rem 0.75rem;
        font-size: 0.85rem;
        width: 100%;
        transition: all 0.2s;
    }

    .clean-input:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        outline: none;
    }

    .weight-input {
        width: 80px;
        text-align: center;
        font-weight: bold;
    }

    /* أزرار */
    .btn-icon-action {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 0.3rem 0.6rem;
        color: #64748b;
        transition: all 0.2s;
        font-size: 0.8rem;
    }
    
    .btn-icon-action:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    .btn-icon-danger:hover {
        background: #fee2e2;
        color: #ef4444;
        border-color: #fca5a5;
    }

    .btn-icon-primary:hover {
        background: #e0f2fe;
        color: #0284c7;
        border-color: #7dd3fc;
    }

    #emptyGoalsMsg {
        background: #ffffffdd;
        border-radius: 20px;
        padding: 3rem 2rem;
        border: 1px dashed #cbd5e1;
    }
</style>

<!-- بداية الشريط الجانبي للأهداف -->
<div class="offcanvas offcanvas-end goals-offcanvas shadow-lg" tabindex="-1" id="goalsOffcanvas" aria-labelledby="goalsOffcanvasLabel">

    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title fs-6 fw-bold text-info" id="goalsOffcanvasLabel">
                <i class="fas fa-bullseye me-2"></i> إدارة الأهداف الخاصة والنتائج والمخرجات
            </h5>
            <div class="small text-white-50 mt-1" style="font-size: 0.8rem;">
                المشروع: <span id="displayGoalsProjectName" class="text-white fw-bold ms-1">---</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body bg-light p-0">
        <!-- شريط علوي -->
        <div class="sticky-top bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm" style="z-index: 10;">
            <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" id="addGoalBtn">
                <i class="fas fa-plus me-1"></i> إضافة هدف خاص
            </button>

            <div class="d-flex align-items-center gap-2 border rounded-pill px-3 py-1 bg-white">
                <span class="small text-muted fw-bold">إجمالي الأوزان:</span>
                <span id="goalsTotalWeightDisplay" class="fw-bold fs-6">0%</span>
                <i id="goalsWeightStatusIcon" class="fas fa-circle-check text-success d-none"></i>
            </div>
        </div>

        <!-- محتوى الأهداف -->
        <div class="goals-wrapper" id="goalsContainer">
            <!-- سيتم إضافة الأهداف هنا عبر JavaScript -->
        </div>

        <!-- رسالة الحالة الفارغة -->
        <div id="emptyGoalsMsg" class="text-center text-muted m-4 d-none">
            <div class="mb-3 opacity-50">
                <i class="fas fa-bullseye fa-3x"></i>
            </div>
            <h6 class="fw-bold text-secondary">لا توجد أهداف مضافة</h6>
            <p class="small mb-0">ابدأ بإضافة الأهداف الخاصة، ثم النتائج، ثم المخرجات.</p>
        </div>
    </div>

    <div class="offcanvas-footer p-3 bg-white border-top d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light btn-sm px-4 border" data-bs-dismiss="offcanvas">إلغاء</button>
        <button type="button" class="btn btn-info btn-sm px-4 fw-bold shadow-sm text-white" id="saveGoalsBtn">
            <i class="fas fa-save me-1"></i> حفظ التغييرات
        </button>
    </div>
</div>

@push('scripts')
<script>
    window.GoalsManager = {
        currentProjectRow: null,
        goals: [],
        bsOffcanvas: null,

        init: function() {
            const el = document.getElementById('goalsOffcanvas');
            if (el) {
                this.bsOffcanvas = new bootstrap.Offcanvas(el);
            }
            document.getElementById('addGoalBtn')?.addEventListener('click', () => this.addGoal());
            document.getElementById('saveGoalsBtn')?.addEventListener('click', () => this.saveGoals());
        },

        open: function(projectRow) {
            this.currentProjectRow = projectRow;
            const nameInput = projectRow.querySelector('input[name*="[name]"]');
            const jsonInput = projectRow.querySelector('.project-goals-input');
            const projectName = nameInput ? nameInput.value : 'مشروع جديد';
            document.getElementById('displayGoalsProjectName').textContent = projectName;

            try {
                this.goals = jsonInput && jsonInput.value ? JSON.parse(jsonInput.value) : [];
            } catch (e) {
                this.goals = [];
            }

            this.render();
            if (this.bsOffcanvas) this.bsOffcanvas.show();
        },

        addGoal: function() {
            this.goals.push({
                specific_goal: '',
                weight: 0,
                indicator_value: '',
                unit_of_measurement: '',
                results_json: []
            });
            this.render();
        },

        addResult: function(goalIndex) {
            if(!this.goals[goalIndex].results_json) this.goals[goalIndex].results_json = [];
            this.goals[goalIndex].results_json.push({
                result_name: '',
                target_value: '',
                indicator_type: '',
                indicator_unit: '',
                outputs: []
            });
            this.render();
        },

        addOutput: function(goalIndex, resultIndex) {
            if(!this.goals[goalIndex].results_json[resultIndex].outputs) {
                this.goals[goalIndex].results_json[resultIndex].outputs = [];
            }
            this.goals[goalIndex].results_json[resultIndex].outputs.push({
                output_name: '',
                target_value: '',
                indicator_type: '',
                indicator_unit: ''
            });
            this.render();
        },

        removeGoal: function(goalIndex) {
            Swal.fire({
                title: 'تأكيد العملية',
                text: 'هل أنت متأكد من حذف هذا الهدف؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.goals.splice(goalIndex, 1);
                    this.render();
                }
            });
        },

        removeResult: function(goalIndex, resultIndex) {
            Swal.fire({
                title: 'تأكيد العملية',
                text: 'هل أنت متأكد من حذف هذه النتيجة؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم',
                cancelButtonText: 'لا',
                reverseButtons: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.goals[goalIndex].results_json.splice(resultIndex, 1);
                    this.render();
                }
            });
        },

        removeOutput: function(goalIndex, resultIndex, outputIndex) {
            this.goals[goalIndex].results_json[resultIndex].outputs.splice(outputIndex, 1);
            this.render();
        },

        render: function() {
            const container = document.getElementById('goalsContainer');
            const emptyState = document.getElementById('emptyGoalsMsg');
            if (!container) return;
            container.innerHTML = '';

            if (this.goals.length === 0) {
                emptyState?.classList.remove('d-none');
            } else {
                emptyState?.classList.add('d-none');
                
                this.goals.forEach((goal, gIndex) => {
                    const goalEl = document.createElement('div');
                    goalEl.className = 'goal-card';
                    goalEl.innerHTML = `
                        <div class="goal-card-header">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">الهدف الخاص</label>
                                    <input type="text" class="clean-input" value="${this.escapeHtml(goal.specific_goal)}" 
                                        onchange="GoalsManager.updateGoal(${gIndex}, 'specific_goal', this.value)" placeholder="أدخل الهدف الخاص">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">الوزن %</label>
                                    <input type="number" class="clean-input weight-input" value="${goal.weight}" 
                                        onchange="GoalsManager.updateGoal(${gIndex}, 'weight', this.value)" placeholder="0">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">قيمة المؤشر</label>
                                    <input type="number" class="clean-input" value="${goal.indicator_value}" 
                                        onchange="GoalsManager.updateGoal(${gIndex}, 'indicator_value', this.value)" placeholder="القيمة">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">وحدة القياس</label>
                                    <input type="text" class="clean-input" value="${this.escapeHtml(goal.unit_of_measurement)}" 
                                        onchange="GoalsManager.updateGoal(${gIndex}, 'unit_of_measurement', this.value)" placeholder="الوحدة">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn-icon-action btn-icon-danger mb-1" onclick="GoalsManager.removeGoal(${gIndex})" title="حذف الهدف">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fs-8" onclick="GoalsManager.addResult(${gIndex})">
                                    <i class="fas fa-plus fa-xs"></i> إضافة نتيجة
                                </button>
                            </div>
                        </div>
                        <div class="results-container">
                            ${this.renderResults(goal.results_json || [], gIndex)}
                        </div>
                    `;
                    container.appendChild(goalEl);
                });
            }
            this.calculateTotal();
        },

        renderResults: function(results, gIndex) {
            if(results.length === 0) return '';
            let html = '';
            results.forEach((result, rIndex) => {
                const indicatorTypeOpts = this.getIndicatorOptions(result.indicator_type);
                html += `
                    <div class="result-card border-success border-start border-3">
                        <div class="result-card-header d-flex align-items-center justify-content-between">
                            <span class="fs-8 fw-bold text-success"><i class="fas fa-level-down-alt me-1"></i> نتيجة</span>
                            <div class="btn-group btn-group-sm gap-1">
                                <button type="button" class="btn-icon-action btn-icon-primary py-0" onclick="GoalsManager.addOutput(${gIndex}, ${rIndex})" title="إضافة مخرج">
                                    <i class="fas fa-plus fa-xs"></i> مخرج
                                </button>
                                <button type="button" class="btn-icon-action btn-icon-danger py-0" onclick="GoalsManager.removeResult(${gIndex}, ${rIndex})" title="حذف النتيجة">
                                    <i class="fas fa-trash fa-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-2">
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-md-4">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">اسم النتيجة</label>
                                    <input type="text" class="clean-input" value="${this.escapeHtml(result.result_name)}"
                                        onchange="GoalsManager.updateResult(${gIndex}, ${rIndex}, 'result_name', this.value)" placeholder="اسم النتيجة">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">القيمة المستهدفة</label>
                                    <input type="number" class="clean-input" value="${result.target_value}"
                                        onchange="GoalsManager.updateResult(${gIndex}, ${rIndex}, 'target_value', this.value)" placeholder="0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">نوع المؤشر</label>
                                    <select class="clean-input" onchange="GoalsManager.updateResult(${gIndex}, ${rIndex}, 'indicator_type', this.value)">
                                        ${indicatorTypeOpts}
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-8 text-muted fw-bold mb-1">وحدة المؤشر</label>
                                    <input type="text" class="clean-input" value="${this.escapeHtml(result.indicator_unit)}"
                                        onchange="GoalsManager.updateResult(${gIndex}, ${rIndex}, 'indicator_unit', this.value)" placeholder="الوحدة">
                                </div>
                            </div>
                            <div class="outputs-container border-start border-info ms-2 ps-2">
                                <div class="fs-8 text-info fw-bold mb-1 mt-2"><i class="fas fa-file-alt me-1"></i> المخرجات</div>
                                ${this.renderOutputs(result.outputs || [], gIndex, rIndex)}
                            </div>
                        </div>
                    </div>
                `;
            });
            return html;
        },

        renderOutputs: function(outputs, gIndex, rIndex) {
            if(outputs.length === 0) return '<div class="text-muted fs-8 py-1">لا توجد مخرجات مضافة.</div>';
            let html = '';
            outputs.forEach((output, oIndex) => {
                const indicatorTypeOpts = this.getIndicatorOptions(output.indicator_type);
                html += `
                    <div class="output-item border-light shadow-sm">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <input type="text" class="clean-input fs-8" value="${this.escapeHtml(output.output_name)}"
                                    onchange="GoalsManager.updateOutput(${gIndex}, ${rIndex}, ${oIndex}, 'output_name', this.value)" placeholder="اسم المخرج">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="clean-input fs-8" value="${output.target_value}"
                                    onchange="GoalsManager.updateOutput(${gIndex}, ${rIndex}, ${oIndex}, 'target_value', this.value)" placeholder="القيمة">
                            </div>
                            <div class="col-md-3">
                                <select class="clean-input fs-8" onchange="GoalsManager.updateOutput(${gIndex}, ${rIndex}, ${oIndex}, 'indicator_type', this.value)">
                                    ${indicatorTypeOpts}
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="clean-input fs-8" value="${this.escapeHtml(output.indicator_unit)}"
                                    onchange="GoalsManager.updateOutput(${gIndex}, ${rIndex}, ${oIndex}, 'indicator_unit', this.value)" placeholder="الوحدة">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" class="btn-icon-action btn-icon-danger p-1" onclick="GoalsManager.removeOutput(${gIndex}, ${rIndex}, ${oIndex})" title="حذف">
                                    <i class="fas fa-times fa-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            return html;
        },

        getIndicatorOptions: function(selectedValue) {
            const options = [
                { val: '', text: '-- اختر --' },
                { val: 'quantitative', text: 'كمي' },
                { val: 'relative', text: 'نسبي' },
                { val: 'qualitative', text: 'كيفي' }
            ];
            return options.map(opt => `<option value="${opt.val}" ${selectedValue === opt.val ? 'selected' : ''}>${opt.text}</option>`).join('');
        },

        updateGoal: function(index, field, value) {
            if(field === 'weight') value = parseFloat(value) || 0;
            this.goals[index][field] = value;
            if(field === 'weight') this.calculateTotal();
        },

        updateResult: function(gIndex, rIndex, field, value) {
            this.goals[gIndex].results_json[rIndex][field] = value;
        },

        updateOutput: function(gIndex, rIndex, oIndex, field, value) {
            this.goals[gIndex].results_json[rIndex].outputs[oIndex][field] = value;
        },

        escapeHtml: function(text) {
            if(!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        calculateTotal: function() {
            const total = this.goals.reduce((sum, goal) => sum + (parseFloat(goal.weight) || 0), 0);
            const display = document.getElementById('goalsTotalWeightDisplay');
            const icon = document.getElementById('goalsWeightStatusIcon');
            if (!display) return;
            
            display.textContent = total.toFixed(1) + '%';
            icon?.classList.add('d-none');

            if (Math.abs(total - 100) < 0.1) {
                display.className = 'fw-bold fs-6 text-success';
                icon?.classList.remove('d-none');
            } else if (total > 100.1) {
                display.className = 'fw-bold fs-6 text-danger';
            } else {
                display.className = 'fw-bold fs-6 text-warning';
            }
        },

        saveGoals: function() {
            if (!this.currentProjectRow) return;

            const total = this.goals.reduce((sum, goal) => sum + (parseFloat(goal.weight) || 0), 0);
            
            if (this.goals.length > 0 && Math.abs(total - 100) > 0.1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'يجب أن يكون مجموع أوزان الأهداف 100% (المجموع الحالي: ' + total.toFixed(1) + '%)',
                    confirmButtonText: 'حسناً'
                });
                return;
            }

            const hiddenInput = this.currentProjectRow.querySelector('.project-goals-input');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(this.goals);
                hiddenInput.dispatchEvent(new Event('change'));
            }

            const countBadge = this.currentProjectRow.querySelector('.goal-count-badge');
            if (countBadge) {
                countBadge.textContent = this.goals.length;
            }

            // وميض تأكيد
            this.currentProjectRow.style.transition = 'background-color 0.5s';
            this.currentProjectRow.style.backgroundColor = '#e0f2fe';
            setTimeout(() => {
                this.currentProjectRow.style.backgroundColor = '';
            }, 1000);

            this.bsOffcanvas?.hide();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        GoalsManager.init();
    });
</script>
@endpush
