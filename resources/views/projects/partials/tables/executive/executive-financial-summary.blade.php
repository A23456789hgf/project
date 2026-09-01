<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">الملخص المالي التنفيذي</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
             <div class="col-md-6">
                <div class="d-flex justify-content-between border p-2 rounded bg-light">
                    <span>عدد الأنشطة التنفيذية:</span>
                    <span id="total-executive-activities-count" class="fw-bold text-primary">0</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between border p-2 rounded bg-light">
                    <span>عدد الإجراءات التنفيذية:</span>
                    <span id="total-executive-actions-count" class="fw-bold text-success">0</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered project-table">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">#</th>
                        <th>البند المالي</th>
                        <th class="text-center">إجمالي المبلغ</th>
                    </tr>
                </thead>
                <tbody id="executive-financial-summary-body">
                    <!-- Rows will be populated by JavaScript -->
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <th colspan="2" class="text-center">الإجمالي الكلي</th>
                        <th class="text-center" id="executive-financial-total">0.00</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initial calculation
    calculateExecutiveFinancialSummary();
    
    // Auto-update summary when costs change
    // We listen to input changes and also row removals
    document.addEventListener('input', function(e) {
        if (e.target.matches('.executive-cost-amount, .executive-cost-quantity, select[name$="[financial_item_id]"]')) {
            // Debounce for better performance
            clearTimeout(window.executiveSummaryTimer);
            window.executiveSummaryTimer = setTimeout(calculateExecutiveFinancialSummary, 500);
        }
    });

    // Listen for removals (custom event or checking on mutation)
    // Actually, calculateExecutiveTotals is called when rows are removed, 
    // we can hook into that or just listen for clicks on delete buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-executive-cost-btn, .delete-executive-action-btn, .btn-outline-danger')) {
            setTimeout(calculateExecutiveFinancialSummary, 600);
        }
    });
});

function calculateExecutiveFinancialSummary() {
    const financialItems = {};
    let grandTotal = 0;

    // Group by financial_item_id (numeric ID - stable identifier)
    document.querySelectorAll('.executive-cost-row').forEach(row => {
        const select = row.querySelector('select[name$="[financial_item_id]"]');
        const itemId = select ? select.value : '';
        const itemName = select && select.options[select.selectedIndex] ?
                         select.options[select.selectedIndex].text : '';
        
        // Use the same logic as calculateExecutiveTotals for line total
        const amount = parseFloat(row.querySelector('.executive-cost-amount')?.value) || 0;
        const quantity = parseFloat(row.querySelector('.executive-cost-quantity')?.value) || 0;
        const total = amount * quantity;

        if (itemId && itemName && total > 0) {
            if (!financialItems[itemId]) {
                financialItems[itemId] = { name: itemName, total: 0 };
            }
            financialItems[itemId].total += total;
        }
    });

    // Display one row per unique financial_item_id
    const tbody = document.getElementById('executive-financial-summary-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    let rowNumber = 1;
    const sortedItems = Object.values(financialItems).sort((a, b) => b.total - a.total);
    
    sortedItems.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">${rowNumber++}</td>
            <td>${item.name}</td>
            <td class="text-center">${item.total.toLocaleString('ar-SA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
        `;
        tbody.appendChild(row);
        grandTotal += item.total;
    });

    // If empty
    if (sortedItems.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">لا توجد تكاليف مضافة</td></tr>';
    }

    // Update grand total
    const totalDisplay = document.getElementById('executive-financial-total');
    if (totalDisplay) {
        totalDisplay.textContent = grandTotal.toLocaleString('ar-SA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        // Set raw value for the project cost calculator
        totalDisplay.dataset.rawValue = grandTotal;
    }

    // Count Executive Activities and Actions
    // Assuming standard class names for executive rows similar to preliminary ones
    // If not standard, we look for elements with specific attributes or classes used in executive/activity_actions.blade.php
    
    // Based on file list, we have: activity-row.blade.php (executive), activity-action-row.blade.php
    // Let's count them by querying the document
    const totalExecutiveActivities = document.querySelectorAll('.executive-activity-row').length;
    // Fallback if class is different
    const totalExecutiveActivitiesFallback = document.querySelectorAll('[name^="executive_activities"][name$="[name]"]').length;
    
    const totalExecutiveActions = document.querySelectorAll('.executive-action-row').length;
    // Fallback
    const totalExecutiveActionsFallback = document.querySelectorAll('[name^="executive_activities"][name*="[actions]"][name$="[name]"]').length;

    const finalActivitiesCount = totalExecutiveActivities || totalExecutiveActivitiesFallback;
    const finalActionsCount = totalExecutiveActions || totalExecutiveActionsFallback;

    const execActivityCounter = document.getElementById('total-executive-activities-count');
    const execActionCounter = document.getElementById('total-executive-actions-count');

    if (execActivityCounter) execActivityCounter.textContent = finalActivitiesCount;
    if (execActionCounter) execActionCounter.textContent = finalActionsCount;
}
</script>