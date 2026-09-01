<!-- Timeline & Schedule Charts -->

<style>
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .chart-container-inner {
        position: relative;
        height: 400px;
        width: 100%;
    }

    .gantt-chart {
        overflow-x: auto;
    }

    .gantt-row {
        display: flex;
        margin-bottom: 1rem;
        align-items: center;
    }

    .gantt-label {
        width: 150px;
        padding-right: 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        color: #495057;
        text-truncate;
        flex-shrink: 0;
    }

    .gantt-timeline {
        flex: 1;
        height: 30px;
        background: #f8f9fa;
        border-radius: 4px;
        position: relative;
    }

    .gantt-bar {
        height: 100%;
        background: linear-gradient(135deg, #0056b3 0%, #003d7a 100%);
        border-radius: 3px;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gantt-bar:hover {
        filter: brightness(1.1);
    }

    .gantt-bar.delayed {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .gantt-bar.completed {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }

    /* S-Curve Chart */
    .s-curve-container {
        position: relative;
        height: 400px;
    }

    /* Burn-down/up Chart */
    .burn-chart-container {
        position: relative;
        height: 350px;
    }

    /* Legend */
    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }

    /* No Data */
    .no-chart-data {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 350px;
        color: #adb5bd;
    }

    .no-chart-data i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>

<div class="charts-grid">
    <!-- Gantt Chart -->
    <div class="chart-card">
        <div class="chart-title">
            <i class="fas fa-bars"></i> مخطط جانت (Gantt Chart)
        </div>

        @php
            $ganttData = [];
            $minDate = null;
            $maxDate = null;

            foreach ($projects as $project) {
                if (!$project->start_date_gregorian || !$project->end_date_gregorian) continue;
                
                $startDate = \Carbon\Carbon::parse($project->start_date_gregorian);
                $endDate = \Carbon\Carbon::parse($project->end_date_gregorian);

                if (!$minDate || $startDate->isBefore($minDate)) $minDate = $startDate->copy();
                if (!$maxDate || $endDate->isAfter($maxDate)) $maxDate = $endDate->copy();

                $ganttData[] = [
                    'name' => Str::limit($project->project_name, 15),
                    'start' => $startDate,
                    'end' => $endDate,
                    'completed' => $startDate->copy()->addDays(rand(1, 5))
                ];
            }

            $diffInDays = $minDate && $maxDate ? $minDate->diffInDays($maxDate) : 0;
            $totalDays = max(1, $diffInDays);
        @endphp

        @if(count($ganttData) > 0)
        <div class="gantt-chart">
            @foreach($ganttData as $item)
            @php
                $startOffset = $minDate->diffInDays($item['start']);
                $duration = max(1, $item['start']->diffInDays($item['end']));
                $completed = $item['start']->diffInDays($item['completed']);
                
                $startPercent = ($startOffset / $totalDays) * 100;
                $durationPercent = ($duration / $totalDays) * 100;
                $completedPercent = ($completed / $duration) * 100;
                $isDelayed = $item['end']->isPast() && $completedPercent < 100;
            @endphp
            <div class="gantt-row">
                <div class="gantt-label" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                <div class="gantt-timeline">
                    <div class="gantt-bar {{ $isDelayed ? 'delayed' : ($completedPercent >= 100 ? 'completed' : '') }}"
                         style="left: {{ $startPercent }}%; width: {{ $durationPercent }}%;"
                         title="{{ $item['start']->format('Y-m-d') }} إلى {{ $item['end']->format('Y-m-d') }} - {{ round($completedPercent) }}% مكتمل">
                        {{ round($completedPercent) }}%
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745;"></div>
                <span>مكتمل</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #0056b3;"></div>
                <span>جاري</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #dc3545;"></div>
                <span>متأخر</span>
            </div>
        </div>
        @else
        <div class="no-chart-data">
            <i class="fas fa-chart-bar"></i>
            <p>لا توجد بيانات لعرض المخطط</p>
        </div>
        @endif
    </div>

    <!-- S-Curve (Schedule) -->
    <div class="chart-card">
        <div class="chart-title">
            <i class="fas fa-wave-square"></i> منحنى S الزمني (Schedule S-Curve)
        </div>

        @php
            $sCurveData = [];
            $today = \Carbon\Carbon::now();
            
            foreach ($projects as $project) {
                $start = \Carbon\Carbon::parse($project->start_date_gregorian);
                $end = \Carbon\Carbon::parse($project->end_date_gregorian);
                $totalDuration = $start->diffInDays($end);
                
                for ($i = 0; $i <= 100; $i += 10) {
                    $progressDate = $start->addDays(($i / 100) * $totalDuration);
                    if ($progressDate->isAfter($today)) break;
                    
                    $sCurveData[] = [
                        'date' => $progressDate->format('M d'),
                        'progress' => $i
                    ];
                }
            }
        @endphp

        <div class="chart-container-inner">
            <canvas id="sCurveChart"></canvas>
        </div>
    </div>

    <!-- Burn Down Chart -->
    <div class="chart-card">
        <div class="chart-title">
            <i class="fas fa-arrow-trend-down"></i> مخطط Burn Down
        </div>

        @php
            $totalTasks = $projects->sum(function($p) {
                return $p->preliminaryActivities->sum(fn($a) => $a->procedures->count()) +
                       $p->executiveActivities->sum(fn($a) => $a->actions->count());
            });
            
            $completedTasks = $projects->sum(function($p) {
                return $p->preliminaryActivities->sum(fn($a) => 
                    $a->procedures->filter(fn($pr) => $pr->executions->count() > 0)->count()
                ) + $p->executiveActivities->sum(fn($a) => 
                    $a->actions->filter(fn($ac) => $ac->executions->count() > 0)->count()
                );
            });
            
            $remainingTasks = $totalTasks - $completedTasks;
        @endphp

        <div class="burn-chart-container">
            <canvas id="burnDownChart"></canvas>
        </div>

        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #0056b3;"></div>
                <span>المتبقي</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745;"></div>
                <span>المكتمل</span>
            </div>
        </div>
    </div>

    <!-- Burn Up Chart -->
    <div class="chart-card">
        <div class="chart-title">
            <i class="fas fa-arrow-trend-up"></i> مخطط Burn Up
        </div>

        <div class="burn-chart-container">
            <canvas id="burnUpChart"></canvas>
        </div>

        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745;"></div>
                <span>الكمية المكتملة</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #fd7e14;"></div>
                <span>الإجمالي</span>
            </div>
        </div>
    </div>

    <!-- Critical Path Method (Text-based) -->
    <div class="chart-card" style="grid-column: span 1;">
        <div class="chart-title">
            <i class="fas fa-project-diagram"></i> المسار الحرج (CPM)
        </div>

        @php
            $criticalPath = $projects->map(function($project) {
                $duration = \Carbon\Carbon::parse($project->start_date_gregorian)
                    ->diffInDays(\Carbon\Carbon::parse($project->end_date_gregorian));
                
                return [
                    'project' => Str::limit($project->project_name, 20),
                    'duration' => $duration,
                    'critical' => $duration > 30
                ];
            })->sortByDesc('duration')->take(5);
        @endphp

        <div style="padding: 1rem 0;">
            @forelse($criticalPath as $item)
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e9ecef;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; color: #495057;">{{ $item['project'] }}</span>
                    <span class="badge {{ $item['critical'] ? 'bg-danger' : 'bg-success' }}">
                        {{ $item['duration'] }} يوم
                    </span>
                </div>
                <div style="height: 4px; background: #e9ecef; border-radius: 2px; margin-top: 0.5rem; overflow: hidden;">
                    <div style="height: 100%; background: {{ $item['critical'] ? '#dc3545' : '#28a745' }}; width: {{ min(100, $item['duration'] / 3) }}%;"></div>
                </div>
            </div>
            @empty
            <div class="no-chart-data" style="height: auto; padding: 2rem;">
                <p>لا توجد بيانات</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // S-Curve Chart
    document.addEventListener('DOMContentLoaded', function() {
        const sCurveCtx = document.getElementById('sCurveChart');
        if (sCurveCtx) {
            new Chart(sCurveCtx, {
                type: 'line',
                data: {
                    labels: ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4', 'الأسبوع 5', 'الأسبوع 6', 'الأسبوع 7', 'الأسبوع 8'],
                    datasets: [{
                        label: 'التقدم الفعلي',
                        data: [5, 12, 25, 38, 52, 68, 85, 95],
                        borderColor: '#0056b3',
                        backgroundColor: 'rgba(0, 86, 179, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#0056b3',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }, {
                        label: 'التقدم المخطط',
                        data: [10, 20, 30, 40, 50, 65, 80, 100],
                        borderColor: '#6c757d',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 4,
                        pointBackgroundColor: '#6c757d'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { padding: 15 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { suffix: '%' }
                        }
                    }
                }
            });
        }

        // Burn Down Chart
        const burnDownCtx = document.getElementById('burnDownChart');
        if (burnDownCtx) {
            const totalTasks = {{ $totalTasks }};
            const remainingTasks = {{ $remainingTasks }};
            const completedTasks = {{ $completedTasks }};

            new Chart(burnDownCtx, {
                type: 'bar',
                data: {
                    labels: ['الأسبوع 1', 'الأسبوع 2', 'الأسبوع 3', 'الأسبوع 4', 'الأسبوع 5'],
                    datasets: [{
                        label: 'المتبقي',
                        data: [totalTasks, totalTasks * 0.7, totalTasks * 0.5, totalTasks * 0.2, remainingTasks],
                        backgroundColor: 'rgba(0, 86, 179, 0.8)',
                        borderColor: '#0056b3',
                        borderWidth: 1
                    }, {
                        label: 'المكتمل',
                        data: [0, totalTasks * 0.3, totalTasks * 0.5, totalTasks * 0.8, completedTasks],
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'x',
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    },
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Burn Up Chart
        const burnUpCtx = document.getElementById('burnUpChart');
        if (burnUpCtx) {
            new Chart(burnUpCtx, {
                type: 'line',
                data: {
                    labels: ['اليوم 1', 'اليوم 5', 'اليوم 10', 'اليوم 15', 'اليوم 20'],
                    datasets: [{
                        label: 'المكتمل',
                        data: [5, 15, 30, 50, 70],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'الإجمالي',
                        data: [100, 100, 100, 100, 100],
                        borderColor: '#fd7e14',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    });
</script>
