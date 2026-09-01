@extends('layouts.app')

@section('styles')
<style>
    :root {
        --emp-primary: #1a5276;
        --emp-secondary: #2e86c1;
        --emp-accent: #1abc9c;
    }

    .emp-hero {
        background: linear-gradient(135deg, var(--emp-primary) 0%, #2980b9 60%, var(--emp-accent) 100%);
        border-radius: 18px;
        padding: 2rem 2.5rem;
        color: white;
        margin-bottom: 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .emp-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.07);
    }
    .emp-hero h1 { font-size: 1.6rem; font-weight: 800; }
    .emp-hero p  { opacity: .85; }

    .stat-card {
        background: white;
        border-radius: 14px;
        padding: 1.2rem 1.4rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        border-right: 5px solid var(--emp-secondary);
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 22px rgba(0,0,0,0.10); }
    .stat-card .stat-value { font-size: 1.7rem; font-weight: 800; color: var(--emp-primary); }
    .stat-card .stat-label { color: #6c757d; font-size: .78rem; font-weight: 600; text-transform: uppercase; }

    .table-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .table-card .card-header {
        background: linear-gradient(135deg, var(--emp-primary), var(--emp-secondary));
        color: white;
        padding: 1rem 1.5rem;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .65rem;
    }

    .emp-table { width: 100%; border-collapse: collapse; }
    .emp-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .9rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .emp-table td {
        padding: .85rem 1rem;
        font-size: .88rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .emp-table tbody tr { transition: background .15s; }
    .emp-table tbody tr:hover { background: #f0f9ff; }
    .emp-table tbody tr:last-child td { border-bottom: none; }

    .project-link { color: var(--emp-primary); font-weight: 700; text-decoration: none; }
    .project-link:hover { text-decoration: underline; }

    .search-box {
        border-radius: 50px;
        border: 2px solid rgba(255,255,255,.4);
        background: rgba(255,255,255,.15);
        color: white;
        padding: .4rem 1rem;
        font-size: .83rem;
        width: 200px;
    }
    .search-box::placeholder { color: rgba(255,255,255,.65); }
    .search-box:focus { border-color: white; background: rgba(255,255,255,.25); outline: none; color: white; }

    .status-select {
        border-radius: 50px;
        border: 2px solid rgba(255,255,255,.4);
        background: rgba(255,255,255,.15);
        color: white;
        padding: .4rem .9rem;
        font-size: .83rem;
    }
    .status-select option { color: #333; background: white; }
    .status-select:focus { border-color: white; outline: none; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .28rem .7rem;
        border-radius: 50px;
        font-size: .72rem;
        font-weight: 700;
    }

    .no-data-emp { text-align: center; padding: 3.5rem 2rem; color: #94a3b8; }
    .no-data-emp i { font-size: 3rem; margin-bottom: .9rem; display: block; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">

    {{-- Hero --}}
    <div class="emp-hero">
        <div class="row align-items-center">
            <div class="col">
                <h1><i class="fas fa-hand-holding-usd me-2"></i> إدارة التمكين — القروض والحسابات</h1>
                <p class="mb-0">مشاريع التمكين المُحالة تلقائياً فور اكتمال المسودة للمعالجة في قسم القروض.</p>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="background:transparent;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-white-50">المشاريع</a></li>
                        <li class="breadcrumb-item active text-white">التمكين</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">{{ \App\Models\EmpowermentProject::count() }}</div>
                <div class="stat-label"><i class="fas fa-project-diagram me-1"></i> إجمالي</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-right-color:#f39c12;">
                <div class="stat-value" style="color:#e67e22;">{{ \App\Models\EmpowermentProject::where('status','pending')->count() }}</div>
                <div class="stat-label"><i class="fas fa-clock me-1"></i> بانتظار المعالجة</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-right-color:#17a2b8;">
                <div class="stat-value" style="color:#17a2b8;">{{ \App\Models\EmpowermentProject::where('status','under_review')->count() }}</div>
                <div class="stat-label"><i class="fas fa-search me-1"></i> قيد المراجعة</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-right-color:#1abc9c;">
                <div class="stat-value text-success">{{ \App\Models\EmpowermentProject::where('status','processed')->count() }}</div>
                <div class="stat-label"><i class="fas fa-check-circle me-1"></i> تمت المعالجة</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="card-header">
            <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2"></i> قائمة مشاريع التمكين</h6>

            <form method="GET" action="{{ route('projects.empowerment') }}" class="d-flex align-items-center gap-2">
                <select name="status" class="status-select" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    @foreach($statuses as $key => $s)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" class="search-box" placeholder="بحث...">
                <button class="btn btn-light btn-sm rounded-pill" type="submit"><i class="fas fa-search"></i></button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('projects.empowerment') }}" class="btn btn-outline-light btn-sm rounded-pill"><i class="fas fa-times"></i></a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            @if($empowermentProjects->count() > 0)
            <table class="emp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>رقم المشروع</th>
                        <th>اسم المشروع</th>
                        <th>الجهة مقدمة المشروع</th>
                        <th class="text-end">إجمالي مبلغ القرض</th>
                        <th class="text-center">عدد المستفيدين</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">تاريخ الإحالة</th>
                        <th class="text-center">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empowermentProjects as $item)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration + ($empowermentProjects->currentPage() - 1) * $empowermentProjects->perPage() }}</td>
                        <td>
                            @can('empowerment.view', $item)
                                <a href="{{ route('projects.empowerment.show', $item->id) }}" class="project-link auth-perm-empowerment-view">
                                    {{ $item->project_number ?? '—' }}
                                </a>
                            @else
                                <span class="text-muted">{{ $item->project_number ?? '—' }}</span>
                            @endcan
                        </td>
                        <td class="fw-semibold">
                            @can('empowerment.view', $item)
                                <a href="{{ route('projects.empowerment.show', $item->id) }}" class="project-link auth-perm-empowerment-view">
                                    {{ Str::limit($item->project_name, 45) }}
                                </a>
                            @else
                                <span class="text-muted">{{ Str::limit($item->project_name, 45) }}</span>
                            @endcan
                        </td>
                        <td class="text-muted small">{{ $item->submitting_entity ?? '—' }}</td>
                        <td class="text-end font-monospace fw-bold text-success">
                            {{ number_format($item->total_loan_amount, 0) }}
                        </td>
                        <td class="text-center">{{ number_format($item->number_of_beneficiaries) }}</td>
                        <td class="text-center">
                            <span class="status-badge bg-{{ $item->status_badge }}-subtle text-{{ $item->status_badge }}">
                                <i class="fas {{ $item->status_icon }}"></i>
                                {{ $item->status_label }}
                            </span>
                        </td>
                        <td class="text-center small text-muted">{{ $item->created_at->format('Y-m-d') }}</td>
                        <td class="text-center">
                            @can('empowerment.view', $item)
                                <a href="{{ route('projects.empowerment.show', $item->id) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-3 auth-perm-empowerment-view">
                                    <i class="fas fa-eye me-1"></i> عرض
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data-emp">
                <i class="fas fa-hand-holding-usd text-muted opacity-25"></i>
                <h5 class="text-muted">لا توجد مشاريع تمكين</h5>
                <p class="text-muted small">ستظهر المشاريع هنا تلقائياً فور اكتمال المسودة وتضمين تمويل التمكين.</p>
            </div>
            @endif
        </div>

        @if($empowermentProjects->hasPages())
        <div class="p-3 border-top d-flex justify-content-center">
            {{ $empowermentProjects->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
