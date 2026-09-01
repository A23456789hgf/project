@extends('layouts.app')

@section('title', 'سجل الاستيراد الشامل')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">سجل الاستيراد الشامل</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">تصفية السجلات</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('import-logs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">اسم الوحدة</label>
                    <input type="text" name="unit_name" class="form-control" value="{{ request('unit_name') }}" placeholder="الجهات الداخلية، المحافظات، إلخ...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">المستخدم</label>
                    <select name="user_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="Success" {{ request('status') == 'Success' ? 'selected' : '' }}>نجاح</option>
                        <option value="Partial" {{ request('status') == 'Partial' ? 'selected' : '' }}>جزئي</option>
                        <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>فشل</option>
                        <option value="Rolled Back" {{ request('status') == 'Rolled Back' ? 'selected' : '' }}>تم التراجع</option>
                        <option value="Processing" {{ request('status') == 'Processing' ? 'selected' : '' }}>قيد المعالجة</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الوحدة</th>
                            <th>اسم الملف</th>
                            <th>المستخدم</th>
                            <th>حالة العملية</th>
                            <th>إجمالي</th>
                            <th>نجاح</th>
                            <th>فشل</th>
                            <th>وقت البدء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($importLogs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->unit_name ?? 'غير محدد' }}</td>
                                <td>{{ $log->file_name ?? '-' }}</td>
                                <td>{{ $log->user->name ?? 'نظام' }}</td>
                                <td>
                                    @if($log->status === 'Success')
                                        <span class="badge bg-success">نجاح</span>
                                    @elseif($log->status === 'Partial')
                                        <span class="badge bg-warning text-dark">جزئي</span>
                                    @elseif($log->status === 'Failed')
                                        <span class="badge bg-danger">فشل</span>
                                    @elseif($log->status === 'Rolled Back')
                                        <span class="badge bg-secondary">تم التراجع</span>
                                    @else
                                        <span class="badge bg-info">{{ $log->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $log->total_records }}</td>
                                <td class="text-success">{{ $log->successful_records }}</td>
                                <td class="text-danger">{{ $log->failed_records }}</td>
                                <td>{{ $log->started_at ? $log->started_at->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('import-logs.show', $log->id) }}" class="btn btn-sm btn-info text-white" title="التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($log->status, ['Success', 'Partial']) && $log->successful_records > 0)
                                            @can('import-logs.rollback')
                                                <form action="{{ route('import-logs.rollback', $log->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من التراجع عن عملية الاستيراد هذه؟ سيتم حذف جميع السجلات التي تم إنشاؤها عبرها.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning" title="تراجع (حذف السجلات المستوردة)">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">لا توجد سجلات استيراد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $importLogs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
