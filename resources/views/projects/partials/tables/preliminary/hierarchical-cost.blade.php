<!-- LEVEL 4: Cost Row (Always visible when parent expanded) -->
<tr class="cost-row level-4" data-activity-index="{{ $activityIndex }}" data-procedure-index="{{ $procedureIndex }}"
    data-cost-index="{{ $costIndex }}" style="display: none;">
    <td colspan="6" class="p-0">
        <div class="ps-6 pe-3 py-2 bg-white border-start border-success border-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold">البند المالي </label>
                    <select class="form-select form-select-sm financial-item-select"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][financial_item_id]">
                        <option value="">اختر البند</option>
                        @php
                            $oldFiId = old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.financial_item_id", data_get($cost, 'financial_item_id', ''));
                            $fiName = $oldFiId;
                            $financialItemsCollection = isset($financialItems) ? collect($financialItems) : collect();
                            if ($oldFiId && (!$financialItemsCollection->count() || !$financialItemsCollection->contains('id', $oldFiId))) {
                                $fiName = data_get($cost, 'financialItem.name')
                                    ?? \App\Models\FinancialItem::query()->whereKey($oldFiId)->value('name')
                                    ?? $oldFiId;
                            }
                        @endphp
                        @if($oldFiId && (!$financialItemsCollection->count() || !$financialItemsCollection->contains('id', $oldFiId)))
                            <option value="{{ $oldFiId }}" selected>{{ $fiName }}</option>
                        @endif
                        @if($financialItemsCollection->count() > 0)
                            @foreach($financialItems as $item)
                                <option value="{{ $item->id }}" {{ $oldFiId == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <input type="hidden"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][id]"
                        value="{{ data_get($cost, 'id', '') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">الوحدة </label>
                    <select class="form-select form-select-sm unit-select"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][unit_id]">
                        <option value="">اختر الوحدة</option>
                        @php
                            $oldUId = old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.unit_id", data_get($cost, 'unit_id', ''));
                            $uName = $oldUId;
                            $unitsCollection = isset($units) ? collect($units) : collect();
                            if ($oldUId && (!$unitsCollection->count() || !$unitsCollection->contains('id', $oldUId))) {
                                $uName = data_get($cost, 'unit.unit_name')
                                    ?? \App\Models\Unit::withTrashed()->whereKey($oldUId)->value('unit_name')
                                    ?? $oldUId;
                            }
                        @endphp
                        @if($oldUId && (!$unitsCollection->count() || !$unitsCollection->contains('id', $oldUId)))
                            <option value="{{ $oldUId }}" selected>{{ $uName }}</option>
                        @endif
                        @if($unitsCollection->count() > 0)
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $oldUId == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->unit_name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">المبلغ (ريال) </label>
                    <input type="number" class="form-control form-control-sm amount-input" step="0.01" min="0"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][amount]"
                        value="{{ old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.amount", data_get($cost, 'amount', 0)) }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold">الكمية </label>
                    <input type="number" class="form-control form-control-sm quantity-input" min="1" step="1"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][quantity]"
                        value="{{ old("preliminary_activities.{$activityIndex}.procedures.{$procedureIndex}.costs.{$costIndex}.quantity", data_get($cost, 'quantity', 1)) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">الإجمالي (ريال)</label>
                    <input type="number" class="form-control form-control-sm total-input bg-light" readonly step="0.01"
                        name="preliminary_activities[{{ $activityIndex }}][procedures][{{ $procedureIndex }}][costs][{{ $costIndex }}][total]"
                        value="{{ $cost->total ?? 0 }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-bold d-block opacity-0">حذف</label>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-cost-btn w-100"
                        title="حذف هذه التكلفة">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </td>
</tr>

<script>
    if (window.initCostRowCalculation) {
        window.initCostRowCalculation(document.querySelector('.cost-row[data-cost-index="{{ $costIndex }}"]'));
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            const row = document.querySelector('.cost-row[data-activity-index="{{ $activityIndex }}"][data-procedure-index="{{ $procedureIndex }}"][data-cost-index="{{ $costIndex }}"]');
            if (!row) return;

            const amountInput = row.querySelector('.amount-input');
            const quantityInput = row.querySelector('.quantity-input');
            const totalInput = row.querySelector('.total-input');

            const updateTotal = () => {
                const amount = parseFloat(amountInput?.value) || 0;
                const quantity = parseFloat(quantityInput?.value) || 0;
                if (totalInput) totalInput.value = (amount * quantity).toFixed(2);
            };

            amountInput?.addEventListener('input', updateTotal);
            quantityInput?.addEventListener('input', updateTotal);
            updateTotal();
        });
    }
</script>