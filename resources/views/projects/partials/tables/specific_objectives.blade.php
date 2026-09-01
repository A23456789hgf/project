<div class="project-table-container">
    @php $globalResultIndex = 0;
    $globalOutputIndex = 0; @endphp
    <div class="project-table-header">
        <i class="fas fa-list-check"></i>الأهداف الخاصة والنتائج والمخرجات
    </div>

    <div class="project-table-wrapper">
        <table class="project-table" id="specificObjectivesTable">
            <thead>
                <tr>
                    <th>الهدف الخاص</th>
                    <th>
                        الوزن (%)
                        <div class="mt-2 small d-flex align-items-center gap-2">
                            <span>المجموع:</span>
                            <span id="totalWeightDisplay" class="badge bg-secondary">0.00%</span>
                            <span id="weightStatus" class="badge bg-warning" style="display: none;">غير موازن</span>
                        </div>
                    </th>
                    <th>قيمة المؤشر </th>
                    <th>وحدة القياس</th>
                    <th width="120">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($project) && $project->specialObjectives->count() > 0)
                    @foreach($project->specialObjectives as $index => $objective)
                                                <!-- Objective Row -->
                                                <tr class="project-animated-row objective-row" data-objective-index="{{ $index }}">
                                                    <td>
                                                        <input type="text" 
                                                               name="special_objectives[{{ $index }}][objective]"
                                                               class="form-control form-control-sm"
                                                               value="{{ $objective->objective }}">
                                                        <input type="hidden" name="special_objectives[{{ $index }}][id]" value="{{ $objective->id }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="special_objectives[{{ $index }}][objective_weight]"
                                                               class="form-control form-control-sm objective-weight"
                                                               value="{{ $objective->objective_weight }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01"
                                                               name="special_objectives[{{ $index }}][target_value]"
                                                               class="form-control form-control-sm"
                                                               value="{{ $objective->target_value }}">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                               name="special_objectives[{{ $index }}][measurement_unit]"
                                                               class="form-control form-control-sm"
                                                               value="{{ $objective->measurement_unit }}">
                                                    </td>
                                                    <td>
                                                        <div class="project-action-buttons">
                                                            <button type="button" class="project-btn project-btn-info add-result-btn"
                                                                    data-objective-index="{{ $index }}"
                                                                    title="إضافة نتيجة">
                                                                <i class="fas fa-plus-circle"></i>نتيجة
                                                            </button>
                                                            <button type="button" class="project-btn project-btn-danger remove-objective"
                                                                    title="حذف الهدف">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Results for this objective -->
                                                @include('projects.partials.tables.objective_results', [
                                                    'objective' => $objective,
                                                    'index' => $index,
                                                    'globalResultIndex' => $globalResultIndex,
                                                    'globalOutputIndex' => $globalOutputIndex
                                                ])
                        @php $globalResultIndex = $globalResultIndex + $objective->results->count(); @endphp
                        @php foreach ($objective->results as $r) {
                            $globalOutputIndex += $r->outputs->count();
                        } @endphp
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="5" class="text-center">
                            <i class="fas fa-list-check fa-2x mb-2"></i><br>
                            لا توجد أهداف خاصة مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addObjectiveBtn">
                <i class="fas fa-plus"></i>إضافة هدف خاص جديد
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof AppUtils === 'undefined') {
            console.error('AppUtils not found! Make sure app-utils.js is loaded.');
            return;
        }

        const SpecificObjectivesManager = {
            objectiveCounter: {{ isset($project) ? $project->specialObjectives->count() : 0 }},
            resultCounter: {{ isset($project) ? $project->objectiveResults->count() : 0 }},
            outputCounter: {{ isset($project) ? $project->resultOutputs->count() : 0 }},

            init: function() {
                console.log('🚀 SpecificObjectivesManager: Initializing...');
                
                // Ensure AppUtils is ready
                if (typeof AppUtils === 'undefined') {
                    console.warn('⚠️ AppUtils not ready, retrying SpecificObjectivesManager.init in 500ms...');
                    setTimeout(() => this.init(), 500);
                    return;
                }

                this.bindEvents();
                this.initializeExistingObjectives();
                this.updateWeightDisplay();
                console.log('✅ SpecificObjectivesManager: Initialization Complete');
            },

            bindEvents: function() {
                // Add objective button
                const addBtn = document.getElementById('addObjectiveBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => this.addObjective());
                }

                // Remove objective (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-objective')) {
                        this.removeObjective(e.target.closest('tr'));
                    }
                });

                // Add result button (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.add-result-btn')) {
                        const btn = e.target.closest('.add-result-btn');
                        const objectiveIndex = btn.dataset.objectiveIndex;
                        this.addResult(objectiveIndex);
                    }
                });

                // Update result/objective name display when input changes
                document.addEventListener('input', (e) => {
                    if (e.target.closest('.result-name-input')) {
                        const input = e.target.closest('.result-name-input');
                        const resultRow = input.closest('.result-row');
                        const resultName = input.value || 'بدون اسم';
                        const displays = resultRow.querySelectorAll('.result-name-display, .result-name-display-outputs');
                        displays.forEach(display => {
                            display.textContent = resultName;
                        });
                    }
                    
                    // Update objective name when objective input changes
                    if (e.target.matches('input[name*="special_objectives"][name*="objective"]')) {
                        const objectiveInput = e.target;
                        const objectiveRow = objectiveInput.closest('.objective-row');
                        const objectiveIndex = objectiveRow.dataset.objectiveIndex;
                        const objectiveName = objectiveInput.value || 'بدون اسم';
                        
                        // Update all result rows for this objective
                        const resultRows = document.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`);
                        resultRows.forEach(resultRow => {
                            const displays = resultRow.querySelectorAll('.objective-name-display, .objective-name-outputs');
                            displays.forEach(display => {
                                display.textContent = objectiveName;
                            });
                        });
                    }

                    // Update weight display when weight input changes
                    if (e.target.closest('.objective-weight')) {
                        this.updateWeightDisplay();
                    }
                });

                // Remove result (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-result')) {
                        this.removeResult(e.target.closest('tr'));
                    }
                });

                // Add output button (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.add-output-btn')) {
                        const btn = e.target.closest('.add-output-btn');
                        const objectiveIndex = btn.dataset.objectiveIndex;
                        const resultIndex = btn.dataset.resultIndex;
                        this.addOutput(objectiveIndex, resultIndex);
                    }
                });

                // Remove output (delegated event)
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-output-item')) {
                        this.removeOutput(e.target.closest('.project-output-item'));
                    }
                });
            },

            calculateTotalWeight: function() {
                let totalWeight = 0;
                document.querySelectorAll('input.objective-weight').forEach(input => {
                    totalWeight += parseFloat(input.value) || 0;
                });
                return parseFloat(totalWeight.toFixed(2));
            },

            updateWeightDisplay: function() {
                const totalWeight = this.calculateTotalWeight();
                const totalWeightDisplay = document.getElementById('totalWeightDisplay');
                const weightStatus = document.getElementById('weightStatus');

                if (totalWeightDisplay) {
                    totalWeightDisplay.textContent = totalWeight.toFixed(2) + '%';
                    totalWeightDisplay.className = 'badge bg-info';
                    if (weightStatus) {
                        weightStatus.style.display = 'none';
                    }
                }
            },

            initializeExistingObjectives: function() {
                const rows = document.querySelectorAll('.objective-row');
                console.log(`🔧 SpecificObjectivesManager: Initializing existing objectives (${rows.length} rows found)...`);
                
                rows.forEach(row => {
                    const objectiveIndex = row.dataset.objectiveIndex;
                    const resultRows = row.parentElement.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`);
                    const objectiveInput = row.querySelector('input[name*="[objective]"]');
                    const objectiveName = objectiveInput ? objectiveInput.value : 'بدون اسم';

                    resultRows.forEach(resultRow => {
                        const resultNameInput = resultRow.querySelector('.result-name-input');
                        const resultName = resultNameInput ? resultNameInput.value : 'بدون اسم';
                        
                        // Update displays
                        resultRow.querySelectorAll('.result-name-display, .result-name-display-outputs').forEach(d => d.textContent = resultName);
                        resultRow.querySelectorAll('.objective-name-display, .objective-name-outputs').forEach(d => d.textContent = objectiveName);

                        // Ensure Select2 is initialized correctly for this row
                        setTimeout(() => {
                            if (typeof window.initGlobalSelect2 === 'function') {
                                window.initGlobalSelect2(resultRow, true);
                            }

                            // Also initialize for each output in this result
                            resultRow.querySelectorAll('.project-output-item').forEach(outputItem => {
                                if (typeof window.initGlobalSelect2 === 'function') {
                                    window.initGlobalSelect2(outputItem, true);
                                }
                            });
                        }, 150);
                    });
                });
                console.log('✅ SpecificObjectivesManager: Existing objectives fully initialized');
            },

            addObjective: function() {
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) {
                    emptyRow.remove();
                }

                const tbody = document.querySelector('#specificObjectivesTable tbody');
                const row = this.createObjectiveRow();
                tbody.appendChild(row);

                AppUtils.Utils.animate(row, 'fadeIn');
                this.objectiveCounter++;
                this.updateWeightDisplay();
            },

            createObjectiveRow: function() {
                const row = document.createElement('tr');
                row.className = 'project-animated-row objective-row';
                row.dataset.objectiveIndex = this.objectiveCounter;

                row.innerHTML = `
                    <td>
                        <input type="text" 
                               name="special_objectives[${this.objectiveCounter}][objective]"
                               class="form-control form-control-sm"
                               placeholder="أدخل الهدف الخاص">
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" max="100"
                               name="special_objectives[${this.objectiveCounter}][objective_weight]"
                               class="form-control form-control-sm objective-weight"
                               placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" step="0.01"
                               name="special_objectives[${this.objectiveCounter}][target_value]"
                               class="form-control form-control-sm"
                               placeholder="القيمة">
                    </td>
                    <td>
                        <input type="text"
                               name="special_objectives[${this.objectiveCounter}][measurement_unit]"
                               class="form-control form-control-sm"
                               placeholder="الوحدة">
                    </td>
                    <td>
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-info add-result-btn"
                                    data-objective-index="${this.objectiveCounter}"
                                    title="إضافة نتيجة">
                                <i class="fas fa-plus-circle"></i>نتيجة
                            </button>
                            <button type="button" class="project-btn project-btn-danger remove-objective"
                                    title="حذف الهدف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                return row;
            },

            addResult: function(objectiveIndex) {
                const tbody = document.querySelector('#specificObjectivesTable tbody');
                const resultIndex = this.resultCounter;
                const row = this.createResultRow(objectiveIndex, resultIndex);
                
                // Insert after the objective row and any existing results for this objective
                const objectiveRow = document.querySelector(`.objective-row[data-objective-index="${objectiveIndex}"]`);
                let insertAfter = objectiveRow;
                
                const lastResultRow = tbody.querySelector(`.result-row[data-objective-index="${objectiveIndex}"]:last-of-type`);
                if (lastResultRow) {
                    insertAfter = lastResultRow;
                }
                
                insertAfter.insertAdjacentElement('afterend', row);
                
                // Initialize Select2 for the new result row
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2(row);
                }
                
                AppUtils.Utils.animate(row, 'fadeIn');
                
                this.resultCounter++;
            },

            createResultRow: function(objectiveIndex, resultIndex) {
                const row = document.createElement('tr');
                row.className = 'project-animated-row result-row';
                row.dataset.objectiveIndex = objectiveIndex;
                row.dataset.resultIndex = resultIndex;

                // Get objective name and ID from the objective row
                const objectiveRow = document.querySelector(`.objective-row[data-objective-index="${objectiveIndex}"]`);
                const objectiveName = objectiveRow ? objectiveRow.querySelector('input[name*="objective"]').value || 'بدون اسم' : 'بدون اسم';
                // Get actual database ID if it exists, otherwise use the index
                const objectiveIdInput = objectiveRow ? objectiveRow.querySelector('input[name*="[id]"]') : null;
                const specialObjectiveId = objectiveIdInput && objectiveIdInput.value ? objectiveIdInput.value : objectiveIndex;

                row.innerHTML = `
                    <td colspan="5" class="p-0">
                        <div class="project-result-container bg-light p-3 border-start border-success border-3">
                            <h6 class="border-bottom pb-2 mb-3 text-success">
                                <i class="fas fa-arrow-down me-1"></i>نتيجة الهدف<span class="objective-name-display">${objectiveName}</span>
                            </h6>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">اسم النتيجة</label>
                                    <input type="text" 
                                           name="objective_results[${resultIndex}][result_name]"
                                           class="form-control form-control-sm result-name-input"
                                           placeholder="أدخل اسم النتيجة">
                                    <input type="hidden" 
                                           name="objective_results[${resultIndex}][special_objective_id]"
                                           value="${specialObjectiveId}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">القيمة المستهدفة</label>
                                    <input type="number" step="0.01"
                                           name="objective_results[${resultIndex}][target_value]"
                                           class="form-control form-control-sm"
                                           placeholder="0.00">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">نوع المؤشر</label>
                                    <select name="objective_results[${resultIndex}][indicator_type]"
                                            class="form-select form-select-sm border-success-subtle select2">
                                        <option value="">-- اختر --</option>
                                        <option value="quantitative">كمي</option>
                                        <option value="relative">نسبي</option>
                                        <option value="qualitative">كيفي</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">وحدة المؤشر</label>
                                    <input type="text"
                                           name="objective_results[${resultIndex}][indicator_unit]"
                                           class="form-control form-control-sm"
                                           placeholder="الوحدة">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">الإجراءات</label>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="project-btn project-btn-info add-output-btn"
                                                data-objective-index="${objectiveIndex}"
                                                data-result-index="${resultIndex}">
                                            <i class="fas fa-plus-circle"></i>مخرج
                                        </button>
                                        <button type="button" class="project-btn project-btn-danger remove-result">
                                            <i class="fas fa-trash-alt"></i>حذف
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Outputs Section -->
                            <div class="outputs-section">
                                <h6 class="border-bottom pb-2 mb-3 text-info">
                                    <i class="fas fa-file-alt me-1"></i>مخرجات النتيجة <span class="result-name-display-outputs">بدون اسم</span> للهدف  <span class="objective-name-outputs">${objectiveName}</span>
                                </h6>
                                <div class="outputs-list">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                        لا توجد مخرجات مضافة بعد
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                `;

                return row;
            },

            removeObjective: function(row) {
                AppUtils.Table.removeRow(row).then((removed) => {
                    if (removed) {
                        this.checkEmptyTable();
                        this.updateWeightDisplay();
                    }
                });
            },

            removeResult: function(row) {
                AppUtils.Table.removeRow(row).then((removed) => {
                    if (removed) {
                        this.checkEmptyTable();
                    }
                });
            },

            addOutput: function(objectiveIndex, resultIndex) {
                const resultRow = document.querySelector(`.result-row[data-objective-index="${objectiveIndex}"][data-result-index="${resultIndex}"]`);
                const outputsList = resultRow.querySelector('.outputs-list');
                
                // Remove empty message if exists
                const emptyMsg = outputsList.querySelector('.text-center');
                if (emptyMsg) {
                    emptyMsg.remove();
                }

                const outputIndex = this.outputCounter;
                const outputItem = this.createOutputItem(objectiveIndex, resultIndex, outputIndex);
                outputsList.appendChild(outputItem);
                
                // Initialize Select2 for the new output item
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2(outputItem);
                }
                
                AppUtils.Utils.animate(outputItem, 'fadeIn');
                this.outputCounter++;
            },

            createOutputItem: function(objectiveIndex, resultIndex, outputIndex) {
                const item = document.createElement('div');
                item.className = 'project-output-item bg-white p-3 mb-3 rounded border';

                // Get actual database IDs if they exist
                const objectiveRow = document.querySelector(`.objective-row[data-objective-index="${objectiveIndex}"]`);
                const objectiveIdInput = objectiveRow ? objectiveRow.querySelector('input[name*="[id]"]') : null;
                const specialObjectiveId = objectiveIdInput && objectiveIdInput.value ? objectiveIdInput.value : objectiveIndex;

                const resultRow = document.querySelector(`.result-row[data-objective-index="${objectiveIndex}"][data-result-index="${resultIndex}"]`);
                const resultIdInput = resultRow ? resultRow.querySelector('input[name*="[id]"]') : null;
                const objectiveResultId = resultIdInput && resultIdInput.value ? resultIdInput.value : resultIndex;

                item.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">اسم المخرج</label>
                            <input type="text"
                                   name="result_outputs[${outputIndex}][output]"
                                   class="form-control form-control-sm"
                                   placeholder="أدخل اسم المخرج">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">القيمة المستهدفة</label>
                            <input type="number" step="0.01"
                                   name="result_outputs[${outputIndex}][target_value]"
                                   class="form-control form-control-sm"
                                   placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">نوع المؤشر</label>
                            <select name="result_outputs[${outputIndex}][indicator_type]"
                                    class="form-control form-control-sm select2">
                                <option value="">-- اختر --</option>
                                <option value="quantitative">كمي</option>
                                <option value="relative">نسبي</option>
                                <option value="qualitative">كيفي</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">وحدة المؤشر</label>
                            <input type="text"
                                   name="result_outputs[${outputIndex}][indicator_unit]"
                                   class="form-control form-control-sm"
                                   placeholder="الوحدة">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">الإجراءات</label>
                            <button type="button" class="project-btn project-btn-danger remove-output-item"
                                    title="حذف المخرج">
                                <i class="fas fa-trash-alt"></i>حذف المخرج
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="result_outputs[${outputIndex}][special_objective_id]" value="${specialObjectiveId}">
                    <input type="hidden" name="result_outputs[${outputIndex}][objective_result_id]" value="${objectiveResultId}">
                `;

                return item;
            },

            removeOutput: function(item) {
                AppUtils.Utils.animate(item, 'fadeOut', () => {
                    item.remove();
                    
                    // Check if outputs list is now empty
                    const outputsList = item.closest('.outputs-list');
                    if (outputsList && outputsList.children.length === 0) {
                        outputsList.innerHTML = `
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                لا توجد مخرجات مضافة بعد
                            </div>
                        `;
                    }
                });
            },

            checkEmptyTable: function() {
                const tbody = document.querySelector('#specificObjectivesTable tbody');
                const rows = tbody.querySelectorAll('.objective-row');

                if (rows.length === 0) {
                    tbody.innerHTML = `
                        <tr class="project-empty-row">
                            <td colspan="5" class="text-center">
                                <i class="fas fa-list-check fa-2x mb-2"></i><br>
                                لا توجد أهداف خاصة مضافة بعد
                            </td>
                        </tr>
                    `;
                }
            }
        };

        // Initialize Specific Objectives Manager
        SpecificObjectivesManager.init();
        window.SpecificObjectivesManager = SpecificObjectivesManager;

        // Synchronize outputs to activity dropdown
        window.updateOutputDropdowns = function() {
            if (window.FormManager && typeof window.FormManager.populateExecutiveActivityDropdowns === 'function') {
                window.FormManager.populateExecutiveActivityDropdowns();
                return;
            }

            const outputs = [];
            
            // Collect all outputs from the form (both existing and new)
            document.querySelectorAll('.project-output-item').forEach(outputItem => {
                const resultRow = outputItem.closest('.result-row');
                const objectiveNameDisplay = resultRow ? resultRow.querySelector('.objective-name-display') : null;
                const objectiveName = objectiveNameDisplay ? objectiveNameDisplay.textContent : 'بدون هدف';
                
                const resultNameInput = resultRow ? resultRow.querySelector('.result-name-input') : null;
                const resultName = (resultNameInput ? resultNameInput.value : null) || 'بدون نتيجة';
                
                const outputInput = outputItem.querySelector('input[name*="[output]"]');
                const outputValue = outputInput ? outputInput.value : '';

                if (outputValue) {
                    outputs.push({
                        text: `${resultName} / ${outputValue}`,
                        objective: objectiveName,
                        result: resultName,
                        output: outputValue
                    });
                }
            });

            // Update all output dropdowns
            document.querySelectorAll('select.project-output-select').forEach(select => {
                const currentValue = select.value;
                const existingOptions = Array.from(select.options).map(opt => opt.textContent.trim());

                // Add options grouped by objective if not already present
                outputs.forEach(output => {
                    if (!existingOptions.includes(output.text) && !existingOptions.includes(output.output)) {
                        const option = document.createElement('option');
                        option.value = output.output;
                        option.textContent = output.text;
                        select.appendChild(option);
                    }
                });

                // Restore selected value if it exists and wasn't matched
                if (currentValue && !select.value) {
                    for (let opt of select.options) {
                        if (opt.value === currentValue || opt.textContent.trim() === currentValue) {
                            opt.selected = true;
                            break;
                        }
                    }
                }
            });
        }

        // Update dropdowns when output is added
        const originalAddOutput = SpecificObjectivesManager.addOutput;
        SpecificObjectivesManager.addOutput = function(objectiveIndex, resultIndex) {
            originalAddOutput.call(this, objectiveIndex, resultIndex);
            setTimeout(updateOutputDropdowns, 100);
        };

        // Update dropdowns when output is removed
        const originalRemoveOutput = SpecificObjectivesManager.removeOutput;
        SpecificObjectivesManager.removeOutput = function(item) {
            originalRemoveOutput.call(this, item);
            setTimeout(updateOutputDropdowns, 100);
        };

        // Update dropdowns when output value changes
        document.addEventListener('input', (e) => {
            if (e.target.closest('.project-output-item input[name*="[output]"]')) {
                setTimeout(updateOutputDropdowns, 100);
            }
        });
    });
})();
</script>
