<!-- Delay Justification Modal - Reusable Component -->
<div class="modal fade" id="delayJustificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-hourglass-end text-warning me-2"></i>
                    تبرير التأخر الزمني
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="delayJustificationForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="delayExecutionType" name="execution_type">
                <input type="hidden" id="delayExecutionTypeId" name="execution_type_id">
                <input type="hidden" id="delayPlannedStartDate" name="planned_start_date">
                <input type="hidden" id="delayPlannedEndDate" name="planned_end_date">
                <input type="hidden" id="delayActualStartDate" name="actual_start_date">
                <input type="hidden" id="delayActualEndDate" name="actual_end_date">

                <div class="modal-body">
                    <!-- Dates Summary -->
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="row text-sm">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <span class="text-muted small">تاريخ البداية المخطط</span>
                                        <div class="fw-bold" id="displayPlannedStart"></div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">تاريخ النهاية المخطط</span>
                                        <div class="fw-bold" id="displayPlannedEnd"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <span class="text-muted small">تاريخ البداية الفعلي</span>
                                        <div class="fw-bold text-info" id="displayActualStart"></div>
                                    </div>
                                    <div>
                                        <span class="text-muted small">تاريخ النهاية الفعلي</span>
                                        <div class="fw-bold text-danger" id="displayActualEnd"></div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="alert alert-warning mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong id="delayDaysDisplay"></strong> يوم تأخر عن الموعد المخطط
                            </div>
                        </div>
                    </div>

                    <!-- Justification Textarea -->
                    <div class="mb-3">
                        <label for="delayExplanation" class="form-label fw-bold">
                            التبرير التقني 
                        </label>
                        <textarea 
                            class="form-control" 
                            id="delayExplanation" 
                            name="explanation"
                            rows="5"
                            placeholder="اشرح أسباب التأخر الزمني والإجراءات المتخذة لتجنب تكراره..."
                            minlength="10"></textarea>
                        <small class="text-muted">الحد الأدنى 10 أحرف</small>
                    </div>

                    <!-- File Attachments -->
                    <div class="mb-3">
                        <label for="delayAttachments" class="form-label fw-bold">
                            المرفقات <span class="text-muted">(اختياري)</span>
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="delayAttachments" 
                               name="attachments[]" 
                               multiple 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        <small class="text-muted">
                            الصيغ المدعومة: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
                            <br>الحد الأقصى لحجم الملف: 10 MB
                        </small>
                    </div>

                    <!-- File Preview -->
                    <div id="delayFilePreview" class="mb-3" style="display: none;">
                        <label class="form-label fw-bold">الملفات المختارة:</label>
                        <div id="delayFileList" class="list-group list-group-sm"></div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> حفظ التبرير
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('delayAttachments').addEventListener('change', function(e) {
    const fileList = this.files;
    const preview = document.getElementById('delayFilePreview');
    const fileListDiv = document.getElementById('delayFileList');
    
    fileListDiv.innerHTML = '';
    
    if (fileList.length > 0) {
        preview.style.display = 'block';
        for (let file of fileList) {
            const size = (file.size / 1024).toFixed(2);
            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';
            item.innerHTML = `
                <span><i class="fas fa-file me-2"></i>${file.name}</span>
                <span class="badge bg-info rounded-pill">${size} KB</span>
            `;
            fileListDiv.appendChild(item);
        }
    } else {
        preview.style.display = 'none';
    }
});

function openDelayJustificationModal(executionType, executionTypeId, plannedStartDate, plannedEndDate, actualStartDate, actualEndDate) {
    const modal = new bootstrap.Modal(document.getElementById('delayJustificationModal'));
    
    // Calculate days delayed
    const planned = new Date(plannedEndDate);
    const actual = new Date(actualEndDate);
    const delayDays = Math.ceil((actual - planned) / (1000 * 60 * 60 * 24));
    
    // Set form values
    document.getElementById('delayExecutionType').value = executionType;
    document.getElementById('delayExecutionTypeId').value = executionTypeId;
    document.getElementById('delayPlannedStartDate').value = plannedStartDate;
    document.getElementById('delayPlannedEndDate').value = plannedEndDate;
    document.getElementById('delayActualStartDate').value = actualStartDate;
    document.getElementById('delayActualEndDate').value = actualEndDate;
    
    // Display values
    document.getElementById('displayPlannedStart').textContent = new Date(plannedStartDate).toLocaleDateString('ar-SA');
    document.getElementById('displayPlannedEnd').textContent = new Date(plannedEndDate).toLocaleDateString('ar-SA');
    document.getElementById('displayActualStart').textContent = new Date(actualStartDate).toLocaleDateString('ar-SA');
    document.getElementById('displayActualEnd').textContent = new Date(actualEndDate).toLocaleDateString('ar-SA');
    document.getElementById('delayDaysDisplay').textContent = delayDays > 0 ? `${delayDays}` : '0';
    
    // Clear form
    document.getElementById('delayJustificationForm').reset();
    document.getElementById('delayFilePreview').style.display = 'none';
    
    modal.show();
}

// Form submission
document.getElementById('delayJustificationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const projectId = window.location.pathname.match(/projects\/(\d+)/)[1];
    
    fetch(`/projects/${projectId}/execution-delay/store`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            flasher.success(data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('delayJustificationModal'));
            modal.hide();
            
            // Reload the page or update the UI
            setTimeout(() => location.reload(), 1500);
        } else {
            flasher.error('حدث خطأ: ' + (data.message || 'الرجاء محاولة مجددا'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        flasher.error('حدث خطأ في حفظ التبرير');
    });
});
</script>
