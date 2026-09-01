<div class="executive-actions-table-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="fas fa-list-check text-primary me-2"></i>إجراءات النشاط
        </h6>
        <button type="button" class="btn btn-success btn-sm add-executive-action-btn" data-activity-index="{{ $activityIndex }}" onclick="addNewExecutiveAction(this)">
            <i class="fas fa-plus me-1"></i> إضافة إجراء جديد
        </button>
    </div>

    <div class="table-responsive border rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 project-table" style="min-width: 1350px;">
            <thead class="bg-light">
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th width="300">الإجراء</th>
                    <th width="150" class="text-center">الوزن %</th>
                    <th width="180">تاريخ البداية</th>
                    <th width="180">تاريخ النهاية</th>
                    <th width="150" class="text-center">المدة</th>
                    <th width="250">وسائل التحقق</th>
                    <th width="80" class="text-center">الخيارات</th>
                </tr>
            </thead>
            <tbody class="executive-actions-tbody executive-actions-container" id="executive-actions-container-{{ $activityIndex }}" data-activity-index="{{ $activityIndex }}">
                @if(isset($activity) && $activity->actions && $activity->actions->count() > 0)
                    @foreach($activity->actions as $actionIndex => $action)
                        @include('projects.partials.tables.executive.activity-action-row', [
                            'activityIndex' => $activityIndex,
                            'actionIndex'   => $actionIndex,
                            'action'        => $action,
                            'projectOutputs'=> $projectOutputs ?? [],
                            'projectRisks'  => $projectRisks ?? [],
                            'financialItems' => $financialItems ?? \App\Models\FinancialItem::where('is_active', true)->orderBy('name')->get(),
                            'units' => $units ?? \App\Models\Unit::orderBy('unit_name')->get()
                        ])
                    @endforeach
                @else
                    <tr class="no-actions-row">
                        <td colspan="8" class="text-center py-3 text-muted">
                            <i class="fas fa-inbox fa-lg me-2"></i>
                            لا توجد إجراءات مضافة
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-end">
                        <small class="text-muted">
                            مجموع أوزان الإجراءات: 
                            <span class="executive-action-total-weight fw-bold" data-activity-index="{{ $activityIndex }}">0</span>%
                        </small>
                    </td>
                    <td>
                        <span class="executive-action-weight-status" data-activity-index="{{ $activityIndex }}"></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>


