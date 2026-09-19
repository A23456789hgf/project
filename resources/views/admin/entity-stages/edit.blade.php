@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 text-primary fw-bold">تعديل مراحل الجهة: {{ $entity->name }}</h4>
            <small class="text-muted">تفعيل أو تعطيل مراحل الموافقات وتعيين المسؤولين</small>
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
    @if(session('warning'))
        <div class="alert alert-warning border-0 shadow-sm rounded-4"><i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('admin.entity-stages.update', $entity->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <h5 class="fw-bold mb-4">المراحل المتاحة للجهة</h5>
                
                <div class="row g-4">
                    @foreach($stageTypes as $stageType)
                        @php
                            $stageConfig = $entity->approvalStages->firstWhere('stage', $stageType->value);
                            $isEnabled = $stageConfig !== null;
                            $assignedUserId = $stageConfig?->responsible_user_id;
                        @endphp
                        
                        <div class="col-12">
                            <div class="card border border-1 shadow-sm rounded-4 stage-edit-card {{ $isEnabled ? 'border-primary' : '' }}">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        
                                        <!-- Stage Toggle -->
                                        <div class="col-md-4 d-flex align-items-center gap-3">
                                            <div class="form-check form-switch fs-3 m-0">
                                                <input class="form-check-input stage-toggle" type="checkbox" role="switch" 
                                                    name="stages[{{ $stageType->value }}][enabled]" 
                                                    value="1" 
                                                    id="stage_toggle_{{ $stageType->value }}"
                                                    data-target="#user_select_container_{{ $stageType->value }}"
                                                    data-stage="{{ $stageType->value }}"
                                                    {{ old("stages.{$stageType->value}.enabled", $isEnabled) ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <label class="form-check-label fw-bold d-block fs-5" for="stage_toggle_{{ $stageType->value }}">
                                                    {{ $stageType->label() }}
                                                </label>
                                                <span class="badge bg-secondary">الترتيب: {{ $stageType->stageOrder() }}</span>
                                            </div>
                                        </div>

                                        <!-- User Selection -->
                                        <div class="col-md-8">
                                            <div id="user_select_container_{{ $stageType->value }}" 
                                                 class="{{ old("stages.{$stageType->value}.enabled", $isEnabled) ? '' : 'd-none' }}">
                                                
                                                <label class="form-label fw-semibold text-muted small">المسؤول عن هذه المرحلة</label>
                                                <select name="stages[{{ $stageType->value }}][responsible_user_id]" 
                                                        class="form-select user-select" 
                                                        data-entity="{{ $entity->id }}"
                                                        data-stage="{{ $stageType->value }}">
                                                    
                                                    <option value="">-- يتم تحديده تلقائياً من المستخدمين إن وجد / أو اختر يدوياً --</option>
                                                    @if($assignedUserId && $stageConfig->responsibleUser)
                                                        <option value="{{ $assignedUserId }}" selected>
                                                            {{ $stageConfig->responsibleUser->name }} (الحالي)
                                                        </option>
                                                    @endif
                                                </select>
                                                <div class="form-text">سيتم جلب المستخدمين الذين لديهم مسؤولية "{{ $stageType->label() }}" في هذه الجهة.</div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5 border-top pt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .stage-edit-card {
        transition: border-color 0.2s;
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        
        // Handle toggle visibility
        $('.stage-toggle').on('change', function() {
            const targetContainer = $($(this).data('target'));
            const card = $(this).closest('.stage-edit-card');
            
            if ($(this).is(':checked')) {
                targetContainer.removeClass('d-none');
                card.addClass('border-primary');
                
                // Fetch eligible users if the select is empty
                const selectEl = targetContainer.find('select');
                if(selectEl.find('option').length <= 1) {
                    loadEligibleUsers(selectEl);
                }
            } else {
                targetContainer.addClass('d-none');
                card.removeClass('border-primary');
            }
        });

        // Initialize Select2
        $('.user-select').select2({
            dir: "rtl",
            theme: "bootstrap-5",
            width: '100%'
        });

        // Load users on initial load for checked items
        $('.user-select').each(function() {
            if (!$(this).closest('div[id^="user_select_container"]').hasClass('d-none')) {
                // If there's only the default option, or just 1 selected option from backend, fetch full list
                loadEligibleUsers($(this));
            }
        });

        function loadEligibleUsers(selectElement) {
            const entityId = selectElement.data('entity');
            const stage = selectElement.data('stage');
            const currentValue = selectElement.val(); // Keep current selection

            $.ajax({
                url: "{{ route('admin.entity-stages.eligible-users') }}",
                data: { entity_id: entityId, stage: stage },
                success: function(users) {
                    // Store current option text if it exists so we don't lose it if it's not in the list (though it should be)
                    const currentText = selectElement.find('option:selected').text();
                    
                    selectElement.empty();
                    selectElement.append('<option value="">-- اختر المسؤول --</option>');
                    
                    let foundCurrent = false;
                    
                    users.forEach(function(user) {
                        const isSelected = (currentValue == user.id);
                        if (isSelected) foundCurrent = true;
                        
                        const option = new Option(user.name + ' (' + user.user_id + ')', user.id, false, isSelected);
                        selectElement.append(option);
                    });
                    
                    // Re-append the current value if it wasn't returned by AJAX (e.g. user was deactivated)
                    if (currentValue && !foundCurrent) {
                        selectElement.append(new Option(currentText + ' (غير متوفر في القائمة)', currentValue, false, true));
                    }
                    
                    selectElement.trigger('change');
                },
                error: function() {
                    console.error("Failed to load eligible users for stage " + stage);
                }
            });
        }
    });
</script>
@endsection
