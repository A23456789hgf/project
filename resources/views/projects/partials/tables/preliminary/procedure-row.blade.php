@php
    $entitiesList = collect();
    if (isset($project)) {
        if (method_exists($project, 'projectEntities') && $project->projectEntities) {
            $entitiesList = $project->projectEntities;
        } else {
            // Fallback for ProjectRequest or if relation not loaded
            $linkedNames = $project->getProjectLinkedEntities();
            $entitiesList = \App\Models\ProjectEntity::where('project_id', $project->id)
                ->whereIn('entity_name', $linkedNames)
                ->get();
            if ($entitiesList->isEmpty()) {
                $entitiesList = collect($linkedNames)->map(function($name) {
                    return (object)[
                        'id' => $name,
                        'entity_name' => $name
                    ];
                });
            }
        }
    }
@endphp

<tr class="procedure-row bg-white border-bottom shadow-sm" data-activity-index="{{ $activityIndex }}" data-procedure-index="{{ $procedureIndex }}">
    <td class="text-center">
        <span class="procedure-number fw-bold text-info">{{ is_numeric($procedureIndex) ? (int)$procedureIndex + 1 : '' }}</span>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-light border-0 me-2 procedure-collapse-btn"
                    onclick="toggleCosts(this)"
                    aria-expanded="false">
                <i class="fas fa-chevron-down text-info"></i>
            </button>
            <input type="hidden" name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][id]" value="{{ $procedure->id ?? '' }}">
            <textarea 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][procedure_name]" 
                   class="form-control form-control-sm flex-grow-1 auto-expand"
                   placeholder="اسم الإجراء"
                   rows="1"
                   {{ !$canModifyStructure ? 'disabled' : '' }}>{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.procedure_name", $procedure->procedure_name ?? '') }}</textarea>
            <span class="badge bg-soft-info text-info ms-2">
                <i class="fas fa-coins me-1"></i>
                <span class="costs-count-badge">{{ isset($procedure) && $procedure->costs ? $procedure->costs->count() : 0 }}</span>
            </span>
        </div>
    </td>
    <td class="text-center">
        <div class="input-group input-group-sm">
            <input type="number" 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][weight]" 
                   class="form-control text-center procedure-weight"
                   min="0" 
                   max="100" 
                   step="0.01" 
                   placeholder="0.00"
                   value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.weight", $procedure->weight ?? '') }}"
                   {{ !$canModifyStructure ? 'disabled' : '' }}>
            <span class="input-group-text">%</span>
        </div>
    </td>
    <td>
        <select name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][project_entities_id]" 
                class="form-select form-select-sm"
                {{ !$canModifyStructure ? 'disabled' : '' }}>
            <option value="">اختر الجهة المنفذة</option>
            @foreach($entitiesList as $entityItem)
                <option value="{{ $entityItem->id }}" 
                    {{ (old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.project_entities_id", $procedure->project_entities_id ?? '') == $entityItem->id) ? 'selected' : '' }}>
                    {{ $entityItem->entity_name }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <div class="mb-1">
            <input type="date" 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][start_date]" 
                   class="form-control form-control-sm procedure-start-date"
                   value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.start_date", (isset($procedure->start_date) && $procedure->start_date instanceof \Carbon\Carbon) ? $procedure->start_date->format('Y-m-d') : ($procedure->start_date ?? '')) }}"
                   {{ !$canModifyStructure ? 'disabled' : '' }}
                   title="التاريخ الميلادي">
        </div>
        <div>
            <input type="text" 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][start_date_hijri]" 
                   class="form-control form-control-sm procedure-start-date-hijri"
                   placeholder="هجري"
                   value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.start_date_hijri", $procedure->start_date_hijri ?? '') }}"
                   readonly
                   title="التاريخ الهجري">
        </div>
    </td>
    <td>
        <div class="mb-1">
            <input type="date" 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][end_date]" 
                   class="form-control form-control-sm procedure-end-date"
                   value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.end_date", (isset($procedure->end_date) && $procedure->end_date instanceof \Carbon\Carbon) ? $procedure->end_date->format('Y-m-d') : ($procedure->end_date ?? '')) }}"
                   {{ !$canModifyStructure ? 'disabled' : '' }}
                   title="التاريخ الميلادي">
        </div>
        <div>
            <input type="text" 
                   name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][end_date_hijri]" 
                   class="form-control form-control-sm procedure-end-date-hijri"
                   placeholder="هجري"
                   value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.end_date_hijri", $procedure->end_date_hijri ?? '') }}"
                   readonly
                   title="التاريخ الهجري">
        </div>
    </td>
    <td class="text-center">
        <input type="text" 
               name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][duration]" 
               class="form-control form-control-sm text-center procedure-duration" 
               placeholder="المدة"
               value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.duration", $procedure->duration ?? '') }}"
               readonly>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-xs" role="group">
            <button type="button" class="btn btn-light border-0 text-info btn-sm" 
                    onclick="toggleCosts(this)" title="إدارة التكاليف">
                <i class="fas fa-plus"></i>
            </button>
            @if($canModifyStructure)
            <button type="button" class="btn btn-light border-0 text-danger btn-sm" 
                    onclick="removeProcedure(this)" title="حذف الإجراء">
                <i class="fas fa-trash-alt"></i>
            </button>
            @endif
        </div>
    </td>
</tr>
<!-- Costs Table for this Procedure -->
<tr class="procedure-details-row d-none">
    <td colspan="8" class="p-0 border-0">
        <div class="collapse">
            <div class="p-3 bg-light border-start border-4 border-info">
                @include('projects.partials.tables.preliminary.procedure_costs', [
                    'procedure' => $procedure, 
                    'activityIndex' => $activityIndex, 
                    'procedureIndex' => $procedureIndex,
                    'financialItems' => $financialItems ?? [],
                    'units' => $units ?? []
                ])
            </div>
        </div>
    </td>
</tr>


