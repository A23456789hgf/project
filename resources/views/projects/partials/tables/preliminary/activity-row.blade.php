
    <tr class="activity-row bg-white" data-activity-index="{{ $activityIndex }}">
        <td>
            <span class="activity-number fw-bold text-primary"></span>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-light border-0 me-2 collapse-icon" 
                        onclick="toggleProcedures(this)"
                        aria-expanded="false">
                    <x-icon name="chevron-down" class="text-primary" />
                </button>
                <input type="hidden" name="preliminary_activities[{{ $activityIndex }}][id]" value="{{ $activity->id ?? '' }}">
                <textarea 
                       name="preliminary_activities[{{ $activityIndex }}][name]" 
                       class="form-control flex-grow-1 auto-expand" 
                       placeholder="أدخل اسم النشاط"
                       rows="1"
                       {{ !$canModifyStructure ? 'disabled' : '' }}>{{ old("preliminary_activities.$activityIndex.name", $activity->name ?? '') }}</textarea>
            </div>
        </td>
        <td class="text-center">
            <div class="input-group input-group-sm">
                <input type="number" 
                       name="preliminary_activities[{{ $activityIndex }}][weight]" 
                       class="form-control weight-input activity-weight-input text-center"
                       min="0" 
                       max="100" 
                       step="0.01"
                       placeholder="0.00"
                       value="{{ old("preliminary_activities.$activityIndex.weight", $activity->weight ?? '') }}">
                <span class="input-group-text">%</span>
            </div>
        </td>

        <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light border-0 text-primary" 
                        onclick="toggleProcedures(this)" title="عرض الإجراءات">
                    <x-icon name="clipboard-list" />
                </button>
                @if($canModifyStructure)
                <button type="button" class="btn btn-light border-0 text-danger" 
                        onclick="removeActivity(this)" title="حذف النشاط">
                    <i class="fas fa-trash-alt"></i>
                </button>
                @endif
            </div>
        </td>
    </tr>
    <!-- Procedures Table for this Activity -->
    <tr class="activity-details-row d-none">
        <td colspan="5" class="p-0 border-0">
            <div class="collapse">
                <div class="p-3 bg-light border-top">
                    @include('projects.partials.tables.preliminary.activity_procedures', [
                        'financialItems' => $financialItems ?? [],
                        'units' => $units ?? []
                    ])
                </div>
            </div>
        </td>
    </tr>