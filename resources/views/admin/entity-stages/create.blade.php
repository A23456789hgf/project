@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-primary fw-bold">إضافة مراحل لجهة</h4>
            <small class="text-muted">تحديد مراحل المراجعة والاعتماد لجهة معينة</small>
        </div>
        <a href="{{ route('admin.entity-stages.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('admin.entity-stages.store') }}" method="POST">
                @csrf
                
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">الجهة <span class="text-danger">*</span></label>
                        <select name="entity_id" class="form-select select2-search" required>
                            <option value="">-- اختر الجهة --</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity->id }}" @selected(old('entity_id') == $entity->id)>
                                    {{ $entity->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">اختر الجهة التي تريد تفعيل مسار الموافقات لها.</div>
                    </div>
                </div>

                <hr class="border-light">

                <div class="mb-4">
                    <h5 class="fw-bold mb-3">مراحل المراجعة والاعتماد</h5>
                    <p class="text-muted small mb-4">اختر المراحل التي سيمر بها المشروع عند إرساله من هذه الجهة. سيتم ترتيبها منطقياً بشكل تلقائي.</p>
                    
                    <div class="row g-4">
                        @foreach($stageTypes as $stageType)
                            <div class="col-md-4">
                                <div class="card border border-2 h-100 rounded-4 stage-card">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <div class="form-check form-switch fs-4 m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                name="stages[]" 
                                                value="{{ $stageType->value }}" 
                                                id="stage_{{ $stageType->value }}"
                                                {{ in_array($stageType->value, old('stages', [])) ? 'checked' : '' }}>
                                        </div>
                                        <div>
                                            <label class="form-check-label fw-bold d-block" for="stage_{{ $stageType->value }}">
                                                {{ $stageType->label() }}
                                            </label>
                                            <small class="text-muted">ترتيب التنفيذ: {{ $stageType->stageOrder() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5">
                    <button type="reset" class="btn btn-light px-4">تفريغ الحقول</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> حفظ المراحل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .stage-card {
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .stage-card:hover {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.03);
    }
    .stage-card:has(input:checked) {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2-search').select2({
            dir: "rtl",
            theme: "bootstrap-5",
            width: '100%'
        });

        // Make the whole card clickable for the checkbox
        $('.stage-card').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });
</script>
@endsection
