<tr class="executive-activity-row bg-white" id="executive-activity-{{ $activityIndex }}" data-activity-index="{{ $activityIndex }}">
    <td class="text-center align-middle">
        <span class="activity-number fw-bold text-primary">{{ is_numeric($activityIndex) ? $activityIndex + 1 : '' }}</span>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-outline-primary me-2 executive-collapse-icon" 
                    onclick="toggleExecutiveActions(this)"
                    aria-expanded="false"
                    style="transition: transform 0.3s ease;">
                <i class="fas fa-square-plus"></i>
            </button>
            <input type="hidden" name="executive_activities[{{ $activityIndex }}][id]" value="{{ $activity->id ?? '' }}">
            <textarea
                   class="form-control flex-grow-1 auto-expand"
                   placeholder="اسم النشاط"
                   name="executive_activities[{{ $activityIndex }}][name]"
                   rows="1"
                   {{ !$canModifyStructure ? 'disabled' : '' }}>{{ old("executive_activities.$activityIndex.name", $activity->name ?? '') }}</textarea>
        </div>
    </td>
    <td>
        <div class="input-group input-group-sm">
            <input type="number"
                   class="form-control executive-activity-weight text-center"
                   placeholder="0.00"
                   name="executive_activities[{{ $activityIndex }}][weight]"
                   value="{{ old("executive_activities.$activityIndex.weight", $activity->weight ?? 0) }}"
                   step="0.01"
                   min="0"
                   max="100">
            <span class="input-group-text">%</span>
        </div>
    </td>
    <td>
        <select class="form-select form-select-sm project-output-select"
                name="executive_activities[{{ $activityIndex }}][result_output_id]"
                {{ !$canModifyStructure ? 'disabled' : '' }}>
            <option value="">-- المخرج --</option>
            @foreach($projectOutputs ?? [] as $output)
                <option value="{{ $output->id }}"
                    @selected(old("executive_activities.$activityIndex.result_output_id", $activity->result_output_id ?? null) == $output->id)>
                    {{ $output->output }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="form-select form-select-sm project-risk-select"
                name="executive_activities[{{ $activityIndex }}][project_risk_id]"
                {{ !$canModifyStructure ? 'disabled' : '' }}>
            <option value="">-- المخاطر --</option>
            @foreach ($projectRisks ?? [] as $risk)
                <option value="{{ $risk->id }}"
                    @selected(old("executive_activities.$activityIndex.project_risk_id", $activity->project_risk_id ?? null) == $risk->id)>
                    {{ Str::limit($risk->risk, 30) }} ({{ $risk->risk_rate }})
                </option>
            @endforeach
        </select>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" 
                    onclick="toggleExecutiveActions(this)" title="عرض الإجراءات">
                <i class="fas fa-list"></i>
            </button>
            @if($canModifyStructure)
            <button type="button"
                    class="btn btn-outline-danger"
                    onclick="removeExecutiveActivity(this)"
                    title="حذف النشاط">
                <i class="fas fa-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>

<!-- Details Row (Actions) -->
<tr class="activity-details-row d-none" id="activity-details-{{ $activityIndex }}">
    <td colspan="6" class="p-0 border-0">
        <div class="collapse">
            <div class="p-3 bg-light border-top">
                @include('projects.partials.tables.executive.activity_actions')
            </div>
        </div>
    </td>
</tr>


