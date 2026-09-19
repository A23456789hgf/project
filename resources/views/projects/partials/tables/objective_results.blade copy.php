<!-- C:\Users\Am\Desktop\pro\resources\views\projects\partials\tables\objective_results.blade.php -->

@if($objective->results && $objective->results->count() > 0)
    @foreach($objective->results as $rIdx => $result)
    @php $resultIndex = $globalResultIndex + $rIdx; @endphp
    <!-- صفوف النتائج - صف منفصل لكل نتيجة -->
    <tr class="project-animated-row result-row" data-objective-index="{{ $index }}" data-result-index="{{ $resultIndex }}">
        <td colspan="5" class="p-0">
            <div class="project-result-container bg-light p-3 border-start border-success border-3">
                <h6 class="border-bottom pb-2 mb-3 text-success">
                    <i class="fas fa-arrow-down me-1"></i>نتيجة للهدف <span class="objective-name-display">{{ $objective->objective ?? 'بدون اسم' }}</span>
                </h6>
                <div class="row mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">اسم النتيجة</label>
                        <input type="text" name="objective_results[{{ $resultIndex }}][result_name]" 
                               class="form-control form-control-sm result-name-input border-success-subtle" value="{{ $result->result_name }}" placeholder="أدخل اسم النتيجة" required>
                        <input type="hidden" name="objective_results[{{ $resultIndex }}][id]" value="{{ $result->id }}">
                        <input type="hidden" name="objective_results[{{ $resultIndex }}][special_objective_id]" value="{{ $objective->id ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">القيمة المستهدفة</label>
                        <input type="number" step="0.01" name="objective_results[{{ $resultIndex }}][target_value]" 
                               class="form-control form-control-sm border-success-subtle" value="{{ $result->target_value }}" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">نوع المؤشر</label>
                        <select name="objective_results[{{ $resultIndex }}][indicator_type]" class="form-select form-select-sm border-success-subtle select2" required>
                            <option value="">-- اختر --</option>
                            <option value="quantitative" {{ $result->indicator_type === 'quantitative' ? 'selected' : '' }}>كمي</option>
                            <option value="relative" {{ $result->indicator_type === 'relative' ? 'selected' : '' }}>نسبي</option>
                            <option value="qualitative" {{ $result->indicator_type === 'qualitative' ? 'selected' : '' }}>كيفي</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">وحدة المؤشر</label>
                        <input type="text" name="objective_results[{{ $resultIndex }}][indicator_unit]" 
                               class="form-control form-control-sm border-success-subtle" value="{{ $result->indicator_unit }}" placeholder="الوحدة" required>
                    </div>
                    <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                        <button type="button" class="project-btn project-btn-info add-output-btn"
                                data-objective-index="{{ $index }}"
                                data-result-index="{{ $resultIndex }}"
                                title="إضافة مخرج">
                            <i class="fas fa-plus-circle"></i> مخرج
                        </button>
                        <button type="button" class="project-btn project-btn-danger remove-result" title="حذف النتيجة">
                            <i class="fas fa-trash-alt"></i> حذف
                        </button>
                    </div>
                </div>
                
                <!-- تضمين جدول المخرجات -->
                <div class="outputs-list-container mt-3 px-3 py-2 bg-white rounded border">
                    @include('projects.partials.tables.result_outputs', [
                        'result' => $result,
                        'objective' => $objective,
                        'objectiveIndex' => $index,
                        'resultIndex' => $resultIndex,
                        'globalOutputIndex' => $globalOutputIndex
                    ])
