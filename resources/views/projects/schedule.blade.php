@extends('layouts.app')

@section('styles')
<style>
    .schedule-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .schedule-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .schedule-tabs {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .schedule-tab {
        padding: 0.75rem 1.5rem;
        background: rgba(255,255,255,0.15);
        border: 2px solid transparent;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .schedule-tab:hover {
        background: rgba(255,255,255,0.25);
        transform: translateY(-2px);
    }

    .schedule-tab.active {
        background: white;
        color: #667eea;
        border-color: white;
    }

    .schedule-content {
        display: none;
    }

    .schedule-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .gantt-chart {
        overflow-x: auto;
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .gantt-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        align-items: center;
        border-bottom: 1px solid #eee;
        padding-bottom: 1rem;
    }

    .gantt-label {
        min-width: 250px;
        word-break: break-word;
    }

    .gantt-label-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }

    .gantt-label-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .gantt-timeline {
        flex: 1;
        position: relative;
        min-height: 50px;
        background: #f8f9fa;
        border-radius: 6px;
        overflow: hidden;
    }

    .gantt-bar {
        position: absolute;
        height: 36px;
        top: 7px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .gantt-bar:hover {
        filter: brightness(0.9);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .gantt-bar.planned {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
        opacity: 0.7;
    }

    .gantt-bar.actual {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        opacity: 0.9;
    }

    .gantt-bar.delayed {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }

    .gantt-grid {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        pointer-events: none;
    }

    .gantt-grid-line {
        flex: 1;
        border-right: 1px solid #ddd;
    }

    .timeline-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .timeline-item {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        position: relative;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 60px;
        bottom: -60px;
        width: 2px;
        background: linear-gradient(180deg, #667eea, transparent);
    }

    .timeline-marker {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: white;
        border: 3px solid #667eea;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
    }

    .timeline-marker.completed {
        background: #28a745;
        border-color: #1e7e34;
        color: white;
    }

    .timeline-marker.in_progress {
        background: #0dcaf0;
        border-color: #0aa2c0;
        color: white;
        animation: pulse 2s infinite;
    }

    .timeline-marker.pending {
        background: #6c757d;
        border-color: #495057;
        color: white;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(13, 202, 240, 0.7);
        }
        50% {
            box-shadow: 0 0 0 8px rgba(13, 202, 240, 0);
        }
    }

    .timeline-content {
        flex: 1;
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    .timeline-content.completed {
        border-left-color: #28a745;
        background: #d1e7dd;
    }

    .timeline-content.in_progress {
        border-left-color: #0dcaf0;
        background: #cfe2ff;
    }

    .timeline-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .timeline-meta {
        font-size: 0.85rem;
        color: #6c757d;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .calendar-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .calendar-grid {
        display: grid;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .calendar-month {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 1rem;
    }

    .calendar-month-header {
        font-weight: 700;
        color: #667eea;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #667eea;
    }

    .calendar-events {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .calendar-event {
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid;
        font-size: 0.9rem;
    }

    .calendar-event.preliminary {
        background: #cfe2ff;
        border-left-color: #0dcaf0;
        color: #084298;
    }

    .calendar-event.executive {
        background: #d1e7dd;
        border-left-color: #28a745;
        color: #0f5132;
    }

    .financial-timeline {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .financial-row {
        display: grid;
        grid-template-columns: 250px 1fr 150px 150px;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #eee;
    }

    .financial-row:hover {
        background: #f8f9fa;
    }

    .financial-label {
        font-weight: 600;
        color: #2c3e50;
    }

    .financial-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        height: 30px;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }

    .financial-bar-inner {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        height: 100%;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        transition: width 0.3s ease;
    }

    .financial-amount {
        text-align: right;
        font-weight: 600;
        color: #667eea;
    }

    .no-data {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6c757d;
    }

    .no-data i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
        display: block;
    }

    .schedule-legend {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        margin-bottom: 2rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-box {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="schedule-header flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="mb-2">
                        <i class="fas fa-calendar-alt me-3"></i>جدول المشروع
                    </h1>
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>عرض شامل لجدول الأنشطة والتنفيذ والمالية
                    </p>
                    <div class="schedule-tabs">
                        <div class="schedule-tab active" data-tab="gantt">
                            <i class="fas fa-bars me-2"></i>مخطط جانت
                        </div>
                        <div class="schedule-tab" data-tab="timeline">
                            <i class="fas fa-stream me-2"></i>الخط الزمني
                        </div>
                        <div class="schedule-tab" data-tab="calendar">
                            <i class="fas fa-calendar-days me-2"></i>التقويم
                        </div>
                        <div class="schedule-tab" data-tab="financial">
                            <i class="fas fa-chart-line me-2"></i>المالية
                        </div>
                    </div>
                </div>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>عودة
                </a>
            </div>
        </div>
    </div>

    <!-- Gantt Chart Tab -->
    <div id="gantt" class="schedule-content active">
        <div class="gantt-chart">
            <div class="schedule-legend">
                <div class="legend-item">
                    <div class="legend-box" style="background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); opacity: 0.7;"></div>
                    <small>مخطط (مرحلة التخطيط)</small>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);"></div>
                    <small>فعلي (تنفيذي)</small>
                </div>
                <div class="legend-item">
                    <div class="legend-box" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);"></div>
                    <small>متأخر</small>
                </div>
            </div>

            @php
                $projectStart = \Carbon\Carbon::parse($project->start_date_gregorian);
                $projectEnd = \Carbon\Carbon::parse($project->end_date_gregorian);
                $totalDays = $projectStart->diffInDays($projectEnd);
            @endphp

            <!-- Preliminary Activities -->
            @if($project->preliminaryActivities->count() > 0)
                <h3 class="mb-3">
                    <i class="fas fa-tasks" style="color: #0dcaf0;"></i> الأنشطة التمهيدية
                </h3>
                @foreach($project->preliminaryActivities as $activity)
                    @foreach($activity->procedures as $procedure)
                        <div class="gantt-row">
                            <div class="gantt-label">
                                <div class="gantt-label-title">{{ $procedure->procedure_name }}</div>
                                <div class="gantt-label-meta">
                                    <i class="fas fa-layer-group me-1"></i>{{ $activity->name }}
                                </div>
                            </div>
                            <div class="gantt-timeline">
                                <div class="gantt-grid">
                                    @for($i = 0; $i < 12; $i++)
                                        <div class="gantt-grid-line"></div>
                                    @endfor
                                </div>

                                @if($procedure->start_date && $procedure->end_date)
                                    @php
                                        $start = \Carbon\Carbon::parse($procedure->start_date);
                                        $end = \Carbon\Carbon::parse($procedure->end_date);
                                        $startOffset = $projectStart->diffInDays($start);
                                        $duration = $start->diffInDays($end);
                                        $startPercent = ($startOffset / $totalDays) * 100;
                                        $widthPercent = ($duration / $totalDays) * 100;
                                    @endphp
                                    <div class="gantt-bar planned" style="left: {{ $startPercent }}%; width: {{ $widthPercent }}%;" title="{{ $start->format('Y-m-d') }} → {{ $end->format('Y-m-d') }}">
                                        مخطط
                                    </div>
                                @endif

                                @if($procedure->executions->count() > 0)
                                    @foreach($procedure->executions as $execution)
                                        @if($execution->actual_start_date_gregorian && $execution->actual_finish_date_gregorian)
                                            @php
                                                $actualStart = \Carbon\Carbon::parse($execution->actual_start_date_gregorian);
                                                $actualEnd = \Carbon\Carbon::parse($execution->actual_finish_date_gregorian);
                                                $actualStartOffset = $projectStart->diffInDays($actualStart);
                                                $actualDuration = $actualStart->diffInDays($actualEnd);
                                                $actualStartPercent = ($actualStartOffset / $totalDays) * 100;
                                                $actualWidthPercent = ($actualDuration / $totalDays) * 100;
                                                $isDelayed = $actualEnd->gt(\Carbon\Carbon::parse($procedure->end_date));
                                            @endphp
                                            <div class="gantt-bar {{ $isDelayed ? 'delayed' : 'actual' }}" style="left: {{ $actualStartPercent }}%; width: {{ $actualWidthPercent }}%;" title="{{ $actualStart->format('Y-m-d') }} → {{ $actualEnd->format('Y-m-d') }}">
                                                فعلي
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @endif

            <!-- Executive Activities -->
            @if($project->executiveActivities->count() > 0)
                <h3 class="mb-3" style="margin-top: 2rem;">
                    <i class="fas fa-briefcase" style="color: #28a745;"></i> الأنشطة التنفيذية
                </h3>
                @foreach($project->executiveActivities as $activity)
                    @foreach($activity->actions as $action)
                        @if($action->start_date && $action->end_date)
                            <div class="gantt-row">
                                <div class="gantt-label">
                                    <div class="gantt-label-title">{{ $action->action_name }}</div>
                                    <div class="gantt-label-meta">
                                        <i class="fas fa-briefcase me-1"></i>{{ $activity->name }}
                                    </div>
                                </div>
                                <div class="gantt-timeline">
                                    <div class="gantt-grid">
                                        @for($i = 0; $i < 12; $i++)
                                            <div class="gantt-grid-line"></div>
                                        @endfor
                                    </div>

                                    @php
                                        $start = \Carbon\Carbon::parse($action->start_date);
                                        $end = \Carbon\Carbon::parse($action->end_date);
                                        $startOffset = $projectStart->diffInDays($start);
                                        $duration = $start->diffInDays($end);
                                        $startPercent = ($startOffset / $totalDays) * 100;
                                        $widthPercent = ($duration / $totalDays) * 100;
                                    @endphp
                                    <div class="gantt-bar planned" style="left: {{ $startPercent }}%; width: {{ $widthPercent }}%;" title="{{ $start->format('Y-m-d') }} → {{ $end->format('Y-m-d') }}">
                                        مخطط
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach
            @endif

            @if($project->preliminaryActivities->count() == 0 && $project->executiveActivities->count() == 0)
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد أنشطة لهذا المشروع</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Execution Timeline Tab -->
    <div id="timeline" class="schedule-content">
        <div class="timeline-container">
            @php
                $executions = collect();
                foreach($project->preliminaryActivities as $activity) {
                    foreach($activity->procedures as $procedure) {
                        foreach($procedure->executions as $execution) {
                            $executions->push($execution);
                        }
                    }
                }
                $executions = $executions->sortBy('actual_start_date_gregorian');
            @endphp

            @if($executions->count() > 0)
                @foreach($executions as $execution)
                    <div class="timeline-item">
                        <div class="timeline-marker {{ $execution->status }}">
                            @if($execution->status == 'completed')
                                <i class="fas fa-check"></i>
                            @elseif($execution->status == 'in_progress')
                                <i class="fas fa-spinner"></i>
                            @else
                                <i class="fas fa-clock"></i>
                            @endif
                        </div>
                        <div class="timeline-content {{ $execution->status }}">
                            <div class="timeline-title">
                                {{ $execution->procedure->procedure_name }}
                            </div>
                            <div class="timeline-meta">
                                <span>
                                    <i class="fas fa-calendar me-1"></i>
                                    من {{ \Carbon\Carbon::parse($execution->actual_start_date_gregorian)->format('d/m/Y') }}
                                    إلى {{ \Carbon\Carbon::parse($execution->actual_finish_date_gregorian)->format('d/m/Y') }}
                                </span>
                                <span>
                                    <i class="fas fa-money-bill me-1"></i>
                                    المبلغ: {{ number_format($execution->actual_amount, 2) }} ﷼
                                </span>
                                <span>
                                    <i class="fas fa-percent me-1"></i>
                                    الإنجاز: {{ $execution->completion_percentage }}%
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد بيانات تنفيذ</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Calendar Tab -->
    <div id="calendar" class="schedule-content">
        <div class="calendar-container">
            @php
                $months = [];
                $start = \Carbon\Carbon::parse($project->start_date_gregorian);
                $end = \Carbon\Carbon::parse($project->end_date_gregorian);
                
                $current = $start->copy();
                while($current->lte($end)) {
                    $monthKey = $current->format('Y-m');
                    if(!isset($months[$monthKey])) {
                        $months[$monthKey] = [
                            'display' => $current->format('F Y'),
                            'events' => []
                        ];
                    }
                    $current->addMonth();
                }

                foreach($project->preliminaryActivities as $activity) {
                    foreach($activity->procedures as $procedure) {
                        if($procedure->start_date) {
                            $monthKey = \Carbon\Carbon::parse($procedure->start_date)->format('Y-m');
                            if(isset($months[$monthKey])) {
                                $months[$monthKey]['events'][] = [
                                    'type' => 'preliminary',
                                    'title' => $procedure->procedure_name,
                                    'date' => $procedure->start_date,
                                    'activity' => $activity->name
                                ];
                            }
                        }
                    }
                }

                foreach($project->preliminaryActivities as $activity) {
                    foreach($activity->procedures as $procedure) {
                        foreach($procedure->executions as $execution) {
                            if($execution->actual_start_date_gregorian) {
                                $monthKey = \Carbon\Carbon::parse($execution->actual_start_date_gregorian)->format('Y-m');
                                if(isset($months[$monthKey])) {
                                    $months[$monthKey]['events'][] = [
                                        'type' => 'preliminary',
                                        'title' => 'تنفيذ: ' . $procedure->procedure_name,
                                        'date' => $execution->actual_start_date_gregorian,
                                        'activity' => $activity->name
                                    ];
                                }
                            }
                        }
                    }
                }
            @endphp

            @if(count($months) > 0)
                <div class="calendar-grid">
                    @foreach($months as $monthKey => $month)
                        <div class="calendar-month">
                            <div class="calendar-month-header">
                                {{ $month['display'] }}
                            </div>
                            @if(count($month['events']) > 0)
                                <div class="calendar-events">
                                    @foreach($month['events'] as $event)
                                        <div class="calendar-event {{ $event['type'] }}">
                                            <div class="fw-600">{{ $event['title'] }}</div>
                                            <small>
                                                <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y') }}
                                                <i class="fas fa-layer-group ms-2 me-1"></i>{{ $event['activity'] }}
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small" style="padding: 1rem;">
                                    <i class="fas fa-inbox me-2"></i>لا توجد أحداث في هذا الشهر
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد أحداث محددة</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Financial Timeline Tab -->
    <div id="financial" class="schedule-content">
        <div class="financial-timeline">
            @php
                $totalPlanned = 0;
                $totalActual = 0;
                $totalSpent = 0;
                $totalRemaining = 0;

                foreach($project->preliminaryActivities as $activity) {
                    foreach($activity->procedures as $procedure) {
                        $planned = optional($procedure->costs)->sum('total') ?? 0;
                        $totalPlanned += $planned;

                        foreach($procedure->executions as $execution) {
                            $totalActual += $execution->actual_amount;
                            $totalSpent += $execution->amount_spent;
                            $totalRemaining += $execution->remaining_amount;
                        }
                    }
                }
            @endphp

            @if($totalPlanned > 0)
                <h4 class="mb-3">ملخص المالية</h4>
                <div class="financial-row" style="background: #f8f9fa; font-weight: 600;">
                    <div class="financial-label">المجموع الكلي</div>
                    <div class="financial-bar">
                        <div class="financial-bar-inner" style="width: 100%;">المخطط مقابل الفعلي</div>
                    </div>
                    <div class="financial-amount">{{ number_format($totalPlanned, 2) }} ﷼</div>
                    <div class="financial-amount">{{ number_format($totalActual, 2) }} ﷼</div>
                </div>

                <h4 class="mb-3" style="margin-top: 2rem;">تفاصيل الإجراءات</h4>
                @foreach($project->preliminaryActivities as $activity)
                    @foreach($activity->procedures as $procedure)
                        @php
                            $procedurePlanned = optional($procedure->costs)->sum('total') ?? 0;
                            $procedureActual = optional($procedure->executions)->sum('actual_amount') ?? 0;
                            $procedureSpent = optional($procedure->executions)->sum('amount_spent') ?? 0;
                            $percentage = $totalPlanned > 0 ? ($procedureActual / $procedurePlanned) * 100 : 0;
                        @endphp
                        <div class="financial-row">
                            <div class="financial-label">
                                <div>{{ $procedure->procedure_name }}</div>
                                <small class="text-muted">{{ $activity->name }}</small>
                            </div>
                            <div class="financial-bar" style="background: #e9ecef;">
                                <div class="financial-bar-inner" style="width: {{ min($percentage, 100) }}%; background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%);"></div>
                            </div>
                            <div class="financial-amount">
                                <small class="text-muted">المخطط:</small><br>{{ number_format($procedurePlanned, 2) }} ﷼
                            </div>
                            <div class="financial-amount">
                                <small class="text-muted">الفعلي:</small><br>{{ number_format($procedureActual, 2) }} ﷼
                            </div>
                        </div>
                    @endforeach
                @endforeach
            @else
                <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>لا توجد بيانات مالية</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.schedule-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');

        document.querySelectorAll('.schedule-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.schedule-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        document.getElementById(tabName).classList.add('active');
    });
});
</script>
@endsection
