@extends('layouts.app')

@section('title', 'ملخص الإيرادات والنفقات حسب البنود')

@section('content')
<div class="container-fluid">
    <!-- رأس الصفحة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 fw-bold">
                        <i class="fas fa-chart-pie text-primary me-2"></i>تقرير الإيرادات والنفقات
                    </h1>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        يتم عرض التقرير للجهة المحددة، ويمكن تفعيل «تضمين الجهات الأبناء» لعرض بيانات التابعين.
                    </p>
                </div>
                <div class="mt-2 mt-sm-0">
                    <span class="badge bg-light text-dark border px-3 py-2">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- حاوية الطباعة -->
    <div id="printable-wrapper">

        <!-- بطاقات الملخص -->
        @if(isset($summaryData) && count($summaryData) > 0)
        <div class="row g-4 mb-4">
            @foreach($summaryData as $summary)
                @php
                    $indicatorColor = 'primary';
                    if (isset($summary['indicator'])) {
                        switch(strtolower($summary['indicator'])) {
                            case 'green': $indicatorColor = 'success'; break;
                            case 'red': $indicatorColor = 'danger'; break;
                            case 'blue': $indicatorColor = 'info'; break;
                            case 'orange': $indicatorColor = 'warning'; break;
                            default: $indicatorColor = 'primary';
                        }
                    }
                    $formattedValue = number_format($summary['value'] ?? 0, 2);
                    $currency = $summary['currency'] ?? 'YER';
                    $iconMap = [
                        'success' => 'fa-arrow-up',
                        'danger'  => 'fa-arrow-down',
                        'warning' => 'fa-exclamation-triangle',
                        'info'    => 'fa-chart-line',
                        'primary' => 'fa-chart-bar',
                    ];
                    $icon = $iconMap[$indicatorColor] ?? 'fa-chart-bar';
                @endphp
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100 summary-card" style="border-right: 5px solid var(--bs-{{ $indicatorColor }});">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-uppercase text-xs fw-bold text-{{ $indicatorColor }} mb-1">
                                    {{ $summary['label'] ?? '' }}
                                </div>
                                <div class="h5 mb-0 fw-bold text-gray-800">
                                    {{ $formattedValue }} <small class="text-muted fs-6">{{ $currency }}</small>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="rounded-circle p-3 bg-{{ $indicatorColor }}-subtle text-{{ $indicatorColor }}">
                                    <i class="fas {{ $icon }} fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- فلاتر البحث -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fas fa-sliders-h me-2"></i>فلاتر البحث
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('projects.reports.pl_expense_summary') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <!-- الشركة -->
                        <div class="col-md-3">
                            <label for="company" class="form-label fw-semibold small">الجهة</label>
                            <select class="form-select form-select-sm" id="company" name="company" required onchange="this.form.submit()">
                                @if(isset($companies) && count($companies) > 0)
                                    @php
                                        $defaultUserCompany = auth()->user()?->entity?->erpnext_id ?: (auth()->user()?->entity?->name ?: 'New Alfajr');
                                    @endphp
                                    @foreach($companies as $comp)
                                        <option value="{{ $comp }}" {{ request('company', $defaultUserCompany) == $comp ? 'selected' : '' }}>{{ $comp }}</option>
                                    @endforeach
                                @else
                                    <option value="{{ auth()->user()?->entity?->erpnext_id ?: (auth()->user()?->entity?->name ?: 'New Alfajr') }}" selected>{{ auth()->user()?->entity?->name ?: 'New Alfajr' }}</option>
                                @endif
                            </select>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="include_children" id="include_children" value="1" {{ request()->boolean('include_children') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label text-muted small" for="include_children" style="cursor: pointer;">
                                    <i class="fas fa-sitemap me-1"></i> تضمين الجهات الأبناء
                                </label>
                            </div>
                        </div>

                        <!-- نوع الفلترة -->
                        <div class="col-md-2">
                            <label for="filter_based_on" class="form-label fw-semibold small">الفلترة بناءً على</label>
                            <select class="form-select form-select-sm" id="filter_based_on" name="filter_based_on" onchange="toggleDateInputs(this.value)">
                                <option value="Date Range" {{ request('filter_based_on') == 'Date Range' ? 'selected' : '' }}>نطاق زمني</option>
                                <option value="Fiscal Year" {{ request('filter_based_on') == 'Fiscal Year' ? 'selected' : '' }}>سنة مالية</option>
                            </select>
                        </div>

                        <!-- نطاق التاريخ -->
                        <div class="col-md-2 date-range-group" style="{{ request('filter_based_on', 'Date Range') == 'Date Range' ? '' : 'display:none;' }}">
                            <label for="from_date" class="form-label fw-semibold small">من تاريخ</label>
                            <input type="date" class="form-control form-control-sm" id="from_date" name="from_date" value="{{ request('from_date', now()->startOfYear()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2 date-range-group" style="{{ request('filter_based_on', 'Date Range') == 'Date Range' ? '' : 'display:none;' }}">
                            <label for="to_date" class="form-label fw-semibold small">إلى تاريخ</label>
                            <input type="date" class="form-control form-control-sm" id="to_date" name="to_date" value="{{ request('to_date', now()->endOfYear()->format('Y-m-d')) }}">
                        </div>

                        <!-- السنة المالية -->
                        <div class="col-md-2 fiscal-year-group" style="{{ request('filter_based_on') == 'Fiscal Year' ? '' : 'display:none;' }}">
                            <label for="from_fiscal_year" class="form-label fw-semibold small">من سنة</label>
                            <input type="number" min="2000" max="2100" class="form-control form-control-sm" id="from_fiscal_year" name="from_fiscal_year" value="{{ request('from_fiscal_year', now()->year) }}">
                        </div>
                        <div class="col-md-2 fiscal-year-group" style="{{ request('filter_based_on') == 'Fiscal Year' ? '' : 'display:none;' }}">
                            <label for="to_fiscal_year" class="form-label fw-semibold small">إلى سنة</label>
                            <input type="number" min="2000" max="2100" class="form-control form-control-sm" id="to_fiscal_year" name="to_fiscal_year" value="{{ request('to_fiscal_year', now()->year) }}">
                        </div>

                        <!-- مركز التكلفة -->
                        <div class="col-md-2">
                            <label for="cost_center" class="form-label fw-semibold small">مركز التكلفة</label>
                            <input type="text" class="form-control form-control-sm" id="cost_center" name="cost_center" value="{{ request('cost_center') }}" placeholder="اختياري">
                        </div>

                        <!-- المشروع -->
                        <div class="col-md-2">
                            <label for="project" class="form-label fw-semibold small">المشروع</label>
                            <select class="form-select form-select-sm" id="project" name="project" onchange="this.form.submit()">
                                <option value="">-- الكل --</option>
                                @if(isset($projects))
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj['name'] }}" {{ request('project') == $proj['name'] ? 'selected' : '' }}>
                                            {{ $proj['project_name'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- طريقة التجميع -->
                        <div class="col-md-2">
                            <label for="categorize_by" class="form-label fw-semibold small">طريقة العرض</label>
                            <select class="form-select form-select-sm" id="categorize_by" name="categorize_by" onchange="this.form.submit()">
                                <option value="" {{ request('categorize_by') == '' ? 'selected' : '' }}>تفصيلي (افتراضي)</option>
                                <option value="Categorize by Voucher (Consolidated)" {{ request('categorize_by') == 'Categorize by Voucher (Consolidated)' ? 'selected' : '' }}>تجميع حسب السند (موحد)</option>
                                <option value="Categorize by Voucher" {{ request('categorize_by') == 'Categorize by Voucher' ? 'selected' : '' }}>تجميع حسب السند</option>
                                <option value="Categorize by Account" {{ request('categorize_by') == 'Categorize by Account' ? 'selected' : '' }}>تجميع حسب الحساب</option>
                            </select>
                        </div>

                        <!-- أزرار الإجراء -->
                        <div class="col-md-12 mt-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sync-alt me-1"></i> تحديث
                                </button>
                                @can('reports.pl_expense_summary.export')
                                <a href="{{ route('projects.reports.pl_expense_summary', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-file-excel me-1"></i> إكسل
                                </a>
                                @endcan
                                @can('reports.pl_expense_summary.print')
                                <a href="{{ route('projects.reports.pl_expense_summary', array_merge(request()->all(), ['export' => 'print'])) }}" target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-print me-1"></i> طباعة
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- رسائل الخطأ -->
        @if(isset($reportData['success']) && !$reportData['success'])
        <div class="alert alert-danger shadow-sm border-0">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i> فشل جلب التقرير</h5>
            <p class="mb-0">{{ $reportData['error'] ?? 'حدث خطأ غير معروف.' }}</p>
        </div>
        @endif

        <!-- عرض البيانات في تبويبات -->
        @if(isset($reportData['success']) && $reportData['success'])
            @php
                $isProjectFiltered = request()->filled('project');
                $hasProjectGl = isset($reportData['project_gl_result']) && count($reportData['project_gl_result']) > 0;
                $hasGeneralGl = isset($reportData['gl_result']) && count($reportData['gl_result']) > 0;
                $hasSummaryRows = isset($reportData['result']) && count($reportData['result']) > 0;
                $activeTab = 'summary';
                if ($isProjectFiltered && $hasProjectGl) {
                    $activeTab = 'project-gl';
                } elseif (!$hasSummaryRows && $hasGeneralGl) {
                    $activeTab = 'gl';
                } elseif (!$hasSummaryRows && $hasProjectGl) {
                    $activeTab = 'project-gl';
                }
            @endphp

            <!-- تبويبات التنقل -->
            <ul class="nav nav-tabs nav-fill mb-4" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'summary' ? 'active' : '' }}" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                        <i class="fas fa-chart-bar me-1"></i> ملخص التقرير
                        @if(isset($reportData['result']))
                            <span class="badge bg-secondary ms-1">{{ count($reportData['result']) }}</span>
                        @endif
                    </button>
                </li>
                @if(isset($reportData['gl_result']) && count($reportData['gl_result']) > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'gl' ? 'active' : '' }}" id="gl-tab" data-bs-toggle="tab" data-bs-target="#gl" type="button" role="tab">
                        <i class="fas fa-university me-1"></i> المالية العامة
                        <span class="badge bg-primary ms-1">{{ count($reportData['gl_result']) }}</span>
                    </button>
                </li>
                @endif
                @if(isset($reportData['project_gl_result']) && count($reportData['project_gl_result']) > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'project-gl' ? 'active' : '' }}" id="project-gl-tab" data-bs-toggle="tab" data-bs-target="#project-gl" type="button" role="tab">
                        <i class="fas fa-folder-open me-1"></i> عمليات المشاريع
                        <span class="badge bg-success ms-1">{{ count($reportData['project_gl_result']) }}</span>
                    </button>
                </li>
                @endif
                @if(isset($itemsBreakdown) && count($itemsBreakdown) > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="items-tab" data-bs-toggle="tab" data-bs-target="#items" type="button" role="tab">
                        <i class="fas fa-tags me-1"></i> المصروفات حسب البنود
                        <span class="badge bg-warning text-dark ms-1">{{ count($itemsBreakdown) }}</span>
                    </button>
                </li>
                @endif
                @if(isset($activityActionCosts) && count($activityActionCosts) > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="activity-costs-tab" data-bs-toggle="tab" data-bs-target="#activity-costs" type="button" role="tab">
                        <i class="fas fa-tasks me-1"></i> النفقات بالأنشطة
                        <span class="badge bg-info ms-1">{{ count($activityActionCosts) }}</span>
                    </button>
                </li>
                @endif
            </ul>

            <!-- محتوى التبويبات -->
            <div class="tab-content" id="reportTabsContent">
                <!-- تبويب الملخص -->
                <div class="tab-pane fade {{ $activeTab === 'summary' ? 'show active' : '' }}" id="summary" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-1"></i> نتائج التقرير</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light"><i class="far fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control tab-date-from" data-table="profitAndLossTable" style="max-width:130px;" title="من">
                                    <span class="input-group-text bg-light">إلى</span>
                                    <input type="date" class="form-control tab-date-to" data-table="profitAndLossTable" style="max-width:130px;" title="إلى">
                                    <button type="button" class="btn btn-primary apply-date-filter" data-table="profitAndLossTable"><i class="fas fa-filter"></i></button>
                                    <button type="button" class="btn btn-outline-secondary reset-date-filter" data-table="profitAndLossTable"><i class="fas fa-undo"></i></button>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary sort-asc-btn" data-table="profitAndLossTable" data-col="0"><i class="fas fa-sort-amount-up-alt"></i></button>
                                    <button type="button" class="btn btn-outline-primary sort-desc-btn" data-table="profitAndLossTable" data-col="0"><i class="fas fa-sort-amount-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="profitAndLossTable" width="100%">
                                    <thead class="table-light">
                                        <tr>
                                            @foreach($reportData['columns'] as $column)
                                                <th>{{ is_array($column) ? ($column['label'] ?? $column['fieldname']) : $column }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['result'] as $row)
                                            @php
                                                $acc = $row['account'] ?? '';
                                                $isTotal = str_contains($acc, 'إجمالي') || str_contains($acc, 'الربح') || str_contains($acc, 'الخسار');
                                            @endphp
                                            <tr class="{{ $isTotal ? 'table-secondary fw-bold' : '' }}">
                                                @foreach($reportData['columns'] as $column)
                                                    @php
                                                        $fieldName = is_array($column) ? ($column['fieldname'] ?? '') : $column;
                                                        $value = is_array($row) ? ($row[$fieldName] ?? '') : '';
                                                        $isNumeric = is_numeric($value) && !in_array($fieldName, ['acc_number', 'posting_date']);
                                                    @endphp
                                                    <td class="{{ $isNumeric ? 'text-end fw-bold' : '' }}">
                                                        @if($fieldName === 'company')
                                                            <span class="badge bg-light text-primary border">{{ $value }}</span>
                                                        @elseif($fieldName === 'posting_date')
                                                            <span class="text-nowrap"><i class="far fa-calendar-alt text-muted me-1"></i>{{ $value }}</span>
                                                        @elseif($isNumeric)
                                                            {{ number_format($value, 2) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- تبويب المالية العامة -->
                @if(isset($reportData['gl_result']) && count($reportData['gl_result']) > 0)
                <div class="tab-pane fade {{ $activeTab === 'gl' ? 'show active' : '' }}" id="gl" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-university me-1"></i> العمليات المالية العامة (خارج المشاريع)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light"><i class="far fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control tab-date-from" data-table="glTable" style="max-width:130px;">
                                    <span class="input-group-text bg-light">إلى</span>
                                    <input type="date" class="form-control tab-date-to" data-table="glTable" style="max-width:130px;">
                                    <button type="button" class="btn btn-primary apply-date-filter" data-table="glTable"><i class="fas fa-filter"></i></button>
                                    <button type="button" class="btn btn-outline-secondary reset-date-filter" data-table="glTable"><i class="fas fa-undo"></i></button>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary sort-asc-btn" data-table="glTable" data-col="0"><i class="fas fa-sort-amount-up-alt"></i></button>
                                    <button type="button" class="btn btn-outline-primary sort-desc-btn" data-table="glTable" data-col="0"><i class="fas fa-sort-amount-down"></i></button>
                                </div>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('projects.reports.pl_expense_summary.print_general', request()->all()) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-print me-1"></i> طباعة</a>
                                    <a href="{{ route('projects.reports.pl_expense_summary.export_general_excel', request()->all()) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i> إكسل</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="glTable" width="100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>التاريخ</th>
                                            @if(request()->boolean('include_children'))<th>الجهة</th>@endif
                                            <th>رقم السند</th>
                                            <th>الحساب</th>
                                            <th>بند النفقة</th>
                                            <th class="text-end">مدين</th>
                                            <th class="text-end">دائن</th>
                                            <th>المشروع</th>
                                            <th>مركز التكلفة</th>
                                            <th>البيان</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['gl_result'] as $glRow)
                                        <tr>
                                            <td class="text-nowrap"><i class="far fa-calendar-alt text-muted me-1"></i>{{ $glRow['posting_date'] ?? '' }}</td>
                                            @if(request()->boolean('include_children'))<td><span class="badge bg-light text-primary border">{{ $glRow['company'] ?? '' }}</span></td>@endif
                                            <td>{{ $glRow['voucher_no'] ?? '' }}</td>
                                            <td>{{ $glRow['account'] ?? '' }}</td>
                                            <td>{{ $glRow['claim_expense_type'] ?? '' }}</td>
                                            <td class="text-end text-danger fw-bold">{{ number_format($glRow['debit'] ?? 0, 2) }}</td>
                                            <td class="text-end text-success fw-bold">{{ number_format($glRow['credit'] ?? 0, 2) }}</td>
                                            <td>{{ $glRow['project'] ?? '' }}</td>
                                            <td>{{ $glRow['cost_center'] ?? '' }}</td>
                                            <td>{{ $glRow['remarks'] ?? '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- تبويب عمليات المشاريع -->
                @if(isset($reportData['project_gl_result']) && count($reportData['project_gl_result']) > 0)
                <div class="tab-pane fade {{ $activeTab === 'project-gl' ? 'show active' : '' }}" id="project-gl" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-success"><i class="fas fa-folder-open me-1"></i> العمليات المالية للمشاريع (ERPNext)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <div class="input-group input-group-sm" style="width: auto;">
                                    <span class="input-group-text bg-light"><i class="far fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control tab-date-from" data-table="projectGlTable" style="max-width:130px;">
                                    <span class="input-group-text bg-light">إلى</span>
                                    <input type="date" class="form-control tab-date-to" data-table="projectGlTable" style="max-width:130px;">
                                    <button type="button" class="btn btn-success apply-date-filter" data-table="projectGlTable"><i class="fas fa-filter"></i></button>
                                    <button type="button" class="btn btn-outline-secondary reset-date-filter" data-table="projectGlTable"><i class="fas fa-undo"></i></button>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-success sort-asc-btn" data-table="projectGlTable" data-col="0"><i class="fas fa-sort-amount-up-alt"></i></button>
                                    <button type="button" class="btn btn-outline-success sort-desc-btn" data-table="projectGlTable" data-col="0"><i class="fas fa-sort-amount-down"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="projectGlTable" width="100%">
                                    <thead class="table-success">
                                        <tr>
                                            <th>التاريخ</th>
                                            @if(request()->boolean('include_children'))<th>الجهة</th>@endif
                                            <th>رقم السند</th>
                                            <th>الحساب</th>
                                            <th>بند النفقة</th>
                                            <th>المشروع</th>
                                            <th class="text-end">مدين</th>
                                            <th class="text-end">دائن</th>
                                            <th>مركز التكلفة</th>
                                            <th>البيان</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['project_gl_result'] as $glRow)
                                        <tr>
                                            <td class="text-nowrap"><i class="far fa-calendar-alt text-muted me-1"></i>{{ $glRow['posting_date'] ?? '' }}</td>
                                            @if(request()->boolean('include_children'))<td><span class="badge bg-light text-primary border">{{ $glRow['company'] ?? '' }}</span></td>@endif
                                            <td>{{ $glRow['voucher_no'] ?? '' }}</td>
                                            <td>{{ $glRow['account'] ?? '' }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $glRow['claim_expense_type'] ?? '' }}</span></td>
                                            <td><span class="fw-bold text-primary"><i class="fas fa-project-diagram me-1"></i>{{ $glRow['project'] ?? '' }}</span></td>
                                            <td class="text-end text-danger fw-bold">{{ number_format($glRow['debit'] ?? 0, 2) }}</td>
                                            <td class="text-end text-success fw-bold">{{ number_format($glRow['credit'] ?? 0, 2) }}</td>
                                            <td>{{ $glRow['cost_center'] ?? '' }}</td>
                                            <td>{{ $glRow['remarks'] ?? '' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- تبويب المصروفات حسب البنود -->
                @if(isset($itemsBreakdown) && count($itemsBreakdown) > 0)
                <div class="tab-pane fade" id="items" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary"><i class="fas fa-tags me-1"></i> ملخص المصروفات حسب البنود المالية</h6>
                            <div class="d-flex gap-2">
                                <a href="{{ route('projects.reports.pl_expense_summary.print_items', request()->all()) }}" target="_blank" class="btn btn-outline-warning btn-sm"><i class="fas fa-print me-1"></i> طباعة</a>
                                <a href="{{ route('projects.reports.pl_expense_summary.export_items_excel', request()->all()) }}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i> إكسل</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="itemsTable" width="100%">
                                    <thead class="table-primary">
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th>بند النفقة</th>
                                            <th class="text-center">عدد العمليات</th>
                                            <th class="text-end">إجمالي المبلغ (YER)</th>
                                            <th class="text-end">النسبة المئوية</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1; $totalCount = 0; @endphp
                                        @foreach($itemsBreakdown as $itemType => $data)
                                            @php
                                                $cnt = $data['count'] ?? 0;
                                                $amt = $data['amount'] ?? 0.0;
                                                $totalCount += $cnt;
                                                $pct = ($totalItemsExpenses ?? 0) > 0 ? ($amt / ($totalItemsExpenses ?? 1)) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td><span class="fw-bold"><i class="fas fa-tag text-muted me-1"></i>{{ $itemType }}</span></td>
                                                <td class="text-center"><span class="badge bg-light text-dark border">{{ number_format($cnt) }}</span></td>
                                                <td class="text-end fw-bold text-danger">{{ number_format($amt, 2) }}</td>
                                                <td class="text-end fw-bold text-primary">{{ number_format($pct, 1) }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-secondary fw-bold">
                                        <tr>
                                            <td colspan="2">الإجمالي العام</td>
                                            <td class="text-center">{{ number_format($totalCount) }}</td>
                                            <td class="text-end text-danger fs-6">{{ number_format($totalItemsExpenses ?? 0, 2) }}</td>
                                            <td class="text-end text-primary">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- تبويب النفقات بالأنشطة -->
                @if(isset($activityActionCosts) && count($activityActionCosts) > 0)
                <div class="tab-pane fade" id="activity-costs" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <h6 class="m-0 fw-bold text-primary">
                                <i class="fas fa-tasks me-1"></i> النفقات المرتبطة بالأنشطة والإجراءات
                                <small class="text-muted fw-normal ms-2">(إجمالي الميزانية: {{ number_format($totalActivityBudget ?? 0, 2) }} YER)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="activityCostsTable" width="100%">
                                    <thead class="table-info">
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>المشروع</th>
                                            <th>النشاط</th>
                                            <th>الإجراء</th>
                                            <th>البند المالي</th>
                                            <th class="text-center">الكمية</th>
                                            <th class="text-end">سعر الوحدة</th>
                                            <th class="text-end">الميزانية (YER)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activityActionCosts as $idx => $costRow)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $costRow['project_name'] }}</span>
                                                @if(!empty($costRow['erpnext_project_id']))
                                                    <br><small class="text-muted">{{ $costRow['erpnext_project_id'] }}</small>
                                                @endif
                                            </td>
                                            <td><i class="fas fa-layer-group text-muted me-1"></i>{{ $costRow['activity_name'] }}</td>
                                            <td><i class="fas fa-angle-right text-muted me-1"></i>{{ $costRow['action_name'] }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $costRow['financial_item_name'] }}</span></td>
                                            <td class="text-center">{{ number_format($costRow['quantity'], 0) }}</td>
                                            <td class="text-end">{{ number_format($costRow['unit_cost'], 2) }}</td>
                                            <td class="text-end fw-bold text-info">{{ number_format($costRow['approved_budget'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-secondary fw-bold">
                                        <tr>
                                            <td colspan="7">الإجمالي العام للميزانية</td>
                                            <td class="text-end text-info fs-6">{{ number_format($totalActivityBudget ?? 0, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>

@push('styles')
<!-- Google Fonts (اختياري) -->
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css" rel="stylesheet">

<style>
    /* الخط العام */
    body, .form-control, .btn, .table, .nav-link, .card-title, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
    }
    /* تحسين المظهر العام */
    .container-fluid {
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }
    .card {
        border-radius: 0.75rem;
        transition: box-shadow 0.2s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.08) !important;
    }
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    .summary-card {
        border-radius: 0.75rem;
        border-right-width: 6px !important;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.75rem 1.5rem rgba(0,0,0,0.1) !important;
    }
    .bg-soft-primary { background-color: #e8f0fe; }
    .bg-soft-success { background-color: #e6f9f0; }
    .bg-soft-danger { background-color: #fce4e4; }
    .bg-soft-warning { background-color: #fef3d7; }
    .bg-soft-info { background-color: #e0f4f7; }
    .text-xs {
        font-size: 0.7rem;
        letter-spacing: 0.05em;
    }
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
        background-color: #f8f9fc;
    }
    .table td {
        font-size: 0.9rem;
        vertical-align: middle;
    }
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    .btn-sm {
        padding: 0.35rem 0.8rem;
        font-size: 0.8rem;
    }
    .form-select-sm, .form-control-sm {
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
    }
    .nav-tabs .nav-link {
        font-weight: 500;
        color: #4a5568;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.6rem 1rem;
        transition: color 0.15s, border-color 0.15s;
    }
    .nav-tabs .nav-link:hover {
        border-color: #e2e8f0;
        color: #1a202c;
    }
    .nav-tabs .nav-link.active {
        color: #4e73df;
        border-bottom-color: #4e73df;
        background-color: transparent;
    }
    .nav-tabs .nav-link .badge {
        font-size: 0.7rem;
        margin-right: 0.25rem;
    }
    /* تنسيق حقول التاريخ في الجداول */
    .tab-date-from, .tab-date-to {
        max-width: 130px;
        min-width: 110px;
    }
    @media (max-width: 768px) {
        .tab-date-from, .tab-date-to {
            max-width: 100px;
            min-width: 80px;
        }
    }
    /* تحسين الطباعة */
    @media print {
        body * { visibility: hidden; }
        #printable-wrapper, #printable-wrapper * { visibility: visible; }
        #printable-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .tab-pane { display: block !important; opacity: 1 !important; }
        .nav-tabs, .dataTables_wrapper .row:first-child, .dataTables_wrapper .row:last-child,
        .btn, .input-group, .d-flex.gap-2, .card-header .d-flex {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 2rem !important;
        }
        .card-header {
            background: transparent !important;
            border-bottom: 2px solid #000 !important;
            padding: 0 0 10px 0 !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            border: 1px solid #ddd !important;
            padding: 6px !important;
        }
        .badge { background-color: #f0f0f0 !important; color: #000 !important; border: 1px solid #ccc !important; }
    }
    /* تنسيق الأيقونات في البطاقات */
    .rounded-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
    }
    .bg-success-subtle { background-color: #d1e7dd; }
    .bg-danger-subtle { background-color: #f8d7da; }
    .bg-warning-subtle { background-color: #fff3cd; }
    .bg-info-subtle { background-color: #cff4fc; }
    .bg-primary-subtle { background-color: #cfe2ff; }
</style>
@endpush

@push('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script>
    var dataTablesInstances = {};
    var tableDateFilters = {
        profitAndLossTable: { from: null, to: null, col: 0 },
        glTable: { from: null, to: null, col: 0 },
        projectGlTable: { from: null, to: null, col: 0 },
        activityCostsTable: { from: null, to: null, col: 0 }
    };

    var allGlTransactions = @json(array_merge($reportData['gl_result'] ?? [], $reportData['project_gl_result'] ?? []));
    var initialItemsBreakdown = @json($itemsBreakdown ?? []);
    var initialTotalItemsExpenses = {{ (float) ($totalItemsExpenses ?? 0) }};

    // إضافة فلترة التاريخ المخصصة لـ DataTables
    $.fn.dataTable.ext.afnFiltering.push(function(settings, data, dataIndex) {
        var tableId = settings.sTableId;
        if (!tableDateFilters[tableId]) return true;
        var filter = tableDateFilters[tableId];
        if (!filter.from && !filter.to) return true;
        var colIdx = filter.col !== undefined ? filter.col : 0;
        var cellText = data[colIdx] || '';
        var match = cellText.match(/\d{4}-\d{2}-\d{2}/);
        if (!match) return true;
        var rowDate = match[0];
        if (filter.from && rowDate < filter.from) return false;
        if (filter.to && rowDate > filter.to) return false;
        return true;
    });

    $(document).ready(function() {
        // تهيئة الجداول
        var tables = [
            { id: 'profitAndLossTable', order: [[0, 'desc']] },
            { id: 'glTable', order: [[0, 'desc']] },
            { id: 'projectGlTable', order: [[0, 'desc']] },
            { id: 'itemsTable', order: [[3, 'desc']] },
            { id: 'activityCostsTable', order: [[0, 'desc']] }
        ];

        tables.forEach(function(t) {
            if ($('#' + t.id).length) {
                dataTablesInstances[t.id] = $('#' + t.id).DataTable({
                    language: { url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json" },
                    pageLength: 50,
                    ordering: true,
                    order: t.order
                });
            }
        });

        // أحداث أزرار الفلترة والفرز
        $('.apply-date-filter').on('click', function() {
            var tableId = $(this).data('table');
            var container = $(this).closest('.card-header');
            var fromDate = container.find('.tab-date-from').val();
            var toDate = container.find('.tab-date-to').val();
            if (tableId === 'itemsTable') {
                recalculateItemsTable(fromDate, toDate);
            } else {
                applyDateFilterToTable(tableId, fromDate, toDate);
            }
        });

        $('.tab-date-from, .tab-date-to').on('change', function() {
            var tableId = $(this).data('table');
            var container = $(this).closest('.card-header');
            var fromDate = container.find('.tab-date-from').val();
            var toDate = container.find('.tab-date-to').val();
            if (tableId === 'itemsTable') {
                recalculateItemsTable(fromDate, toDate);
            } else {
                applyDateFilterToTable(tableId, fromDate, toDate);
            }
        });

        $('.reset-date-filter').on('click', function() {
            var tableId = $(this).data('table');
            var container = $(this).closest('.card-header');
            container.find('.tab-date-from').val('');
            container.find('.tab-date-to').val('');
            if (tableId === 'itemsTable') {
                recalculateItemsTable('', '');
            } else {
                applyDateFilterToTable(tableId, '', '');
            }
        });

        $('.sort-asc-btn').on('click', function() {
            var tableId = $(this).data('table');
            var colIdx = parseInt($(this).data('col') || 0);
            if (dataTablesInstances[tableId]) {
                dataTablesInstances[tableId].order([[colIdx, 'asc']]).draw();
            }
        });

        $('.sort-desc-btn').on('click', function() {
            var tableId = $(this).data('table');
            var colIdx = parseInt($(this).data('col') || 0);
            if (dataTablesInstances[tableId]) {
                dataTablesInstances[tableId].order([[colIdx, 'desc']]).draw();
            }
        });

        // دوال مساعدة
        function applyDateFilterToTable(tableId, fromDate, toDate) {
            tableDateFilters[tableId] = {
                from: fromDate || null,
                to: toDate || null,
                col: 0
            };
            if (dataTablesInstances[tableId]) {
                dataTablesInstances[tableId].draw();
                updateBadgeCount(tableId);
            }
        }

        function recalculateItemsTable(fromDate, toDate) {
            if (!dataTablesInstances['itemsTable'] || !allGlTransactions.length) return;

            var filteredGl = allGlTransactions.filter(function(gl) {
                var date = (gl.posting_date || '').trim();
                if (fromDate && date && date < fromDate) return false;
                if (toDate && date && date > toDate) return false;
                return true;
            });

            var itemsMap = {};
            var totalExpenses = 0.0;
            filteredGl.forEach(function(gl) {
                var debit = parseFloat(gl.debit) || 0;
                var credit = parseFloat(gl.credit) || 0;
                var rootType = (gl.root_type || '').toLowerCase();
                var accountType = (gl.account_type || '').toLowerCase();
                if (!rootType && !accountType) {
                    var acc = (gl.account || '').toLowerCase();
                    if (acc.includes('expense') || acc.includes('مصاريف') || acc.includes('نفقات') || acc.includes('ايجار')) {
                        rootType = 'expense';
                    }
                }
                if (rootType === 'expense' || accountType.includes('expense')) {
                    var amt = debit - credit;
                    var claimType = (gl.claim_expense_type || '').trim();
                    if (!claimType || claimType === 'No Expense Type found') {
                        claimType = 'مصروفات غير محددة البند (Unlinked)';
                    }
                    if (!itemsMap[claimType]) {
                        itemsMap[claimType] = { count: 0, amount: 0.0 };
                    }
                    itemsMap[claimType].count++;
                    itemsMap[claimType].amount += amt;
                    totalExpenses += amt;
                }
            });

            var table = dataTablesInstances['itemsTable'];
            table.clear();
            var i = 1;
            var totalCount = 0;
            Object.keys(itemsMap).forEach(function(itemType) {
                var data = itemsMap[itemType];
                var cnt = data.count;
                var amt = data.amount;
                totalCount += cnt;
                var pct = totalExpenses > 0 ? (amt / totalExpenses) * 100 : 0;
                table.row.add([
                    i++,
                    '<span class="fw-bold"><i class="fas fa-tag text-muted me-1"></i>' + itemType + '</span>',
                    '<span class="badge bg-light text-dark border">' + cnt.toLocaleString() + '</span>',
                    amt.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                    pct.toFixed(1) + '%'
                ]);
            });
            table.draw();

            // تحديث التذييل
            var footer = $('#itemsTable tfoot');
            footer.find('td:eq(1)').text('الإجمالي العام');
            footer.find('td:eq(2)').text(totalCount.toLocaleString());
            footer.find('td:eq(3)').text(totalExpenses.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            footer.find('td:eq(4)').text('100%');
        }

        function updateBadgeCount(tableId) {
            if (dataTablesInstances[tableId]) {
                var info = dataTablesInstances[tableId].page.info();
                var count = info.recordsDisplay;
                var badge = $('#' + tableId.replace('Table', 'TabBadge'));
                if (badge.length) badge.text(count);
                if (tableId === 'projectGlTable') {
                    var badge2 = $('#projectGlBadge');
                    if (badge2.length) badge2.text(count + ' عملية مالية');
                }
            }
        }

        // دالة toggle لتاريخ الفلترة (منطق موجود مسبقاً)
        window.toggleDateInputs = function(filterType) {
            if (filterType === 'Date Range') {
                $('.date-range-group').show();
                $('.fiscal-year-group').hide();
            } else {
                $('.date-range-group').hide();
                $('.fiscal-year-group').show();
            }
        };

        // تنفيذ التبديل عند تحميل الصفحة
        toggleDateInputs($('#filter_based_on').val());

        // تحديث البادجات بعد التحميل
        setTimeout(function() {
            ['profitAndLossTable', 'glTable', 'projectGlTable', 'activityCostsTable'].forEach(updateBadgeCount);
        }, 300);
    });
</script>
@endpush
@endsection