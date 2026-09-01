<div class="financial-summary-container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-success text-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>ملخص التكاليف المالية
            </h5>
            <small class="text-white-50">إجمالي التكاليف حسب البنود المالية</small>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="min-width: 600px;">
                            <thead class="table-light">
                                <tr>
                                    <th>البند المالي</th>
                                    <th class="text-center">عدد المرات</th>
                                    <th class="text-end">الإجمالي</th>
                                    <th class="text-end">النسبة %</th>
                                </tr>
                            </thead>
                            <tbody id="financial-summary-tbody">
                                <!-- Summary rows will be inserted here -->
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th class="text-end">الإجمالي:</th>
                                    <th class="text-center" id="total-items-count">
                                        {{ isset($project) ? $project->preliminaryFinancialSummaries->count() : 0 }}
                                    </th>
                                    <th class="text-end" id="grand-total-cost" data-raw-value="{{ isset($project) ? $project->preliminaryFinancialSummaries->sum('aggregated_total') : 0 }}">
                                        {{ isset($project) ? number_format($project->preliminaryFinancialSummaries->sum('aggregated_total'), 2, '.', ',') : '0.00' }} ريال
                                    </th>
                                    <th class="text-end">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <canvas id="financialSummaryChart" width="400" height="400"></canvas>
                    </div>
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            توزيع التكاليف حسب البنود المالية
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                التوزيع حسب الأنشطة
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm" style="min-width: 400px;">
                                    <tbody id="activities-summary-tbody">
                                        <!-- Activities summary will be inserted here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-chart-line text-success me-2"></i>
                                الإحصائيات الرئيسية
                            </h6>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-label">متوسط تكلفة البند</div>
                                    <div class="stat-value text-primary" id="average-item-cost">0</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">أعلى بند تكلفة</div>
                                    <div class="stat-value text-success" id="highest-item-cost">0</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">أقل بند تكلفة</div>
                                    <div class="stat-value text-warning" id="lowest-item-cost">0</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">عدد البنود المستخدمة</div>
                                    <div class="stat-value text-info" id="unique-items-count">0</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">عدد الأنشطة</div>
                                    <div class="stat-value text-primary" id="total-activities-count">0</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">عدد الإجراءات</div>
                                    <div class="stat-value text-success" id="total-procedures-count">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        آخر تحديث: <span id="last-updated">الآن</span>
                    </small>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-success btn-sm" 
                            onclick="updateFinancialSummary()">
                        <i class="fas fa-sync-alt me-1"></i>تحديث الملخص
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" 
                            onclick="exportFinancialSummary()">
                        <i class="fas fa-download me-1"></i>تصدير
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .financial-summary-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }
    
    .chart-container {
        position: relative;
        height: 250px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-top: 15px;
    }
    
    .stat-item {
        background: white;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .stat-label {
        font-size: 12px;
        color: #718096;
        margin-bottom: 5px;
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: bold;
        color: #2d3748;
    }
    
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    
    .table th {
        font-weight: 600;
        color: #4a5568;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
    
    .text-end {
        text-align: left;
    }
    
    [dir="rtl"] .text-end {
        text-align: right;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let financialSummaryChart = null;
    
    function updateFinancialSummary() {
        // Collect all costs data
        const costsData = {};
        const activitiesData = {};
        
        // Sets for counting unique items
        const uniqueActivityNames = new Set();
        const uniqueProcedureNames = new Set();
        
        document.querySelectorAll('.cost-row').forEach(costRow => {
            const financialItemSelect = costRow.querySelector('.financial-item-select');
            const financialItemId = financialItemSelect.value;
            const financialItemName = financialItemSelect.selectedOptions[0].text;
            const amount = parseFloat(costRow.querySelector('.amount-input').value) || 0;
            const quantity = parseFloat(costRow.querySelector('.quantity-input').value) || 1;
            const total = amount * quantity;
            
            // Get activity name
            const activityRow = costRow.closest('.activity-details-row')
                .previousElementSibling;
            const activityName = activityRow.querySelector('[name*="[name]"]').value;
            
            // Get procedure context (unique identifier logic)
            // A procedure is identified by its parent activity index and its own index
            const procedureRow = costRow.closest('.procedure-details-row');
            // Assuming the previous sibling of procedure-details-row is the procedure-row which might have an index or name
            // For general counting, we can just count all procedure-row elements in the document
            
            // However, to be more localized to "what has costs", let's rely on indices
            const activityIndex = costRow.getAttribute('data-activity-index');
            const procedureIndex = costRow.getAttribute('data-procedure-index');
            
            if (activityName) uniqueActivityNames.add(activityName);
            uniqueProcedureNames.add(`${activityIndex}-${procedureIndex}`);

            // Aggregate by financial item
            if (!costsData[financialItemId]) {
                costsData[financialItemId] = {
                    name: financialItemName,
                    count: 0,
                    total: 0
                };
            }
            costsData[financialItemId].count++;
            costsData[financialItemId].total += total;
            
            // Aggregate by activity
            if (!activitiesData[activityName]) {
                activitiesData[activityName] = 0;
            }
            activitiesData[activityName] += total;
        });
        
        // Fallback: If no costs, we might still want to count activities/procedures that have been added to the DOM
        // unrelated to costs. But the user asked for "Actual number", implying confirmed items.
        // Let's count existing DOM elements for a broader "Actual number" view.
        const totalActivities = document.querySelectorAll('.activity-row').length;
        const totalProcedures = document.querySelectorAll('.procedure-row').length;

        // Update summary table
        const tbody = document.getElementById('financial-summary-tbody');
        const activitiesTbody = document.getElementById('activities-summary-tbody');
        
        let html = '';
        let activitiesHtml = '';
        let grandTotal = 0;
        let totalItems = 0;
        let uniqueItems = Object.keys(costsData).length;
        
        // Sort items by total (descending)
        const sortedItems = Object.entries(costsData).sort((a, b) => b[1].total - a[1].total);
        
        sortedItems.forEach(([itemId, data]) => {
            grandTotal += data.total;
            totalItems += data.count;
            
            const percentage = (data.total / (grandTotal || 1)) * 100;
            
            html += `
                <tr>
                    <td>${data.name}</td>
                    <td class="text-center">${data.count}</td>
                    <td class="text-end">${data.total.toLocaleString('ar-SA')} ريال</td>
                    <td class="text-end">${percentage.toFixed(1)}%</td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        // Update activities summary
        Object.entries(activitiesData).forEach(([activityName, total]) => {
            const percentage = (total / (grandTotal || 1)) * 100;
            activitiesHtml += `
                <tr>
                    <td>${activityName}</td>
                    <td class="text-end">${total.toLocaleString('ar-SA')} ريال</td>
                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 style="width: ${percentage}%"
                                 role="progressbar"></div>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        activitiesTbody.innerHTML = activitiesHtml;
        
        // Update totals
        document.getElementById('total-items-count').textContent = totalItems;
        document.getElementById('grand-total-cost').textContent = 
            grandTotal.toLocaleString('ar-SA') + ' ريال';
            // Set raw value for the project cost calculator
        document.getElementById('grand-total-cost').dataset.rawValue = grandTotal;
        
        // Update statistics
        const averageCost = grandTotal / (totalItems || 1);
        const highestCost = sortedItems.length > 0 ? sortedItems[0][1].total : 0;
        const lowestCost = sortedItems.length > 0 ? sortedItems[sortedItems.length - 1][1].total : 0;
        
        document.getElementById('average-item-cost').textContent = 
            averageCost.toLocaleString('ar-SA', { minimumFractionDigits: 2 }) + ' ريال';
        document.getElementById('highest-item-cost').textContent = 
            highestCost.toLocaleString('ar-SA') + ' ريال';
        document.getElementById('lowest-item-cost').textContent = 
            lowestCost.toLocaleString('ar-SA') + ' ريال';
        document.getElementById('unique-items-count').textContent = uniqueItems;
        
        // Update counters
        const activityCounter = document.getElementById('total-activities-count');
        const procedureCounter = document.getElementById('total-procedures-count');

        if(activityCounter) activityCounter.textContent = totalActivities;
        if(procedureCounter) procedureCounter.textContent = totalProcedures;
        
        // Update chart
        updateChart(sortedItems.map(([_, data]) => data), grandTotal);
        
        // Update timestamp
        const now = new Date();
        document.getElementById('last-updated').textContent = 
            now.toLocaleTimeString('ar-SA');
        
    }
    
    function updateChart(itemsData, grandTotal) {
        const ctx = document.getElementById('financialSummaryChart').getContext('2d');
        
        if (financialSummaryChart) {
            financialSummaryChart.destroy();
        }
        
        const labels = itemsData.map(item => item.name);
        const data = itemsData.map(item => item.total);
        const percentages = itemsData.map(item => (item.total / grandTotal * 100).toFixed(1));
        
        // Generate colors
        const backgroundColors = generateColors(itemsData.length);
        
        financialSummaryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const percentage = (value / grandTotal * 100).toFixed(1);
                                return `${context.label}: ${value.toLocaleString('ar-SA')} ريال (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    function generateColors(count) {
        const colors = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#06B6D4', '#84CC16', '#F97316', '#6366F1', '#EC4899',
            '#14B8A6', '#84CC16', '#F43F5E', '#8B5CF6', '#0EA5E9'
        ];
        
        return colors.slice(0, count).concat(
            Array(Math.max(0, count - colors.length)).fill().map(() => 
                `#${Math.floor(Math.random()*16777215).toString(16)}`
            )
        );
    }
    
    function exportFinancialSummary() {
        // Create CSV content
        let csv = 'البند المالي,عدد المرات,الإجمالي,النسبة%\n';
        
        document.querySelectorAll('#financial-summary-tbody tr').forEach(row => {
            const cols = row.querySelectorAll('td');
            if (cols.length >= 4) {
                const item = cols[0].textContent;
                const count = cols[1].textContent;
                const total = cols[2].textContent.replace(/[^0-9.]/g, '');
                const percentage = cols[3].textContent.replace('%', '');
                
                csv += `"${item}",${count},${total},${percentage}\n`;
            }
        });
        
        // Create and download file
        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `ملخص_التكاليف_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Auto-update summary when costs change
    document.addEventListener('input', function(e) {
        if (e.target.matches('.amount-input, .quantity-input, .financial-item-select')) {
            setTimeout(updateFinancialSummary, 500);
        }
    });
    
    // Initial update
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(updateFinancialSummary, 1000);
    });
</script>
@endpush