@extends('layouts.app')

@section('title', 'تقرير استيراد الجهات الداخلية')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">
                <i class="fas fa-file-alt"></i> تقرير استيراد الجهات الداخلية
            </h1>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-lg">{{ $total }}</div>
                    <div class="text-muted">إجمالي السجلات</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-lg">{{ $ok }}</div>
                    <div class="text-muted">سجلات نجحت</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-lg">{{ $failed }}</div>
                    <div class="text-muted">سجلات فشلت</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-info font-weight-bold text-lg">{{ round(($ok/$total)*100, 1) }}%</div>
                    <div class="text-muted">معدل النجاح</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Messages -->
    @if(count($addedRecords) > 0 || count($updatedRecords) > 0)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle"></i> السجلات الناجحة ({{ $ok }})
                </h5>
            </div>
            <div class="card-body">
                @if(count($addedRecords) > 0)
                    <div class="mb-3">
                        <h6 class="text-success">
                            <i class="fas fa-plus-circle"></i> جهات تم إضافتها ({{ count($addedRecords) }})
                        </h6>
                        <div class="list-group">
                            @foreach($addedRecords as $record)
                                <div class="list-group-item">
                                    <i class="fas fa-sitemap text-success"></i> {{ $record }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($updatedRecords) > 0)
                    <div class="mb-3">
                        <h6 class="text-info">
                            <i class="fas fa-edit"></i> جهات تم تحديثها ({{ count($updatedRecords) }})
                        </h6>
                        <div class="list-group">
                            @foreach($updatedRecords as $record)
                                <div class="list-group-item">
                                    <i class="fas fa-sitemap text-info"></i> {{ $record }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Error Messages -->
    @if(count($errors) > 0)
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-circle"></i> أخطاء ({{ count($errors) }})
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم السطر</th>
                            <th>الأخطاء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($errors as $row => $rowErrors)
                            <tr>
                                <td>
                                    <span class="badge bg-danger">{{ $row }}</span>
                                </td>
                                <td>
                                    <ul class="mb-0">
                                        @foreach($rowErrors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex gap-2">
                <a href="{{ route('internal-entities.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> عودة إلى القائمة
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print"></i> طباعة التقرير
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
}

.text-lg {
    font-size: 1.5rem;
    line-height: 1;
}

@media print {
    .btn {
        display: none !important;
    }
}
</style>
@endsection
