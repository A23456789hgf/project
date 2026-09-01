{{--
  _financing.blade.php
  STRATEGY: Existing financing records are rendered entirely server-side (Blade)
  so that every stored ID maps to a guaranteed <option selected> tag regardless
  of collection size.  The JS addFinancingCard() is used ONLY for new blank cards.
--}}

{{-- ══════════════════════════════════════════════════════════════════
     SERVER-SIDE: Render existing financing cards
     ══════════════════════════════════════════════════════════════════ --}}
<div id="financing-section-wrapper">
<div id="financing-cards-container" data-initial-count="{{ isset($financings) ? $financings->count() : 0 }}">

@if(isset($financings) && $financings->count() > 0)
    @foreach($financings as $fIdx => $financing)
    @php
        /* Resolve display labels once in PHP so Blade outputs correct text */
        $fSource    = $fundingSources->firstWhere('id', $financing->funding_source_id);
        $fAuthority = $authorities->firstWhere('id', $financing->authority_id);
        $fType      = $financingTypes->firstWhere('id', $financing->financing_type_id);
        $fForm      = $financingForms->firstWhere('id', $financing->financing_form_id);
        $fSubForm   = $subFinancingForms->firstWhere('id', $financing->sub_financing_form_id);
    @endphp
    <div class="card financing-card mb-3" data-index="{{ $fIdx }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">بطاقة التمويل #<span class="card-number">{{ $fIdx + 1 }}</span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-card">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- مصدر التمويل --}}
                <div class="col-md-4">
                    <label class="form-label">مصدر التمويل</label>
                    <select class="form-select select-search funding-source-select"
                            name="financings[{{ $fIdx }}][funding_source_id]"
                            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                            data-ajax-type="funding_source">
                        <option value="">-- اختر مصدر التمويل --</option>
                        @if($fSource)
                            <option value="{{ $fSource->id }}" selected>{{ $fSource->name }}</option>
                        @elseif($financing->funding_source_id)
                            <option value="{{ $financing->funding_source_id }}" selected>
                                {{ $financing->fundingSource->name ?? '[ID: '.$financing->funding_source_id.']' }}
                            </option>
                        @endif
                    </select>
                </div>

                {{-- الجهة الممولة --}}
                <div class="col-md-4">
                    <label class="form-label">الجهة الممولة</label>
                    <select class="form-select select-search authority-select"
                            name="financings[{{ $fIdx }}][authority_id]"
                            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                            data-ajax-type="authority"
                            data-ajax-params="funding_source_id=.financing-card|.funding-source-select">
                        <option value="">-- اختر الجهة --</option>
                        @if($fAuthority)
                            <option value="{{ $fAuthority->id }}" selected>
                                {{ $fAuthority->agency_name ?? $fAuthority->name }}
                            </option>
                        @elseif(isset($financing) && $financing->authority_id)
                            @php
                                $authModel = $financing->authority ?? \App\Models\Authority::withoutGlobalScopes()->find($financing->authority_id);
                            @endphp
                            <option value="{{ $financing->authority_id }}" selected>
                                {{ $authModel->agency_name ?? $authModel->name ?? ('[ID: '.$financing->authority_id.']') }}
                            </option>
                        @endif
                    </select>
                </div>

                {{-- نوع التمويل --}}
                <div class="col-md-4">
                    <label class="form-label">نوع التمويل</label>
                    <select class="form-select select-search"
                            name="financings[{{ $fIdx }}][financing_type_id]"
                            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                            data-ajax-type="financing_type">
                        <option value="">-- اختر النوع --</option>
                        @if($fType)
                            <option value="{{ $fType->id }}" selected>{{ $fType->name }}</option>
                        @elseif($financing->financing_type_id)
                            <option value="{{ $financing->financing_type_id }}" selected>
                                {{ $financing->financingType->name ?? '[ID: '.$financing->financing_type_id.']' }}
                            </option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                {{-- شكل التمويل --}}
                <div class="col-md-3">
                    <label class="form-label">شكل التمويل</label>
                    <select class="form-select select-search financing-form-select"
                            data-index="{{ $fIdx }}"
                            name="financings[{{ $fIdx }}][financing_form_id]"
                            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                            data-ajax-type="financing_form">
                        <option value="">-- اختر الشكل --</option>
                        @if($fForm)
                            <option value="{{ $fForm->id }}" selected>{{ $fForm->name }}</option>
                        @elseif($financing->financing_form_id)
                            <option value="{{ $financing->financing_form_id }}" selected>
                                {{ $financing->financingForm->name ?? '[ID: '.$financing->financing_form_id.']' }}
                            </option>
                        @endif
                    </select>
                </div>

                {{-- الشكل الفرعي --}}
                <div class="col-md-3">
                    <label class="form-label">الشكل الفرعي</label>
                    <select class="form-select select-search sub-financing-form-select"
                            data-index="{{ $fIdx }}"
                            name="financings[{{ $fIdx }}][sub_financing_form_id]"
                            data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                            data-ajax-type="sub_financing_form"
                            data-ajax-params="financing_form_id=.financing-card|.financing-form-select">
                        <option value="">-- اختر الشكل الفرعي --</option>
                        @if($fSubForm)
                            <option value="{{ $fSubForm->id }}" selected>{{ $fSubForm->name }}</option>
                        @elseif($financing->sub_financing_form_id)
                            <option value="{{ $financing->sub_financing_form_id }}" selected>
                                {{ $financing->subFinancingForm->name ?? '[ID: '.$financing->sub_financing_form_id.']' }}
                            </option>
                        @endif
                    </select>
                </div>

                {{-- مبلغ التمويل --}}
                <div class="col-md-3">
                    <label class="form-label">مبلغ التمويل</label>
                    <input type="number" class="form-control"
                           name="financings[{{ $fIdx }}][financing_amount]"
                           value="{{ $financing->financing_amount }}"
                           min="0" step="0.01" placeholder="0.00">
                </div>

                {{-- نسبة التمويل --}}
                <div class="col-md-3">
                    <label class="form-label">نسبة التمويل (%)</label>
                    <input type="number" class="form-control"
                           name="financings[{{ $fIdx }}][financing_percentage]"
                           value="{{ $financing->financing_percentage }}"
                           min="0" max="100" step="0.01" placeholder="0" readonly>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

