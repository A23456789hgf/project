@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">إحصائيات المراسلات</h4>
            <a href="{{ route('correspondence.index') }}" class="btn btn-primary">
                <i class="fas fa-list me-1"></i> عرض كل المراسلات
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Total Sent -->
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-2">إجمالي المرسل</h6>
                        <h2 class="display-4 mb-0">{{ $statistics['total_sent'] }}</h2>
                    </div>
                    <i class="fas fa-paper-plane fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Received -->
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-2">إجمالي المستلم</h6>
                        <h2 class="display-4 mb-0">{{ $statistics['total_received'] }}</h2>
                    </div>
                    <i class="fas fa-inbox fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Urgent -->
    <div class="col-md-3 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-2">عاجلة جداً</h6>
                        <h2 class="display-4 mb-0">{{ $statistics['urgent'] }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue -->
    <div class="col-md-3 mb-4">
        <div class="card bg-dark text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase mb-2">متأخرة</h6>
                        <h2 class="display-4 mb-0">{{ $statistics['overdue'] }}</h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">توزيع الحالات (الواردة)</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        بانتظار الإجراء
                        <span class="badge bg-warning rounded-pill">{{ $statistics['pending'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        تم الرد عليها
                        <span class="badge bg-success rounded-pill">{{ $statistics['replied'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        محالة لجهات أخرى
                        <span class="badge bg-info rounded-pill">{{ $statistics['referred'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        مغلقة
                        <span class="badge bg-secondary rounded-pill">{{ $statistics['closed'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-center d-flex align-items-center justify-content-center">
        <div class="p-5 border rounded bg-light">
            <i class="fas fa-chart-bar fa-5x text-muted mb-3 d-block"></i>
            <p class="text-muted">نظام المراسلات المتطور V2.0</p>
        </div>
    </div>
</div>
@endsection
