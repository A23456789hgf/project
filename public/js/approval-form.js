// ============================================
// Approval Form JavaScript
// ============================================

function initializeApprovalForm(projectId) {
    const statusSelect = document.getElementById('status');
    const notesGroup = document.getElementById('notesGroup');
    const requiredActionGroup = document.getElementById('requiredActionGroup');
    const rejectionReasonGroup = document.getElementById('rejectionReasonGroup');
    const attachmentGroup = document.getElementById('attachmentGroup');
    const notesField = document.getElementById('notes');
    const requiredActionField = document.getElementById('required_action');
    const rejectionReasonField = document.getElementById('rejection_reason');
    const attachmentInput = document.getElementById('attachment');
    const approvalForm = document.getElementById('approvalForm');
    const uploadArea = document.getElementById('uploadArea');
    const fileName = document.getElementById('fileName');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const statusMessage = document.getElementById('statusMessage');
    const approvalsHistoryDiv = document.getElementById('approvalsHistory');
    const submitBtn = document.getElementById('submitBtn');
    const returnBtn = document.getElementById('returnBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const selectApprovedBtn = document.getElementById('selectApprovedBtn');
    const selectNeedActionBtn = document.getElementById('selectNeedActionBtn');
    const selectRejectedBtn = document.getElementById('selectRejectedBtn');
    const selectResubmitBtn = document.getElementById('selectResubmitBtn');
    const selectReviewBtn = document.getElementById('selectReviewBtn');

    if (!statusSelect) return;

    // Button click listeners to update dropdown and trigger UI logic
    if (selectApprovedBtn) {
        selectApprovedBtn.addEventListener('click', () => {
            statusSelect.value = 'approved';
            statusSelect.dispatchEvent(new Event('change'));
        });
    }
    if (selectNeedActionBtn) {
        selectNeedActionBtn.addEventListener('click', () => {
            statusSelect.value = 'need_action';
            statusSelect.dispatchEvent(new Event('change'));
        });
    }
    if (selectRejectedBtn) {
        selectRejectedBtn.addEventListener('click', () => {
            statusSelect.value = 'rejected';
            statusSelect.dispatchEvent(new Event('change'));
        });
    }
    if (selectResubmitBtn) {
        selectResubmitBtn.addEventListener('click', () => {
            statusSelect.value = 'resubmitted';
            statusSelect.dispatchEvent(new Event('change'));
        });
    }
    if (selectReviewBtn) {
        selectReviewBtn.addEventListener('click', () => {
            statusSelect.value = 'financial_technical_review';
            statusSelect.dispatchEvent(new Event('change'));
        });
    }

    // Status change listener to show/hide fields and buttons
    statusSelect.addEventListener('change', function () {
        const status = this.value;

        // Reset all selector buttons visibility initially
        [selectApprovedBtn, selectNeedActionBtn, selectRejectedBtn, selectResubmitBtn, selectReviewBtn].forEach(btn => {
            if (btn) btn.style.display = 'inline-flex';
        });

        // Hide all action buttons initially
        if (submitBtn) submitBtn.style.display = 'none';
        if (returnBtn) returnBtn.style.display = 'none';
        const resubmitBtn = document.getElementById('resubmitBtn'); // Re-declare if needed, but instruction implies removal
        if (resubmitBtn) resubmitBtn.style.display = 'none';
        const reviewActionBtn = document.getElementById('reviewActionBtn');
        if (reviewActionBtn) reviewActionBtn.style.display = 'none';

        // Requirement: "OK" (Approved) - Only OK button displayed, all others hidden.
        if (status === 'approved') {
            [selectNeedActionBtn, selectRejectedBtn, selectResubmitBtn, selectReviewBtn].forEach(btn => {
                if (btn) btn.style.display = 'none';
            });
            if (selectApprovedBtn) selectApprovedBtn.style.display = 'none';
            if (submitBtn) submitBtn.style.display = 'inline-flex';
            submitBtn.innerHTML = '<i class="fas fa-check"></i> موافق (OK)';

            if (notesGroup) notesGroup.style.display = 'none';
            if (attachmentGroup) attachmentGroup.style.display = 'none';

        } else if (status === 'financial_technical_review') {
            [selectApprovedBtn, selectNeedActionBtn, selectRejectedBtn, selectResubmitBtn].forEach(btn => {
                if (btn) btn.style.display = 'none';
            });
            if (selectReviewBtn) selectReviewBtn.style.display = 'none';
            if (reviewActionBtn) reviewActionBtn.style.display = 'inline-flex';

            if (notesGroup) notesGroup.style.display = 'block';
            if (attachmentGroup) attachmentGroup.style.display = 'block';

        } else if (status === 'need_action' || status === 'rejected') {
            [selectApprovedBtn, selectNeedActionBtn, selectRejectedBtn, selectResubmitBtn, selectReviewBtn].forEach(btn => {
                if (btn) btn.style.display = 'none';
            });

            if (returnBtn) returnBtn.style.display = 'none';
            if (notesGroup) notesGroup.style.display = 'none';
            if (attachmentGroup) attachmentGroup.style.display = 'block';

            if (submitBtn) submitBtn.style.display = 'inline-flex';
            submitBtn.innerHTML = '<i class="fas fa-check"></i> تأكيد (Confirm)';

        } else if (status === 'resubmitted' || status === 'resubmit') {
            [selectApprovedBtn, selectNeedActionBtn, selectRejectedBtn, selectResubmitBtn, selectReviewBtn, submitBtn, returnBtn, resubmitBtn, reviewActionBtn].forEach(btn => {
                if (btn) btn.style.display = 'none';
            });

            if (notesGroup) notesGroup.style.display = 'none';
            if (attachmentGroup) attachmentGroup.style.display = 'none';

            // Auto-submit for resubmit if everything is hidden as requested
            console.log('Auto-submitting resubmit action...');
            setTimeout(() => {
                approvalForm.dispatchEvent(new Event('submit'));
            }, 500);
        }
    });

    // Trigger change event on page load to show initial fields
    console.log('Triggering initial status change');
    statusSelect.dispatchEvent(new Event('change'));

    // File upload handling
    if (uploadArea) {
        uploadArea.addEventListener('click', function () {
            attachmentInput.click();
        });
    }

    attachmentInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            const fileCount = this.files.length;
            let fileNames = '';
            let totalSize = 0;

            for (let i = 0; i < this.files.length; i++) {
                fileNames += (i > 0 ? ', ' : '') + this.files[i].name;
                totalSize += this.files[i].size;
            }

            const sizeMB = (totalSize / 1024 / 1024).toFixed(2);
            fileName.textContent = `✓ تم اختيار ${fileCount} ملف(ات) - ${sizeMB} MB`;
            fileName.style.display = 'block';
            uploadArea.style.borderColor = '#28a745';
            uploadArea.style.backgroundColor = '#f0fdf4';
        }
    });

    // Drag and drop for file upload
    if (uploadArea) {
        uploadArea.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#007bff';
            uploadArea.style.backgroundColor = '#f0f7ff';
        });

        uploadArea.addEventListener('dragleave', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#dee2e6';
            uploadArea.style.backgroundColor = 'transparent';
        });

        uploadArea.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadArea.style.borderColor = '#dee2e6';
            uploadArea.style.backgroundColor = 'transparent';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                attachmentInput.files = files;

                let fileNames = '';
                let totalSize = 0;

                for (let i = 0; i < files.length; i++) {
                    fileNames += (i > 0 ? ', ' : '') + files[i].name;
                    totalSize += files[i].size;
                }

                const sizeMB = (totalSize / 1024 / 1024).toFixed(2);
                fileName.textContent = `✓ تم اختيار ${files.length} ملف(ات) - ${sizeMB} MB`;
                fileName.style.display = 'block';
                uploadArea.style.borderColor = '#28a745';
                uploadArea.style.backgroundColor = '#f0fdf4';
            }
        });
    }

    // Form submission
    approvalForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const drop = document.getElementById('drop').value;
        const status = statusSelect.value;
        const notes = notesField ? notesField.value : '';
        const attachments = attachmentInput.files;

        // Validation
        if (!drop) {
            showMessage('يرجى اختيار المرحلة', 'warning');
            return;
        }

        if (!status) {
            showMessage('يرجى اختيار حالة الموافقة', 'warning');
            return;
        }

        // Show loading spinner
        loadingSpinner.style.display = 'block';
        statusMessage.style.display = 'none';

        try {
            const formData = new FormData();
            formData.append('drop', drop);
            formData.append('authority_id', document.querySelector(`#drop option[value="${drop}"]`).dataset.authorityId);
            formData.append('status', status);
            formData.append('notes', notes || '');

            // Add financial and technical review notes if status is financial_review
            if (status === 'financial_review') {
                const financialReviewNotes = document.getElementById('financial_review_notes')?.value || '';
                const technicalReviewNotes = document.getElementById('technical_review_notes')?.value || '';
                formData.append('financial_review_notes', financialReviewNotes);
                formData.append('technical_review_notes', technicalReviewNotes);
            }

            // Add multiple attachments
            for (let i = 0; i < attachments.length; i++) {
                formData.append('attachments[]', attachments[i]);
            }

            formData.append('_token', document.querySelector('input[name="_token"]').value);

            const url = `/projects/${projectId}/approval-workflow/approve`;

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'حدث خطأ في الخادم');
            }

            if (data.success) {
                showMessage(data.message, 'success');
                approvalForm.reset();
                fileName.style.display = 'none';
                notesGroup.style.display = 'none';
                requiredActionGroup.style.display = 'none';
                rejectionReasonGroup.style.display = 'none';
                attachmentGroup.style.display = 'none';

                // Reload page after success
                setTimeout(() => {
                    window.location.reload();
                }, 2000);

            } else {
                showMessage(data.message || 'حدث خطأ أثناء المعالجة', 'danger');
            }
        } catch (error) {
            showMessage('خطأ في الاتصال: ' + error.message, 'danger');
            console.error('Request error:', error);
        } finally {
            loadingSpinner.style.display = 'none';
        }
    });

    // Show/hide status message
    function showMessage(message, type) {
        statusMessage.innerHTML = message;
        statusMessage.className = `alert alert-${type}`;
        statusMessage.style.display = 'block';

        if (type === 'success') {
            setTimeout(() => {
                statusMessage.style.display = 'none';
            }, 5000);
        }
    }
}
