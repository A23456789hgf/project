<tr class="executive-activity-action-row bg-white border-bottom shadow-sm" id="action-row-{{ $activityIndex }}-{{ $actionIndex }}" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}">
    <td class="text-center align-middle">
        <span class="action-number fw-bold text-info">{{ (int)$actionIndex + 1 }}</span>
    </td>
    <td>
        <input type="hidden" name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][id]" value="{{ $action->id ?? '' }}">
        <textarea class="form-control form-control-sm auto-expand" 
                  rows="1"
                  placeholder="وصف الإجراء"
                  name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][action]" 
                  {{ !$canModifyStructure ? 'disabled' : '' }}>{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.action", $action->action ?? '') }}</textarea>
    </td>
    <td class="text-center align-middle">
        <div class="input-group input-group-sm">
            <input type="number" 
                   class="form-control form-control-sm text-center action-weight-input" 
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][weight]" 
                   value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.weight", $action->weight ?? 0) }}"
                   step="0.01" min="0" max="100">
            <span class="input-group-text">%</span>
        </div>
    </td>
    <td>
        <div class="date-group-vertical">
            <span class="date-label-small">ميلادي</span>
            <input type="date" 
                   class="form-control form-control-sm executive-start-date" 
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][start_date]" 
                   {{ !$canModifyStructure ? 'disabled' : '' }}
                   value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.start_date", (isset($action->start_date) && $action->start_date instanceof \Carbon\Carbon) ? $action->start_date->format('Y-m-d') : ($action->start_date ?? '')) }}">
            
            <span class="date-label-small">هجري</span>
            <input type="text" 
                   class="form-control form-control-sm executive-start-date-hijri" 
                   placeholder="يوم/شهر/سنة"
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][start_date_hijri]" 
                   value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.start_date_hijri", $action->start_date_hijri ?? '') }}"
                   readonly>
        </div>
    </td>
    <td>
        <div class="date-group-vertical">
            <span class="date-label-small">ميلادي</span>
            <input type="date" 
                   class="form-control form-control-sm executive-end-date" 
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][end_date]" 
                   {{ !$canModifyStructure ? 'disabled' : '' }}
                   value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.end_date", (isset($action->end_date) && $action->end_date instanceof \Carbon\Carbon) ? $action->end_date->format('Y-m-d') : ($action->end_date ?? '')) }}">
            
            <span class="date-label-small">هجري</span>
            <input type="text" 
                   class="form-control form-control-sm executive-end-date-hijri" 
                   placeholder="يوم/شهر/سنة"
                   name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][end_date_hijri]" 
                   value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.end_date_hijri", $action->end_date_hijri ?? '') }}"
                   readonly>
        </div>
    </td>
    <td class="text-center align-middle">
        <input type="text" 
               name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][duration]" 
               class="form-control form-control-sm text-center executive-duration" 
               placeholder="المدة"
               value="{{ old("executive_activities.$activityIndex.actions.$actionIndex.duration", $action->duration_days ?? '') }}"
               readonly>
    </td>
    <td>
        <textarea class="form-control form-control-sm auto-expand" 
                  rows="1"
                  placeholder="وسائل التحقق"
                  name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][verification_means]"
                  {{ !$canModifyStructure ? 'disabled' : '' }}>{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.verification_means", $action->verification_means ?? '') }}</textarea>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-info" onclick="toggleActionAssignees(this)" title="إدارة المكلفين">
                <i class="fas fa-users"></i>
                <span class="badge bg-info ms-1 assigned-count-badge">{{ isset($action) && $action->assignedEntities ? $action->assignedEntities->count() : 0 }}</span>
            </button>
            <button type="button" class="btn btn-outline-warning" onclick="toggleActionCosts(this)" title="إدارة التكاليف">
                <i class="fas fa-money-bill-wave"></i>
                <span class="badge bg-warning ms-1 costs-count-badge">{{ isset($action) && $action->costs ? $action->costs->count() : 0 }}</span>
            </button>
            @if($canModifyStructure)
            <button type="button" class="btn btn-outline-danger" onclick="removeExecutiveAction(this)" title="حذف الإجراء">
                <i class="fas fa-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>

<!-- Sub-tables for Assignees and Costs -->
<tr class="action-details-row d-none" id="action-details-{{ $activityIndex }}-{{ $actionIndex }}">
    <td colspan="8" class="p-0 border-0">
        <!-- Assignees Section -->
        <div class="action-assignees-collapse collapse p-3 bg-light border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-info"><i class="fas fa-users me-2"></i>الجهات المكلفة بالتنفيذ</h6>
                @if($canModifyStructure)
                <button type="button" class="btn btn-sm btn-info text-white" onclick="addNewActionAssignee(this)">
                    <i class="fas fa-plus me-1"></i>إضافة مكلف
                </button>
                @endif
            </div>
            <div class="action-assignees-container">
                @if(isset($action) && $action->assignedEntities && $action->assignedEntities->count() > 0)
                    @foreach($action->assignedEntities as $assignedIndex => $assigned)
                        @include('projects.partials.tables.executive.action-assigned-row', [
                            'activityIndex' => $activityIndex,
                            'actionIndex' => $actionIndex,
                            'assignedIndex' => $assignedIndex,
                            'assigned' => $assigned
                        ])
                    @endforeach
                @else
                    <div class="text-center py-2 text-muted no-assignees-msg">
                        <small>لا يوجد مكلفون لهذا الإجراء</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Costs Section -->
        <div class="action-costs-collapse collapse p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-warning"><i class="fas fa-wallet me-2"></i>تكالifs الإجراء</h6>
                @if($canModifyCosts)
                <button type="button" class="btn btn-sm btn-warning text-white" onclick="addNewActionCost(this)">
                    <i class="fas fa-plus me-1"></i>إضافة تكلفة
                </button>
                @endif
            </div>
            <div class="action-costs-container">
…
                @if(isset($action) && $action->costs && $action->costs->count() > 0)
                    @foreach($action->costs as $costIndex => $cost)
                        @include('projects.partials.tables.executive.action-cost-row', [
                            'activityIndex' => $activityIndex,
                            'actionIndex' => $actionIndex,
                            'costIndex' => $costIndex,
                            'cost' => $cost,
                            'financialItems' => $financialItems ?? [],
                            'units' => $units ?? []
                        ])
                    @endforeach
                @else
                    <div class="text-center py-2 text-muted no-costs-msg">
                        <small>لا توجد تكاليف لهذا الإجراء</small>
                    </div>
                @endif
            </div>
        </div>
    </td>
</tr>