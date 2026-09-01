<!-- Performance & Quality Charts -->

<style>
    .performance-charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .performance-chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .performance-chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .performance-chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    /* Pareto Chart */
    .pareto-bar-container {
        margin-bottom: 1.5rem;
    }

    .pareto-bar {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pareto-label {
        width: 120px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #495057;
        text-truncate;
    }

    .pareto-bar-fill {
        flex: 1;
        height: 25px;
        background: linear-gradient(90deg, #0056b3 0%, #0056b3 60%, #dc3545 60%, #dc3545 100%);
        border-radius: 4px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 10px;
        color: white;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .pareto-percentage {
        width: 50px;
        text-align: right;
        font-weight: 600;
        color: #003d7a;
    }

    /* Fishbone Diagram */
    .fishbone-container {
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #f8f9fa;
        font-size: 0.85rem;
    }

    .fishbone-main {
        text-align: center;
        padding: 0.75rem;
        background: #0056b3;
        color: white;
        border-radius: 4px;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .fishbone-category {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .fishbone-category:last-child {
        border-bottom: none;
    }

    .fishbone-category-title {
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 0.5rem;
    }

    .fishbone-items {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-left: 1rem;
    }

    .fishbone-item {
        padding: 0.5rem;
        background: white;
        border-left: 3px solid #0056b3;
        border-radius: 3px;
    }

    /* Control Chart */
    .control-line {
        position: absolute;
        width: 100%;
        height: 2px;
    }

    .ucl { background: #dc3545; top: 20%; }
    .lcl { background: #dc3545; top: 80%; }
    .mean { background: #0056b3; top: 50%; }

    /* KPI Dashboard */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .kpi-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.25rem;
        border-radius: 8px;
        border-left: 4px solid;
        text-align: center;
    }

    .kpi-card.excellent { border-left-color: #28a745; }
    .kpi-card.good { border-left-color: #17a2b8; }
    .kpi-card.warning { border-left-color: #fd7e14; }
    .kpi-card.critical { border-left-color: #dc3545; }

    .kpi-label {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        font-weight: 600;
    }

    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #003d7a;
        margin-bottom: 0.5rem;
    }

    .kpi-status {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 3px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .kpi-status.excellent {
        background: #d1e7dd;
        color: #155724;
    }

    .kpi-status.good {
        background: #cfe2ff;
        color: #084298;
    }

    .kpi-status.warning {
        background: #fff3cd;
        color: #664d03;
    }

    .kpi-status.critical {
        background: #f8d7da;
        color: #842029;
    }

    /* Quality Matrix */
    .quality-matrix {
        margin-top: 1rem;
    }

    .matrix-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .matrix-cell {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .matrix-cell:hover {
        transform: scale(1.05);
    }

    .matrix-cell.high { background: #28a745; }
    .matrix-cell.medium { background: #fd7e14; }
    .matrix-cell.low { background: #dc3545; }
</style>

<div class="performance-charts-grid">
    <!-- Pareto Chart -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-sort-amount-down"></i> مخطط باريتو (Pareto)
        </div>

        @php
            $paretoData = [
                ['issue' => 'التأخر الزمني', 'impact' => 45],
                ['issue' => 'نقص الموارد', 'impact' => 25],
                ['issue' => 'الأخطاء الفنية', 'impact' => 15],
                ['issue' => 'مشاكل التواصل', 'impact' => 10],
                ['issue' => 'أخرى', 'impact' => 5],
            ];
            $totalImpact = array_sum(array_column($paretoData, 'impact'));
            $cumulativePercent = 0;
        @endphp

        <div class="pareto-bar-container">
            @foreach($paretoData as $data)
            @php
                $cumulativePercent += ($data['impact'] / $totalImpact) * 100;
            @endphp
            <div class="pareto-bar">
                <div class="pareto-label" title="{{ $data['issue'] }}">{{ $data['issue'] }}</div>
                <div class="pareto-bar-fill" style="width: {{ ($data['impact'] / $totalImpact) * 100 }}%;">
                    {{ round($cumulativePercent) }}%
                </div>
                <div class="pareto-percentage">{{ $data['impact'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Fishbone Diagram -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-sitemap"></i> مخطط السبب والأثر (Fishbone)
        </div>

        <div class="fishbone-container">
            <div class="fishbone-main">المشكلة: تأخر المشروع</div>

            <div class="fishbone-category">
                <div class="fishbone-category-title">👥 الموارد البشرية</div>
                <div class="fishbone-items">
                    <div class="fishbone-item">نقص عدد العمال</div>
                    <div class="fishbone-item">عدم تدريب كافي</div>
                    <div class="fishbone-item">تغيير الفريق</div>
                </div>
            </div>

            <div class="fishbone-category">
                <div class="fishbone-category-title">⚙️ المواد والمعدات</div>
                <div class="fishbone-items">
                    <div class="fishbone-item">عدم توفر المعدات</div>
                    <div class="fishbone-item">أعطال في الآلات</div>
                    <div class="fishbone-item">جودة المواد الخام</div>
                </div>
            </div>

            <div class="fishbone-category">
                <div class="fishbone-category-title">📋 الطرق والعمليات</div>
                <div class="fishbone-items">
                    <div class="fishbone-item">خطة ضعيفة</div>
                    <div class="fishbone-item">تصميم غير فعال</div>
                    <div class="fishbone-item">عدم الامتثال للمعايير</div>
                </div>
            </div>

            <div class="fishbone-category">
                <div class="fishbone-category-title">🌍 العوامل الخارجية</div>
                <div class="fishbone-items">
                    <div class="fishbone-item">ظروف الطقس</div>
                    <div class="fishbone-item">تغييرات تنظيمية</div>
                    <div class="fishbone-item">تأثيرات اقتصادية</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Chart -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-sliders-h"></i> مخطط التحكم (Control Chart)
        </div>

        <div class="performance-chart-container">
            <div style="position: relative; height: 100%;">
                <div class="control-line ucl" title="الحد الأعلى (UCL)"></div>
                <div class="control-line mean" title="المتوسط"></div>
                <div class="control-line lcl" title="الحد الأدنى (LCL)"></div>
            </div>
            <canvas id="controlChart" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
        </div>
    </div>

    <!-- KPI Dashboard -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-tachometer-alt"></i> لوحة مؤشرات الأداء (KPI)
        </div>

        @php
            $kpis = [
                ['label' => 'إكمال الوقت', 'value' => '87%', 'status' => 'good'],
                ['label' => 'كفاءة التكلفة', 'value' => '92%', 'status' => 'excellent'],
                ['label' => 'جودة العمل', 'value' => '78%', 'status' => 'good'],
                ['label' => 'رضا الأطراف', 'value' => '85%', 'status' => 'good'],
                ['label' => 'درجة المخاطرة', 'value' => '35%', 'status' => 'warning'],
                ['label' => 'معدل الأخطاء', 'value' => '5%', 'status' => 'good'],
            ];
        @endphp

        <div class="kpi-grid">
            @foreach($kpis as $kpi)
            <div class="kpi-card {{ $kpi['status'] }}">
                <div class="kpi-label">{{ $kpi['label'] }}</div>
                <div class="kpi-value">{{ $kpi['value'] }}</div>
                <span class="kpi-status {{ $kpi['status'] }}">
                    @if($kpi['status'] === 'excellent') ممتاز @elseif($kpi['status'] === 'good') جيد @elseif($kpi['status'] === 'warning') تحذير @else حرج @endif
                </span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Quality Matrix -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-th"></i> مصفوفة الجودة
        </div>

        <div style="padding: 1rem; text-align: center; font-weight: 600; margin-bottom: 1rem;">
            مستويات الأداء حسب الأولوية
        </div>

        <div class="quality-matrix">
            <div class="matrix-row">
                <div class="matrix-cell high" title="الأداء: ممتاز">A1</div>
                <div class="matrix-cell high" title="الأداء: ممتاز">A2</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">A3</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">A4</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">A5</div>
            </div>
            <div class="matrix-row">
                <div class="matrix-cell high" title="الأداء: ممتاز">B1</div>
                <div class="matrix-cell high" title="الأداء: ممتاز">B2</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">B3</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">B4</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">B5</div>
            </div>
            <div class="matrix-row">
                <div class="matrix-cell medium" title="الأداء: متوسط">C1</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">C2</div>
                <div class="matrix-cell medium" title="الأداء: متوسط">C3</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">C4</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">C5</div>
            </div>
            <div class="matrix-row">
                <div class="matrix-cell medium" title="الأداء: متوسط">D1</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">D2</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">D3</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">D4</div>
                <div class="matrix-cell low" title="الأداء: ضعيف">D5</div>
            </div>
        </div>

        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e9ecef; font-size: 0.85rem; color: #6c757d;">
            <div style="margin-bottom: 0.5rem;"><i class="fas fa-square" style="color: #28a745;"></i> أداء ممتاز (90-100%)</div>
            <div style="margin-bottom: 0.5rem;"><i class="fas fa-square" style="color: #fd7e14;"></i> أداء متوسط (50-89%)</div>
            <div><i class="fas fa-square" style="color: #dc3545;"></i> أداء ضعيف (أقل من 50%)</div>
        </div>
    </div>

    <!-- Performance Trends -->
    <div class="performance-chart-card">
        <div class="performance-chart-title">
            <i class="fas fa-chart-line"></i> اتجاهات الأداء
        </div>

        <div class="performance-chart-container">
            <canvas id="performanceTrendChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Control Chart
        const controlCtx = document.getElementById('controlChart');
        if (controlCtx) {
            new Chart(controlCtx, {
                type: 'line',
                data: {
                    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                    datasets: [{
                        label: 'القيم المقاسة',
                        data: [95, 98, 94, 97, 99, 96, 95, 98, 94, 97],
                        borderColor: '#0056b3',
                        backgroundColor: 'rgba(0, 86, 179, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#0056b3'
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
                            beginAtZero: false,
                            min: 90,
                            max: 105
                        }
                    }
                }
            });
        }

        // Performance Trend Chart
        const performanceCtx = document.getElementById('performanceTrendChart');
        if (performanceCtx) {
            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4', 'الأسبوع 5', 'الأسبوع 6'],
                    datasets: [{
                        label: 'كفاءة التنفيذ',
                        data: [72, 75, 78, 82, 85, 87],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'جودة العمل',
                        data: [68, 71, 75, 78, 80, 82],
                        borderColor: '#0056b3',
                        backgroundColor: 'rgba(0, 86, 179, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'الرضا العام',
                        data: [70, 73, 76, 79, 82, 85],
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253, 126, 20, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
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
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    });
</script>
