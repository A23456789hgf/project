<div class="procedure-costs-container">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fs-6">
            <i class="fas fa-money-bill-wave text-warning me-2"></i>
            تكاليف الإجراء
        </h6>
        @if($canModifyCosts)
        <button type="button" class="btn btn-warning btn-sm shadow-sm text-dark px-3" onclick="addCostToProcedure(this)">
            <i class="fas fa-plus-circle me-1"></i>إضافة تكلفة جديدة
        </button>
        @endif
    </div>

    <div class="table-responsive border rounded shadow-sm bg-white">
        <table class="table table-hover align-middle mb-0 no-stack" style="min-width: 900px;">
            <thead class="table-light">
                <tr>
                    <th width="40" class="text-center">#</th>
                    <th width="250">البند المالي</th>
                    <th width="150">الوحدة</th>
                    <th width="120">المبلغ</th>
                    <th width="100" class="text-center">الكمية</th>
                    <th width="120">الإجمالي</th>
                    <th width="80" class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="costs-tbody">
                @if(isset($procedure) && $procedure->costs->count() > 0)
                    @foreach($procedure->costs as $costIndex => $cost)
                        @include('projects.partials.tables.preliminary.cost-row', [
                            'activityIndex' => $activityIndex,
                            'procedureIndex' => $procedureIndex,
                            'costIndex' => $costIndex,
                            'cost' => $cost,
                            'financialItems' => $financialItems ?? [],
                            'units' => $units ?? []
                        ])
                    @endforeach
                @else
                    <tr class="no-costs-row">
                        <td colspan="7" class="text-center py-2 text-muted">
                            <i class="fas fa-coins me-2"></i>
                            لا توجد تكاليف مضافة
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
