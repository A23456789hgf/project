@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/excel-drag-drop.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-chart-bar text-primary me-2"></i>
                    تقرير استيراد سلاسل القيمة
                </h2>
                <a href="{{ route('value-chains.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i>
                    العودة للقائمة
                </a>
            </div>

            <!-- Import Summary Card -->
            <div class="card import-report-card mb-4 {{ $report['failed'] > 0 ? 'report-warning' : 'report-success' }}">
                <div class="card-header">
                    <h5 class="mb-0">
                        @if($report['failed'] > 0)
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            اكتمل الاستيراد مع وجود بعض الأخطاء
                        @else
                            <i class="fas fa-check-circle text-success me-2"></i>
                            تم الاستيراد بنجاح
                        @endif
                    </h5>
                </div>
                
                <!-- Statistics -->
                <div class="report-stats">
                    <div class="report-stat stat-total">
                        <div class="report-stat-number">{{ $report['total'] }}</div>
                        <div class="report-stat-label">إجمالي السجلات</div>
                    </div>
                    <div class="report-stat stat-success">
                        <div class="report-stat-number">{{ $report['successful'] }}</div>
                        <div class="report-stat-label">تمت بنجاح</div>
                    </div>
                    <div class="report-stat stat-failed">
                        <div class="report-stat-number">{{ $report['failed'] }}</div>
                        <div class="report-stat-label">فشل</div>
                    </div>
                </div>

                <div class="card-body">
                    @if($report['successful'] > 0)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            تم استيراد <strong>{{ $report['successful'] }}</strong> سلسلة قيمة بنجاح.
                        </div>
                    @endif

                    @if($report['failed'] > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            فشل في استيراد <strong>{{ $report['failed'] }}</strong> سجل. يرجى مراجعة الأخطاء أدناه.
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('value-chains.import.form') }}" class="btn btn-outline-primary">
                            <i class="fas fa-upload me-1"></i>
                            استيراد ملف آخر
                        </a>
                    </div>
                </div>
            </div>

            <!-- Error Details -->
            @if($report['failed'] > 0 && !empty($errors))
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        تفاصيل الأخطاء
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="error-details">
                        @foreach($errors as $row => $errorData)
                        <div class="error-item">
                            <div class="error-row">
                                <i class="fas fa-times-circle me-1 text-danger"></i>
                                الصف رقم {{ $row }}
                            </div>
                            
                            <ul class="error-messages">
                                @if(is_array($errorData))
                                    @foreach($errorData as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                @else
                                    <li>{{ $errorData }}</li>
                                @endif
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-hide alerts after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 10000);
    });
});
</script>
@endsection