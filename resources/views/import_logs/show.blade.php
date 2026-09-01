@extends('layouts.app')

@section('title', 'تفاصيل عملية الاستيراد')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">
            <a href="{{ route('import-logs.index') }}" class="btn btn-secondary me-2"><i class="fas fa-arrow-right"></i> رجوع</a>
            تفاصيل الاستيراد #{{ $importLog->id }}
        </h2>
        @if(in_array($importLog->status, ['Success', 'Partial']) && $importLog->successful_records > 0)
            @can('import-logs.rollback')
                <form action="{{ route('import-logs.rollback', $importLog->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من التراجع عن عملية الاستيراد هذه؟ سيتم حذف جميع السجلات التي تم إنشاؤها عبرها.');">
                    @csrf
                    <button type="submit" class="btn btn-warning shadow-sm">
                        <i class="fas fa-undo me-1"></i> التراجع عن عملية الاستيراد
                    </button>
                </form>
            @endcan
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <!-- Overview Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                معلومات العملية
                            </div>
                            <div class="mb-1"><strong>الوحدة:</strong> {{ $importLog->unit_name ?? 'غير محدد' }}</div>
                            <div class="mb-1"><strong>الملف:</strong> {{ $importLog->file_name ?? '-' }}</div>
                            <div class="mb-1"><strong>المستخدم:</strong> {{ $importLog->user->name ?? 'نظام' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-info-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                التواريخ والحالة
                            </div>
                            <div class="mb-1"><strong>الحالة:</strong> 
                                @if($importLog->status === 'Success')
                                    <span class="badge bg-success">نجاح</span>
                                @elseif($importLog->status === 'Partial')
                                    <span class="badge bg-warning text-dark">جزئي</span>
                                @elseif($importLog->status === 'Failed')
                                    <span class="badge bg-danger">فشل</span>
                                @elseif($importLog->status === 'Rolled Back')
                                    <span class="badge bg-secondary">تم التراجع</span>
                                @else
                                    <span class="badge bg-info">{{ $importLog->status }}</span>
                                @endif
                            </div>
                            <div class="mb-1"><strong>وقت البدء:</strong> {{ $importLog->started_at ? $importLog->started_at->format('Y-m-d H:i:s') : '-' }}</div>
                            <div class="mb-1"><strong>وقت الانتهاء:</strong> {{ $importLog->completed_at ? $importLog->completed_at->format('Y-m-d H:i:s') : '-' }}</div>
                            @if($importLog->rolled_back_at)
                                <div class="mb-1 text-danger"><strong>وقت التراجع:</strong> {{ $importLog->rolled_back_at->format('Y-m-d H:i:s') }}</div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                الإحصائيات
                            </div>
                            <div class="mb-1"><strong>إجمالي السجلات:</strong> {{ $importLog->total_records }}</div>
                            <div class="mb-1 text-success"><strong>نجاح:</strong> {{ $importLog->successful_records }}</div>
                            <div class="mb-1 text-danger"><strong>فشل:</strong> {{ $importLog->failed_records }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Errors Section -->
    @if($importLog->failed_records > 0 && !empty($importLog->errors))
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">سجلات بها أخطاء ({{ $importLog->failed_records }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-danger">
                        <tr>
                            <th style="width: 100px;">رقم الصف</th>
                            <th>رسائل الخطأ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($importLog->errors as $rowNum => $errorMessages)
                            <tr>
                                <td>{{ $rowNum }}</td>
                                <td>
                                    <ul class="mb-0">
                                        @foreach((array)$errorMessages as $msg)
                                            <li>{{ $msg }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