</div>{{-- #financing-cards-container --}}

<button type="button" id="add-financing-card" class="btn btn-primary mt-3">
    <i class="fas fa-plus"></i> إضافة تمويل جديد
</button>
</div>{{-- #financing-section-wrapper --}}

@push('scripts')
<script>
(function ($) {
    'use strict';

    // ── Counter: start after the last server-rendered card index ──────────
    // Read from data attribute so value is available after DOM is ready
    const $container = $('#financing-cards-container');
    let financingCardIndex = parseInt($container.data('initial-count') || 0, 10);

    // ── addFinancingCard: used ONLY for new blank cards ───────────────────
    function addFinancingCard(data) {
        data = data || {};
        const index = financingCardIndex++;

        const cardHtml = `
        <div class="card financing-card mb-3" data-index="${index}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">بطاقة التمويل #<span class="card-number">${financingCardIndex}</span></h6>
                <button type="button" class="btn btn-sm btn-danger remove-card">
                    <i class="fas fa-trash"></i> حذف
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">مصدر التمويل</label>
                        <select class="form-select select-search funding-source-select"
                                name="financings[${index}][funding_source_id]"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="funding_source">
                            <option value="">-- اختر مصدر التمويل --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">الجهة الممولة</label>
                        <select class="form-select select-search authority-select"
                                name="financings[${index}][authority_id]"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="authority"
                                data-ajax-params="funding_source_id=.financing-card|.funding-source-select">
                            <option value="">-- اختر الجهة --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نوع التمويل</label>
                        <select class="form-select select-search"
                                name="financings[${index}][financing_type_id]"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="financing_type">
                            <option value="">-- اختر النوع --</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3">
                        <label class="form-label">شكل التمويل</label>
                        <select class="form-select select-search financing-form-select"
                                data-index="${index}"
                                name="financings[${index}][financing_form_id]"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="financing_form">
                            <option value="">-- اختر الشكل --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الشكل الفرعي</label>
                        <select class="form-select select-search sub-financing-form-select"
                                data-index="${index}"
                                name="financings[${index}][sub_financing_form_id]"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="sub_financing_form"
                                data-ajax-params="financing_form_id=.financing-card|.financing-form-select">
                            <option value="">-- اختر الشكل الفرعي --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">مبلغ التمويل</label>
                        <input type="number" class="form-control"
                               name="financings[${index}][financing_amount]"
                               value="${data.financing_amount || ''}"
                               min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">نسبة التمويل (%)</label>
                        <input type="number" class="form-control"
                               name="financings[${index}][financing_percentage]"
                               value="${data.financing_percentage || ''}"
                               min="0" max="100" step="0.01" placeholder="0" readonly>
                    </div>
                </div>
            </div>
        </div>`;

        const $card = $(cardHtml);
        $('#financing-cards-container').append($card);

        if (typeof window.initGlobalSelect2 === 'function') {
            window.initGlobalSelect2($card[0]);
        }

        updateCardNumbers();
    }

    // Expose so FormManager or other scripts can call it
    window.addFinancingCard = addFinancingCard;

    function updateCardNumbers() {
        $('.financing-card').each(function (i) {
            $(this).find('.card-number').text(i + 1);
        });
    }

    function calculateFinancingPercentages() {
        const totalCostInput = document.getElementById('total_project_cost');
        const totalCost = totalCostInput ? parseFloat(totalCostInput.value) || 0 : 0;

        if (totalCost <= 0) {
            $('input[name*="financing_percentage"]').val('0.00');
            return;
        }

        $('.financing-card').each(function () {
            const $amountInput     = $(this).find('input[name*="financing_amount"]');
            const $percentageInput = $(this).find('input[name*="financing_percentage"]');
            if ($amountInput.length && $percentageInput.length) {
                const amount = parseFloat($amountInput.val()) || 0;
                $percentageInput.val(((amount / totalCost) * 100).toFixed(2));
            }
        });
    }

    $(document).ready(function () {

        // ── Initialize Select2 on server-rendered existing cards ──────────
        // The <option selected> tags are already in the DOM from Blade,
        // so initGlobalSelect2 will pick them up automatically.
        $('.financing-card').each(function (idx) {
            if (typeof window.initGlobalSelect2 === 'function') {
                setTimeout(() => window.initGlobalSelect2(this), idx * 60);
            }
        });

        // ── If no cards were server-rendered, add one blank card ──────────
        if ($('.financing-card').length === 0) {
            addFinancingCard();
        }

        // ── Add new card button ───────────────────────────────────────────
        $('#add-financing-card').on('click', function () {
            addFinancingCard();
        });

        // ── Remove card ───────────────────────────────────────────────────
        $(document).on('click', '.remove-card', function () {
            if ($('.financing-card').length > 1) {
                $(this).closest('.financing-card').remove();
                updateCardNumbers();
                calculateFinancingPercentages();
            } else {
                alert('يجب أن يكون هناك على الأقل بطاقة تمويل واحدة');
            }
        });

        // ── financing_form → sub_financing_form cascade ───────────────────
        let financingCascadeInit = true;
        setTimeout(() => { financingCascadeInit = false; }, 800);

        $(document).on('change', '.financing-form-select', function () {
            if (financingCascadeInit) return;
            $(this).closest('.financing-card').find('.sub-financing-form-select')
                   .val(null).trigger('change');
        });

        // ── funding_source → authority cascade ───────────────────
        $(document).on('change', '.funding-source-select', function () {
            if (financingCascadeInit) return;
            $(this).closest('.financing-card').find('.authority-select')
                   .val(null).trigger('change');
        });

        // ── Percentage auto-calculation ───────────────────────────────────
        calculateFinancingPercentages();

        $(document).on('input', 'input[name*="financing_amount"]', function () {
            calculateFinancingPercentages();
        });

        $(document).on('input change', '#total_project_cost', function () {
            calculateFinancingPercentages();
        });
    });

    // Recalculate before submit
    document.addEventListener('submit', function () {
        calculateFinancingPercentages();
    }, true);

})(jQuery);
</script>
@endpush

@push('styles')
<style>
    .financing-card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    .financing-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .remove-card { font-size: 0.875rem; }
</style>
@endpush