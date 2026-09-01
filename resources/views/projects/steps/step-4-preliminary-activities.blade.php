@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Step 4: الأنشطة التمهيدية - Preliminary Activities</h4>
                    <p class="text-muted">
                        استخدام updateOrCreate: إذا كان لديك معرّف (ID)، سيتم تحديث السجل؛ 
                        وإلا، سيتم إنشاء سجل جديد
                    </p>
                </div>
                <div class="card-body">
                    <form id="step4Form" method="POST" action="/projects/step/4">
                        @csrf

                        <!-- Hidden Fields -->
                        <input type="hidden" name="project_id" value="{{ session('current_project_id') }}">
                        <input type="hidden" name="current_step" value="4">
                        <input type="hidden" name="status" value="draft">

                        <!-- Info Alert -->
                        <div class="alert alert-info mb-4">
                            <h6><i class="fas fa-info-circle"></i> كيفية عمل النموذج:</h6>
                            <ul class="mb-0">
                                <li><strong>السجلات الموجودة</strong>: لكل سجل حقل ID مخفي بقيمة. عند حفظ، سيتم البحث بـ ID والتحديث.</li>
                                <li><strong>سجلات جديدة</strong>: الحقول الجديدة لن تحتوي على قيمة ID. عند حفظ، سيتم إنشاء سجل جديد.</li>
                                <li><strong>الحذف</strong>: انقر على "حذف" لإزالة سجل من الجدول. سيتم حذفه من قاعدة البيانات عند الحفظ.</li>
                            </ul>
                        </div>

                        <!-- Preliminary Activities Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="activitiesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;">#</th>
                                        <th>اسم النشاط</th>
                                        <th>الوصف</th>
                                        <th>المبلغ المخطط</th>
                                        <th>نوع النشاط</th>
                                        <th style="width: 80px;">الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody id="activitiesBody">
                                    @forelse($project->preliminaryActivities ?? [] as $index => $activity)
                                        <tr class="activity-row" data-activity-id="{{ $activity->id }}">
                                            <td>
                                                <span class="row-number">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <!-- Hidden ID field: If present = UPDATE, If empty = CREATE -->
                                                <input type="hidden" 
                                                       name="preliminary_activities[id][]" 
                                                       value="{{ $activity->id }}">
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="preliminary_activities[activity_name][]"
                                                       value="{{ $activity->activity_name }}">
                                                <small class="text-muted d-block mt-1">
                                                    ID: <code>{{ $activity->id }}</code> 
                                                    (سيتم تحديثه)
                                                </small>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="preliminary_activities[description][]"
                                                       value="{{ $activity->description }}" 
                                                       placeholder="وصف اختياري">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       step="0.01" 
                                                       name="preliminary_activities[planned_amount][]"
                                                       value="{{ $activity->planned_amount }}">
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm" 
                                                        name="preliminary_activities[activity_type][]">
                                                    <option value="">اختر النوع</option>
                                                    <option value="preparation" 
                                                            {{ $activity->activity_type === 'preparation' ? 'selected' : '' }}>
                                                        تحضير
                                                    </option>
                                                    <option value="training" 
                                                            {{ $activity->activity_type === 'training' ? 'selected' : '' }}>
                                                        تدريب
                                                    </option>
                                                    <option value="assessment" 
                                                            {{ $activity->activity_type === 'assessment' ? 'selected' : '' }}>
                                                        تقييم
                                                    </option>
                                                    <option value="other" 
                                                            {{ $activity->activity_type === 'other' ? 'selected' : '' }}>
                                                        آخر
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="removeActivityRow(this, {{ $activity->id }})">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="emptyRow">
                                            <td colspan="6" class="text-center text-muted py-4">
                                                لا توجد أنشطة تمهيدية. انقر على "إضافة نشاط" لإضافة واحد.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Activity Button -->
                        <button type="button" class="btn btn-primary mt-3" onclick="addActivityRow()">
                            <i class="fas fa-plus"></i> إضافة نشاط تمهيدي جديد
                        </button>

                        <!-- Delete Confirmation (for existing records) -->
                        <input type="hidden" id="deletedActivityIds" name="deleted_activity_ids" value="">

                        <!-- Summary Section -->
                        <div class="card mt-4 bg-light">
                            <div class="card-body">
                                <h6>ملخص</h6>
                                <p>
                                    <strong>عدد الأنشطة:</strong> 
                                    <span id="activityCount">{{ count($project->preliminaryActivities ?? []) }}</span>
                                </p>
                                <p>
                                    <strong>إجمالي المبلغ المخطط:</strong> 
                                    <span id="totalAmount">{{ number_format($project->preliminaryActivities->sum('planned_amount') ?? 0, 2) }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-5 d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="previousStep()">
                                <i class="fas fa-arrow-left"></i> الخطوة السابقة
                            </button>
                            <div>
                                <button type="button" class="btn btn-warning me-2" onclick="saveDraft()">
                                    <i class="fas fa-save"></i> حفظ كمسودة
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">
                                    الخطوة التالية <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">شرح updateOrCreate Pattern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>ما هو updateOrCreate؟</h6>
                <p>
                    هو نمط Laravel الذي يبحث عن سجل بناءً على معايير معينة، 
                    وإذا وجده يقوم بتحديثه، وإلا يقوم بإنشاء سجل جديد.
                </p>

                <h6 class="mt-3">في النموذج:</h6>
                <ul>
                    <li><strong>السجلات الموجودة</strong>: لها قيمة في حقل ID المخفي</li>
                    <li><strong>السجلات الجديدة</strong>: حقل ID المخفي فارغ</li>
                </ul>

                <h6 class="mt-3">في Controller:</h6>
                <pre><code class="language-php">foreach ($request->input('preliminary_activities') as $activity) {
    $attributes = ['project_id' => $projectId];
    
    if (!empty($activity['id'])) {
        $attributes['id'] = $activity['id'];
    }
    
    // updateOrCreate: ابحث عن السجل بـ attributes
    // إذا وجدت: قم بتحديثه
    // إلا: قم بإنشاء واحد جديد
    PreliminaryActivity::updateOrCreate(
        $attributes, 
        $activity
    );
}</code></pre>
            </div>
        </div>
    </div>
</div>

<style>
    .activity-row {
        transition: background-color 0.2s ease;
    }

    .activity-row:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .row-number {
        font-weight: 600;
        color: #666;
    }

    .form-control-sm:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    code {
        background-color: #f8f9fa;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9em;
        color: #d63384;
    }

    pre {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
    }
</style>

<script>
let deletedIds = [];

function addActivityRow() {
    const tbody = document.getElementById('activitiesBody');
    const emptyRow = document.getElementById('emptyRow');
    
    // Remove empty state message
    if (emptyRow) {
        emptyRow.remove();
    }

    const rowCount = tbody.querySelectorAll('tr').length + 1;
    
    const newRow = document.createElement('tr');
    newRow.className = 'activity-row';
    newRow.innerHTML = `
        <td>
            <span class="row-number">${rowCount}</span>
        </td>
        <td>
            <!-- Empty ID = CREATE new record -->
            <input type="hidden" name="preliminary_activities[id][]" value="">
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="preliminary_activities[activity_name][]" 
                   placeholder="أدخل اسم النشاط">
            <small class="text-muted d-block mt-1">
                ID: <code>جديد (سيتم إنشاؤه)</code>
            </small>
        </td>
        <td>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="preliminary_activities[description][]" 
                   placeholder="وصف اختياري">
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm" 
                   step="0.01" 
                   name="preliminary_activities[planned_amount][]" 
                   placeholder="0.00" 
                   value="0">
        </td>
        <td>
            <select class="form-control form-control-sm" name="preliminary_activities[activity_type][]">
                <option value="">اختر النوع</option>
                <option value="preparation">تحضير</option>
                <option value="training">تدريب</option>
                <option value="assessment">تقييم</option>
                <option value="other">آخر</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeActivityRow(this)">
                <i class="fas fa-trash"></i> حذف
            </button>
        </td>
    `;

    tbody.appendChild(newRow);
    updateSummary();
}

function removeActivityRow(btn, activityId = null) {
    const row = btn.closest('tr');
    
    // If this is an existing record, track its ID for deletion
    if (activityId) {
        deletedIds.push(activityId);
        document.getElementById('deletedActivityIds').value = deletedIds.join(',');
    }

    row.remove();

    // Show empty message if no rows left
    const tbody = document.getElementById('activitiesBody');
    if (tbody.querySelectorAll('tr').length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.id = 'emptyRow';
        emptyRow.innerHTML = `
            <td colspan="6" class="text-center text-muted py-4">
                لا توجد أنشطة تمهيدية. انقر على "إضافة نشاط" لإضافة واحد.
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    updateRowNumbers();
    updateSummary();
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.activity-row');
    rows.forEach((row, index) => {
        row.querySelector('.row-number').textContent = index + 1;
    });
}

function updateSummary() {
    const rows = document.querySelectorAll('.activity-row');
    const amounts = Array.from(rows)
        .map(row => {
            const input = row.querySelector('input[name="preliminary_activities[planned_amount][]"]');
            return parseFloat(input.value) || 0;
        })
        .reduce((sum, amount) => sum + amount, 0);

    document.getElementById('activityCount').textContent = rows.length;
    document.getElementById('totalAmount').textContent = amounts.toFixed(2);
}

function saveDraft() {
    submitForm('draft');
}

function nextStep() {
    submitForm('draft');
    // After saving, navigate to next step (Step 5)
    // setTimeout(() => window.location.href = '/projects/step/5', 500);
}

function previousStep() {
    window.history.back();
}

function submitForm(status) {
    const form = document.getElementById('step4Form');
    const formData = new FormData(form);
    
    if (status) {
        formData.set('status', status);
    }

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            flasher.success(data.message || 'تم الحفظ بنجاح');
            
            // Update project_id in form if needed
            if (data.project_id) {
                form.querySelector('input[name="project_id"]').value = data.project_id;
            }
        } else {
            flasher.error(data.message || 'حدث خطأ أثناء الحفظ');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        flasher.error('حدث خطأ في الاتصال');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRowNumbers();
    updateSummary();
});
</script>
@endsection
