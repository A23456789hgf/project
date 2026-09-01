@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>سجل العمليات</h1>
        </div>
        <div class="col-md-4 text-end">
            @if(auth()->user()->hasPermission('audit-logs.export'))
            <div class="btn-group">
                <a href="{{ route('admin.audit-logs.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('admin.audit-logs.export-excel', request()->all()) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.audit-logs.export-pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row mb-3">
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
                </div>
                <div class="col-md-2">
                    <select name="action" class="form-select">
                        <option value="">-- اختر الإجراء --</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="user_id" class="form-select">
                        <option value="">-- اختر المستخدم --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') === (string)$user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="model_type" class="form-select">
                        <option value="">-- اختر النوع --</option>
                        @foreach($modules as $type)
                            <option value="{{ $type }}" {{ request('model_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-search"></i> بحث
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>المستخدم</th>
                            <th>الإجراء</th>
                            <th>النوع</th>
                            <th>الوصف</th>
                            <th>عنوان IP</th>
                            <th>التاريخ والوقت</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $log->action }}</span>
                            </td>
                            <td>{{ $log->model_type ?? 'N/A' }}</td>
                            <td>{{ Str::limit($log->description, 50) }}</td>
                            <td><small>{{ $log->ip_address }}</small></td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted">لا توجد عمليات مسجلة</p>
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
