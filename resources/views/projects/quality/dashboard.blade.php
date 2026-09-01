@extends('layouts.app')

@section('styles')
<style>
    .implementation-dashboard-container {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 2rem;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-filters {
        display: flex;
        gap: 0.5rem;
    }

    /* Statistics Cards */
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 1.5rem;
        align-items: center;
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .budget-card { border-left-color: #007bff; }
    .spent-card { border-left-color: #28a745; }
    .remaining-card { border-left-color: #17a2b8; }
    .remaining-card.negative { border-left-color: #dc3545; }
    .progress-card { border-left-color: #fd7e14; }

    .stat-icon {
        font-size: 2.5rem;
        color: #007bff;
        min-width: 60px;
        text-align: center;
    }

    .spent-card .stat-icon { color: #28a745; }
    .remaining-card .stat-icon { color: #17a2b8; }
    .remaining-card.negative .stat-icon { color: #dc3545; }
    .progress-card .stat-icon { color: #fd7e14; }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #003d7a;
    }

    .stat-currency, .stat-percentage {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .spent-percentage { color: #28a745; font-weight: 600; }

    .stat-alert {
        margin-top: 0.5rem;
    }

    .progress-bar-mini {
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        margin-top: 0.75rem;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #fd7e14, #28a745);
        border-radius: 2px;
    }

    /* Dashboard Sections */
    .dashboard-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .section-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    }

    .section-header h3 {
        margin: 0;
        color: #003d7a;
        font-size: 1.25rem;
    }

    .section-toggle {
        font-size: 1.2rem;
        color: #6c757d;
        transition: transform 0.3s ease;
    }

    .section-content {
        padding: 1.5rem;
    }

    /* Financial Table */
    .financial-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .financial-table tbody tr {
        border-bottom: 1px solid #e9ecef;
    }

    .financial-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Timeline */
    .timeline-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .timeline-item {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .timeline-item.active {
        border-color: #0056b3;
        background: #f0f7ff;
    }

    .timeline-item.delayed {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .timeline-item.upcoming {
        border-color: #6c757d;
        background: #f8f9fa;
    }

    .timeline-project-name {
        font-weight: 600;
        color: #003d7a;
        margin-bottom: 0.5rem;
    }

    .timeline-status {
        display: flex;
        gap: 0.5rem;
    }

    .timeline-dates {
        margin: 1rem 0;
        padding: 0.75rem;
        background: white;
        border-radius: 6px;
    }

    /* Activity Log */
    .activity-log-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .activity-log-item {
        display: flex;
        gap: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .activity-log-item:hover {
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .activity-log-item.preliminary {
        border-left-color: #0056b3;
    }

    .activity-log-item.executive {
        border-left-color: #28a745;
    }

    .log-icon {
        font-size: 1.5rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .log-icon.preliminary {
        background: #cfe2ff;
        color: #0056b3;
    }

    .log-icon.executive {
        background: #d1e7dd;
        color: #28a745;
    }

    .log-content {
        flex: 1;
        min-width: 0;
    }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .log-header strong {
        color: #003d7a;
    }

    .log-date {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .log-details {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.5rem;
    }

    .activity-type-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .activity-type-badge.preliminary {
        background: #cfe2ff;
        color: #0056b3;
    }

    .activity-type-badge.executive {
        background: #d1e7dd;
        color: #28a745;
    }

    .activity-name {
        color: #495057;
        font-size: 0.9rem;
    }

    .log-amount {
        font-size: 0.85rem;
        color: #28a745;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    /* Chart Container */
    .chart-container {
        background: white;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .chart-container h5 {
        color: #003d7a;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .budget-chart-wrapper {
        position: relative;
        height: 300px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .implementation-dashboard-container {
            padding: 1rem;
        }

        .stat-card {
            flex-direction: column;
            text-align: center;
        }

        .stat-icon {
            margin: 0 auto;
        }

        .dashboard-filters {
            flex-wrap: wrap;
            width: 100%;
        }

        .timeline-grid {
            grid-template-columns: 1fr;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }

    /* Chart Tab Buttons */
    .chart-tab-btn {
        background: #f8f9fa;
        border: 2px solid transparent;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        color: #6c757d;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .chart-tab-btn:hover {
        background: #e9ecef;
        color: #003d7a;
    }

    .chart-tab-btn.active {
        background: #0056b3;
        color: white;
        border-color: #003d7a;
    }

    .charts-section {
        transition: all 0.3s ease;
    }
</style>
@endsection

@section('content')
<div class="implementation-dashboard-container mt-5">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                <i class="fas fa-chart-line"></i> لوحة معلومات التنفيذ
            </h2>
            <div class="dashboard-filters">
                <button class="btn btn-sm btn-outline-primary" onclick="filterDashboard('all')">
                    <i class="fas fa-th"></i> الكل
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="filterDashboard('financial')">
                    <i class="fas fa-dollar-sign"></i> مالي
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="filterDashboard('timeline')">
                    <i class="fas fa-calendar"></i> زمني
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4" id="stats-row">
        @php
            $totalBudget = $projects->sum(function($p) {
                return $p->executiveFinancialSummaries->sum('approved_amount') ?? 0;
            });
            
            $totalSpent = $projects->sum(function($p) {
                return $p->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
            });
            
            $totalRemaining = $totalBudget - $totalSpent;
            
            $projectsCount = $projects->count();
            $completionPercentage = $projects->isNotEmpty() 
                ? round($projects->sum(function($p) {
                    $total = $p->preliminaryActivities->sum(function($a) {
                        return $a->procedures->count();
                    }) + $p->executiveActivities->sum(function($a) {
                        return $a->actions->count();
                    });
                    
                    $completed = $p->preliminaryActivities->sum(function($a) {
                        return $a->procedures->filter(fn($pr) => $pr->executions->count() > 0)->count();
                    }) + $p->executiveActivities->sum(function($a) {
                        return $a->actions->filter(fn($ac) => $ac->executions->count() > 0)->count();
                    });
                    
                    return $total > 0 ? ($completed / $total) * 100 : 0;
                }) / max(1, $projectsCount))
                : 0;
        @endphp

        <!-- Total Budget Card -->
        <div class="col-lg-3 col-md-6 dashboard-stat-card">
            <div class="stat-card budget-card">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">إجمالي الميزانية</div>
                    <div class="stat-value">{{ number_format($totalBudget, 2) }}</div>
                    <div class="stat-currency">ريال</div>
                </div>
            </div>
        </div>

        <!-- Amount Spent Card -->
        <div class="col-lg-3 col-md-6 dashboard-stat-card">
            <div class="stat-card spent-card">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">المبلغ المصروف</div>
                    <div class="stat-value">{{ number_format($totalSpent, 2) }}</div>
                    <div class="stat-percentage spent-percentage">
                        @php
                            $spentPercentage = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;
                        @endphp
                        {{ number_format($spentPercentage, 1) }}%
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Budget Card -->
        <div class="col-lg-3 col-md-6 dashboard-stat-card">
            <div class="stat-card remaining-card {{ $totalRemaining < 0 ? 'negative' : '' }}">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">المبلغ المتبقي</div>
                    <div class="stat-value">{{ number_format($totalRemaining, 2) }}</div>
                    <div class="stat-alert">
                        @if($totalRemaining < 0)
                            <span class="badge bg-danger"><i class="fas fa-exclamation-circle"></i> تجاوز الميزانية</span>
                        @else
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> متوازن</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Progress Card -->
        <div class="col-lg-3 col-md-6 dashboard-stat-card">
            <div class="stat-card progress-card">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">إجمالي التقدم</div>
                    <div class="stat-value">{{ $completionPercentage }}%</div>
                    <div class="progress-bar-mini">
                        <div class="progress-bar-fill" style="width: {{ $completionPercentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Tracking Section -->
    <div class="dashboard-section financial-section mb-4" id="financial-section">
        <div class="section-header" onclick="toggleSection('financial-section')">
            <h3><i class="fas fa-chart-bar"></i> تتبع المالية</h3>
            <span class="section-toggle">
                <i class="fas fa-chevron-up"></i>
            </span>
        </div>

        <div class="section-content">
            <!-- Budget vs Spent Chart Container -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="chart-container">
                        <h5 class="mb-3">توزيع الميزانية</h5>
                        <div class="budget-chart-wrapper">
                            <canvas id="budgetChart" style="max-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-container">
                        <h5 class="mb-3">تفصيل المشاريع المالي</h5>
                        <div class="table-responsive">
                            <table class="table table-sm financial-table">
                                <thead>
                                    <tr>
                                        <th>المشروع</th>
                                        <th class="text-end">الميزانية</th>
                                        <th class="text-end">المصروف</th>
                                        <th class="text-end">النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($projects->take(5) as $project)
                                    @php
                                        $projectBudget = $project->executiveFinancialSummaries->sum('approved_amount') ?? 0;
                                        $projectSpent = $project->executiveActivities->sum(function($a) {
                                            return $a->actions->sum(function($ac) {
                                                return $ac->executions->sum('amount_spent') ?? 0;
                                            });
                                        });
                                        $projectPercentage = $projectBudget > 0 ? ($projectSpent / $projectBudget) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-truncate" title="{{ $project->project_name }}">
                                            <a href="{{ route('projects.quality.show', $project->id) }}" style="text-decoration: none; color: #0056b3; font-weight: 600;">
                                                {{ Str::limit($project->project_name, 20) }}
                                            </a>
                                        </td>
                                        <td class="text-end">{{ number_format($projectBudget, 0) }}</td>
                                        <td class="text-end">{{ number_format($projectSpent, 0) }}</td>
                                        <td class="text-end">
                                            <span class="badge {{ $projectPercentage > 100 ? 'bg-danger' : ($projectPercentage > 80 ? 'bg-warning' : 'bg-success') }}">
                                                {{ number_format($projectPercentage, 0) }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">لا توجد بيانات</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline & Schedule Section -->
    <div class="dashboard-section timeline-section mb-4" id="timeline-section">
        <div class="section-header" onclick="toggleSection('timeline-section')">
            <h3><i class="fas fa-calendar-alt"></i> الجدول الزمني</h3>
            <span class="section-toggle">
                <i class="fas fa-chevron-up"></i>
            </span>
        </div>

        <div class="section-content">
            <div class="timeline-grid">
                @forelse($projects->sortBy('start_date_gregorian')->take(10) as $project)
                @php
                    $startDate = \Carbon\Carbon::parse($project->start_date_gregorian);
                    $endDate = \Carbon\Carbon::parse($project->end_date_gregorian);
                    $today = \Carbon\Carbon::now();
                    $totalDays = $startDate->diffInDays($endDate);
                    $elapsedDays = $startDate->diffInDays($today);
                    $elapsedPercentage = $totalDays > 0 ? min(100, ($elapsedDays / $totalDays) * 100) : 0;
                    
                    $isDelayed = $today->isAfter($endDate);
                    $isActive = $today->isBetween($startDate, $endDate);
                @endphp
                <div class="timeline-item {{ $isDelayed ? 'delayed' : ($isActive ? 'active' : 'upcoming') }}">
                    <div class="timeline-header">
                        <div class="timeline-project-name">
                            <a href="{{ route('projects.quality.show', $project->id) }}" style="text-decoration: none; color: #003d7a; font-weight: 600;">
                                {{ Str::limit($project->project_name, 30) }}
                            </a>
                        </div>
                        <div class="timeline-status">
                            @if($isDelayed)
                                <span class="badge bg-danger"><i class="fas fa-clock"></i> متأخر</span>
                            @elseif($isActive)
                                <span class="badge bg-info"><i class="fas fa-play-circle"></i> جاري</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-hourglass-start"></i> قادم</span>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-dates">
                        <small class="text-muted">
                            <i class="fas fa-play"></i> {{ $startDate->format('Y-m-d') }}
                            <i class="fas fa-arrow-right ms-2 me-2"></i>
                            <i class="fas fa-stop"></i> {{ $endDate->format('Y-m-d') }}
                        </small>
                    </div>

                    <div class="timeline-progress">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $elapsedPercentage }}%;" 
                                 aria-valuenow="{{ $elapsedPercentage }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">{{ round($elapsedPercentage) }}% من المدة الزمنية</small>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle"></i> لا توجد مشاريع قيد التنفيذ
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Charts Tabs Navigation -->
    <div style="margin-bottom: 2rem;">
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); padding: 1rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; border-bottom: 2px solid #e9ecef; padding-bottom: 1rem;">
                <button onclick="showCharts('timeline')" class="chart-tab-btn active" data-tab="timeline">
                    <i class="fas fa-clock"></i> المخططات الزمنية
                </button>
                <button onclick="showCharts('financial')" class="chart-tab-btn" data-tab="financial">
                    <i class="fas fa-coins"></i> المخططات المالية
                </button>
                <button onclick="showCharts('performance')" class="chart-tab-btn" data-tab="performance">
                    <i class="fas fa-tachometer-alt"></i> الأداء والجودة
                </button>
                <button onclick="showCharts('comparison')" class="chart-tab-btn" data-tab="comparison">
                    <i class="fas fa-chart-bar"></i> المقارنات
                </button>
            </div>
        </div>
    </div>

    <!-- Timeline Charts Section -->
    <div id="timeline-charts-section" class="charts-section" style="display: block;">
        @include('projects.quality.charts.timeline-charts')
    </div>

    <!-- Financial Charts Section -->
    <div id="financial-charts-section" class="charts-section" style="display: none;">
        @include('projects.quality.charts.financial-charts')
    </div>

    <!-- Performance Charts Section -->
    <div id="performance-charts-section" class="charts-section" style="display: none;">
        @include('projects.quality.charts.performance-charts')
    </div>

    <!-- Comparison Charts Section -->
    <div id="comparison-charts-section" class="charts-section" style="display: none;">
        @include('projects.quality.charts.comparison-charts')
    </div>

    <!-- Implementation Activity Log Section -->
    <div class="dashboard-section activity-log-section" id="activity-log-section" style="margin-top: 3rem;">
        <div class="section-header" onclick="toggleSection('activity-log-section')">
            <h3><i class="fas fa-history"></i> سجل الأنشطة</h3>
            <span class="section-toggle">
                <i class="fas fa-chevron-up"></i>
            </span>
        </div>

        <div class="section-content">
            <div class="activity-log-list">
                @php
                    // Collect all execution records from all projects
                    $allExecutions = [];
                    foreach ($projects as $project) {
                        foreach ($project->preliminaryActivities as $activity) {
                            foreach ($activity->procedures as $procedure) {
                                foreach ($procedure->executions as $execution) {
                                    $allExecutions[] = [
                                        'type' => 'preliminary',
                                        'project' => $project,
                                        'activity' => $activity,
                                        'name' => $procedure->procedure_name,
                                        'execution' => $execution,
                                        'date' => $execution->created_at
                                    ];
                                }
                            }
                        }
                        
                        foreach ($project->executiveActivities as $activity) {
                            foreach ($activity->actions as $action) {
                                foreach ($action->executions as $execution) {
                                    $allExecutions[] = [
                                        'type' => 'executive',
                                        'project' => $project,
                                        'activity' => $activity,
                                        'name' => $action->action_name,
                                        'execution' => $execution,
                                        'date' => $execution->created_at
                                    ];
                                }
                            }
                        }
                    }
                    
                    // Sort by date (newest first) and take first 15
                    $allExecutions = collect($allExecutions)->sortByDesc('date')->take(15);
                @endphp

                @forelse($allExecutions as $log)
                <div class="activity-log-item">
                    <div class="log-icon {{ $log['type'] === 'preliminary' ? 'preliminary' : 'executive' }}">
                        <i class="fas {{ $log['type'] === 'preliminary' ? 'fa-tasks' : 'fa-cogs' }}"></i>
                    </div>

                    <div class="log-content">
                        <div class="log-header">
                            <a href="{{ route('projects.quality.show', $log['project']->id) }}" style="text-decoration: none; color: #1e293b;">
                                <strong>{{ $log['project']->project_name }}</strong>
                            </a>
                            <span class="log-date">
                                <i class="fas fa-clock"></i>
                                {{ $log['date']->diffForHumans() }}
                            </span>
                        </div>

                        <div class="log-details">
                            <span class="activity-type-badge {{ $log['type'] }}">
                                {{ $log['type'] === 'preliminary' ? 'نشاط تمهيدي' : 'نشاط تنفيذي' }}
                            </span>
                            <span class="activity-name">{{ $log['name'] }}</span>
                        </div>

                        @if($log['execution']->amount_spent)
                        <div class="log-amount">
                            <i class="fas fa-dollar-sign"></i>
                            {{ number_format($log['execution']->amount_spent, 2) }} ريال
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="alert alert-info text-center py-4">
                    <i class="fas fa-inbox"></i> لا توجد سجلات نشاط
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Initialize Chart on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeBudgetChart();
    });

    function showCharts(tab) {
        // Hide all chart sections
        document.getElementById('timeline-charts-section').style.display = 'none';
        document.getElementById('financial-charts-section').style.display = 'none';
        document.getElementById('performance-charts-section').style.display = 'none';
        document.getElementById('comparison-charts-section').style.display = 'none';

        // Remove active class from all buttons
        document.querySelectorAll('.chart-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected section
        const sectionMap = {
            'timeline': 'timeline-charts-section',
            'financial': 'financial-charts-section',
            'performance': 'performance-charts-section',
            'comparison': 'comparison-charts-section'
        };

        if (sectionMap[tab]) {
            document.getElementById(sectionMap[tab]).style.display = 'block';
        }

        // Add active class to clicked button
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');

        // Scroll to charts section
        const section = document.querySelector('.charts-section:not([style*="display: none"])');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function initializeBudgetChart() {
        const ctx = document.getElementById('budgetChart');
        if (!ctx) return;

        @php
            $totalBudget = $projects->sum(function($p) {
                return $p->executiveFinancialSummaries->sum('approved_amount') ?? 0;
            });
            $totalSpent = $projects->sum(function($p) {
                return $p->executiveActivities->sum(function($a) {
                    return $a->actions->sum(function($ac) {
                        return $ac->executions->sum('amount_spent') ?? 0;
                    });
                });
            });
            $totalRemaining = $totalBudget - $totalSpent;
        @endphp

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['المصروف', 'المتبقي'],
                datasets: [{
                    data: [{{ $totalSpent }}, {{ max(0, $totalRemaining) }}],
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8'
                    ],
                    borderColor: ['#fff', '#fff'],
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }

    function filterDashboard(type) {
        // Hide all sections
        document.getElementById('financial-section').style.display = 'none';
        document.getElementById('timeline-section').style.display = 'none';
        document.getElementById('activity-log-section').style.display = 'none';

        // Show selected section
        if (type === 'all') {
            document.getElementById('financial-section').style.display = 'block';
            document.getElementById('timeline-section').style.display = 'block';
            document.getElementById('activity-log-section').style.display = 'block';
        } else if (type === 'financial') {
            document.getElementById('financial-section').style.display = 'block';
        } else if (type === 'timeline') {
            document.getElementById('timeline-section').style.display = 'block';
        }
    }

    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        const content = section.querySelector('.section-content');
        const toggle = section.querySelector('.section-toggle i');

        content.style.display = content.style.display === 'none' ? 'block' : 'none';
        toggle.classList.toggle('fa-chevron-up');
        toggle.classList.toggle('fa-chevron-down');
    }
</script>
@endsection
