<script>
// API Unit Fetcher Helper
window.fetchUnitsFromApi = window.fetchUnitsFromApi || async function() {
    if (window.cachedApiUnits) return window.cachedApiUnits;
    if (window.fetchingApiUnits) return window.fetchingApiUnits;

    window.fetchingApiUnits = fetch("{{ route('units.api.fetch') }}")
        .then(response => response.json())
        .then(data => {
            const result = data.data || [];
            window.cachedApiUnits = result;
            return result;
        })
        .catch(err => {
            console.error('Error fetching units:', err);
            return [];
        });
    
    return window.fetchingApiUnits;
};


window.populateUnitSelect = window.populateUnitSelect || async function(selectElement, selectedValue) {
    if (!selectElement) return;
    
    // Set loading state if empty
    if (selectElement.options.length <= 1) {
        // Keep the placeholder if it exists
        const placeholder = selectElement.querySelector('option[value=""]');
        selectElement.innerHTML = '';
        if (placeholder) selectElement.appendChild(placeholder);
        
        const loadingOpt = document.createElement('option');
        loadingOpt.text = 'جاري التحميل...';
        loadingOpt.disabled = true;
        selectElement.appendChild(loadingOpt);
    }
    
    const units = await window.fetchUnitsFromApi();
    
    // Reset options
    let optionsHtml = '<option value="">اختر الوحدة</option>';
    
    if (units && units.length > 0) {
        units.forEach(unit => {
            // Check if selected. Unit ID from API vs saved ID.
            // Use 'name' as value (ID in Frappe)
            const value = unit.name; 
            const label = unit.uom_name || unit.name;
            const isSelected = selectedValue && (String(selectedValue) === String(value) || String(selectedValue) === String(unit.id));
            
            optionsHtml += `<option value="${value}" ${isSelected ? 'selected' : ''}>${label}</option>`;
        });
    } else {
        optionsHtml = '<option value="">فشل التحميل</option>';
    }
    
    selectElement.innerHTML = optionsHtml;
    
    // Initialize Select2 if jQuery and Select2 are available
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        const $select = jQuery(selectElement);
        if ($select.hasClass('select2-initialized')) {
            $select.select2('destroy');
        }
        
        $select.select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            width: '100%',
            dropdownParent: $select.closest('.modal').length ? $select.closest('.modal') : jQuery(document.body)
        }).addClass('select2-initialized');
    }
};

window.initializeSearchableSelects = function(container = document) {
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        // Target both .select2 and .select2-searchable, excluding those already initialized or specific unit selects
        const selects = container.querySelectorAll('.select2:not(.select2-initialized), .select2-searchable:not(.api-unit-select):not(.select2-initialized)');
        
        selects.forEach(select => {
            const $select = jQuery(select);
            const isMultiple = select.hasAttribute('multiple');
            
            $select.select2({
                theme: 'bootstrap-5',
                dir: 'rtl',
                width: '100%',
                placeholder: $select.data('placeholder') || 'اختر من القائمة',
                allowClear: true,
                dropdownParent: $select.closest('.modal').length ? $select.closest('.modal') : jQuery(document.body)
            }).addClass('select2-initialized');
        });
    }
};

window.initializeApiUnitSelects = function(container = document) {
    // Initialize general searchable selects first
    window.initializeSearchableSelects(container);
    
    // Then initialize unit selects (which will populate then init select2)
    const selects = container.querySelectorAll('.api-unit-select');
    selects.forEach(select => {
        window.populateUnitSelect(select, select.dataset.selectedUnit);
    });
};

// Also initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.initializeSearchableSelects();
});
</script>
