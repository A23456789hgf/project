@php
    $linkedEntities = collect();
    $targetProject = $currentProject ?? $project ?? null;
    if ($targetProject) {
        $linkedEntities = $targetProject->getProjectLinkedEntities();
    }
@endphp

<div class="executive-assigned-row mb-2 p-2 border rounded bg-light" data-activity-index="{{ $activityIndex }}" data-action-index="{{ $actionIndex }}" data-assigned-index="{{ $assignedIndex }}">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label class="form-label small fw-bold">الجهة *</label>
                <input type="hidden" name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][id]" value="{{ $assigned->id ?? '' }}">
                <select class="form-select form-select-sm" 
                        name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][entity]"
                        {{ !$canModifyStructure ? 'disabled' : '' }}>
                    <option value="">اختر الجهة المكلفة</option>
                    @foreach($linkedEntities as $entityName)
                        <option value="{{ $entityName }}" 
                            {{ (old("executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.entity", $assigned->entity ?? '') == $entityName) ? 'selected' : '' }}>
                            {{ $entityName }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text text-muted small">اسم الجهة المنفذة</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group mb-2">
                <label class="form-label small fw-bold">الاسم *</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][name]" 
                       value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.name", $assigned->name ?? '') }}"
                       placeholder="أدخل اسم الشخص أو الفريق"
                       {{ !$canModifyStructure ? 'disabled' : '' }}
                       maxlength="255">
                <div class="form-text text-muted small">اسم المسؤول عن التنفيذ</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-2">
                <label class="form-label small fw-bold">المهمة *</label>
                <textarea 
                    class="form-control form-control-sm" 
                    name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][task]" 
                    placeholder="وصف المهمة أو الدور المكلف به"
                    {{ !$canModifyStructure ? 'disabled' : '' }}
                    rows="2"
                    maxlength="500">{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.task", $assigned->task ?? '') }}</textarea>
                <div class="form-text text-muted small">وصف المهمة المكلف بها</div>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group mb-2 d-flex align-items-end h-100">
                @if($canModifyStructure)
                <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-executive-assigned-btn" 
                        title="حذف الجهة المكلفة"
                        data-bs-toggle="tooltip">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    
    <!-- معلومات إضافية مخفية -->
    <div class="row mt-2 additional-fields" style="display: none;">
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label small">البريد الإلكتروني</label>
                <input type="email" 
                       class="form-control form-control-sm" 
                       name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][email]" 
                       value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.email", $assigned->email ?? '') }}"
                       placeholder="example@domain.com"
                       maxlength="255">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label class="form-label small">رقم الهاتف</label>
                <input type="tel" 
                       class="form-control form-control-sm" 
                       name="executive_activities[{{ $activityIndex }}][actions][{{ $actionIndex }}][assigned_entities][{{ $assignedIndex }}][phone]" 
                       value="{{ old("executive_activities.{$activityIndex}.actions.{$actionIndex}.assigned_entities.{$assignedIndex}.phone", $assigned->phone ?? '') }}"
                       placeholder="05XXXXXXXX"
                       maxlength="20">
            </div>
        </div>
    </div>
    
    <!-- زر عرض/إخفاء الحقول الإضافية -->
    {{-- <div class="row">
        <div class="col-12">
            <button type="button" class="btn btn-sm btn-outline-secondary toggle-additional-fields">
                <i class="fas fa-plus me-1"></i>
                إضافة معلومات اتصال
            </button>
        </div>
    </div> --}}
</div>

<style>
.executive-assigned-row {
    transition: all 0.3s ease;
    border-left: 4px solid #007bff !important;
}

.executive-assigned-row:hover {
    background-color: #f8f9fa !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.delete-executive-assigned-btn {
    transition: all 0.3s ease;
}

.delete-executive-assigned-btn:hover {
    transform: scale(1.1);
}

.additional-fields {
    border-top: 1px dashed #dee2e6;
    padding-top: 10px;
}

.toggle-additional-fields {
    font-size: 0.8rem;
    padding: 2px 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثيرات للحقول
    const assignedRows = document.querySelectorAll('.executive-assigned-row');
    
    assignedRows.forEach(row => {
        // زر الحذف
        const deleteBtn = row.querySelector('.delete-executive-assigned-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'تأكيد العملية',
                    text: 'هل أنت متأكد من حذف هذه الجهة المكلفة؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'لا',
                    reverseButtons: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            updateAssignedIndexes();
                        }, 300);
                    }
                });
            });
        }
        
        // تبديل الحقول الإضافية
        const toggleBtn = row.querySelector('.toggle-additional-fields');
        const additionalFields = row.querySelector('.additional-fields');
        const toggleIcon = toggleBtn.querySelector('i');
        
        if (toggleBtn && additionalFields) {
            toggleBtn.addEventListener('click', function() {
                if (additionalFields.style.display === 'none') {
                    additionalFields.style.display = 'block';
                    toggleIcon.className = 'fas fa-minus me-1';
                    toggleBtn.innerHTML = '<i class="fas fa-minus me-1"></i>إخفاء معلومات الاتصال';
                } else {
                    additionalFields.style.display = 'none';
                    toggleIcon.className = 'fas fa-plus me-1';
                    toggleBtn.innerHTML = '<i class="fas fa-plus me-1"></i>إضافة معلومات اتصال';
                }
            });
        }
    });
    
    // تحديث الفهرس بعد الحذف
    function updateAssignedIndexes() {
        const activityContainers = document.querySelectorAll('[id^="executive-activity-"]');
        
        activityContainers.forEach((activityContainer, activityIndex) => {
            const actionContainers = activityContainer.querySelectorAll('[id^="executive-action-"]');
            
            actionContainers.forEach((actionContainer, actionIndex) => {
                const assignedRows = actionContainer.querySelectorAll('.executive-assigned-row');
                
                assignedRows.forEach((assignedRow, assignedIndex) => {
                    // تحديث أسماء الحقول
                    const inputs = assignedRow.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        const name = input.name;
                        const newName = name.replace(
                            /executive_activities\[\d+\]\[actions\]\[\d+\]\[assigned_entities\]\[\d+\]/,
                            `executive_activities[${activityIndex}][actions][${actionIndex}][assigned_entities][${assignedIndex}]`
                        );
                        input.name = newName;
                    });
                });
            });
        });
    }
});

// تهيئة أدوات التلميح
if (typeof bootstrap !== 'undefined') {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}
</script>