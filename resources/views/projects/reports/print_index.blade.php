@extends('layouts.print')

@section('report_subject', 'تقرير ملخص إنجاز المشاريع الموحد')
@section('report_title', 'تقارير مركز البيانات الموحد')

@section('content')

<div class="row mb-4 text-center">
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">إجمالي المشاريع</h6>
            <h4 class="fw-bold mb-0">{{ $stats['total_projects'] ?? 0 }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">سجلات التنفيذ</h6>
            <h4 class="fw-bold text-info mb-0">{{ ($stats['implementation']['preliminary'] ?? 0) + ($stats['implementation']['executive'] ?? 0) }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">سجلات الجودة</h6>
            <h4 class="fw-bold text-warning mb-0">{{ $stats['quality']['total_records'] ?? 0 }}</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="p-3 border rounded">
            <h6 class="text-secondary mb-2">الاستخدام المالي</h6>
            <h4 class="fw-bold text-success mb-0" dir="ltr">{{ number_format($stats['financial']['utilization_percentage'] ?? 0, 1) }}%</h4>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-6">
        <div class="p-3 border rounded h-100">
            <h5 class="fw-bold mb-3 text-primary-print">حالة المشاريع</h5>
            <canvas id="chartProjectsStatus" height="250"></canvas>
        </div>
    </div>
    <div class="col-6">
        <div class="p-3 border rounded h-100">
            <h5 class="fw-bold mb-3 text-primary-print">التقدم الزمني</h5>
            <canvas id="chartProgress" height="250"></canvas>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-6">
        <div class="p-3 border rounded h-100">
            <h5 class="fw-bold mb-3 text-primary-print">الاستخدام المالي</h5>
            <div class="d-flex justify-content-center mb-3">
                <canvas id="chartFinancial" height="200" width="200" style="max-width: 200px; max-height: 200px;"></canvas>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                <span class="fw-bold text-secondary">إجمالي الميزانية</span>
                <span class="fw-bold">{{ number_format($stats['financial']['total_budget'] ?? 0) }} ﷼</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="fw-bold text-secondary">المصروف</span>
                <span class="fw-bold text-primary-print">{{ number_format($stats['financial']['total_spent'] ?? 0) }} ﷼</span>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="p-3 border rounded h-100">
            <h5 class="fw-bold mb-3 text-primary-print">مؤشرات الجودة وتوزيع الحالات</h5>
            
            <h6 class="fw-bold mb-2">الجودة</h6>
            <div class="row text-center mb-4">
                <div class="col-6">
                    <div class="p-2 border border-success rounded">
                        <h4 class="mb-0 text-success fw-bold">{{ $stats['quality']['positive'] ?? 0 }}</h4>
                        <small>إيجابية</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border border-danger rounded">
                        <h4 class="mb-0 text-danger fw-bold">{{ $stats['quality']['negative'] ?? 0 }}</h4>
                        <small>سلبية</small>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-2">توزيع حالات المشاريع</h6>
            <div class="row text-center">
                <div class="col-6 mb-2">
                    <div class="p-2 bg-light rounded">
                        <h5 class="mb-0 text-primary fw-bold">{{ $stats['projects_by_status']['نشط'] ?? 0 }}</h5>
                        <small>نشطة</small>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="p-2 bg-light rounded">
                        <h5 class="mb-0 text-warning fw-bold">{{ $stats['projects_by_status']['متأخر'] ?? 0 }}</h5>
                        <small>متأخرة</small>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="p-2 bg-light rounded">
                        <h5 class="mb-0 text-success fw-bold">{{ $stats['projects_by_status']['مكتمل'] ?? 0 }}</h5>
                        <small>مكتملة</small>
                    </div>
                </div>
                <div class="col-6 mb-2">
                    <div class="p-2 bg-light rounded">
                        <h5 class="mb-0 text-secondary fw-bold">{{ $stats['projects_by_status']['معلق'] ?? 0 }}</h5>
                        <small>معلّقة</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Chart.defaults.font.family = "'Tajawal', sans-serif";
        Chart.defaults.color = '#78716c';
        Chart.defaults.font.size = 12;

        const stats = @json($stats ?? []);

        const projectsByStatus = stats.projects_by_status ?? {
            "نشط": stats.total_projects ?? 0,
            "مكتمل": stats.completed_projects ?? 0,
            "متأخر": stats.delayed_projects ?? 0,
            "معلق": stats.on_hold_projects ?? 0
        };

        const months = stats.months ?? ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'];
        const progressSeries = stats.progress_series ?? months.map(() => Math.round(Math.random() * 80 + 10));

        /* ===== Bar Chart ===== */
        const ctxBar = document.getElementById('chartProjectsStatus')?.getContext('2d');
        if (ctxBar) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: Object.keys(projectsByStatus),
                    datasets: [{
                        data: Object.values(projectsByStatus),
                        backgroundColor: ['#2c5f2d', '#16a34a', '#ea580c', '#78716c'],
                        borderRadius: 4,
                        barThickness: 30
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        /* ===== Line Chart ===== */
        const ctxLine = document.getElementById('chartProgress')?.getContext('2d');
        if (ctxLine) {
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        data: progressSeries,
                        borderColor: '#2c5f2d',
                        backgroundColor: 'rgba(44, 95, 45, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 2
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        /* ===== Doughnut Chart ===== */
        const utilization = Math.min((stats.financial?.utilization_percentage ?? 0), 100);
        const ctxDough = document.getElementById('chartFinancial')?.getContext('2d');
        if (ctxDough) {
            new Chart(ctxDough, {
                type: 'doughnut',
                data: {
                    labels: ['مستخدم', 'متبقي'],
                    datasets: [{
                        data: [utilization, Math.max(0, 100 - utilization)],
                        backgroundColor: ['#2c5f2d', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: { legend: { display: false } },
                    responsive: true,
                    maintainAspectRatio: false
                },
                plugins: [{
                    id: 'centerText',
                    beforeDraw(chart) {
                        const { width, height, ctx: c } = chart;
                        c.restore();
                        c.font = 'bold 20px Tajawal';
                        c.fillStyle = '#2c5f2d';
                        c.textBaseline = 'middle';
                        c.textAlign = 'center';
                        c.fillText(utilization.toFixed(1) + '%', width / 2, height / 2);
                        c.save();
                    }
                }]
            });
        }
    });
</script>
@endpush
@endsection
