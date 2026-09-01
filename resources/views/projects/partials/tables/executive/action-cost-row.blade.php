<div class="executive-cost-row mb-2 p-2 border rounded" data-executive-cost-row
    data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}"
    data-cost-index="{{ $costIndex }}">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group mb-2">
                <label class="form-label small">البند المالي *</label>
                <input type="hidden"
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][id]"
                    value="{{ $cost->id ?? '' }}">
                <select class="form-control form-control-sm financial-item-select select-search"
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][financial_item_id]"
                    data-ajax-url="{{ route('frappe.financial_items') }}" data-ajax-type="financial_item"
                    data-unit-url="{{ route('lookup.units.by_financial_item') }}"
                    data-target-unit=".unit-select-{{ $activityIndex }}_{{ $actionIndex }}_{{ $costIndex }}" {{ !$canModifyCosts ? 'disabled' : '' }}>
                    <option value="">اختر البند المالي</option>
                    @php
                        $fiId = old("executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.financial_item_id", data_get($cost, 'financial_item_id', ''));
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
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group mb-2">
                <label class="form-label small">الوحدة *</label>
                <select
                    class="form-control form-control-sm unit-select-{{ $activityIndex }}_{{ $actionIndex }}_{{ $costIndex }} unit-select select-search"
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][unit_id]"
                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}" data-ajax-type="unit"
                    data-dependent-financial-item=".financial-item-select" {{ !$canModifyCosts ? 'disabled' : '' }}>
                    <option value="">اختر الوحدة</option>
                    @php
                        $uId = old("executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.unit_id", data_get($cost, 'unit_id', ''));
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
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group mb-2">
                <label class="form-label small">المبلغ *</label>
                <input type="number" class="form-control form-control-sm executive-cost-amount"
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][amount]"
                    value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.amount", $cost->amount ?? 0) }}"
                    {{ !$canModifyCosts ? 'disabled' : '' }} step="0.01" min="0">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group mb-2">
                <label class="form-label small">العدد *</label>
                <input type="number" class="form-control form-control-sm executive-cost-quantity"
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][costs][{{ $costIndex }}][quantity]"
                    value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.costs.{$costIndex}.quantity", $cost->quantity ?? 1) }}"
                    {{ !$canModifyCosts ? 'disabled' : '' }} min="1">
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group mb-2">
                <label class="form-label small">الإجمالي</label>
                <input type="number" class="form-control form-control-sm executive-cost-total" readonly
                    value="{{ isset($cost) ? number_format(($cost->amount ?? 0) * ($cost->quantity ?? 1), 2, '.', '') : 0 }}">
            </div>
        </div>

        <div class="col-md-1">
            <div class="form-group mb-2">
                <label class="form-label small">&nbsp;</label>
                @if($canModifyCosts)
                    <button type="button" class="btn btn-sm btn-danger w-100 delete-executive-cost-btn">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Select2 for all search selects in executive cost rows
            initExecutiveSelect2();

            // Recalculate total when amount or quantity changes
            $(document).on('input', '.executive-cost-amount, .executive-cost-quantity', function () {
                var row = $(this).closest('.executive-cost-row');
                var amount = parseFloat(row.find('.executive-cost-amount').val()) || 0;
                var quantity = parseFloat(row.find('.executive-cost-quantity').val()) || 0;
                var total = amount * quantity;
                row.find('.executive-cost-total').val(total.toFixed(2));

                // Trigger global recalculation if needed (e.g., action total, project total)
                if (typeof calculateExecutiveTotals === 'function') calculateExecutiveTotals();
            });

            // Handle financial item change for cascading units
            $(document).on('change', '.financial-item-select', function (e) {
                // Ignore programmatic/init-triggered changes to prevent wiping existing unit selection on load
                if (!e.originalEvent) return;
                handleExecutiveFinancialItemChange($(this));
            });

            // Delete cost row
            $(document).on('click', '.delete-executive-cost-btn', function () {
                var row = $(this).closest('.executive-cost-row');
                row.remove();
                if (typeof calculateExecutiveTotals === 'function') calculateExecutiveTotals();
            });
        });

        function initExecutiveSelect2() {
            $('.executive-cost-row .select-search').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) return;

                var $el = $(this); // capture element reference for use inside closures

                $el.select2({
                    ajax: {
                        url: $el.data('ajax-url'),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                type: $el.data('ajax-type')
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results || data.items || data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    placeholder: $el.find('option:first').text(),
                    allowClear: true,
                    dir: 'rtl',
                    width: '100%',
                    language: {
                        noResults: function () { return 'لا توجد نتائج'; },
                        searching: function () { return 'جاري البحث...'; },
                        inputTooShort: function () { return 'يرجى إدخال حرف واحد أو أكثر'; }
                    }
                });

                // ✅ Restore pre-selected option for saved drafts / edit mode.
                // Select2 (AJAX mode) does not display a pre-rendered <option selected>
                // on its own — we must explicitly set the value and trigger change.
                var selectedOption = $el.find('option[selected]');
                if (!selectedOption.length) {
                    var currentVal = $el.val();
                    if (currentVal) {
                        selectedOption = $el.find('option[value="' + currentVal + '"]');
                    }
                }

                if (selectedOption.length && selectedOption.val()) {
                    // Only append a new Option if it doesn't already exist in the DOM
                    // (avoids duplicates since Blade pre-rendered it)
                    var existingInSelect = $el.find('option[value="' + selectedOption.val() + '"]');
                    if (!existingInSelect.length) {
                        var newOption = new Option(
                            selectedOption.text(),
                            selectedOption.val(),
                            true,
                            true
                        );
                        $el.append(newOption);
                    } else {
                        existingInSelect.prop('selected', true);
                    }
                    $el.trigger('change');
                }
            });
        }

        function handleExecutiveFinancialItemChange($select) {
            var financialItemId = $select.val();
            var targetUnitSelect = $($select.data('target-unit'));

            if (!targetUnitSelect.length) {
                targetUnitSelect = $select.closest('.row').find('.unit-select');
            }

            targetUnitSelect.val(null).trigger('change');

            if (financialItemId) {
                targetUnitSelect.prop('disabled', true);
                $.ajax({
                    url: $select.data('unit-url'),
                    type: 'GET',
                    data: { financial_item_id: financialItemId },
                    success: function (response) {
                        var options = '<option value="">اختر الوحدة</option>';
                        if (response.units && response.units.length) {
                            $.each(response.units, function (i, unit) {
                                options += '<option value="' + unit.id + '">' + unit.text + '</option>';
                            });
                        }
                        targetUnitSelect.html(options);
                        targetUnitSelect.prop('disabled', false);

                        // Reinitialize Select2 for the unit select
                        if (targetUnitSelect.hasClass('select2-hidden-accessible')) {
                            targetUnitSelect.select2('destroy');
                        }
                        targetUnitSelect.select2({
                            minimumResultsForSearch: 0,
                            placeholder: 'اختر الوحدة',
                            allowClear: true,
                            dir: 'rtl'
                        });
                        },
                        error: function () {
                            targetUnitSelect.html('<option value="">خطأ في تحميل الوحدات</option>');
                            targetUnitSelect.prop('disabled', false);
                        }
                    });
                } else {
                    targetUnitSelect.html('<option value="">اختر الوحدة</option>');
                    targetUnitSelect.prop('disabled', false);
                    if (targetUnitSelect.hasClass('select2-hidden-accessible')) {
                        targetUnitSelect.select2('destroy');
                    }
                    targetUnitSelect.select2({
                        ajax: {
                            url: targetUnitSelect.data('ajax-url'),
                            dataType: 'json',
                            data: function (params) {
                                return { q: params.term, type: targetUnitSelect.data('ajax-type') };
                            },
                            processResults: function (data) {
                                return { results: data.results || data.items || data };
                            }
                        },
                        minimumInputLength: 0,
                        placeholder: 'اختر الوحدة',
                        allowClear: true,
                        language: {
                            noResults: function () { return 'لا توجد نتائج'; },
                            searching: function () { return 'جاري البحث...'; },
                            inputTooShort: function () { return 'يرجى إدخال حرف واحد أو أكثر'; }
                        }
                    });
                }
            }

        if (typeof initExecutiveSelect2 !== 'function') {
            window.initExecutiveSelect2 = function(container) {
                var $container = container ? $(container) : $('.executive-cost-row');
                var $selects = $container.is('.select-search') ? $container : $container.find('.select-search');
                
                $selects.each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) return;

                    var $el = $(this);

                    $el.select2({
                        ajax: {
                            url: $el.data('ajax-url'),
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    type: $el.data('ajax-type')
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.results || data.items || data
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 0,
                        placeholder: $el.find('option:first').text(),
                        allowClear: true,
                        dir: 'rtl',
                        width: '100%',
                        language: {
                            noResults: function () { return 'لا توجد نتائج'; },
                            searching: function () { return 'جاري البحث...'; },
                            inputTooShort: function () { return 'يرجى إدخال حرف واحد أو أكثر'; }
                        }
                    });

                    var selectedOption = $el.find('option[selected]');
                    if (!selectedOption.length) {
                        var currentVal = $el.val();
                        if (currentVal) {
                            selectedOption = $el.find('option[value="' + currentVal + '"]');
                        }
                    }

                    if (selectedOption.length && selectedOption.val()) {
                        var existingInSelect = $el.find('option[value="' + selectedOption.val() + '"]');
                        if (!existingInSelect.length) {
                            var newOption = new Option(
                                selectedOption.text(),
                                selectedOption.val(),
                                true,
                                true
                            );
                            $el.append(newOption);
                        } else {
                            existingInSelect.prop('selected', true);
                        }
                        $el.trigger('change');
                    }
                });
            };
        }

        $(function () {
            // Only run initialization if not already initialized
            if (!window.executiveSelect2Initialized) {
                window.initExecutiveSelect2();
                window.executiveSelect2Initialized = true;
                
                $(document).on('change', '.financial-item-select', function (e) {
                    if (!e.originalEvent) return;
                    handleExecutiveFinancialItemChange($(this));
                });

                $(document).on('click', '.delete-executive-cost-btn', function () {
                    var row = $(this).closest('.executive-cost-row');
                    row.remove();
                    if (typeof calculateExecutiveTotals === 'function') calculateExecutiveTotals();
                });
            }
        });
    </script>
@endpush