<!-- LEVEL 2: Procedure Row -->
<tr class="procedure-row level-2" data-activity-index="{{ $activityIndex }}" data-procedure-index="{{ $procedureIndex }}" data-expanded="false" style="display: none;">
    <td class="text-center ps-4">
        <button type="button" class="btn btn-sm btn-link p-0 expand-btn" title="فتح"><i class="fas fa-chevron-left expand-icon"></i></button>
    </td>
    <td class="ps-4">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info">الإجراء #{{ $procedureIndex + 1 }}</span>
            <input type="text" class="form-control form-control-sm procedure-name-input"
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][procedure_name]"
                   value="{{ $procedure->procedure_name }}" placeholder="اسم الإجراء">
            <input type="hidden" name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][id]" value="{{ $procedure->id }}">
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center gap-1">
            <input type="number" step="0.01" min="0" max="100"
                   class="form-control form-control-sm procedure-weight"
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][weight]"
                   value="{{ $procedure->weight }}">
            <span class="text-muted small">%</span>
        </div>
    </td>
    <td>
        <input type="text" class="form-control form-control-sm"
               name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][verification_means]"
               value="{{ $procedure->verification_means }}" placeholder="وسائل التحقق">
    </td>
    <td><div class="text-success fw-bold procedure-total-cost" data-activity-index="{{ $activityIndex }}" data-procedure-index="{{ $procedureIndex }}">0.00 ريال</div></td>
    <td>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-info add-cost-btn" 
                    data-activity-index="{{ $activityIndex }}" 
                    data-procedure-index="{{ $procedureIndex }}" title="إضافة تكلفة">
                <i class="fas fa-plus-circle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger remove-procedure" 
                    title="حذف الإجراء">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

<!-- LEVEL 3: Procedure Details Row -->
<tr class="procedure-details-row level-3" data-activity-index="{{ $activityIndex }}" data-procedure-index="{{ $procedureIndex }}" style="display: none;">
    <td colspan="6" class="p-0">
        <div class="ps-5 pe-3 py-3 bg-light border-start border-info border-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">تاريخ البداية (ميلادي)</label>
                    <input type="date" class="form-control form-control-sm start-date"
                           name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][start_date]"
                           value="{{ $procedure->start_date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">تاريخ البداية (هجري)</label>
                    <input type="text" class="form-control form-control-sm start-date-hijri" readonly 
                           value="{{ $procedure->start_date_hijri ?? '' }}">
                    <input type="hidden" class="start-date-hijri-input" 
                           name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][start_date_hijri]" 
                           value="{{ $procedure->start_date_hijri ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">تاريخ النهاية (ميلادي)</label>
                    <input type="date" class="form-control form-control-sm end-date"
                           name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][end_date]"
                           value="{{ $procedure->end_date }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">تاريخ النهاية (هجري)</label>
                    <input type="text" class="form-control form-control-sm end-date-hijri" readonly 
                           value="{{ $procedure->end_date_hijri ?? '' }}">
                    <input type="hidden" class="end-date-hijri-input" 
                           name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][end_date_hijri]" 
                           value="{{ $procedure->end_date_hijri ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">المدة (أيام)</label>
                    <input type="number" class="form-control form-control-sm duration-days" readonly 
                           value="{{ $procedure->duration_days ?? 0 }}" min="0">
                </div>
            </div>
        </div>
    </td>
</tr>

<!-- LEVEL 4: Costs for this procedure -->
@if($procedure->costs && $procedure->costs->count() > 0)
    @foreach($procedure->costs as $costIndex => $cost)
        @include('projects.partials.tables.preliminary.hierarchical-cost', [
            'activityIndex' => $activityIndex,
            'procedureIndex' => $procedureIndex,
            'costIndex' => $costIndex,
            'cost' => $cost
        ])
    @endforeach
@endif
