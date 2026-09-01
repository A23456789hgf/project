@extends('layouts.app')

@section('content')

<!-- Implementation Dashboard -->
{{-- @include('projects.partials.implementation.dashboard') --}}

<div class="container mt-5">
    <!-- رأس الصفحة -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">
            <i class="fas fa-cogs"></i> المشاريع قيد التنفيذ
        </h2>
        <div class="d-flex gap-2 align-items-center">
            <!-- Bulk Sync Button Removed - Auto Sync Enabled -->
            <span class="badge bg-info fs-6">
                {{ $projects->count() }} مشروع
            </span>
        </div>
    </div>

    <!-- رسالة النجاح -->
    

    <!-- رسالة التنبيه -->
    

    <!-- بطاقة البحث والفرز -->
    <div class="card mb-3 compact-card">
        <div class="card-header bg-light py-2">
            <h6 class="mb-0">
                <i class="fas fa-filter"></i> البحث والفرز
            </h6>
        </div>
        <div class="card-body py-2">
            <form action="{{ route('projects.implementation.index') }}" method="GET" class="row g-2">
                <!-- البحث باسم المشروع -->
                <div class="col-md-4">
                    <label for="search" class="form-label small"><i class="fas fa-search"></i> البحث باسم المشروع</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="أدخل اسم المشروع أو رقمه...">
                </div>

                <!-- فرز بالبرنامج -->
                <div class="col-md-3">
                    <label for="program" class="form-label small"><i class="fas fa-layer-group"></i> البرنامج</label>
                    <select class="form-select form-select-sm" id="program" name="program">
                        <option value="">جميع البرامج</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- فرز بحالة التنفيذ -->
                <div class="col-md-3">
                    <label for="execution_status" class="form-label small"><i class="fas fa-tasks"></i> حالة التنفيذ</label>
                    <select class="form-select form-select-sm" id="execution_status" name="execution_status">
                        <option value="">جميع الحالات</option>
                        <option value="on_track" {{ request('execution_status') === 'on_track' ? 'selected' : '' }}>
                            <i class="fas fa-check-circle text-success"></i> على المسار الصحيح
                        </option>
                        <option value="delayed" {{ request('execution_status') === 'delayed' ? 'selected' : '' }}>
                            <i class="fas fa-exclamation-triangle text-warning"></i> متأخر
                        </option>
                        <option value="at_risk" {{ request('execution_status') === 'at_risk' ? 'selected' : '' }}>
                            <i class="fas fa-times-circle text-danger"></i> في خطر
                        </option>
                    </select>
                </div>

                <!-- أزرار الإجراءات -->
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('projects.implementation.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول المشاريع قيد التنفيذ -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="3%">#</th>
                        <th width="8%">رقم المشروع</th>
                        <th width="18%">اسم المشروع</th>
                        <th width="10%">تاريخ البداية</th>
                        <th width="10%">تاريخ النهاية</th>
                        <th width="12%">حالة التنفيذ</th>
                        <th width="8%" class="text-center">الأنشطة التمهيدية</th>
                        <th width="8%" class="text-center">أنشطة التنفيذ</th>
                        <th width="10%" class="text-center">مزامنة ERP</th>
                        <th width="15%" class="text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                    <tr>
                        <!-- الرقم التسلسلي -->
                        <td>
                            <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                        </td>

                        <!-- رقم المشروع -->
                        <td>
                            <small class="text-monospace">{{ $project->form_number ?? 'N/A' }}</small>
                        </td>

                        <!-- اسم المشروع -->
                        <td>
                            <a href="{{ route('projects.show', $project->id) }}" class="text-decoration-none fw-bold" title="عرض تفاصيل المشروع">
                                {{ $project->project_name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $project->program->name ?? 'بدون برنامج' }}</small>
                        </td>

                        <!-- تاريخ البداية -->
                        <td class="text-center">
                            @if($project->start_date_gregorian)
                                <small class="d-block">{{ \Carbon\Carbon::parse($project->start_date_gregorian)->format('Y-m-d') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- تاريخ النهاية -->
                        <td class="text-center">
                            @if($project->end_date_gregorian)
                                <small class="d-block">{{ \Carbon\Carbon::parse($project->end_date_gregorian)->format('Y-m-d') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <!-- حالة التنفيذ -->
                        <td class="text-center">
                            @php
                                $preliminaryActs = $project->relationLoaded('preliminaryActivities') ? $project->preliminaryActivities : collect();
                                $executiveActs = $project->relationLoaded('executiveActivities') ? $project->executiveActivities : collect();

                                $totalPreliminaryTasks = $preliminaryActs->sum(function($activity) {
                                    $procs = $activity->relationLoaded('procedures') ? $activity->procedures : collect();
                                    return $procs->count();
                                });
                                $completedPreliminaryTasks = $preliminaryActs->sum(function($activity) {
                                    $procs = $activity->relationLoaded('procedures') ? $activity->procedures : collect();
                                    return $procs->filter(function($proc) {
                                        $execs = $proc->relationLoaded('executions') ? $proc->executions : collect();
                                        return $execs->count() > 0;
                                    })->count();
                                });
                                
                                $totalExecutiveTasks = $executiveActs->sum(function($activity) {
                                    $acts = $activity->relationLoaded('actions') ? $activity->actions : collect();
                                    return $acts->count();
                                });
                                $completedExecutiveTasks = $executiveActs->sum(function($activity) {
                                    $acts = $activity->relationLoaded('actions') ? $activity->actions : collect();
                                    return $acts->filter(function($action) {
                                        $execs = $action->relationLoaded('executions') ? $action->executions : collect();
                                        return $execs->count() > 0;
                                    })->count();
                                });

                                $totalTasks = $totalPreliminaryTasks + $totalExecutiveTasks;
                                $completedTasks = $completedPreliminaryTasks + $completedExecutiveTasks;
                                $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

                                $executionStatus = 'on_track';
                                if ($completionPercentage >= 100) {
                                    $executionStatus = 'completed';
                                } elseif ($completionPercentage >= 75) {
                                    $executionStatus = 'on_track';
                                } elseif ($completionPercentage >= 50) {
                                    $executionStatus = 'delayed';
                                } else {
                                    $executionStatus = 'at_risk';
                                }
                            @endphp

                            <div class="progress" style="height: 20px;" title="التقدم: {{ $completionPercentage }}%">
                                <div class="progress-bar {{ $executionStatus === 'completed' ? 'bg-success' : ($executionStatus === 'on_track' ? 'bg-info' : ($executionStatus === 'delayed' ? 'bg-warning' : 'bg-danger')) }}" 
                                     role="progressbar" 
                                     style="width: {{ $completionPercentage }}%;" 
                                     aria-valuenow="{{ $completionPercentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <small class="text-white fw-bold">{{ $completionPercentage }}%</small>
                                </div>
                            </div>
                            <small class="d-block text-muted mt-1">
                                {{ $completedTasks }}/{{ $totalTasks }} مكتمل
                            </small>
                        </td>

                        <!-- عدد الأنشطة التمهيدية -->
                        <td class="text-center">
                            <span class="badge bg-primary">
                                <i class="fas fa-tasks"></i> {{ $preliminaryActs->count() }}
                            </span>
                            <small class="d-block text-muted mt-1">
                                إجراء: {{ $totalPreliminaryTasks }}
                            </small>
                        </td>

                        <!-- عدد أنشطة التنفيذ -->
                        <td class="text-center">
                            <span class="badge bg-success">
                                <i class="fas fa-cogs"></i> {{ $executiveActs->count() }}
                            </span>
                            <small class="d-block text-muted mt-1">
                                إجراء: {{ $totalExecutiveTasks }}
                            </small>
                        </td>

                        <td class="text-center">
                            @if($project->erpnext_project_id)
                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge bg-success" title="معرف المشروع: {{ $project->erpnext_project_id }}">
                                        <i class="fas fa-sync-alt"></i> متزامن
                                    </span>
                                    @can('projects.sync', $project)
                                    <form action="{{ route('projects.sync-to-erp', $project->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 text-primary" style="font-size: 0.7rem;" title="إعادة المزامنة">
                                            <i class="fas fa-redo"></i> تحديث
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            @elseif($project->sync_status === 'failed')
                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge bg-danger" title="خطأ: {{ $project->sync_error }}">
                                        <i class="fas fa-exclamation-circle"></i> فشل
                                    </span>
                                    @can('projects.sync', $project)
                                    <form action="{{ route('projects.sync-to-erp', $project->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.7rem;">
                                            <i class="fas fa-sync"></i> محاولة
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            @else
                                <span class="badge bg-secondary" title="بانتظار المزامنة التلقائية">
                                    <i class="fas fa-clock"></i> في الانتظار
                                </span>
                            @endif
                        </td>

                        <!-- العمليات -->
                        <td>
    <div class="btn-group btn-group-sm" role="group">
        <!-- أيقونة الدخول للتنفيذ -->
        @can('projects.execute', $project)
        <a href="{{ route('projects.execution', $project->id) }}" 
           class="btn btn-primary" 
           title="إدارة التنفيذ">
            <i class="fas fa-play-circle"></i>
        </a>
        @endcan

        <!-- أيقونة الجدول الزمني -->
        @can('projects.schedule', $project)
        <a href="{{ route('projects.schedule', $project->id) }}" 
           class="btn btn-info" 
           title="الجدول الزمني">
            <i class="fas fa-calendar"></i>
        </a>
        @endcan

        <!-- أيقونة العرض -->
        @can('projects.view', $project)
        <a href="{{ route('projects.show', $project->id) }}" 
           class="btn btn-secondary" 
           title="عرض التفاصيل">
            <i class="fas fa-eye"></i>
        </a>
        @endcan

        <!-- أيقونة القائمة المنسدلة للعمليات الإضافية -->
        <div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton{{ $project->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $project->id }}">
                @can('projects.print', $project)
                <li>
                    <a class="dropdown-item" href="{{ route('projects.print', $project->id) }}" title="طباعة">
                        <i class="fas fa-print"></i> طباعة
                    </a>
                </li>
                @endcan
                <!-- يمكن تفعيل خيارات PDF أو Excel لاحقاً -->
            </ul>
        </div>
    </div>
</td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>لا توجد مشاريع قيد التنفيذ حالياً</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- الترقيم -->
    @if($projects->hasPages())
    <nav class="mt-3" aria-label="Page navigation">
        {{ $projects->links() }}
    </nav>
    @endif
</div>

<style>
    .compact-card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .text-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 4px;
    }

    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }

    .badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.6rem;
    }

    .dropdown-menu {
        min-width: 150px;
    }
</style>
@endsection
