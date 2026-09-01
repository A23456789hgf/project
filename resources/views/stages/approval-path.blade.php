@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2>
                    <i class="fas fa-sitemap me-2"></i>مسار الاعتماد: {{ $stage->name_ar }}
                </h2>
                <a href="{{ route('stages.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>رجوع
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-project-diagram me-2"></i>مراحل الاعتماد التالية
                    </h5>
                </div>
                <div class="card-body">
                    @if($nextStages->count() > 0)
                        <div class="approval-path-diagram">
                            <div class="row align-items-center">
                                <div class="col-md-auto text-center mb-3 mb-md-0">
                                    <div class="stage-box bg-primary text-white p-3 rounded">
                                        <h6 class="mb-0">{{ $stage->name_ar }}</h6>
                                        <small class="text-white-50">{{ $stage->code }}</small>
                                    </div>
                                </div>
                                <div class="col-md-auto text-center mb-3 mb-md-0">
                                    <i class="fas fa-arrow-right fa-2x text-primary"></i>
                                </div>
                                @forelse($nextStages as $index => $nextStage)
                                    <div class="col-md-auto text-center mb-3 mb-md-0">
                                        <div class="stage-box bg-success text-white p-3 rounded">
                                            <h6 class="mb-0">{{ $nextStage->name_ar }}</h6>
                                            <small class="text-white-50">{{ $nextStage->code }}</small>
                                        </div>
                                    </div>
                                    @if(!$loop->last)
                                        <div class="col-md-auto text-center mb-3 mb-md-0">
                                            <i class="fas fa-arrow-right fa-2x text-success"></i>
                                        </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">تفاصيل المراحل التالية:</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>المرحلة</th>
                                        <th>الكود</th>
                                        <th>النوع</th>
                                        <th>الوصف</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nextStages as $index => $nextStage)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $nextStage->name_ar }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $nextStage->name_en }}</small>
                                            </td>
                                            <td>
                                                <code class="bg-light p-2 rounded">{{ $nextStage->code }}</code>
                                            </td>
                                            <td>
                                                @php
                                                    $typeLabels = [
                                                        'association' => ['label' => 'جمعية', 'class' => 'bg-primary'],
                                                        'union' => ['label' => 'اتحاد', 'class' => 'bg-success'],
                                                        'unit' => ['label' => 'وحدة', 'class' => 'bg-info'],
                                                        'general_admin' => ['label' => 'إدارة عامة', 'class' => 'bg-secondary'],
                                                        'sector' => ['label' => 'قطاع', 'class' => 'bg-dark'],
                                                        'directorate_office' => ['label' => 'مكتب مديرية', 'class' => 'bg-warning text-dark'],
                                                        'governorate_office' => ['label' => 'مكتب محافظة', 'class' => 'bg-warning'],
                                                        'committee' => ['label' => 'لجنة', 'class' => 'bg-danger'],
                                                        'execution' => ['label' => 'تنفيذ', 'class' => 'bg-success']
                                                    ];
                                                @endphp
                                                <span class="badge {{ $typeLabels[$nextStage->type]['class'] ?? 'bg-secondary' }}">
                                                    {{ $typeLabels[$nextStage->type]['label'] ?? $nextStage->type }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $nextStage->description_ar ? Str::limit($nextStage->description_ar, 50) : '-' }}
                                            </td>
                                            <td>
                                                @if($nextStage->is_active)
                                                    <span class="badge bg-success">نشط</span>
                                                @else
                                                    <span class="badge bg-danger">غير نشط</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>لا توجد مراحل تالية معرفة لهذه المرحلة.</strong>
                            <p class="mb-0 mt-2">هذه قد تكون مرحلة نهائية في مسار الاعتماد.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>معلومات المرحلة الحالية
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>الاسم (عربي):</strong> {{ $stage->name_ar }}
                            </p>
                            <p>
                                <strong>الاسم (إنجليزي):</strong> {{ $stage->name_en ?? '-' }}
                            </p>
                            <p>
                                <strong>الكود:</strong> <code>{{ $stage->code }}</code>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>النوع:</strong> 
                                @php
                                    $typeLabels = [
                                        'association' => 'جمعية',
                                        'union' => 'اتحاد',
                                        'unit' => 'وحدة',
                                        'general_admin' => 'إدارة عامة',
                                        'sector' => 'قطاع',
                                        'directorate_office' => 'مكتب مديرية',
                                        'governorate_office' => 'مكتب محافظة',
                                        'committee' => 'لجنة',
                                        'execution' => 'تنفيذ'
                                    ];
                                @endphp
                                {{ $typeLabels[$stage->type] ?? $stage->type }}
                            </p>
                            <p>
                                <strong>الترتيب:</strong> {{ $stage->order }}
                            </p>
                            <p>
                                <strong>نظامي:</strong> {{ $stage->is_system ? 'نعم' : 'لا' }}
                            </p>
                        </div>
                    </div>
                    @if($stage->description_ar)
                        <p>
                            <strong>الوصف:</strong> {{ $stage->description_ar }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stage-box {
    min-width: 150px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
@endsection
