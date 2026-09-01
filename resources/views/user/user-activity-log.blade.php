@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="fas fa-history"></i> سجل الأنشطة - {{ $user->name }}</h1>
            <p class="text-muted">عرض جميع الأنشطة المتعلقة بالمستخدم</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> العودة إلى ملف المستخدم
            </a>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>معرف المستخدم:</strong> {{ $user->user_id }}
                </div>
                <div class="col-md-3">
                    <strong>الاسم:</strong> {{ $user->name }}
                </div>
                <div class="col-md-3">
                    <strong>الدور:</strong> <span class="badge bg-info">{{ $user->role_display_name }}</span>
                </div>
                <div class="col-md-3">
                    <strong>الحالة:</strong>
                    @if($user->status === 'Active')
                        <span class="badge bg-success">نشط</span>
                    @else
                        <span class="badge bg-danger">معطل</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-filter"></i> تصفية السجل</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('users.activity-log', $user) }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">نوع الإجراء</label>
                    <select name="action" class="form-select">
                        <option value="">-- جميع الإجراءات --</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">البحث</label>
                    <input type="text" name="search" class="form-control" placeholder="ابحث في الوصف" value="{{ request('search') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> تطبيق الفلتر
                    </button>
                    <a href="{{ route('users.activity-log', $user) }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> سجل الأنشطة ({{ $logs->total() }} سجل)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">المستخدم</th>
                            <th width="12%">نوع الإجراء</th>
                            <th width="12%">نوع البيانات</th>
                            <th>الوصف</th>
                            <th width="12%">عنوان IP</th>
                            <th width="15%">التاريخ</th>
                            <th width="8%">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                            <td>
                                <strong>{{ $log->user->name ?? 'N/A' }}</strong>
                                @if($log->user_id === $user->id)
                                    <br><small class="text-muted">(قام بالإجراء)</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeColor = match($log->action) {
                                        'create' => 'success',
                                        'update' => 'primary',
                                        'delete' => 'danger',
                                        'disable' => 'warning',
                                        'enable' => 'success',
                                        'reset_password' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ $log->action }}</span>
                            </td>
                            <td>{{ $log->model_type ?? 'N/A' }}</td>
                            <td>{{ $log->description }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if($log->old_values || $log->new_values)
                                    <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد سجلات نشاط</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
