<!-- LEVEL 2: Action Row -->
<tr class="action-row level-2" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}" data-expanded="false" style="display: none;">
    <td class="text-center ps-4">
        <button type="button" class="btn btn-sm btn-link p-0 expand-btn" title="فتح"><i class="fas fa-chevron-left expand-icon"></i></button>
    </td>
    <td class="ps-4">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-info">الإجراء #{{ $actionIndex + 1 }}</span>
            <input type="text" class="form-control form-control-sm executive-action-name-input"
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][action_name]"
                   value="{{ $action->action_name ?? '' }}" placeholder="اسم الإجراء">
            <input type="hidden" name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][id]" value="{{ $action->id ?? '' }}">
        </div>
    </td>
    <td>
        <div class="d-flex align-items-center gap-1">
            <input type="number" step="0.01" min="0" max="100"
                   class="form-control form-control-sm executive-action-weight"
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][weight]"
                   value="{{ $action->weight ?? 0 }}">
            <span class="text-muted small">%</span>
        </div>
    </td>
    <td></td>
    <td><div class="text-success fw-bold executive-action-total-cost" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}">0.00 ر.س</div></td>
    <td>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-warning add-executive-assigned-btn" 
                    data-activity-index="{{ $activityIndex }}" 
                    data-action-index="{{ $actionIndex }}" title="إضافة جهة">
                <i class="fas fa-user-plus"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-info add-executive-cost-btn" 
                    data-activity-index="{{ $activityIndex }}" 
                    data-action-index="{{ $actionIndex }}" title="إضافة تكلفة">
                <i class="fas fa-plus-circle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-executive-action-btn" 
                    data-activity-index="{{ $activityIndex }}" 
                    data-action-index="{{ $actionIndex }}" title="حذف الإجراء">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

<!-- LEVEL 3: Action Details Row (Dates) -->
<tr class="action-details-row level-3" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}" style="display: none;">
    <td colspan="6" class="p-0">
        <div class="ps-5 pe-3 py-3 bg-light border-start border-info border-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">تاريخ البداية (ميلادي)</label>
                    <input type="date" class="form-control form-control-sm executive-start-date"
                           name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][start_date]"
                           value="{{ $action->start_date ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">تاريخ البداية (هجري)</label>
                    <input type="text" class="form-control form-control-sm start-date-hijri" 
                           placeholder="يوم/شهر/سنة"
                           value="{{ $action->start_date_hijri ?? '' }}">
                    <input type="hidden" class="start-date-hijri-input" 
                           name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][start_date_hijri]" 
                           value="{{ $action->start_date_hijri ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">تاريخ النهاية (ميلادي)</label>
                    <input type="date" class="form-control form-control-sm executive-end-date"
                           name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][end_date]"
                           value="{{ $action->end_date ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">تاريخ النهاية (هجري)</label>
                    <input type="text" class="form-control form-control-sm end-date-hijri" 
                           placeholder="يوم/شهر/سنة"
                           value="{{ $action->end_date_hijri ?? '' }}">
                    <input type="hidden" class="end-date-hijri-input" 
                           name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][end_date_hijri]" 
                           value="{{ $action->end_date_hijri ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">المدة (أيام)</label>
                    <input type="number" class="form-control form-control-sm duration-days" readonly 
                           value="{{ $action->duration_days ?? 0 }}" min="0">
                </div>
            </div>
        </div>
    </td>
</tr>

<!-- LEVEL 3: Assigned Entities for this action -->
@if($action->assignedEntities && $action->assignedEntities->count() > 0)
    @foreach($action->assignedEntities as $assignedIndex => $assigned)
        <tr class="assigned-row level-3" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}" data-assigned-index="{{ $assignedIndex }}" style="display: none;">
            <td colspan="6" class="p-0">
                <div class="ps-5 pe-3 py-2 bg-white border-start border-warning border-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">الجهة المسؤولة </label>
                            <input type="text" class="form-control form-control-sm"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned][{{ $assignedIndex }}][entity_name]"
                                   value="{{ $assigned->entity_name ?? '' }}">
                            <input type="hidden" name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned][{{ $assignedIndex }}][id]" value="{{ $assigned->id ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">النوع</label>
                            <input type="text" class="form-control form-control-sm"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned][{{ $assignedIndex }}][entity_type]"
                                   value="{{ $assigned->entity_type ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">الملاحظات</label>
                            <input type="text" class="form-control form-control-sm"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned][{{ $assignedIndex }}][notes]"
                                   value="{{ $assigned->notes ?? '' }}" placeholder="ملاحظات">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold d-block opacity-0">حذف</label>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-assigned-btn w-100" title="حذف">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
@endif

<!-- LEVEL 4: Costs for this action -->
@if($action->costs && $action->costs->count() > 0)
    @foreach($action->costs as $costIndex => $cost)
        <tr class="cost-row level-4" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}" data-cost-index="{{ $costIndex }}" style="display: none;">
            <td colspan="6" class="p-0">
                <div class="ps-6 pe-3 py-2 bg-light border-start border-success border-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">البند المالي </label>
                            <select class="form-select form-select-sm financial-item-select"
                                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][financial_item_id]">
                                <option value="">اختر البند</option>
                                @if(isset($financialItems) && $financialItems->count() > 0)
                                    @foreach($financialItems as $item)
                                        <option value="{{ $item->id }}" {{ ($cost->financial_item_id ?? null) == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <input type="hidden" name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][id]" value="{{ $cost->id ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الوحدة </label>
                            <select class="form-select form-select-sm unit-select"
                                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][unit_id]">
                                <option value="">اختر الوحدة</option>
                                @if(isset($units) && $units->count() > 0)
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ ($cost->unit_id ?? null) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->unit_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">المبلغ (ر.س) </label>
                            <input type="number" class="form-control form-control-sm amount-input" step="0.01" min="0"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][amount]"
                                   value="{{ $cost->amount ?? 0 }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold">الكمية </label>
                            <input type="number" class="form-control form-control-sm quantity-input" min="1" step="1"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][quantity]"
                                   value="{{ $cost->quantity ?? 1 }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الإجمالي (ر.س)</label>
                            <input type="number" class="form-control form-control-sm total-input bg-light" readonly step="0.01"
                                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][total]"
                                   value="{{ $cost->total ?? 0 }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold d-block opacity-0">حذف</label>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-cost-btn w-100" title="حذف">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
@endif
