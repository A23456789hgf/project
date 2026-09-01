<div class="project-table-container">
    <div class="project-table-header">
        <i class="fas fa-map-marker-alt me-1"></i>مواقع المشروع <span class="text-danger">*</span>
    </div>
    
    <div class="project-table-wrapper">
        <table class="project-table" id="projectLocationsTable">
            <thead>
                <tr>
                    <th>المحافظة</th>
                    <th>المديرية</th>
                    <th>العزلة / المنطقة</th>
                    <th>القرية / الحارة</th>
                    <th width="100">الإجراءات</th>
                </tr> 
            </thead>
            <tbody>
                @if(isset($project) && $project->locations && $project->locations->count() > 0)
                    @foreach($project->locations as $index => $location)
                                                <tr data-location-index="{{ $index }}" class="location-row">
                                                  <td data-label="المحافظة">
                            <select name="locations[{{ $index }}][governorate_id]" 
                                    class="form-select governorate-select select-search" 
                                    data-value="{{ $location->governorate_id ?? '' }}"
                                    data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                    data-ajax-type="governorate">
                                <option value="">اختر المحافظة</option>

                                @php
                                    $selectedGov = $location->governorate_id ?? null;
                                    $showAllOption = isset($governorates) && $governorates->count() !== 1;
                                @endphp

                                @if($showAllOption)
                                    <option value="0" {{ ($selectedGov == '0' || is_null($selectedGov)) ? 'selected' : '' }}>جميع المحافظات</option>
                                @endif

                                @foreach($governorates as $gov)
                                    <option value="{{ $gov->id }}" {{ $selectedGov == $gov->id ? 'selected' : '' }}>
                                        {{ $gov->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                                                    <td data-label="المديرية">
                                                        <select name="locations[{{ $index }}][directorate_id]" 
                                                                class="form-select directorate-select select-search" 
                                                                data-value="{{ $location->directorate_id ?? '' }}"
                                                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                                                data-ajax-type="directorate"
                                                                data-ajax-params="governorate_id=.location-row|.governorate-select">
                                                            <option value="">اختر المديرية</option>
                                                            @if($location->directorate_id == '0' || is_null($location->directorate_id))
                                                                <option value="0" selected>جميع المديريات</option>
                                                            @elseif($location->directorate_id)
                                                                <option value="{{ $location->directorate_id }}" selected>
                                                                    {{ $location->directorate->name ?? $location->directorate_id }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td data-label="العزلة / المنطقة">
                                                        <select name="locations[{{ $index }}][sub_area_id]"
                                                                class="form-select sub-area-select select-search"
                                                                data-value="{{ $location->sub_area_id ?? '' }}"
                                                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                                                data-ajax-type="sub_area"
                                                                data-ajax-params="directorate_id=.location-row|.directorate-select"
                                                                >
                                                            <option value="">اختر المنطقة الفرعية</option>
                                                            @if($location->sub_area_id == '0' || is_null($location->sub_area_id))
                                                                <option value="0" selected>جميع المناطق الفرعية</option>
                                                            @elseif($location->sub_area_id)
                                                                <option value="{{ $location->sub_area_id }}" selected>
                                                                    {{ $location->subArea->name ?? $location->sub_area_id }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                        </div>
                                                    </td>
                                                    <td data-label="القرية / الحارة">
                                                        <div class="select-wrapper" style="{{ (isset($location->sub_area_id) && $location->sub_area_id !== '') ? '' : 'display: none;' }}">
                                                        <select name="locations[{{ $index }}][village_id]"
                                                                class="form-select village-select select-search"
                                                                data-value="{{ $location->village_id ?? '' }}"
                                                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                                                data-ajax-type="village"
                                                                data-ajax-params="sub_area_id=.location-row|.sub-area-select"
                                                                >
                                                            <option value="">اختر القرية</option>
                                                            @if($location->village_id == '0' || is_null($location->village_id))
                                                                <option value="0" selected>جميع القرى والحارات</option>
                                                            @elseif($location->village_id)
                                                                <option value="{{ $location->village_id }}" selected>
                                                                    {{ $location->village->name ?? $location->village_id }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </td>
                                                    <td data-label="الإجراءات">
                                                        <div class="project-action-buttons">
                                                            <button type="button" class="project-btn project-btn-danger remove-location" title="حذف الموقع">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                    @endforeach
                @else
                    <tr class="project-empty-row">
                        <td colspan="5" class="text-center">
                            <i class="fas fa-map-marker-alt fa-2x mb-2"></i><br>
                            لا توجد مواقع مضافة بعد
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div class="p-3">
            <button type="button" class="project-btn project-btn-primary" id="addLocationBtn">
                <i class="fas fa-plus"></i>إضافة موقع جديد
            </button>
        </div>
    </div>
</div>


<script>
// Location Management Module - Using AppUtils & Searchable Dropdowns
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        
        const LocationManager = {
            counter: 0,
            isInitializing: false,
            urls: {
                directorates: @json(url('/api/locations/directorates')),
                subAreas: @json(url('/api/locations/sub-areas')),
                villages: @json(url('/api/locations/villages')),
            },
            
            init: function() {
                 console.log('🔧 LocationManager: Initializing...');
                 this.isInitializing = true;
                 
                 // Initialize counter
                 const rows = document.querySelectorAll('#projectLocationsTable tbody tr[data-location-index]');
                 rows.forEach(row => {
                     const idx = parseInt(row.dataset.locationIndex) || 0;
                     if (idx >= this.counter) this.counter = idx + 1;
                 });

                 this.bindEvents();
                 this.initExistingSelect2();
                 
                 setTimeout(() => {
                     this.isInitializing = false;
                     console.log('✅ LocationManager: Initialization Complete');
                 }, 1000);
            },

            initExistingSelect2: function() {
                const rows = document.querySelectorAll('#projectLocationsTable tbody tr.location-row');
                // Stagger each row by 150 ms to avoid concurrent AJAX floods
                rows.forEach((row, idx) => {
                    setTimeout(() => this.restoreExistingRow(row), idx * 150);
                });
            },

            // ── Sequential hierarchical restoration ──────────────────────────
            // Restores one location row without ever triggering the cascade
            // reset handlers.  Each level waits for the previous AJAX call to
            // complete before proceeding.
            restoreExistingRow: function(row) {
                const govSel    = row.querySelector('.governorate-select');
                const dirSel    = row.querySelector('.directorate-select');
                const subSel    = row.querySelector('.sub-area-select');
                const vilSel    = row.querySelector('.village-select');

                const govVal    = govSel    ? (govSel.getAttribute('data-value')    || govSel.value)    : '';
                const dirVal    = dirSel    ? (dirSel.getAttribute('data-value')    || dirSel.value)    : '';
                const subVal    = subSel    ? (subSel.getAttribute('data-value')    || subSel.value)    : '';
                const vilVal    = vilSel    ? (vilSel.getAttribute('data-value')    || vilSel.value)    : '';

                // Mark row as restoring → cascade handlers will bail out
                row.dataset.restoring = 'true';

                // ── Step 1: Governorate (all options pre-rendered by Blade) ──
                if (govSel && window.initGlobalSelect2) {
                    window.initGlobalSelect2(govSel, true);
                    if (govVal) {
                        $(govSel).val(govVal);
                        // change.select2 updates Select2 display only – does NOT
                        // bubble to the jQuery delegated 'change' handler
                        $(govSel).trigger('change.select2');
                    }
                }

                // ── Step 2: Directorate ──────────────────────────────────────
                if (!dirSel) { row.dataset.restoring = 'false'; return; }

                // Blade already pre-rendered the selected <option>.  Just init
                // Select2 and confirm the value — no fetch needed unless the
                // option is missing (gov = 'all' edge-case).
                const afterDir = () => {
                    if (window.initGlobalSelect2) window.initGlobalSelect2(dirSel, true);
                    if (dirVal && dirVal !== '') {
                        $(dirSel).val(dirVal).trigger('change.select2');
                    }
                    this._restoreSubArea(row, govVal, dirVal, subVal, vilVal);
                };

                if (!govVal || govVal === '0') {
                    // Governorate is 'all' → directorate was already set to 'all' by Blade
                    afterDir();
                } else if (dirVal && !$(dirSel).find(`option[value="${dirVal}"]`).length) {
                    // Pre-rendered option is missing → fetch directorates then restore
                    this._fetchAndSet(
                        dirSel,
                        `${this.urls.directorates}/${govVal}`,
                        dirVal,
                        afterDir
                    );
                } else {
                    afterDir();
                }
            },

            _restoreSubArea: function(row, govVal, dirVal, subVal, vilVal) {
                const subSel = row.querySelector('.sub-area-select');
                const vilSel = row.querySelector('.village-select');
                if (!subSel) { row.dataset.restoring = 'false'; return; }

                const afterSub = () => {
                    if (window.initGlobalSelect2) window.initGlobalSelect2(subSel, true);
                    if (subVal && subVal !== '') {
                        $(subSel).val(subVal).trigger('change.select2');
                    }
                    this._restoreVillage(row, govVal, dirVal, subVal, vilVal);
                };

                if (!dirVal || dirVal === '0') {
                    afterSub();
                } else if (subVal && !$(subSel).find(`option[value="${subVal}"]`).length) {
                    this._fetchAndSet(
                        subSel,
                        `${this.urls.subAreas}/${govVal}/${dirVal}`,
                        subVal,
                        afterSub
                    );
                } else {
                    afterSub();
                }
            },

            _restoreVillage: function(row, govVal, dirVal, subVal, vilVal) {
                const vilSel = row.querySelector('.village-select');
                if (!vilSel) { row.dataset.restoring = 'false'; return; }

                const done = () => {
                    if (window.initGlobalSelect2) window.initGlobalSelect2(vilSel, true);
                    if (vilVal && vilVal !== '') {
                        $(vilSel).val(vilVal).trigger('change.select2');
                    }
                    row.dataset.restoring = 'false'; // Restoration complete
                };

                if (!subVal || subVal === '0') {
                    done();
                } else if (vilVal && !$(vilSel).find(`option[value="${vilVal}"]`).length) {
                    this._fetchAndSet(
                        vilSel,
                        `${this.urls.villages}/${govVal}/${dirVal}/${subVal}`,
                        vilVal,
                        done
                    );
                } else {
                    done();
                }
            },

            // Fetches items from url, adds them as options, sets targetValue, then calls cb
            _fetchAndSet: function(selectEl, url, targetValue, cb) {
                const $sel = $(selectEl);
                $.get(url)
                    .done(data => {
                        // Append fetched options without clearing the placeholder
                        (data || []).forEach(item => {
                            if (!$sel.find(`option[value="${item.id}"]`).length) {
                                $sel.append(`<option value="${item.id}">${item.name}</option>`);
                            }
                        });
                        if (targetValue) $sel.val(targetValue);
                    })
                    .fail(() => console.warn('Location restore fetch failed:', url))
                    .always(() => { if (cb) cb(); });
            },
            
            bindEvents: function() {
                const addBtn = document.getElementById('addLocationBtn');
                if (addBtn) addBtn.addEventListener('click', () => this.addLocation());
                
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.remove-location')) {
                        const row = e.target.closest('tr');
                        Swal.fire({
                            title: 'تأكيد العملية',
                            text: 'هل أنت متأكد من حذف هذا الموقع؟',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'نعم',
                            cancelButtonText: 'لا',
                            reverseButtons: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            if (result.isConfirmed) this.removeLocation(row);
                        });
                    }
                });
                
                // Cascading logic using jQuery for Select2 compatibility
                $(document).on('change', '.governorate-select', (e) => {
                    this.handleGovernorateChange(e.target);
                });
                
                $(document).on('change', '.directorate-select', (e) => {
                    this.handleDirectorateChange(e.target);
                });
                
                $(document).on('change', '.sub-area-select', (e) => {
                    this.handleSubAreaChange(e.target);
                });
            },
            
            addLocation: function() {
                const emptyRow = document.querySelector('.project-empty-row');
                if (emptyRow) emptyRow.remove();
                
                const tbody = document.querySelector('#projectLocationsTable tbody');
                const row = this.createLocationRow();
                tbody.appendChild(row);
                
                // Initialize Select2 for the new row
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2(row);
                }
                
                this.counter++;
            },
            
            createLocationRow: function() {
                const row = document.createElement('tr');
                row.dataset.locationIndex = this.counter;
                row.className = 'location-row';
                
                row.innerHTML = `
                    <td data-label="المحافظة">
                        <select name="locations[${this.counter}][governorate_id]" 
                                class="form-select governorate-select select-search" 
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="governorate">
                            <option value="">اختر المحافظة</option>
                            @php
                                $govs = isset($governorates) ? $governorates : collect([]);
                                $showAll = $govs->count() !== 1;
                            @endphp
                            @if($showAll)
                                <option value="0">جميع المحافظات</option>
                            @endif
                            @foreach($govs as $gov)
                                <option value="{{ $gov->id }}">{{ $gov->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td data-label="المديرية">
                        <select name="locations[${this.counter}][directorate_id]" 
                                class="form-select directorate-select select-search" 
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="directorate"
                                data-ajax-params="governorate_id=.location-row|.governorate-select">
                            <option value="">اختر المديرية</option>
                        </select>
                    </td>
                    <td data-label="العزلة / المنطقة">
                        <select name="locations[${this.counter}][sub_area_id]"
                                class="form-select sub-area-select select-search"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="sub_area"
                                data-ajax-params="directorate_id=.location-row|.directorate-select">
                            <option value="">اختر المنطقة الفرعية</option>
                        </select>
                    </td>
                    <td data-label="القرية / الحارة">
                        <select name="locations[${this.counter}][village_id]"
                                class="form-select village-select select-search"
                                data-ajax-url="{{ route('lookup.search', ['include_pending' => 1]) }}"
                                data-ajax-type="village"
                                data-ajax-params="sub_area_id=.location-row|.sub-area-select">
                            <option value="">اختر القرية</option>
                        </select>
                    </td>
                    <td data-label="الإجراءات">
                        <div class="project-action-buttons">
                            <button type="button" class="project-btn project-btn-danger remove-location" title="حذف الموقع">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                return row;
            },
            
            removeLocation: function(row) {
                row.remove();
                this.checkEmptyTable();
            },
            
            handleGovernorateChange: function(select) {
                if (this.isInitializing) return;
                const row = select.closest('tr');
                // Skip cascade reset while sequential restore is running
                if (row && row.dataset.restoring === 'true') return;
                const $directorateSelect = $(row).find('.directorate-select');
                const $subAreaSelect = $(row).find('.sub-area-select');
                const $villageSelect = $(row).find('.village-select');
                const governorateValue = select.value;
                
                if (governorateValue === '0') {
                    this.setToAllAndDisable($directorateSelect, 'جميع المديريات');
                    this.setToAllAndDisable($subAreaSelect, 'جميع المناطق الفرعية');
                    this.setToAllAndDisable($villageSelect, 'جميع القرى والحارات');
                } else if (governorateValue) {
                    this.resetDropdown($directorateSelect, 'اختر المديرية', false);
                    this.resetDropdown($subAreaSelect, 'اختر المنطقة الفرعية', true);
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                    
                    // Fetch and populate directorates
                    this.fetchAndPopulate($directorateSelect, `${this.urls.directorates}/${governorateValue}`);
                } else {
                    this.resetDropdown($directorateSelect, 'اختر المديرية', true);
                    this.resetDropdown($subAreaSelect, 'اختر المنطقة الفرعية', true);
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                }
            },
            
            handleDirectorateChange: function(select) {
                if (this.isInitializing) return;
                const row = select.closest('tr');
                if (row && row.dataset.restoring === 'true') return;
                const $governorateSelect = $(row).find('.governorate-select');
                const $subAreaSelect = $(row).find('.sub-area-select');
                const $villageSelect = $(row).find('.village-select');
                const directorateValue = select.value;
                const governorateValue = $governorateSelect.val();
                
                if (directorateValue === '0') {
                    this.setToAllAndDisable($subAreaSelect, 'جميع المناطق الفرعية');
                    this.setToAllAndDisable($villageSelect, 'جميع القرى والحارات');
                } else if (directorateValue === 'other') {
                    // "غير ذلك" selected – clear dependants without fetching
                    this.resetDropdown($subAreaSelect, 'اختر المنطقة الفرعية', true);
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                } else if (directorateValue && governorateValue) {
                    this.resetDropdown($subAreaSelect, 'اختر المنطقة الفرعية', false);
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                    
                    // Fetch and populate sub-areas
                    this.fetchAndPopulate($subAreaSelect, `${this.urls.subAreas}/${governorateValue}/${directorateValue}`);
                } else {
                    this.resetDropdown($subAreaSelect, 'اختر المنطقة الفرعية', true);
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                }
            },
            
            handleSubAreaChange: function(select) {
                if (this.isInitializing) return;
                const row = select.closest('tr');
                if (row && row.dataset.restoring === 'true') return;
                const $governorateSelect = $(row).find('.governorate-select');
                const $directorateSelect = $(row).find('.directorate-select');
                const $villageSelect = $(row).find('.village-select');
                const subAreaValue = select.value;
                const governorateValue = $governorateSelect.val();
                const directorateValue = $directorateSelect.val();
                
                if (subAreaValue === '0') {
                    this.setToAllAndDisable($villageSelect, 'جميع القرى والحارات');
                } else if (subAreaValue === 'other') {
                    // "غير ذلك" selected – clear village without fetching
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                } else if (subAreaValue && governorateValue && directorateValue) {
                    this.resetDropdown($villageSelect, 'اختر القرية', false);
                    
                    // Fetch and populate villages
                    this.fetchAndPopulate($villageSelect, `${this.urls.villages}/${governorateValue}/${directorateValue}/${subAreaValue}`);
                } else {
                    this.resetDropdown($villageSelect, 'اختر القرية', true);
                }
            },
            
            fetchAndPopulate: function($select, url) {
                const placeholder = $select.find('option:first').text();
                $select.prop('disabled', true).empty().append(`<option value="">جاري التحميل...</option>`);
                
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2($select[0], true);
                }

                $.get(url)
                    .done((data) => {
                        $select.empty().append(`<option value="">${placeholder}</option>`);
                        
                        // Add "All" option if multiple results exist
                        if (data.length > 1) {
                            let allText = "الكل";
                            if ($select.hasClass('directorate-select')) allText = "جميع المديريات";
                            if ($select.hasClass('sub-area-select')) allText = "جميع المناطق الفرعية";
                            if ($select.hasClass('village-select')) allText = "جميع القرى والحارات";
                            $select.append(`<option value="0">${allText}</option>`);
                        }

                        data.forEach(item => {
                            $select.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                        $select.prop('disabled', false);
                    })
                    .fail((xhr) => {
                        console.error('Failed to fetch data from:', url, xhr);
                        $select.empty().append(`<option value="">خطأ في التحميل</option>`);
                    })
                    .always(() => {
                        if (typeof window.initGlobalSelect2 === 'function') {
                            window.initGlobalSelect2($select[0], true);
                        }
                        $select.trigger('change.select2');
                    });
            },

            resetDropdown: function($select, placeholder, disable) {
                $select.val('').prop('disabled', disable);
                
                // Clear and add initial options
                $select.empty().append(`<option value="">${placeholder}</option>`);
                
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2($select[0], true);
                }
                // No trigger('change') here to avoid loops
            },
            
            setToAllAndDisable: function($select, allText) {
                // Set to "All" (value 0)
                $select.empty().append(`<option value="">${allText.replace('جميع', 'اختر')}</option>`);
                $select.append(`<option value="0" selected>${allText}</option>`);
                $select.val('0').prop('disabled', true);
                
                if (typeof window.initGlobalSelect2 === 'function') {
                    window.initGlobalSelect2($select[0], true);
                }
                $select.trigger('change');
            },
            
            checkEmptyTable: function() {
                const tbody = document.querySelector('#projectLocationsTable tbody');
                if (tbody.children.length === 0) {
                    const empty = document.createElement('tr');
                    empty.className = 'project-empty-row';
                    empty.innerHTML = `<td colspan="5" class="text-center"><i class="fas fa-map-marker-alt fa-2x mb-2 text-muted"></i><br>لا توجد مواقع مضافة بعد</td>`;
                    tbody.appendChild(empty);
                }
            }
        };

        // Form submission handling to ensure disabled "All" selections are sent
        const form = document.querySelector('#projectLocationsTable').closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                document.querySelectorAll('.governorate-select, .directorate-select, .sub-area-select, .village-select').forEach(function(select) {
                    if (select.disabled && select.value === '0') {
                        select.disabled = false;
                    }
                });
            });
        }
        
        LocationManager.init();
        window.LocationManager = LocationManager;
    });
})();
</script>