@php $globalOutputIndex = $globalOutputIndex + $result->outputs->count(); @endphp
                </div>
            </div>
        </td>
    </tr>
    @endforeach
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize result name displays for existing results
    const resultRows = document.querySelectorAll('.result-row');
    resultRows.forEach(resultRow => {
        const resultNameInput = resultRow.querySelector('input[name*="result_name"]');
        if (resultNameInput) {
            const resultName = resultNameInput.value || 'بدون اسم';
            const resultNameDisplay = resultRow.querySelector('.result-name-display-outputs');
            if (resultNameDisplay) {
                resultNameDisplay.textContent = resultName;
            }
        }
    });

    // Update result name display when input changes in existing results
    document.addEventListener('input', (e) => {
        if (e.target.matches('input[name*="result_name"]')) {
            const input = e.target;
            const resultRow = input.closest('.result-row');
            if (resultRow) {
                const resultName = input.value || 'بدون اسم';
                const resultNameDisplay = resultRow.querySelector('.result-name-display-outputs');
                if (resultNameDisplay) {
                    resultNameDisplay.textContent = resultName;
                }
            }
        }
        
        // Update objective name in result header when objective input changes
        if (e.target.matches('input[name*="special_objectives"][name*="objective"]')) {
            const objectiveInput = e.target;
            const objectiveRow = objectiveInput.closest('.objective-row');
            if (objectiveRow) {
                const objectiveIndex = objectiveRow.dataset.objectiveIndex;
                const objectiveName = objectiveInput.value || 'بدون اسم';
                
                // Find all result rows for this objective
                const table = objectiveRow.closest('table');
                if (table) {
                    const resultRows = table.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`);
                    resultRows.forEach(resultRow => {
                        const objectiveDisplay = resultRow.querySelector('.objective-name-display');
                        if (objectiveDisplay) {
                            objectiveDisplay.textContent = objectiveName;
                        }
                    });
                }
            }
        }
    });

    // Add hidden inputs with objective and result names before form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Get all objective rows
            const objectiveRows = document.querySelectorAll('.objective-row');
            objectiveRows.forEach(objRow => {
                const objectiveIndex = objRow.dataset.objectiveIndex;
                const objectiveInput = objRow.querySelector('input[name*="special_objectives"][name*="objective"]');
                const objectiveName = objectiveInput ? objectiveInput.value : '';
                
                // Get all result rows for this objective
                const resultRows = document.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`);
                resultRows.forEach(resRow => {
                    const resultIndex = resRow.dataset.resultIndex;
                    const resultInput = resRow.querySelector('input[name*="objective_results"][name*="result_name"]');
                    const resultName = resultInput ? resultInput.value : '';
                    
                    // Add hidden input for objective name in result
                    if (!resRow.querySelector(`input[name="objective_results[${resultIndex}][objective_name]"]`)) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = `objective_results[${resultIndex}][objective_name]`;
                        hiddenInput.value = objectiveName;
                        resRow.closest('td').appendChild(hiddenInput);
                    }
                    
                    // Get all output items for this result
                    const outputItems = resRow.querySelectorAll('.project-output-item');
                    outputItems.forEach((outputItem, outputIdx) => {
                        const outputInput = outputItem.querySelector('input[name*="result_outputs"][name*="output"]');
                        const outputName = outputInput ? outputInput.value : '';
                        
                        // Create and add hidden inputs for this output
                        if (!outputItem.querySelector(`input[name="result_outputs_data[${resultIndex}][${outputIdx}][objective_name]"]`)) {
                            const hiddenObj = document.createElement('input');
                            hiddenObj.type = 'hidden';
                            hiddenObj.name = `result_outputs_data[${resultIndex}][${outputIdx}][objective_name]`;
                            hiddenObj.value = objectiveName;
                            outputItem.appendChild(hiddenObj);
                            
                            const hiddenRes = document.createElement('input');
                            hiddenRes.type = 'hidden';
                            hiddenRes.name = `result_outputs_data[${resultIndex}][${outputIdx}][result_name]`;
                            hiddenRes.value = resultName;
                            outputItem.appendChild(hiddenRes);
                            
                            const hiddenOut = document.createElement('input');
                            hiddenOut.type = 'hidden';
                            hiddenOut.name = `result_outputs_data[${resultIndex}][${outputIdx}][output_name]`;
                            hiddenOut.value = outputName;
                            outputItem.appendChild(hiddenOut);
                        }
                    });
                });
            });
        });
    }
});
</script>