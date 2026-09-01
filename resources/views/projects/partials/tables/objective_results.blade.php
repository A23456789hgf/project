@if($objective->results && $objective->results->count() > 0)
    @foreach($objective->results as $rIdx => $result)
    @php $resultIndex = $globalResultIndex + $rIdx; @endphp
    <tr class="project-animated-row result-row" data-objective-index="{{ $index }}" data-result-index="{{ $resultIndex }}">
        <td colspan="5" class="p-1">
            <div class="project-result-container bg-light border border-success border-start border-3 rounded p-2 mb-1">
                
                <div class="d-flex justify-content-between border-bottom pb-1 mb-2">
                    <span class="fs-7 text-success fw-bold">
                        <i class="fas fa-level-down-alt fa-xs me-1"></i> نتيجة الهدف: 
                        <span class="objective-name-display text-dark">{{ $objective->objective ?? 'بدون اسم' }}</span>
                    </span>
                </div>

                <div class="row g-2 align-items-end mb-2">
                    <div class="col-md-4">
                        <label class="form-label mb-1 fs-8 text-muted fw-bold">اسم النتيجة</label>
                        <input type="text" name="objective_results[{{ $resultIndex }}][result_name]" 
                               class="form-control form-control-sm fs-7 py-1 result-name-input border-success-subtle" 
                               value="{{ $result->result_name }}" placeholder="أدخل اسم النتيجة">
                        <input type="hidden" name="objective_results[{{ $resultIndex }}][id]" value="{{ $result->id }}">
                        <input type="hidden" name="objective_results[{{ $resultIndex }}][special_objective_id]" value="{{ $objective->id ?? '' }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label mb-1 fs-8 text-muted fw-bold">القيمة المستهدفة</label>
                        <input type="number" step="0.01" name="objective_results[{{ $resultIndex }}][target_value]" 
                               class="form-control form-control-sm fs-7 py-1 border-success-subtle" 
                               value="{{ $result->target_value }}" placeholder="0.00">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label mb-1 fs-8 text-muted fw-bold">نوع المؤشر</label>
                        <select name="objective_results[{{ $resultIndex }}][indicator_type]" 
                                class="form-select form-select-sm fs-7 py-1 border-success-subtle select2">
                            <option value="">-- اختر --</option>
                            <option value="quantitative" {{ $result->indicator_type === 'quantitative' ? 'selected' : '' }}>كمي</option>
                            <option value="relative" {{ $result->indicator_type === 'relative' ? 'selected' : '' }}>نسبي</option>
                            <option value="qualitative" {{ $result->indicator_type === 'qualitative' ? 'selected' : '' }}>كيفي</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label mb-1 fs-8 text-muted fw-bold">وحدة المؤشر</label>
                        <input type="text" name="objective_results[{{ $resultIndex }}][indicator_unit]" 
                               class="form-control form-control-sm fs-7 py-1 border-success-subtle" 
                               value="{{ $result->indicator_unit }}" placeholder="الوحدة">
                    </div>
                    
                    <div class="col-md-2">
                        <div class="btn-group btn-group-sm w-100 gap-1">
                            <button type="button" class="btn btn-outline-info py-0 px-1 add-output-btn"
                                    data-objective-index="{{ $index }}"
                                    data-result-index="{{ $resultIndex }}"
                                    title="إضافة مخرج">
                                <i class="fas fa-plus fa-xs"></i> مخرج
                            </button>
                            <button type="button" class="btn btn-outline-danger py-0 px-1 remove-result" title="حذف النتيجة">
                                <i class="fas fa-trash fa-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="outputs-section ms-3 ps-2 mt-2 border-start border-info outputs-list-container">
                    <div class="fs-8 text-info fw-bold mb-1"><i class="fas fa-file-alt fa-xs me-1"></i> المخرجات المرتبطة</div>
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
// منع تكرار تنفيذ السكريبت عند تضمين الملف عدة مرات
if (!window._objectiveResultsScriptInitialized) {
    window._objectiveResultsScriptInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        // تحديث عرض اسم النتيجة واسم الهدف عند الكتابة
        document.addEventListener('input', (e) => {
            if (e.target.matches('input[name*="result_name"]')) {
                const input = e.target;
                const resultRow = input.closest('.result-row');
                if (resultRow) {
                    const resultName = input.value || 'بدون اسم';
                    resultRow.querySelectorAll('.result-name-display-outputs').forEach(display => {
                        display.textContent = resultName;
                    });
                }
            }
            
            if (e.target.matches('input[name*="special_objectives"][name*="objective"]')) {
                const objectiveInput = e.target;
                const objectiveRow = objectiveInput.closest('.objective-row');
                if (objectiveRow) {
                    const objectiveIndex = objectiveRow.dataset.objectiveIndex;
                    const objectiveName = objectiveInput.value || 'بدون اسم';
                    const table = objectiveRow.closest('table');
                    
                    if (table) {
                        table.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`).forEach(resultRow => {
                            const objectiveDisplay = resultRow.querySelector('.objective-name-display');
                            if (objectiveDisplay) {
                                objectiveDisplay.textContent = objectiveName;
                            }
                        });
                    }
                }
            }
        });

        // تهيئة البيانات المخفية قبل الإرسال
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                document.querySelectorAll('.objective-row').forEach(objRow => {
                    const objectiveIndex = objRow.dataset.objectiveIndex;
                    const objectiveInput = objRow.querySelector('input[name*="special_objectives"][name*="objective"]');
                    const objectiveName = objectiveInput ? objectiveInput.value : '';
                    
                    document.querySelectorAll(`.result-row[data-objective-index="${objectiveIndex}"]`).forEach(resRow => {
                        const resultIndex = resRow.dataset.resultIndex;
                        const resultInput = resRow.querySelector('input[name*="objective_results"][name*="result_name"]');
                        const resultName = resultInput ? resultInput.value : '';
                        
                        // إضافة حقل اسم الهدف إلى النتيجة
                        if (!resRow.querySelector(`input[name="objective_results[${resultIndex}][objective_name]"]`)) {
                            resRow.insertAdjacentHTML('beforeend', `<input type="hidden" name="objective_results[${resultIndex}][objective_name]" value="${objectiveName}">`);
                        }
                        
                        resRow.querySelectorAll('.project-output-item').forEach((outputItem, outputIdx) => {
                            const outputInput = outputItem.querySelector('input[name*="result_outputs"][name*="output"]');
                            const outputName = outputInput ? outputInput.value : '';
                            
                            // إضافة الحقول المخفية للمخرجات
                            if (!outputItem.querySelector(`input[name="result_outputs_data[${resultIndex}][${outputIdx}][objective_name]"]`)) {
                                outputItem.insertAdjacentHTML('beforeend', `
                                    <input type="hidden" name="result_outputs_data[${resultIndex}][${outputIdx}][objective_name]" value="${objectiveName}">
                                    <input type="hidden" name="result_outputs_data[${resultIndex}][${outputIdx}][result_name]" value="${resultName}">
                                    <input type="hidden" name="result_outputs_data[${resultIndex}][${outputIdx}][output_name]" value="${outputName}">
                                `);
                            }
                        });
                    });
                });
            });
        }
    });
}
</script>