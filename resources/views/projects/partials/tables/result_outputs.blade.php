<!-- C:\Users\Am\Desktop\pro\resources\views\projects\partials\tables\result_outputs.blade.php -->

<!-- صفوف المخرجات - صف منفصل لكل مخرج -->
<div class="outputs-section">
    <div class="outputs-list">
        @if($result->outputs && $result->outputs->count() > 0)
            @foreach($result->outputs as $oIdx => $output)
                @php $outputIndex = $globalOutputIndex + $oIdx; @endphp
                @if($loop->first)
                    <h6 class="border-bottom pb-2 mb-3 text-info">
                        <i class="fas fa-file-alt me-1"></i>مخرجات النتيجة <span
                            class="result-name-display-outputs">{{ $result->result_name ?? 'بدون اسم' }}</span> للهدف <span
                            class="objective-name-display">{{ isset($objective) ? ($objective->objective ?? 'بدون اسم') : '' }}</span>
                    </h6>
                @endif
                <div class="project-output-item bg-white p-3 mb-3 rounded border">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">اسم المخرج</label>
                            <input type="text" name="result_outputs[{{ $outputIndex }}][output]"
                                class="form-control form-control-sm" value="{{ $output->output }}">
                            <input type="hidden" name="result_outputs[{{ $outputIndex }}][id]" value="{{ $output->id }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">القيمة المستهدفة</label>
                            <input type="number" step="0.01" name="result_outputs[{{ $outputIndex }}][target_value]"
                                class="form-control form-control-sm" value="{{ $output->target_value }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">نوع المؤشر</label>
                            <select name="result_outputs[{{ $outputIndex }}][indicator_type]"
                                class="form-control form-control-sm select2">
                                <option value="">-- اختر --</option>
                                <option value="quantitative" {{ $output->indicator_type === 'quantitative' ? 'selected' : '' }}>
                                    كمي</option>
                                <option value="relative" {{ $output->indicator_type === 'relative' ? 'selected' : '' }}>نسبي
                                </option>
                                <option value="qualitative" {{ $output->indicator_type === 'qualitative' ? 'selected' : '' }}>كيفي
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">وحدة المؤشر</label>
                            <input type="text" name="result_outputs[{{ $outputIndex }}][indicator_unit]"
                                class="form-control form-control-sm" value="{{ $output->indicator_unit }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">الإجراءات</label>
                            <button type="button" class="project-btn project-btn-danger remove-output-item"
                                data-output-index="{{ $outputIndex }}">
                                <i class="fas fa-trash-alt"></i>حذف المخرج
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="result_outputs[{{ $outputIndex }}][special_objective_id]"
                        value="{{ $objective->id ?? $objectiveIndex }}">
                    <input type="hidden" name="result_outputs[{{ $outputIndex }}][objective_result_id]"
                        value="{{ $result->id ?? $resultIndex }}">
                </div>
            @endforeach
        @else
            <div class="text-center text-muted py-3">
                <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                لا توجد مخرجات مضافة بعد
            </div>
        @endif
    </div>
</div>