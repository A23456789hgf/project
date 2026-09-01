<div class="form-section">
    <h3>Project Cost</h3>

    <div class="row">
        <div class="form-group col-md-6" id="hijri_year_wrapper">
            <label>السنة الهجرية</label>
            <input type="number" name="project_cost[hijri_year]" 
                   value="{{ old('project_cost.hijri_year', $project->cost->hijri_year ?? '') }}" class="form-control" placeholder="مثال: 1445">
        </div>
        <div class="form-group col-md-6" id="total_cost_wrapper">
            <label>إجمالي تكلفة المشروع</label>
            <input type="number" step="0.01" name="project_cost[total_cost]" 
                   id="total_project_cost"
                   value="{{ old('project_cost.total_cost', $project->cost->total_cost ?? '') }}" class="form-control">
        </div>
        <div class="form-group col-md-3 old-project-cost-field" id="spent_amount_wrapper" style="display: none;">
            <label>المبلغ المصروف</label>
            <input type="number" step="0.01" name="project_cost[spent_amount]" 
                   id="spent_project_amount"
                   value="{{ old('project_cost.spent_amount', $project->cost->spent_amount ?? '') }}" class="form-control" placeholder="0.00">
        </div>
        <div class="form-group col-md-3 old-project-cost-field" id="remaining_amount_wrapper" style="display: none;">
            <label>المبلغ المتبقي</label>
            <input type="number" step="0.01" name="project_cost[remaining_amount]" 
                   id="remaining_project_amount"
                   value="{{ old('project_cost.remaining_amount', $project->cost->remaining_amount ?? '') }}" class="form-control" readonly tabindex="-1" style="background-color: #e9ecef; cursor: not-allowed;">
        </div>
    </div>
</div>

<script>
function calculateRemainingProjectCost() {
    const totalInput = document.getElementById('total_project_cost');
    const spentInput = document.getElementById('spent_project_amount');
    const remainingInput = document.getElementById('remaining_project_amount');

    if (!totalInput || !spentInput || !remainingInput) return;

    const total = parseFloat(totalInput.value) || 0;
    const spent = parseFloat(spentInput.value) || 0;
    const remaining = total - spent;

    remainingInput.value = remaining.toFixed(2);
}

function toggleOldProjectCostFields() {
    const oldRadio = document.querySelector('input[name="project_type"][value="old"]');
    const isOld = oldRadio ? oldRadio.checked : false;

    const spentWrapper = document.getElementById('spent_amount_wrapper');
    const remainingWrapper = document.getElementById('remaining_amount_wrapper');
    const hijriWrapper = document.getElementById('hijri_year_wrapper');
    const totalWrapper = document.getElementById('total_cost_wrapper');
    const totalInput = document.getElementById('total_project_cost');

    if (isOld) {
        if (spentWrapper) spentWrapper.style.display = 'block';
        if (remainingWrapper) remainingWrapper.style.display = 'block';
        if (hijriWrapper) {
            hijriWrapper.style.display = 'block';
            hijriWrapper.className = 'form-group col-md-3';
        }
        if (totalWrapper) totalWrapper.className = 'form-group col-md-3';
        if (totalInput) {
            totalInput.readOnly = false;
            totalInput.style.backgroundColor = '';
            totalInput.style.cursor = '';
        }
        calculateRemainingProjectCost();
    } else {
        if (spentWrapper) spentWrapper.style.display = 'none';
        if (remainingWrapper) remainingWrapper.style.display = 'none';
        if (hijriWrapper) {
            hijriWrapper.style.display = 'none';
            // Clear value so it doesn't get submitted accidentally
            const hijriInput = hijriWrapper.querySelector('input');
            if (hijriInput) hijriInput.value = '';
        }
        if (totalWrapper) totalWrapper.className = 'form-group col-md-6';
        if (totalInput) {
            totalInput.readOnly = true;
            totalInput.style.backgroundColor = '#e9ecef';
            totalInput.style.cursor = 'not-allowed';
        }
        calculateTotalProjectCost();
    }
    if (typeof toggleOldProjectFinancingSection === 'function') {
        toggleOldProjectFinancingSection(isOld);
    }
}

function calculateTotalProjectCost() {
    const oldRadio = document.querySelector('input[name="project_type"][value="old"]');
    if (oldRadio && oldRadio.checked) {
        calculateRemainingProjectCost();
        return;
    }

    // Helper to get raw value from element
    const getVal = (id) => {
        const el = document.getElementById(id);
        if (!el) return 0;
        // Check dataset first for raw numeric value
        if (el.dataset.rawValue !== undefined) {
            return parseFloat(el.dataset.rawValue) || 0;
        }
        // Fallback for formatted text (e.g. 1,234.56 ريال)
        // Remove 'ريال', commas and any non-numeric chars except . and -
        const clean = el.textContent
            .replace(/ريال/g, '')
            .replace(/[^\d.-]/g, '');
        return parseFloat(clean) || 0;
    };

    // Updated IDs to match the ones in financial summary files
    const preliminaryTotal = getVal('total-cost');
    const executiveTotal = getVal('executive-total-cost');

    const total = preliminaryTotal + executiveTotal;
    
    const totalInput = document.getElementById('total_project_cost');
    if (totalInput) {
        totalInput.value = total.toFixed(2);
        // Dispatch event to trigger financing ratio calculation
        totalInput.dispatchEvent(new Event('input', { bubbles: true }));
        totalInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleOldProjectCostFields();
    calculateTotalProjectCost();
    
    // Listen for project type change
    document.addEventListener('change', function(e) {
        if (e.target && e.target.name === 'project_type') {
            toggleOldProjectCostFields();
        }
    });

    // Listen for inputs on total cost or spent amount to update remaining
    document.addEventListener('input', function(e) {
        if (e.target && (e.target.id === 'total_project_cost' || e.target.id === 'spent_project_amount')) {
            calculateRemainingProjectCost();
        }
    });
    
    // Listen for changes in the displayed totals (using MutationObserver is best since they are spans)
    const observer = new MutationObserver(function() {
        calculateTotalProjectCost();
    });
    
    const preliminaryDisplay = document.getElementById('total-cost');
    const executiveDisplay = document.getElementById('executive-total-cost');
    
    if (preliminaryDisplay) {
        observer.observe(preliminaryDisplay, { childList: true, characterData: true, subtree: true });
    }
    
    if (executiveDisplay) {
        observer.observe(executiveDisplay, { childList: true, characterData: true, subtree: true });
    }
});
</script>
