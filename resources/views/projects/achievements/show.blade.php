@extends('layouts.app')

@section('title', 'تفاصيل الإنجاز - ' . $project->project_name)

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8f 100%);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        color: white;
    }
    .text-gold { color: #c9a961 !important; }
    .info-label { font-size: 0.78rem; color: #64748b; font-weight: 600; margin-bottom: 0.2rem; }
    .info-value { font-size: 0.95rem; color: #0f172a; font-weight: 700; word-break: break-word; }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e8edf3;
        border-radius: 10px;
        padding: 0.9rem 1rem;
        height: 100%;
    }
    .section-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .section-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 700;
        color: #1e3a5f;
    }
    .section-card-body { padding: 1.5rem; }
    .section-icon {
        width: 36px; height: 36px;
        background: linear-gradient(135deg, #c9a961, #a8843f);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 0.9rem; flex-shrink: 0;
    }
    .funding-table { border-radius: 10px; overflow: hidden; font-size: 0.88rem; }
    .funding-table thead th {
        background: #1e3a5f; color: white; font-weight: 700;
        padding: 0.65rem 0.8rem; border: none;
    }
    .funding-table tbody td { padding: 0.6rem 0.8rem; vertical-align: middle; border-color: #f1f5f9; }
    .funding-table tbody tr:nth-child(even) { background: #f8fafc; }
    .doc-chip {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: #f1f5f9; border: 1px solid #e2e8f0;
        border-radius: 8px; padding: 0.4rem 0.75rem;
        font-size: 0.82rem; color: #334155;
        text-decoration: none; transition: all 0.2s; margin: 0.2rem;
    }
    .doc-chip:hover { background: #e2e8f0; color: #1e3a5f; }

    /* Big progress ring */
    .big-ring {
        width: 130px; height: 130px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        position: relative; margin: auto;
    }
    .big-ring-inner {
        width: 98px; height: 98px; border-radius: 50%;
        background: white; position: absolute;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
    }
    .big-ring-pct { font-size: 1.4rem; font-weight: 800; color: #1e3a5f; line-height: 1; }
    .big-ring-lbl { font-size: 0.65rem; color: #64748b; margin-top: 2px; }
</style>
@endsection

@section('content')
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="page-header mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width:48px;height:48px;background:rgba(201,169,97,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-trophy" style="color:#c9a961;font-size:1.4rem;"></i>
                    </div>
                    <div>
                        <h1 class="h4 fw-bold mb-0 text-white">تفاصيل الإنجاز</h1>
                        <p class="mb-0 opacity-75 small">{{ $project->project_name }}</p>
                    </div>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" style="font-size:0.82rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-white-50 text-decoration-none">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-white-50 text-decoration-none">المشاريع</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('projects.achievements.index', $project->id) }}" class="text-white-50 text-decoration-none">سجل الإنجازات</a></li>
                        <li class="breadcrumb-item active text-gold">تفاصيل الإنجاز</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('projects.achievements.print-single', [$project->id, $achievement->id]) }}" target="_blank"
                    class="btn btn-sm px-4 fw-bold"
                    style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-print me-2"></i>طباعة
                </a>
                <a href="{{ route('projects.achievements.index', $project->id) }}"
                    class="btn btn-sm px-4 fw-bold"
                    style="background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-arrow-right me-2"></i>العودة للسجل
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- Left: Progress & Summary --}}
        <div class="col-lg-3">
            <div class="section-card text-center">
                <div class="section-card-body">
                    {{-- Progress Ring --}}
                    @php $pct = (float)$achievement->new_achievement; @endphp
                    <div class="mb-3">
                        <div class="big-ring" style="background: conic-gradient(#c9a961 {{ $pct * 3.6 }}deg, #e2e8f0 0);">
                            <div class="big-ring-inner">
                                <div class="big-ring-pct">{{ number_format($pct, 1) }}%</div>
                                <div class="big-ring-lbl">نسبة الإنجاز</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label text-center">الإنجاز السابق</div>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                            {{ number_format($achievement->previous_achievement, 1) }}%
                        </span>
                    </div>
                    <div class="progress mb-3" style="height:8px;border-radius:20px;background:#f1f5f9;">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:linear-gradient(90deg,#c9a961,#a8843f);border-radius:20px;"></div>
                    </div>
                    <hr>
                    <div class="text-start">
                        <div class="info-label">نوع التقرير</div>
                        <div class="info-value small mb-2">{{ optional($achievement->reportType)->name ?? 'ـ' }}</div>
                        <div class="info-label">المدة</div>
                        <div class="info-value small mb-2">{{ $achievement->duration ?? 'ـ' }}</div>
                        <div class="info-label">عدد المستفيدين</div>
                        <div class="info-value fw-bold text-primary">{{ number_format($achievement->number_of_beneficiaries) }}</div>
                    </div>
                    <hr>
                    <div class="text-muted small mt-2">
                        <div><i class="fas fa-user-circle me-1 text-gold"></i> {{ optional($achievement->creator)->name ?? 'ـ' }}</div>
                        <div class="mt-1"><i class="fas fa-clock me-1 text-gold"></i> {{ $achievement->created_at?->format('Y/m/d H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Details --}}
        <div class="col-lg-9">

            {{-- Period --}}
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                    الفترة الزمنية
                </div>
                <div class="section-card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <div class="info-label">من (ميلادي)</div>
                                <div class="info-value">{{ $achievement->start_date_gregorian?->format('Y/m/d') ?? 'ـ' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <div class="info-label">من (هجري)</div>
                                <div class="info-value">{{ $achievement->start_date_hijri ?? 'ـ' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <div class="info-label">إلى (ميلادي)</div>
                                <div class="info-value">{{ $achievement->end_date_gregorian?->format('Y/m/d') ?? 'ـ' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <div class="info-label">إلى (هجري)</div>
                                <div class="info-value">{{ $achievement->end_date_hijri ?? 'ـ' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Outputs & Indicators --}}
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-tasks"></i></div>
                    المخرجات والمؤشرات
                </div>
                <div class="section-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label mb-2"><i class="fas fa-check-circle me-1 text-gold"></i>المخرجات المحققة</div>
                                <div class="info-value small" style="white-space:pre-line;font-weight:500;">{{ $achievement->achieved_outputs }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <div class="info-label mb-2"><i class="fas fa-chart-bar me-1 text-gold"></i>المؤشرات المحققة</div>
                                <div class="info-value small" style="white-space:pre-line;font-weight:500;">{{ $achievement->achieved_indicators }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Beneficiaries --}}
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-users"></i></div>
                    بيانات المستفيدين
                </div>
                <div class="section-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="background:linear-gradient(135deg,#1e3a5f,#2d5a8f);color:white;border-radius:12px;padding:0.75rem 1.5rem;font-size:1.8rem;font-weight:800;">
                            {{ number_format($achievement->number_of_beneficiaries) }}
                        </div>
                        <div class="text-muted small">إجمالي المستفيدين المسجّلين في هذه الفترة</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label mb-1">ملاحظات المستفيدين</div>
                        <div class="small" style="white-space:pre-line;">{{ $achievement->notes_on_beneficiaries }}</div>
                    </div>
                </div>
            </div>

            {{-- Funding --}}
            @if($achievement->fundings->count())
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-coins"></i></div>
                    بيانات الصرف والتمويل
                </div>
                <div class="section-card-body">
                    <div class="table-responsive">
                        <table class="table funding-table mb-0">
                            <thead>
                                <tr>
                                    <th>جهة التمويل</th>
                                    <th class="text-center">إجمالي التمويل</th>
                                    <th class="text-center">الصرف السابق</th>
                                    <th class="text-center">المتبقي السابق</th>
                                    <th class="text-center">الصرف الجديد</th>
                                    <th class="text-center">نسبة الصرف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($achievement->fundings as $f)
                                @php $dp = (float)$f->disbursement_percentage; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $f->funding_entity }}</td>
                                    <td class="text-center">{{ number_format($f->total_funding, 2) }}</td>
                                    <td class="text-center">{{ number_format($f->previous_disbursement, 2) }}</td>
                                    <td class="text-center">{{ number_format($f->previous_remaining_disbursement, 2) }}</td>
                                    <td class="text-center fw-bold text-success">{{ number_format($f->new_disbursement, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill" style="background:{{ $dp > 90 ? '#dc2626' : ($dp > 60 ? '#d97706' : '#059669') }};color:white;">
                                            {{ number_format($dp, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background:#f8fafc;font-weight:700;border-top:2px solid #1e3a5f;">
                                    <td class="text-gold">الإجمالي</td>
                                    <td class="text-center">{{ number_format($achievement->fundings->sum('total_funding'), 2) }}</td>
                                    <td class="text-center">{{ number_format($achievement->fundings->sum('previous_disbursement'), 2) }}</td>
                                    <td class="text-center">{{ number_format($achievement->fundings->sum('previous_remaining_disbursement'), 2) }}</td>
                                    <td class="text-center text-success">{{ number_format($achievement->fundings->sum('new_disbursement'), 2) }}</td>
                                    <td class="text-center">ـ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Documents --}}
            @if($achievement->documents->count())
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-paperclip"></i></div>
                    المستندات المرفقة ({{ $achievement->documents->count() }})
                </div>
                <div class="section-card-body">
                    @foreach($achievement->documents as $doc)
                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-chip">
                        <i class="fas fa-file-alt text-gold"></i>
                        {{ $doc->file_name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Comments --}}
            @if($achievement->comments)
            <div class="section-card">
                <div class="section-card-header">
                    <div class="section-icon"><i class="fas fa-comment-dots"></i></div>
                    ملاحظات إضافية
                </div>
                <div class="section-card-body">
                    <div style="white-space:pre-line;" class="text-dark">{{ $achievement->comments }}</div>
                </div>
            </div>
            @endif

        </div>{{-- /col-lg-9 --}}
    </div>{{-- /row --}}
</div>
@endsection
