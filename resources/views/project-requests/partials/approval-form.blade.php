@php
    $parent = $projectRequest;
    $parentId = $parent->id;
    $parentType = 'project-requests';
@endphp

<div class="card shadow-sm border-0 rounded-4 mb-4" id="approvalSection">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-check-circle me-2"></i> اعتماد وموافقة الطلب
        </h5>
    </div>
    <div class="card-body p-4">
        <form id="approvalForm" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">المرحلة</label>
                    <select id="drop" name="drop" class="form-select" required>
                        <option value="">-- اختر المرحلة --</option>
                        @foreach($approvalStages as $stage)
                            <option value="{{ $stage['drop'] }}" 
                                    data-entity-id="{{ $stage['entity_id'] }}" 
                                    {{ $stage['is_current_stage'] ? 'selected' : '' }}>
                                {{ $stage['stage_name'] }} {{ $stage['is_current_stage'] ? '(المرحلة الحالية)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">الحالة</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="">-- اختر الحالة --</option>
                        <option value="approved">موافق عليها</option>
                        <option value="need_action">تحتاج إلى </option>
                        <option value="rejected">مرفوضة</option>
                        <option value="resubmitted">إعادة تقديم</option>
                        <option value="referral">إحالة للجهة</option>
                    </select>
                </div>
                <div class="col-12" id="referralSection" style="display: none;">
                    <label class="form-label fw-bold">الجهة المحال إليها <span class="text-danger">*</span></label>
                    <select id="referred_entity_id" name="referred_entity_id" class="form-select">
                        <option value="">-- اختر الجهة --</option>
                    </select>
                    <div class="form-text text-muted">سيتم إرسال الطلب لهذه الجهة للمراجعة دون تغيير المرحلة الحالية للطلب.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">الملاحظات <span id="notesRequired" class="text-danger" style="display: none;">*</span></label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="أدخل الملاحظات أو نص الإحالة هنا..."></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">المرفق (اختياري)</label>
                    <input type="file" id="attachment" name="attachment" class="form-control">
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fas fa-paper-plane me-2"></i> إرسال القرار
                    </button>
                </div>
            </div>
            <input type="hidden" name="entity_id" id="entity_id" value="{{ auth()->user()->entity_id }}">
        </form>

        <!-- Movement Log Section -->
        <div class="mt-5 pt-4 border-top">
            <h6 class="fw-bold mb-3"><i class="fas fa-history me-2"></i> سجل حركة الطلب</h6>
            <div id="movementLogContainer">
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-spinner fa-spin me-2"></i> جاري تحميل السجل...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approvalForm = document.getElementById('approvalForm');
    const movementLogContainer = document.getElementById('movementLogContainer');
    const statusSelect = document.getElementById('status');
    const referralSection = document.getElementById('referralSection');
    const referredEntitySelect = document.getElementById('referred_entity_id');
    const notesRequired = document.getElementById('notesRequired');
    const dropSelect = document.getElementById('drop');
    const requestId = {{ $parentId }};

    // Handle Status Change
    statusSelect.addEventListener('change', function() {
        const status = this.value;
        if (status === 'referral') {
            referralSection.style.display = 'block';
            referredEntitySelect.required = true;
            notesRequired.style.display = 'inline';
            fetchReferralEntities();
        } else {
            referralSection.style.display = 'none';
            referredEntitySelect.required = false;
            notesRequired.style.display = (status === 'need_action' || status === 'rejected') ? 'inline' : 'none';
        }
    });

    function fetchReferralEntities() {
        const selectedOption = dropSelect.options[dropSelect.selectedIndex];
        const entityId = selectedOption.getAttribute('data-entity-id');
        
        if (!entityId) return;

        // Use the updated lookup route that supports parent_id
        fetch(`/lookup/search?type=internal_entity&parent_id=${entityId}`)
            .then(res => res.json())
            .then(data => {
                referredEntitySelect.innerHTML = '<option value="">-- اختر الجهة (الأقسام/الإدارات التابعة) --</option>';
                if (data.results && data.results.length > 0) {
                    data.results.forEach(item => {
                        if (item.id != '0') { // Skip "All" option if present
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.text;
                            referredEntitySelect.appendChild(option);
                        }
                    });
                } else {
                    referredEntitySelect.innerHTML = '<option value="">لا توجد جهات تابعة لهذه المرحلة</option>';
                }
            });
    }

    // Load Movement Log
    function loadMovementLog() {
        fetch(`/project-requests/${requestId}/movement-log`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.logs) {
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover">';
                    html += '<thead class="table-light"><tr><th>التاريخ</th><th>بواسطة</th><th>الإجراء</th><th>الملاحظات</th></tr></thead><tbody>';
                    data.logs.forEach(log => {
                        html += `<tr>
                            <td>${log.date_formatted}</td>
                            <td>${log.user_name}</td>
                            <td><span class="badge bg-${getStatusColor(log.status)}">${log.status_arabic}</span></td>
                            <td>${log.notes || '-'}</td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                    movementLogContainer.innerHTML = html;
                } else {
                    movementLogContainer.innerHTML = '<p class="text-muted text-center">لا يوجد سجلات متاحة</p>';
                }
            });
    }

    function getStatusColor(status) {
        const colors = {
            'approved': 'success',
            'rejected': 'danger',
            'need_action': 'warning',
            'resubmitted': 'info',
            'pending': 'secondary',
            'referral': 'primary'
        };
        return colors[status] || 'secondary';
    }

    loadMovementLog();

    // Handle Form Submission
    approvalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(`/project-requests/${requestId}/approve`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('حدث خطأ: ' + (data.message || 'غير معروف'));
            }
        })
        .catch(err => alert('خطأ في الاتصال بالسيرفر'));
    });

    // Update entity_id when drop changes
    dropSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const entityId = selectedOption.getAttribute('data-entity-id');
        document.getElementById('entity_id').value = entityId;
        
        if (statusSelect.value === 'referral') {
            fetchReferralEntities();
        }
    });
});
</script>
