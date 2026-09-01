@extends('layouts.app')

@section('styles')
<style>
    /* Achievement Cards */
    .achievement-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        margin-bottom: 1.5rem;
        transition: box-shadow 0.25s, transform 0.25s;
        overflow: hidden;
    }
    .achievement-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.10);
        transform: translateY(-2px);
    }
    .achievement-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
        border-bottom: 2px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .achievement-number {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #c9a961, #a8843f);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 800;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .achievement-body { padding: 1.5rem; }

    /* Progress ring */
    .progress-ring-circle {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .progress-ring-inner {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.9rem;
        color: #1e3a5f;
        position: absolute;
    }

    .info-label {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .info-value {
        font-size: 0.95rem;
        color: #0f172a;
        font-weight: 700;
        word-break: break-word;
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e8edf3;
        border-radius: 10px;
        padding: 0.9rem 1rem;
        height: 100%;
    }

    .funding-table { border-radius: 10px; overflow: hidden; font-size: 0.88rem; }
    .funding-table thead th {
        background: #1e3a5f; color: white; font-weight: 700;
        padding: 0.6rem 0.75rem; border: none;
    }
    .funding-table tbody td { padding: 0.55rem 0.75rem; vertical-align: middle; border-color: #f1f5f9; }
    .funding-table tbody tr:nth-child(even) { background: #f8fafc; }

    .timeline-wrap { position: relative; }
    .timeline-wrap::before {
        content: '';
        position: absolute;
        right: 21px;
        top: 60px;
        bottom: 10px;
        width: 2px;
        background: linear-gradient(to bottom, #c9a961, transparent);
    }

    .doc-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.4rem 0.75rem;
        font-size: 0.82rem;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s;
        margin: 0.2rem;
    }
    .doc-chip:hover { background: #e2e8f0; color: #1e3a5f; }

    .empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
    .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.4; }

    .stats-bar {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .stat-item { display: flex; flex-direction: column; align-items: center; min-width: 80px; }
    .stat-num { font-size: 1.6rem; font-weight: 800; color: #1e3a5f; line-height: 1; }
    .stat-lbl { font-size: 0.78rem; color: #64748b; margin-top: 0.25rem; }
    .stat-divider { width: 1px; background: #e2e8f0; height: 40px; flex-shrink: 0; }

    @media (max-width: 768px) {
        .achievement-card-header { flex-direction: column; align-items: flex-start; }
        .stats-bar { flex-direction: column; gap: 1rem; }
        .stat-divider { display: none; }
    }
</style>
@endsection

@section('content')
    <x-index-page title="سجل إنجازات المشروع" icon="trophy">

        <x-slot name="breadcrumb">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:0.82rem;">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.index') }}" class="text-decoration-none text-muted">المشاريع</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('projects.show', $project->id) }}" class="text-decoration-none text-muted">{{ Str::limit($project->project_name, 30) }}</a></li>
                    <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">سجل الإنجازات</li>
                </ol>
            </nav>
        </x-slot>

        <x-slot name="headerActions">
            <a href="{{ route('projects.achievements.print', $project->id) }}" target="_blank"
                class="btn btn-outline-secondary fw-bold">
                <i class="fas fa-print me-2"></i>طباعة التقرير الكامل
            </a>
            @can('update', $project)
            <a href="{{ route('projects.achievements.create', $project->id) }}"
                class="btn btn-primary fw-bold">
                <i class="fas fa-plus-circle me-2"></i>تسجيل إنجاز جديد
            </a>
            @endcan
        </x-slot>

        <x-slot name="filters">
            {{-- Project Info Summary --}}
            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-hashtag me-1 text-gold" style="color:#c9a961;"></i> رقم المشروع</div>
                        <div class="info-value">{{ $project->form_number ?? 'ـ' }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-map-marker-alt me-1 text-gold" style="color:#c9a961;"></i> المحافظة</div>
                        <div class="info-value">{{ $governorates }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-building me-1 text-gold" style="color:#c9a961;"></i> جهة التنفيذ</div>
                        <div class="info-value">{{ $implementingEntitiesList }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box">
                        <div class="info-label"><i class="fas fa-trophy me-1 text-gold" style="color:#c9a961;"></i> آخر نسبة إنجاز</div>
                        <div class="info-value">
                            <span class="badge rounded-pill px-3 py-1" style="background:linear-gradient(135deg,#c9a961,#a8843f);color:white;font-size:0.9rem;">
                                {{ number_format($latestAchievement, 1) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot>

        @if($achievements->count() > 0)
            <x-slot name="stats">
                <div class="stat-item">
                    <div class="stat-num text-gold" style="color:#c9a961;">{{ $achievements->count() }}</div>
                    <div class="stat-lbl">إجمالي الإنجازات</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-num" style="color:#1e3a5f;">{{ number_format($latestAchievement, 1) }}%</div>
                    <div class="stat-lbl">نسبة الإنجاز الحالية</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-num" style="color:#059669;font-size:1.2rem;">{{ number_format($achievements->sum(fn($a) => $a->fundings->sum('new_disbursement')), 0) }}</div>
                    <div class="stat-lbl">إجمالي الصرف (ريال)</div>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <div class="stat-num" style="color:#7c3aed;">{{ number_format($achievements->sum('number_of_beneficiaries')) }}</div>
                    <div class="stat-lbl">إجمالي المستفيدين</div>
                </div>
            </x-slot>
        @endif

        {{-- Achievements Timeline --}}
        @if($achievements->isEmpty())
            <div class="empty-state text-center py-5">
                <i class="fas fa-trophy fa-3x mb-3" style="color:#c9a961;"></i>
                <h5 class="fw-bold text-muted mb-2">لا توجد إنجازات مسجّلة بعد</h5>
                <p class="text-muted mb-4">ابدأ بتسجيل أول إنجاز لهذا المشروع لتتبع التقدم المحقق</p>
                @can('update', $project)
                <a href="{{ route('projects.achievements.create', $project->id) }}"
                    class="btn btn-primary px-5 fw-bold">
                    <i class="fas fa-plus-circle me-2"></i>تسجيل أول إنجاز
                </a>
                @endcan
            </div>
        @else
            <div class="timeline-wrap">
            @foreach($achievements as $index => $achievement)
                @php $pct = (float)$achievement->new_achievement; @endphp
                <div class="achievement-card">
                    {{-- Card Header --}}
                    <div class="achievement-card-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="achievement-number">{{ $achievements->count() - $index }}</div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size:1rem;">
                                    {{ optional($achievement->reportType)->name ?? 'تقرير إنجاز' }}
                                </div>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-alt me-1 text-gold" style="color:#c9a961;"></i>
                                    {{ $achievement->start_date_gregorian?->format('Y/m/d') ?? 'ـ' }}
                                    <span class="mx-1">←</span>
                                    {{ $achievement->end_date_gregorian?->format('Y/m/d') ?? 'ـ' }}
                                    @if($achievement->duration)
                                        <span class="badge bg-secondary-subtle text-secondary ms-2 small">{{ $achievement->duration }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            {{-- Progress Ring (CSS conic-gradient) --}}
                            <div class="text-center">
                                <div class="progress-ring-circle" style="background: conic-gradient(#c9a961 {{ $pct * 3.6 }}deg, #e2e8f0 0); position:relative;">
                                    <div class="progress-ring-inner">{{ number_format($pct, 1) }}%</div>
                                </div>
                                <div class="text-muted mt-1" style="font-size:0.72rem;">نسبة الإنجاز</div>
                            </div>
                            {{-- Actions --}}
                            <div class="d-flex flex-column gap-1">
                                <a href="{{ route('projects.achievements.show', [$project->id, $achievement->id]) }}"
                                   class="btn btn-sm btn-primary px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-eye me-1"></i>تفاصيل
                                </a>
                                <a href="{{ route('projects.achievements.print-single', [$project->id, $achievement->id]) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-secondary px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-print me-1"></i>طباعة
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="achievement-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="info-label">من (هجري)</div>
                                <div class="info-value">{{ $achievement->start_date_hijri ?? 'ـ' }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">إلى (هجري)</div>
                                <div class="info-value">{{ $achievement->end_date_hijri ?? 'ـ' }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">الإنجاز السابق</div>
                                <div class="info-value">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        {{ number_format($achievement->previous_achievement, 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">عدد المستفيدين</div>
                                <div class="info-value text-primary fw-bold">{{ number_format($achievement->number_of_beneficiaries) }}</div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold text-muted">نسبة التقدم الكلية</span>
                                <span class="fw-bold text-gold" style="color:#c9a961;">{{ number_format($pct, 1) }}%</span>
                            </div>
                            <div class="progress" style="height:10px;border-radius:20px;background:#f1f5f9;">
                                <div class="progress-bar" role="progressbar"
                                    style="width:{{ $pct }}%;background:linear-gradient(90deg,#c9a961,#a8843f);border-radius:20px;">
                                </div>
                            </div>
                        </div>

                        {{-- Outputs & Indicators --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label"><i class="fas fa-tasks me-1 text-gold" style="color:#c9a961;"></i>المخرجات المحققة</div>
                                    <div class="info-value small" style="white-space:pre-line;">{{ $achievement->achieved_outputs }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <div class="info-label"><i class="fas fa-chart-bar me-1 text-gold" style="color:#c9a961;"></i>المؤشرات المحققة</div>
                                    <div class="info-value small" style="white-space:pre-line;">{{ $achievement->achieved_indicators }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="info-box mb-3">
                            <div class="info-label"><i class="fas fa-users me-1 text-gold" style="color:#c9a961;"></i>ملاحظات المستفيدين</div>
                            <div class="info-value small">{{ $achievement->notes_on_beneficiaries }}</div>
                        </div>

                        @if($achievement->comments)
                        <div class="info-box mb-3" style="border-color:#c9a961;">
                            <div class="info-label"><i class="fas fa-comment-dots me-1 text-gold" style="color:#c9a961;"></i>ملاحظات إضافية</div>
                            <div class="info-value small">{{ $achievement->comments }}</div>
                        </div>
                        @endif

                        {{-- Funding Table --}}
                        @if($achievement->fundings->count())
                        <div class="mb-3">
                            <div class="info-label mb-2"><i class="fas fa-coins me-1 text-gold" style="color:#c9a961;"></i>بيانات الصرف والتمويل</div>
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
                                        <tr>
                                            <td class="fw-semibold">{{ $f->funding_entity }}</td>
                                            <td class="text-center">{{ number_format($f->total_funding, 2) }}</td>
                                            <td class="text-center">{{ number_format($f->previous_disbursement, 2) }}</td>
                                            <td class="text-center">{{ number_format($f->previous_remaining_disbursement, 2) }}</td>
                                            <td class="text-center fw-bold text-success">{{ number_format($f->new_disbursement, 2) }}</td>
                                            <td class="text-center">
                                                @php $dp = (float)$f->disbursement_percentage; @endphp
                                                <span class="badge rounded-pill" style="background:{{ $dp > 90 ? '#dc2626' : ($dp > 60 ? '#d97706' : '#059669') }};color:white;">
                                                    {{ number_format($dp, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr style="background:#f8fafc;font-weight:700;border-top:2px solid #1e3a5f;">
                                            <td style="color:#c9a961;">الإجمالي</td>
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
                        @endif

                        {{-- Documents --}}
                        @if($achievement->documents->count())
                        <div>
                            <div class="info-label mb-2"><i class="fas fa-paperclip me-1 text-gold" style="color:#c9a961;"></i>المستندات المرفقة ({{ $achievement->documents->count() }})</div>
                            @foreach($achievement->documents as $doc)
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="doc-chip">
                                <i class="fas fa-file-alt text-gold" style="color:#c9a961;"></i>
                                {{ $doc->file_name }}
                            </a>
                            @endforeach
                        </div>
                        @endif

                        <div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="text-muted small">
                                <i class="fas fa-user-circle me-1"></i>
                                سُجِّل بواسطة: <strong>{{ optional($achievement->creator)->name ?? 'غير محدد' }}</strong>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                {{ $achievement->created_at?->format('Y/m/d H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>
        @endif

        {{-- Back Button --}}
        <div class="text-center mt-3 mb-2">
            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-outline-secondary px-5">
                <i class="fas fa-arrow-right me-2"></i>العودة إلى المشروع
            </a>
        </div>

    </x-index-page>
@endsection
