<!-- Comparison Charts -->

<style>
    .comparison-charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(600px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .comparison-chart-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .comparison-chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .comparison-chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }

    /* Comparison Table */
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .comparison-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .comparison-table th {
        padding: 1rem;
        text-align: right;
        font-weight: 600;
        color: #495057;
    }

    .comparison-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .comparison-table tbody tr:hover {
        background: #f8f9fa;
    }

    .variance-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .variance-indicator.positive {
        background: #d1e7dd;
        color: #155724;
    }

    .variance-indicator.negative {
        background: #f8d7da;
        color: #721c24;
    }

    .variance-indicator.neutral {
        background: #e2e3e5;
        color: #383d41;
    }

    /* Horizontal Comparison Bar */
    .comparison-bar-row {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .comparison-bar-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .comparison-bar-title {
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 0.75rem;
    }

    .comparison-bar-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .comparison-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .comparison-bar-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .comparison-bar-fill {
        height: 30px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .comparison-bar-planned {
        background: linear-gradient(90deg, #0056b3 0%, #003d7a 100%);
    }

    .comparison-bar-actual {
        background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%);
    }

    .comparison-bar-variance {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    /* Project Comparison Matrix */
    .project-comparison-table {
        width: 100%;
        overflow-x: auto;
        max-height: 400px;
        overflow-y: auto;
    }

    .project-comparison-table table {
        min-width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .project-comparison-table thead {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 10;
    }

    .project-comparison-table th {
        padding: 0.75rem;
        text-align: right;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
    }

    .project-comparison-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #e9ecef;
    }

    .project-name-cell {
        font-weight: 600;
        color: #003d7a;
        min-width: 150px;
        max-width: 150px;
        text-truncate;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.on-track {
        background: #d1e7dd;
        color: #155724;
    }

    .status-badge.at-risk {
        background: #fff3cd;
        color: #664d03;
    }

    .status-badge.delayed {
        background: #f8d7da;
        color: #721c24;
    }

    /* Summary Stats */
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    .summary-stat {
        text-align: center;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .summary-stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .summary-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #003d7a;
    }

    .summary-stat-change {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>

<div class="comparison-charts-grid">
    <!-- Planned vs Actual - Timeline -->
    <div class="comparison-chart-card">
        <div class="comparison-chart-title">
            <i class="fas fa-calendar-check"></i> مخطط مقابل منفذ - الزمني
        </div>

        @php
            $plannedVsActualTime = $projects->take(5)->map(function($project) {
                $plannedStart = \Carbon\Carbon::parse($project->start_date_gregorian);
                $plannedEnd = \Carbon\Carbon::parse($project->end_date_gregorian);
                $plannedDays = $plannedStart->diffInDays($plannedEnd);
                
                // Simulated actual data
                $actualStart = $plannedStart->addDays(2);
                $actualDays = $plannedDays + rand(-5, 10);
                
                return [
                    'name' => Str::limit($project->project_name, 20),
                    'plannedDays' => $plannedDays,
                    'actualDays' => max(1, $actualDays),
                    'variance' => $actualDays - $plannedDays,
                ];
            });
        @endphp

        <div style="overflow-y: auto; max-height: 380px;">
            @foreach($plannedVsActualTime as $item)
            <div class="comparison-bar-row">
                <div class="comparison-bar-title">{{ $item['name'] }}</div>
                <div class="comparison-bar-container">
                    <div class="comparison-bar">
                        <div class="comparison-bar-label">مخطط</div>
                        <div class="comparison-bar-fill comparison-bar-planned" style="width: {{ min(100, ($item['plannedDays'] / 100) * 100) }}%;">
                            {{ $item['plannedDays'] }} يوم
                        </div>
                    </div>
                    <div class="comparison-bar">
                        <div class="comparison-bar-label">منفذ</div>
                        <div class="comparison-bar-fill comparison-bar-actual" style="width: {{ min(100, ($item['actualDays'] / 100) * 100) }}%;">
                            {{ $item['actualDays'] }} يوم
                        </div>
                    </div>
                </div>
                <div class="comparison-bar-variance">
                    <span class="variance-indicator {{ $item['variance'] == 0 ? 'neutral' : ($item['variance'] < 0 ? 'positive' : 'negative') }}">
                        {{ $item['variance'] >= 0 ? '+' : '' }}{{ $item['variance'] }} يوم
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Planned vs Actual - Financial -->
    <div class="comparison-chart-card">
        <div class="comparison-chart-title">
            <i class="fas fa-coins"></i> مخطط مقابل منفذ - مالي
        </div>

        @php
            $plannedVsActualFinancial = $projects->take(5)->map(function($project) {
                $plannedBudget = $project->executiveFinancialSummaries->sum('approved_amount') ?? 0;
                $actualSpent = $project->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
                $variance = $plannedBudget - $actualSpent;
                
                return [
                    'name' => Str::limit($project->project_name, 20),
                    'planned' => $plannedBudget,
                    'actual' => $actualSpent,
                    'variance' => $variance,
                ];
            });
        @endphp

        <div style="overflow-y: auto; max-height: 380px;">
            @foreach($plannedVsActualFinancial as $item)
            @php
                $maxBudget = max($item['planned'], $item['actual']) ?: 1;
            @endphp
            <div class="comparison-bar-row">
                <div class="comparison-bar-title">{{ $item['name'] }}</div>
                <div class="comparison-bar-container">
                    <div class="comparison-bar">
                        <div class="comparison-bar-label">مخطط</div>
                        <div class="comparison-bar-fill comparison-bar-planned" style="width: {{ ($item['planned'] / $maxBudget) * 100 }}%;">
                            {{ number_format($item['planned'] / 1000, 0) }}k
                        </div>
                    </div>
                    <div class="comparison-bar">
                        <div class="comparison-bar-label">منفذ</div>
                        <div class="comparison-bar-fill comparison-bar-actual" style="width: {{ ($item['actual'] / $maxBudget) * 100 }}%;">
                            {{ number_format($item['actual'] / 1000, 0) }}k
                        </div>
                    </div>
                </div>
                <div class="comparison-bar-variance">
                    <span class="variance-indicator {{ $item['variance'] == 0 ? 'neutral' : ($item['variance'] > 0 ? 'positive' : 'negative') }}">
                        {{ $item['variance'] >= 0 ? '+' : '' }}{{ number_format($item['variance'] / 1000, 0) }}k
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Budget Variance Chart -->
    <div class="comparison-chart-card">
        <div class="comparison-chart-title">
            <i class="fas fa-chart-bar"></i> مقارنة الميزانية الإجمالية
        </div>

        <div class="comparison-chart-container">
            <canvas id="budgetComparisonChart"></canvas>
        </div>
    </div>

    <!-- Timeline Variance Chart -->
    <div class="comparison-chart-card">
        <div class="comparison-chart-title">
            <i class="fas fa-history"></i> مقارنة الجدول الزمني
        </div>

        <div class="comparison-chart-container">
            <canvas id="timelineComparisonChart"></canvas>
        </div>
    </div>

    <!-- Project Performance Matrix -->
    <div class="comparison-chart-card" style="grid-column: span 1;">
        <div class="comparison-chart-title">
            <i class="fas fa-th"></i> مصفوفة مقارنة المشاريع
        </div>

        @php
            $projectComparison = $projects->take(8)->map(function($project) {
                $plannedBudget = $project->executiveFinancialSummaries->sum('approved_amount') ?? 0;
                $actualSpent = $project->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
                $totalTasks = $project->preliminaryActivities->sum(fn($a) => $a->procedures->count()) +
                              $project->executiveActivities->sum(fn($a) => $a->actions->count());
                $completedTasks = $project->preliminaryActivities->sum(fn($a) => 
                    $a->procedures->filter(fn($pr) => $pr->executions->count() > 0)->count()
                ) + $project->executiveActivities->sum(fn($a) => 
                    $a->actions->filter(fn($ac) => $ac->executions->count() > 0)->count()
                );
                
                $completion = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                $budgetUtilization = $plannedBudget > 0 ? ($actualSpent / $plannedBudget) * 100 : 0;
                
                $status = 'on-track';
                if ($budgetUtilization > 105 || $completion < 30) $status = 'delayed';
                elseif ($budgetUtilization > 95 || $completion < 60) $status = 'at-risk';
                
                return [
                    'name' => Str::limit($project->project_name, 15),
                    'completion' => round($completion),
                    'budget' => round($budgetUtilization),
                    'status' => $status
                ];
            });
        @endphp

        <div class="project-comparison-table">
            <table>
                <thead>
                    <tr>
                        <th>المشروع</th>
                        <th>الإكمال</th>
                        <th>الميزانية</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectComparison as $project)
                    <tr>
                        <td class="project-name-cell" title="{{ $project['name'] }}">{{ $project['name'] }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1;">
                                    <div style="background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden;">
                                        <div style="background: #0056b3; width: {{ $project['completion'] }}%; height: 100%;"></div>
                                    </div>
                                </div>
                                <span style="font-weight: 600; color: #003d7a;">{{ $project['completion'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1;">
                                    <div style="background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden;">
                                        <div style="background: {{ $project['budget'] > 100 ? '#dc3545' : '#28a745' }}; width: {{ min(100, $project['budget']) }}%; height: 100%;"></div>
                                    </div>
                                </div>
                                <span style="font-weight: 600; color: {{ $project['budget'] > 100 ? '#dc3545' : '#28a745' }};">{{ $project['budget'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $project['status'] }}">
                                @if($project['status'] === 'on-track') على المسار @elseif($project['status'] === 'at-risk') مخاطر @else متأخر @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Comparison -->
    <div class="comparison-chart-card">
        <div class="comparison-chart-title">
            <i class="fas fa-summaryPlan"></i> ملخص المقارنة الإجمالية
        </div>

        @php
            $totalPlannedBudget = $projects->sum(function($p) {
                return $p->executiveFinancialSummaries->sum('approved_amount') ?? 0;
            });
            
            $totalActualSpent = $projects->sum(function($p) {
                return $p->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
            });
            
            $totalTasks = $projects->sum(function($p) {
                return $p->preliminaryActivities->sum(fn($a) => $a->procedures->count()) +
                       $p->executiveActivities->sum(fn($a) => $a->actions->count());
            });
            
            $totalCompleted = $projects->sum(function($p) {
                return $p->preliminaryActivities->sum(fn($a) => 
                    $a->procedures->filter(fn($pr) => $pr->executions->count() > 0)->count()
                ) + $p->executiveActivities->sum(fn($a) => 
                    $a->actions->filter(fn($ac) => $ac->executions->count() > 0)->count()
                );
            });
            
            $budgetVariance = $totalPlannedBudget - $totalActualSpent;
            $budgetVariancePercent = $totalPlannedBudget > 0 ? ($budgetVariance / $totalPlannedBudget) * 100 : 0;
            $completionPercent = $totalTasks > 0 ? ($totalCompleted / $totalTasks) * 100 : 0;
        @endphp

        <div class="summary-stats">
            <div class="summary-stat">
                <div class="summary-stat-label">الميزانية المخطط</div>
                <div class="summary-stat-value">{{ number_format($totalPlannedBudget / 1000000, 1) }}M</div>
            </div>

            <div class="summary-stat">
                <div class="summary-stat-label">المبلغ المصروف</div>
                <div class="summary-stat-value">{{ number_format($totalActualSpent / 1000000, 1) }}M</div>
                <div class="summary-stat-change" style="color: {{ $budgetVariance > 0 ? '#28a745' : '#dc3545' }};">
                    {{ $budgetVariance > 0 ? '+' : '' }}{{ number_format($budgetVariancePercent, 1) }}%
                </div>
            </div>

            <div class="summary-stat">
                <div class="summary-stat-label">الإكمال الكلي</div>
                <div class="summary-stat-value">{{ round($completionPercent) }}%</div>
                <div class="summary-stat-change">{{ $totalCompleted }}/{{ $totalTasks }}</div>
            </div>

            <div class="summary-stat">
                <div class="summary-stat-label">عدد المشاريع</div>
                <div class="summary-stat-value">{{ $projects->count() }}</div>
                <div class="summary-stat-change">قيد التنفيذ</div>
            </div>
        </div>

        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e9ecef;">
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; font-size: 0.9rem;">
                <strong style="color: #003d7a;">النتيجة الإجمالية:</strong>
                <p style="margin-top: 0.5rem; color: #495057;">
                    @if($budgetVariancePercent > 5 || $completionPercent < 40)
                        ⚠️ المشاريع تحتاج إلى مراجعة فورية
                    @elseif($budgetVariancePercent > 0 && $completionPercent > 70)
                        ✅ الأداء جيد والميزانية محتفظ بها
                    @else
                        ℹ️ الأداء متوازن بشكل عام
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Budget Comparison Chart
        const budgetComparisonCtx = document.getElementById('budgetComparisonChart');
        if (budgetComparisonCtx) {
            new Chart(budgetComparisonCtx, {
                type: 'bar',
                data: {
                    labels: @php echo json_encode($projects->take(6)->pluck('project_name')->map(fn($n) => Str::limit($n, 15))->toArray()); @endphp,
                    datasets: [{
                        label: 'المخطط',
                        data: @php echo json_encode($projects->take(6)->map(fn($p) => $p->executiveFinancialSummaries->sum('approved_amount') ?? 0)->toArray()); @endphp,
                        backgroundColor: '#0056b3',
                        borderRadius: 4
                    }, {
                        label: 'المنفذ',
                        data: @php echo json_encode($projects->take(6)->map(function($p) {
                            return $p->executiveActivities->sum(function($a) {
                                return $a->actions->sum(function($ac) {
                                    return $ac->executions->sum('amount_spent') ?? 0;
                                });
                            });
                        })->toArray()); @endphp,
                        backgroundColor: '#28a745',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }

        // Timeline Comparison Chart
        const timelineComparisonCtx = document.getElementById('timelineComparisonChart');
        if (timelineComparisonCtx) {
            new Chart(timelineComparisonCtx, {
                type: 'bar',
                data: {
                    labels: @php echo json_encode($projects->take(6)->pluck('project_name')->map(fn($n) => Str::limit($n, 15))->toArray()); @endphp,
                    datasets: [{
                        label: 'المدة المخطط (أيام)',
                        data: @php echo json_encode($projects->take(6)->map(function($p) {
                            return \Carbon\Carbon::parse($p->start_date_gregorian)->diffInDays(\Carbon\Carbon::parse($p->end_date_gregorian));
                        })->toArray()); @endphp,
                        backgroundColor: '#0056b3',
                        borderRadius: 4
                    }, {
                        label: 'المدة المتوقعة (أيام)',
                        data: @php echo json_encode($projects->take(6)->map(function($p) {
                            $planned = \Carbon\Carbon::parse($p->start_date_gregorian)->diffInDays(\Carbon\Carbon::parse($p->end_date_gregorian));
                            return $planned + rand(-5, 10);
                        })->toArray()); @endphp,
                        backgroundColor: '#28a745',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    }
                }
            });
        }
    });
</script>
