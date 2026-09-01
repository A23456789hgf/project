<!-- LEVEL 1: Activity Row -->
<tr class="activity-row level-1" data-activity-index="{{ $activityIndex }}" data-expanded="false">
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-link p-0 expand-btn" title="فتح"><i class="fas fa-chevron-left expand-icon"></i></button>
        <input type="hidden" name="preliminary_activities[{{ $activityIndex }}][id]" value="{{ $activity->id }}">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm activity-name-input"
               name="preliminary_activities[{{ $activityIndex }}][name]"
               value="{{ $activity->name }}">
    </td>
    <td>
        <div class="d-flex align-items-center gap-1">
            <input type="number" step="0.01" min="0" max="100"
                   class="form-control form-control-sm activity-weight"
                   name="preliminary_activities[{{ $activityIndex }}][weight]"
                   value="{{ $activity->weight }}">
            <span class="text-muted small">%</span>
        </div>
    </td>
    <td></td>
    <td><div class="text-success fw-bold activity-total-cost" data-activity-index="{{ $activityIndex }}">0.00 ريال</div></td>
    <td>
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-info add-procedure-btn" 
                    data-activity-index="{{ $activityIndex }}" title="إضافة إجراء">
                <i class="fas fa-plus-circle"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger remove-activity" 
                    title="حذف النشاط">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>

<!-- LEVEL 2: Procedures -->
@if($activity->procedures && $activity->procedures->count() > 0)
    @foreach($activity->procedures as $procedureIndex => $procedure)
        @include('projects.partials.tables.preliminary.hierarchical-procedure', [
            'activityIndex' => $activityIndex,
            'procedureIndex' => $procedureIndex,
            'procedure' => $procedure
        ])
    @endforeach
@endif
