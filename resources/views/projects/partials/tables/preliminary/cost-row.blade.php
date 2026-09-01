<tr class="cost-row bg-light border-bottom" data-activity-index="{{ $activityIndex }}"
    data-procedure-index="{{ $procedureIndex }}" data-cost-index="{{ $costIndex }}">
    <td class="text-center">
        <span class="cost-number fw-bold text-warning">{{ is_numeric($costIndex) ? (int) $costIndex + 1 : '' }}</span>
    </td>
    <td>
        <input type="hidden"
            name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][id]"
            value="{{ $cost->id ?? '' }}">
        <select
            name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][financial_item_id]"
            class="form-select form-select-sm financial-item-select select-search shadow-sm"
            data-ajax-url="{{ route('frappe.financial_items') }}" data-ajax-type="financial_item" {{ !$canModifyCosts ? 'disabled' : '' }}>
            <option value="">اختر البند المالي</option>
            @php
                $fiId = old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.financial_item_id", data_get($cost, 'financial_item_id', ''));
                $fiName = $fiId;
                if ($fiId) {
                    $fiName = data_get($cost, 'financialItem.name')
                        ?? (isset($financialItems) ? collect($financialItems)->firstWhere('id', $fiId)?->name : null)
                        ?? \App\Models\FinancialItem::query()->whereKey($fiId)->value('name')
                        ?? $fiId;
                }
            @endphp
            @if($fiId)
                <option value="{{ $fiId }}" selected>{{ __($fiName) }}</option>
            @endif
            @if(isset($financialItems) && count($financialItems) > 0)
                @foreach($financialItems as $item)
                    <option value="{{ $item->id }}" {{ (string) $fiId === (string) $item->id ? 'selected' : '' }}>
                        {{ __($item->name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </td>
    <td>
        <select
            name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][unit_id]"
            class="form-select form-select-sm unit-select select-search shadow-sm"
            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="unit" {{ !$canModifyCosts ? 'disabled' : '' }}>
            <option value="">اختر الوحدة</option>
            @php
                $uId = old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.unit_id", data_get($cost, 'unit_id', ''));
                $uName = $uId;
                if ($uId) {
                    $uName = data_get($cost, 'unit.unit_name')
                        ?? (isset($units) ? collect($units)->firstWhere('id', $uId)?->unit_name : null)
                        ?? \App\Models\Unit::withTrashed()->whereKey($uId)->value('unit_name')
                        ?? $uId;
                }
            @endphp
            @if($uId)
                <option value="{{ $uId }}" selected>{{ $uName }}</option>
            @endif
            @if(isset($units) && count($units) > 0)
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ (string) $uId === (string) $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_name }}
                    </option>
                @endforeach
            @endif
        </select>
    </td>
    <td>
        <div class="input-group input-group-sm">
            <input type="number"
                name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][amount]"
                class="form-control form-control-sm amount-input shadow-sm" min="0" step="0.01" placeholder="0.00"
                value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.costs.$costIndex.amount", $cost->amount ?? '') }}"
                {{ !$canModifyCosts ? 'disabled' : '' }}>
            <span class="input-group-text bg-light text-muted">ريال</span>
        </div>
    </td>
    <td class="text-center">
        <input type="number"
            name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][quantity]"
            class="form-control form-control-sm quantity-input shadow-sm text-center" min="0"
            value="{{ old("preliminary_activities.$activityIndex.procedures.$procedureIndex.costs.$costIndex.quantity", $cost->quantity ?? 1) }}"
            {{ !$canModifyCosts ? 'disabled' : '' }}>
    </td>
    <td>
        <div class="d-flex align-items-center">
            <span
                class="badge bg-success cost-total-display">{{ isset($cost) ? number_format($cost->amount * ($cost->quantity ?: 1), 0, '.', ',') : 0 }}</span>
            <small class="ms-1 text-muted">ريال</small>
        </div>
    </td>
    <td class="text-center">
        @if($canModifyCosts)
            <button type="button" class="btn btn-light border-0 text-danger btn-sm" onclick="removeCost(this)"
                title="حذف التكلفة">
                <i class="fas fa-trash-alt"></i>
            </button>
        @endif
    </td>
</tr>

<script>
    if (typeof removeCost !== 'function') {
        window.removeCost = function (button) {
            const costRow = button.closest('.cost-row');
            const procedureRow = costRow.closest('.procedure-details-row').previousElementSibling;
            const costsTbody = costRow.closest('.costs-tbody');

            costRow.remove();

            if (typeof updateCostNumbers === 'function') updateCostNumbers(procedureRow);
            if (typeof calculateProcedureTotal === 'function') calculateProcedureTotal(procedureRow);
            if (typeof calculateTotals === 'function') calculateTotals();
            if (typeof updateFinancialSummary === 'function') updateFinancialSummary();

            // If no costs left, show empty message
            if (costsTbody.querySelectorAll('.cost-row').length === 0) {
                costsTbody.innerHTML = `
                <tr class="no-costs-row">
                    <td colspan="7" class="text-center py-2 text-muted">
                        <i class="fas fa-coins me-2"></i>
                        لا توجد تكاليف مضافة
                    </td>
                </tr>
            `;
            }
        };
    }
</script>