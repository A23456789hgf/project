 @php
    // Get tasks where this correspondence is linked via task_id (if any)
    $parentTask = $correspondence->task;
    
    // Get tasks linked via TaskMemo pivot (if any exist)
    $linkedTaskMemos = \App\Models\TaskMemo::with('task')->where('correspondence_id', $correspondence->id)->get();
    
    $allTasks = collect();
    if ($parentTask) {
        $allTasks->push($parentTask);
    }
    foreach($linkedTaskMemos as $memo) {
        if ($memo->task && !$allTasks->contains('id', $memo->task->id)) {
            $allTasks->push($memo->task);
        }
    }
@endphp

@if($allTasks->count() > 0)
    <!-- Linked Tasks Table -->
    <div class="card mb-4 border-warning shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>المهام المرتبطة بالمراسلة (التي تم إصدارها)</h6>
            <span class="badge bg-dark text-white">{{ $allTasks->count() }} مهام</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم المهمة</th>
                            <th>عنوان المهمة</th>
                            <th>الحالة</th>
                            <th>الأولوية</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allTasks as $task)
                            <tr>
                                <td>{{ $task->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ $task->title }}</div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'secondary';
                                        switch($task->status) {
                                            case 'pending': $statusClass = 'warning'; break;
                                            case 'in_progress': $statusClass = 'info'; break;
                                            case 'completed': $statusClass = 'success'; break;
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ __('tasks.status.' . $task->status) ?? $task->status }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityClass = 'secondary';
                                        switch($task->priority) {
                                            case 'high': $priorityClass = 'danger'; break;
                                            case 'medium': $priorityClass = 'warning'; break;
                                            case 'low': $priorityClass = 'info'; break;
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $priorityClass }}">
                                        {{ __('tasks.priority.' . $task->priority) ?? $task->priority }}
                                    </span>
                                </td>
                                <td>{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : 'غير محدد' }}</td>
                                <td>
                                    <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>عرض المهمة
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
