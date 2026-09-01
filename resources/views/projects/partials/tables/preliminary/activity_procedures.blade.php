<div class="activity-procedures-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="fas fa-list-check text-primary me-2"></i>
            إجراءات النشاط
        </h6>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="addProcedureToActivity(this)">
            <i class="fas fa-plus-circle me-1"></i>إضافة إجراء جديد
        </button>
    </div>

    <div class="table-responsive border rounded shadow-sm">
        <table class="table table-hover align-middle mb-0 no-stack" style="min-width: 1150px;">
            <thead class="bg-light">
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th width="250">اسم الإجراء</th>
                    <th width="100" class="text-center">الوزن %</th>
                    <th width="200">الجهة المنفذة</th>
                    <th width="130">تاريخ البدء</th>
                    <th width="130">تاريخ الانتهاء</th>
                    <th width="100" class="text-center">المدة</th>
                    <th width="80" class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="procedures-tbody">
                @if(isset($activity) && $activity->procedures->count() > 0)
                    @foreach($activity->procedures as $procedureIndex => $procedure)
                        @include('projects.partials.tables.preliminary.procedure-row', [
                            'activityIndex' => $activityIndex,
                            'procedureIndex' => $procedureIndex,
                            'procedure' => $procedure,
                            'financialItems' => $financialItems ?? [],
                            'units' => $units ?? []
                        ])
                    @endforeach
                @else
                    <tr class="no-procedures-row">
                        <td colspan="8" class="text-center py-3 text-muted">
                            <i class="fas fa-inbox fa-lg me-2"></i>
                            لا توجد إجراءات مضافة
                        </td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="text-end">
                        <small class="text-muted">
                            مجموع أوزان الإجراءات: 
                            <span class="activity-procedures-total-weight activity-procedures-weight-total fw-bold" data-activity-index="{{ $activityIndex }}">0</span>%
                        </small>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
