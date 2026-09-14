@extends('layouts.app')

@section('title', 'تقرير الأرباح والخسائر')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 text-gray-800">تقرير الأرباح والخسائر (Profit and Loss Statement)</h1>
        </div>
    </div>

    <!-- عرض ملخص التقرير (Summary) -->
    @if(isset($summaryData) && count($summaryData) > 0)
        <div class="row mb-4 printable-area">
            @foreach($summaryData as $summary)
                @php
                    $indicatorColor = 'primary';
                    if (isset($summary['indicator'])) {
                        switch(strtolower($summary['indicator'])) {
                            case 'green': $indicatorColor = 'success'; break;
                            case 'red': $indicatorColor = 'danger'; break;
                            case 'blue': $indicatorColor = 'info'; break;
                            case 'orange': $indicatorColor = 'warning'; break;
                            default: $indicatorColor = 'primary'; break;
                        }
                    }
                    $formattedValue = number_format($summary['value'] ?? 0, 2);
                    $currency = $summary['currency'] ?? '';
                @endphp
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-{{ $indicatorColor }} shadow h-100 py-2 custom-card" style="border-left: 4px solid var(--{{ $indicatorColor }});">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-{{ $indicatorColor }} text-uppercase mb-1">
                                        {{ $summary['label'] ?? '' }}
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $formattedValue }} <small>{{ $currency }}</small>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- فلاتر التقرير -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">فلاتر البحث</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('projects.reports.profit_and_loss') }}">
                <div class="row">
                    <!-- الشركة -->
                    <div class="col-md-3 mb-3">
                        <label for="company">الشركة (Company)</label>
                        <select class="form-control" id="company" name="company" required onchange="this.form.submit()">
                            @if(isset($companies) && count($companies) > 0)
                                @php
                                    $defaultUserCompany = auth()->user()->entity?->erpnext_id ?: (auth()->user()->entity?->name ?: 'New Alfajr');
                                @endphp
                                @foreach($companies as $comp)
                                    <option value="{{ $comp }}" {{ request('company', $defaultUserCompany) == $comp ? 'selected' : '' }}>{{ $comp }}</option>
                                @endforeach
                            @else
                                <option value="{{ auth()->user()->entity?->erpnext_id ?: (auth()->user()->entity?->name ?: 'New Alfajr') }}" selected>{{ auth()->user()->entity?->name ?: 'New Alfajr' }}</option>
                            @endif
                        </select>
                    </div>

                    <!-- نوع الفلترة -->
                    <div class="col-md-3 mb-3">
                        <label for="filter_based_on">الفلترة بناءً على</label>
                        <select class="form-control" id="filter_based_on" name="filter_based_on" onchange="toggleDateInputs(this.value)">
                            <option value="Date Range" {{ request('filter_based_on') == 'Date Range' ? 'selected' : '' }}>نطاق زمني (Date Range)</option>
                            <option value="Fiscal Year" {{ request('filter_based_on') == 'Fiscal Year' ? 'selected' : '' }}>سنة مالية (Fiscal Year)</option>
                        </select>
                    </div>

                    <!-- نطاق التاريخ -->
                    <div class="col-md-3 mb-3 date-range-group" style="{{ request('filter_based_on', 'Date Range') == 'Date Range' ? '' : 'display:none;' }}">
                        <label for="from_date">من تاريخ (From Date)</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date', now()->startOfYear()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3 date-range-group" style="{{ request('filter_based_on', 'Date Range') == 'Date Range' ? '' : 'display:none;' }}">
                        <label for="to_date">إلى تاريخ (To Date)</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date', now()->endOfYear()->format('Y-m-d')) }}">
                    </div>

                    <!-- السنة المالية -->
                    <div class="col-md-3 mb-3 fiscal-year-group" style="{{ request('filter_based_on') == 'Fiscal Year' ? '' : 'display:none;' }}">
                        <label for="from_fiscal_year">من سنة مالية</label>
                        <input type="number" min="2000" max="2100" class="form-control" id="from_fiscal_year" name="from_fiscal_year" value="{{ request('from_fiscal_year', now()->year) }}">
                    </div>
                    <div class="col-md-3 mb-3 fiscal-year-group" style="{{ request('filter_based_on') == 'Fiscal Year' ? '' : 'display:none;' }}">
                        <label for="to_fiscal_year">إلى سنة مالية</label>
                        <input type="number" min="2000" max="2100" class="form-control" id="to_fiscal_year" name="to_fiscal_year" value="{{ request('to_fiscal_year', now()->year) }}">
                    </div>

                    <!-- مركز التكلفة -->
                    <div class="col-md-3 mb-3">
                        <label for="cost_center">مركز التكلفة (Cost Center) - اختياري</label>
                        <input type="text" class="form-control" id="cost_center" name="cost_center" value="{{ request('cost_center') }}">
                    </div>

                    <!-- المشروع -->
                    <div class="col-md-3 mb-3">
                        <label for="project">المشروع (Project) - اختياري</label>
                        <select class="form-control" id="project" name="project" onchange="this.form.submit()">
                            <option value="">-- كل المشاريع --</option>
                            @if(isset($projects))
                                @foreach($projects as $proj)
                                    <option value="{{ $proj['name'] }}" {{ request('project') == $proj['name'] ? 'selected' : '' }}>
                                        - {{ $proj['project_name'] }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-sync-alt"></i> تحديث التقرير
                        </button>
                        @can('reports.profit_and_loss.export')
                        <a href="{{ route('projects.reports.profit_and_loss', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success mr-2">
                            <i class="fas fa-file-excel"></i> تصدير إكسل
                        </a>
                        @endcan
                        @can('reports.profit_and_loss.print')
                        <a href="{{ route('projects.reports.profit_and_loss', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-secondary">
                            <i class="fas fa-print"></i> طباعة
                        </a>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- رسائل الخطأ -->
    @if(isset($reportData['success']) && !$reportData['success'])
        <div class="alert alert-danger shadow">
            <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> فشل في جلب التقرير!</h4>
            <p>{{ $reportData['error'] ?? 'حدث خطأ غير معروف.' }}</p>
        </div>
    @endif


    <!-- عرض البيانات -->
    @if(isset($reportData['success']) && $reportData['success'])
        <div class="card shadow mb-4 printable-area">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">نتائج التقرير</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="profitAndLossTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                @foreach($reportData['columns'] as $column)
                                    <th>{{ is_array($column) ? ($column['label'] ?? $column['fieldname']) : $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['result'] as $row)
                                <tr>
                                    @foreach($reportData['columns'] as $column)
                                        @php
                                            $fieldName = is_array($column) ? ($column['fieldname'] ?? '') : $column;
                                            $value = is_array($row) ? ($row[$fieldName] ?? '') : '';
                                        @endphp
                                        <td>{{ $value }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .printable-area, .printable-area * {
            visibility: visible;
        }
        .printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
    .gap-2 { gap: 0.5rem; }
</style>
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    $(document).ready(function() {
        if ($('#profitAndLossTable').length) {
            $('#profitAndLossTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json"
                },
                "pageLength": 50,
                "ordering": false // Disable initial sorting to maintain accounting structure
            });
        }
    });

    function toggleDateInputs(filterType) {
        if (filterType === 'Date Range') {
            $('.date-range-group').show();
            $('.fiscal-year-group').hide();
        } else {
            $('.date-range-group').hide();
            $('.fiscal-year-group').show();
        }
    }
</script>
@endpush
@endsection
