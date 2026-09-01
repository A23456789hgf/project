<!-- LEVEL 1: Executive Activity Row -->
<tr class="activity-row level-1" data-activity-index="{{ $activityIndex }}" data-expanded="false">
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-link p-0 expand-btn" title="فتح"><i class="fas fa-chevron-left expand-icon"></i></button>
        <input type="hidden" name="executive_activities[{{ $activityIndex }}][id]" value="{{ $activity->id ?? '' }}">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm executive-activity-name-input"
               name="executive_activities[{{ $activityIndex }}][name]"
               value="{{ $activity->name ?? '' }}" placeholder="اسم النشاط التنفيذي">
    </td>
    <td>
        <div class="d-flex align-items-center gap-1">
            <input type="number" step="0.01" min="0" max="100"
                   class="form-control form-control-sm executive-activity-weight"
                   name="executive_activities[{{ $activityIndex }}][weight]"
                   value="{{ $activity->weight ?? 0 }}">
            <span class="text-muted small">%</span>
        </div>
    </td>
    <td>
        <select class="form-select form-select-sm"
                name="executive_activities[{{ $activityIndex }}][result_output_id]">
            <option value="">اختر المخرج</option>
            @foreach($projectOutputs ?? [] as $output)
                <option value="{{ $output->id }}" {{ ($activity->result_output_id ?? null) == $output->id ? 'selected' : '' }}>
                    {{ $output->output }}
                </option>
            @endforeach
        </select>
    </td>
    <td><div class="text-success fw-bold executive-activity-total-cost" data-activity-index="{{ $activityIndex }}">0.00 ر.س</div></td>
    <td>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-info add-executive-action-btn" 
                    data-activity-index="{{ $activityIndex }}" title="إضافة إجراء">
                <i class="fas fa-plus-circle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger delete-executive-activity-btn" 
                    data-activity-index="{{ $activityIndex }}" title="حذف النشاط">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

<!-- LEVEL 2: Activity Details Row -->
<tr class="activity-details-row level-2" data-activity-index="{{ $activityIndex }}" style="display: none;">
    <td colspan="6" class="p-0">
        <div class="ps-4 pe-3 py-3 bg-light border-start border-primary border-3">
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">المخاطر المرتبطة</label>
                    <select class="form-select form-select-sm project-risk-select"
                            name="executive_activities[{{ $activityIndex }}][project_risk_id]">
                        <option value="">اختر المخاطرة</option>
                        @foreach($projectRisks ?? [] as $risk)
                            <option value="{{ $risk->id }}" {{ ($activity->project_risk_id ?? null) == $risk->id ? 'selected' : '' }}>
                                {{ $risk->risk }} ({{ $risk->risk_rate }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </td>
</tr>

<!-- LEVEL 2: Actions for this activity -->
@if($activity->actions && $activity->actions->count() > 0)
    @foreach($activity->actions as $actionIndex => $action)
        @include('projects.partials.tables.executive.hierarchical-action', [
            'activityIndex' => $activityIndex,
            'actionIndex' => $actionIndex,
            'action' => $action
        ])
    @endforeach
@endif
