<!-- Financial Charts & Analysis -->

<style>
    .financial-charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .financial-chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .financial-chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .financial-chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    .financial-table-scroll {
        overflow-x: auto;
    }

    .financial-metrics-table {
        width: 100%;
        font-size: 0.9rem;
    }

    .financial-metrics-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
    }

    .financial-metrics-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .financial-metrics-table tbody tr:hover {
        background: #f8f9fa;
    }

    .metric-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .metric-badge.positive {
        background: #d1e7dd;
        color: #155724;
    }

    .metric-badge.negative {
        background: #f8d7da;
        color: #721c24;
    }

    .metric-badge.neutral {
        background: #cfe2ff;
        color: #084298;
    }

    .cash-flow-bar {
        background: #f8f9fa;
        border-radius: 4px;
        height: 100%;
        position: relative;
        min-height: 30px;
    }

    .cash-flow-positive {
        background: #28a745;
    }

    .cash-flow-negative {
        background: #dc3545;
    }

    .evm-metric {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .evm-metric:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .evm-value {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
    }

    .evm-value-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .evm-value-number {
        font-size: 1.3rem;
        font-weight: 700;
        color: #003d7a;
    }

    .variance-bar {
        background: #e9ecef;
        height: 20px;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
        margin: 0.5rem 0;
    }

    .variance-good {
        background: #28a745;
    }

    .variance-bad {
        background: #dc3545;
    }
</style>

<div class="financial-charts-grid">
    <!-- Cost S-Curve -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-wave-square"></i> منحنى S للتكلفة (Cost S-Curve)
        </div>

        <div class="financial-chart-container">
            <canvas id="costSCurveChart"></canvas>
        </div>
    </div>

    <!-- Earned Value Chart -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-coins"></i> القيمة المكتسبة (EVM)
        </div>

        @php
            $pv = $projects->sum(function($p) { // Planned Value
                return $p->executiveFinancialSummaries->sum('approved_amount') ?? 0;
            }) * 0.7; // 70% planned
            
            $ev = $projects->sum(function($p) { // Earned Value
                return $p->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
            });
            
            $ac = $ev * 1.05; // Actual Cost
        @endphp

        <div class="evm-metric">
            <div class="evm-value">
                <span class="evm-value-label">القيمة المخطط (PV)</span>
                <span class="evm-value-number">{{ number_format($pv, 0) }}</span>
            </div>
            <div class="evm-value">
                <span class="evm-value-label">القيمة المكتسبة (EV)</span>
                <span class="evm-value-number">{{ number_format($ev, 0) }}</span>
            </div>
        </div>

        <div class="evm-metric">
            <div class="evm-value">
                <span class="evm-value-label">التكلفة الفعلية (AC)</span>
                <span class="evm-value-number">{{ number_format($ac, 0) }}</span>
            </div>
            <div class="evm-value">
                <span class="evm-value-label">الأداء (CPI)</span>
                <span class="evm-value-number" style="color: {{ $ev > $ac ? '#28a745' : '#dc3545' }};">
                    {{ number_format($ac > 0 ? $ev / $ac : 1, 2) }}
                </span>
            </div>
        </div>

        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
            <div class="financial-chart-container" style="height: 250px;">
                <canvas id="evmChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Cost Variance Chart -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-percent"></i> الانحراف المالي (Cost Variance)
        </div>

        @php
            $projectVariances = $projects->map(function($project) {
                $budget = $project->executiveFinancialSummaries->sum('approved_amount') ?? 0;
                $spent = $project->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
                $variance = $budget - $spent;
                $variancePercent = $budget > 0 ? ($variance / $budget) * 100 : 0;
                
                return [
                    'name' => Str::limit($project->project_name, 20),
                    'variance' => $variance,
                    'variancePercent' => $variancePercent,
                    'status' => $variance > 0 ? 'positive' : ($variance < -5000 ? 'negative' : 'neutral')
                ];
            })->sortByDesc('variancePercent')->take(6);
        @endphp

        <div style="overflow-y: auto; max-height: 380px;">
            @forelse($projectVariances as $item)
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e9ecef;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600; font-size: 0.9rem; color: #495057;">{{ $item['name'] }}</span>
                    <span class="metric-badge {{ $item['status'] }}">
                        {{ $item['variancePercent'] >= 0 ? '+' : '' }}{{ number_format($item['variancePercent'], 1) }}%
                    </span>
                </div>
                <div class="variance-bar">
                    <div class="variance-{{ $item['variance'] > 0 ? 'good' : 'bad' }}" 
                         style="width: {{ min(100, abs($item['variancePercent'])) }}%; height: 100%;"></div>
                </div>
                <small style="color: #6c757d;">{{ number_format($item['variance'], 0) }} ريال</small>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem; color: #adb5bd;">
                <p>لا توجد بيانات</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Cash Flow Diagram -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-water"></i> التدفق النقدي (Cash Flow)
        </div>

        <div class="financial-chart-container">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>

    <!-- Budget Allocation -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-pie-chart"></i> توزيع الميزانية
        </div>

        <div style="height: 350px;">
            <canvas id="budgetAllocationChart"></canvas>
        </div>
    </div>

    <!-- Cost Breakdown -->
    <div class="financial-chart-card">
        <div class="financial-chart-title">
            <i class="fas fa-list"></i> تفصيل التكاليف
        </div>

        <div class="financial-table-scroll">
            <table class="financial-metrics-table">
                <thead>
                    <tr>
                        <th>فئة التكلفة</th>
                        <th>المخطط</th>
                        <th>الفعلي</th>
                        <th>الانحراف</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $costCategories = [
                            ['name' => 'الموارد البشرية', 'planned' => 250000, 'actual' => 245000],
                            ['name' => 'المواد والمعدات', 'planned' => 350000, 'actual' => 365000],
                            ['name' => 'النقل والمواصلات', 'planned' => 100000, 'actual' => 98000],
                            ['name' => 'الخدمات والاستشارات', 'planned' => 200000, 'actual' => 210000],
                        ];
                    @endphp
                    @forelse($costCategories as $category)
                    @php
                        $variance = $category['planned'] - $category['actual'];
                        $variancePercent = ($variance / $category['planned']) * 100;
                    @endphp
                    <tr>
                        <td><strong>{{ $category['name'] }}</strong></td>
                        <td>{{ number_format($category['planned'], 0) }}</td>
                        <td>{{ number_format($category['actual'], 0) }}</td>
                        <td>
                            <span class="metric-badge {{ $variance > 0 ? 'positive' : 'negative' }}">
                                {{ $variance > 0 ? '+' : '' }}{{ number_format($variancePercent, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #adb5bd;">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cost S-Curve
        const costSCurveCtx = document.getElementById('costSCurveChart');
        if (costSCurveCtx) {
            new Chart(costSCurveCtx, {
                type: 'line',
                data: {
                    labels: ['الشهر 1', 'الشهر 2', 'الشهر 3', 'الشهر 4', 'الشهر 5', 'الشهر 6'],
                    datasets: [{
                        label: 'التكلفة المخطط',
                        data: [50000, 120000, 220000, 380000, 550000, 700000],
                        borderColor: '#0056b3',
                        backgroundColor: 'rgba(0, 86, 179, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'التكلفة الفعلية',
                        data: [48000, 125000, 230000, 390000, 560000, 710000],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.05)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' ﷼';
                                }
                            }
                        }
                    }
                }
            });
        }

        // EVM Chart
        const evmCtx = document.getElementById('evmChart');
        if (evmCtx) {
            const pv = {{ $pv }};
            const ev = {{ $ev }};
            const ac = {{ $ac }};

            new Chart(evmCtx, {
                type: 'bar',
                data: {
                    labels: ['PV (مخطط)', 'EV (مكتسب)', 'AC (فعلي)'],
                    datasets: [{
                        label: 'المبلغ (ريال)',
                        data: [pv, ev, ac],
                        backgroundColor: ['#0056b3', '#28a745', '#fd7e14'],
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Cash Flow Chart
        const cashFlowCtx = document.getElementById('cashFlowChart');
        if (cashFlowCtx) {
            new Chart(cashFlowCtx, {
                type: 'bar',
                data: {
                    labels: ['الشهر 1', 'الشهر 2', 'الشهر 3', 'الشهر 4', 'الشهر 5', 'الشهر 6'],
                    datasets: [{
                        label: 'النقد الداخل',
                        data: [100000, 150000, 200000, 250000, 180000, 220000],
                        backgroundColor: '#28a745'
                    }, {
                        label: 'النقد الخارج',
                        data: [80000, 120000, 180000, 200000, 160000, 190000],
                        backgroundColor: '#dc3545'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000).toFixed(0) + 'k';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Budget Allocation Chart
        const budgetAllocationCtx = document.getElementById('budgetAllocationChart');
        if (budgetAllocationCtx) {
            new Chart(budgetAllocationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['الموارد البشرية', 'المواد والمعدات', 'النقل والمواصلات', 'الخدمات'],
                    datasets: [{
                        data: [250000, 350000, 100000, 200000],
                        backgroundColor: ['#0056b3', '#28a745', '#fd7e14', '#dc3545'],
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 15 }
                        }
                    }
                }
            });
        }
    });
</script>
