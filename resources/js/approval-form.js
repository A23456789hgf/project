// ============================================
// Approval Form JavaScript
// ============================================

function initializeApprovalForm(projectId) {
    const statusSelect = document.getElementById('status');
    const notesGroup = document.getElementById('notesGroup');
    const attachmentGroup = document.getElementById('attachmentGroup');
    const notesField = document.getElementById('notes');
    const attachmentInput = document.getElementById('attachment');
    const approvalForm = document.getElementById('approvalForm');
    const uploadArea = document.getElementById('uploadArea');
    const fileName = document.getElementById('fileName');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const statusMessage = document.getElementById('statusMessage');

    // زر موافق
    const submitBtn = document.getElementById('submitBtn');
    // زر يحتاج إجراء
    const returnBtn = document.getElementById('returnBtn');
    // زر إعادة تقديم
    const resubmitActionBtn = document.getElementById('resubmitActionBtn');
    // زر المراجعة المزدوجة
    const reviewActionBtn = document.getElementById('reviewActionBtn');

    // أزرار التحديد السريع
    const selectApprovedBtn = document.getElementById('selectApprovedBtn');
    const selectNeedActionBtn = document.getElementById('selectNeedActionBtn');
    const selectRejectedBtn = document.getElementById('selectRejectedBtn');
    const selectResubmitBtn = document.getElementById('selectResubmitBtn');
    const selectReviewBtn = document.getElementById('selectReviewBtn');

    if (!statusSelect) return;

    // دالة تحديث واجهة المستخدم بناءً على الحالة المختارة
    function updateApprovalFormUI(status) {
        // إخفاء كل شيء في البداية
        hideAllElements();

        if (!status) {
            return;
        }

        // الحالة 1: موافق
        if (status === 'approved') {
            // فقط زر موافق يتم إظهاره
            if (submitBtn) {
                submitBtn.style.display = 'inline-flex';
            }
        }
        // الحالة 2: يحتاج إجراء
        else if (status === 'need_action') {
            // إمكانية إضافة المرفق فقط
            attachmentGroup.style.display = 'block';
            // لا يتم إظهار زر ارجاع المشروع
            // لا يتم إظهار حقل الإجراء الإلزامي
            // جميع الأزرار الأخرى مخفية
        }
        // الحالة 3: مرفوض
        else if (status === 'rejected') {
            // إمكانية إضافة المرفق فقط
            attachmentGroup.style.display = 'block';
            // لا يتم إظهار حقل سبب الرفض الإلزامي
            // لا يتم إظهار زر ارجاع المشروع
            // جميع الأزرار الأخرى مخفية
        }
        // الحالة 4: إعادة تقديم
        else if (status === 'resubmitted') {
            // زر إعادة تقديم مخفي
            // جميع الأزرار الأخرى مخفية
        }
        // الحالة 5: مراجعة مالية وفنية
        else if (status === 'financial_technical_review') {
            // فقط زر المراجعة المزدوجة يتم إظهاره
            if (reviewActionBtn) {
                reviewActionBtn.style.display = 'inline-flex';
            }
        }
    }

    // دالة مساعدة لإخفاء جميع العناصر
    function hideAllElements() {
        // إخفاء جميع أزرار الإجراء
        [submitBtn, returnBtn, resubmitActionBtn, reviewActionBtn].forEach(btn => {
            if (btn) btn.style.display = 'none';
        });

        // إخفاء أزرار التحديد السريع
        const statusSelectors = document.getElementById('statusSelectors');
        if (statusSelectors) statusSelectors.style.display = 'none';

        // إخفاء المجموعات
        notesGroup.style.display = 'none';
        attachmentGroup.style.display = 'none';
        notesField.required = false;
        notesField.value = '';

        // إعادة تعيين المرفق
        if (attachmentInput) {
            attachmentInput.value = '';
            if (fileName) {
                fileName.style.display = 'none';
            }
        }

        if (uploadArea) {
            uploadArea.style.borderColor = '#dee2e6';
            uploadArea.style.backgroundColor = 'transparent';
        }
    }

    // استماع لتغيير القائمة المنسدلة للحالة
    statusSelect.addEventListener('change', function () {
        updateApprovalFormUI(this.value);
    });

    // أزرار التحديد السريع
    if (selectApprovedBtn) {
        selectApprovedBtn.addEventListener('click', () => {
            statusSelect.value = 'approved';
            updateApprovalFormUI('approved');
        });
    }

    if (selectNeedActionBtn) {
        selectNeedActionBtn.addEventListener('click', () => {
            statusSelect.value = 'need_action';
            updateApprovalFormUI('need_action');
        });
    }

    if (selectRejectedBtn) {
        selectRejectedBtn.addEventListener('click', () => {
            statusSelect.value = 'rejected';
            updateApprovalFormUI('rejected');
        });
    }

    if (selectResubmitBtn) {
        selectResubmitBtn.addEventListener('click', () => {
            statusSelect.value = 'resubmitted';
            updateApprovalFormUI('resubmitted');
        });
    }

    if (selectReviewBtn) {
        selectReviewBtn.addEventListener('click', () => {
            statusSelect.value = 'financial_technical_review';
            updateApprovalFormUI('financial_technical_review');
        });
    }

    // التعامل مع رفع الملفات
    if (uploadArea && attachmentInput) {
        uploadArea.addEventListener('click', function () {
            attachmentInput.click();
        });
    }

    if (attachmentInput) {
        attachmentInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                const file = this.files[0];
                if (fileName) {
                    fileName.textContent = '✓ ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                    fileName.style.display = 'block';
                }
                if (uploadArea) {
                    uploadArea.style.borderColor = '#28a745';
                    uploadArea.style.backgroundColor = '#f0fdf4';
                }
            }
        });
    }

    // السحب والإفلات لرفع الملفات
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
            if (files.length > 0 && attachmentInput) {
                attachmentInput.files = files;
                const file = files[0];
                if (fileName) {
                    fileName.textContent = '✓ ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
                    fileName.style.display = 'block';
                }
                uploadArea.style.borderColor = '#28a745';
                uploadArea.style.backgroundColor = '#f0fdf4';
            }
        });
    }

    // إرسال النموذج
    if (approvalForm) {
        approvalForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const drop = document.getElementById('drop')?.value;
            const status = statusSelect.value;
            const notes = notesField.value;
            const attachment = attachmentInput?.files[0];
            const isReturnAction = e.submitter && e.submitter.id === 'returnBtn';
            const isResubmitAction = e.submitter && e.submitter.id === 'resubmitActionBtn';

            // التحقق من الصحة
            if (!drop) {
                showMessage('يرجى اختيار المرحلة', 'warning');
                return;
            }

            if (!status) {
                showMessage('يرجى اختيار حالة الموافقة', 'warning');
                return;
            }

            // إظهار مؤشر التحميل
            if (loadingSpinner) {
                loadingSpinner.style.display = 'block';
            }

            if (statusMessage) {
                statusMessage.style.display = 'none';
            }

            try {
                const formData = new FormData();
                formData.append('drop', drop);

                const dropOption = document.querySelector(`#drop option[value="${drop}"]`);
                if (dropOption && dropOption.dataset.entityId) {
                    formData.append('entity_id', dropOption.dataset.entityId);
                }

                formData.append('status', status);
                formData.append('notes', notes || '');

                if (attachment) {
                    formData.append('attachment', attachment);
                }

                const csrfToken = document.querySelector('input[name="_token"]');
                if (csrfToken) {
                    formData.append('_token', csrfToken.value);
                }

                // إضافة علامة لعملية الإرجاع
                if (isReturnAction) {
                    formData.append('return_action', 'true');
                }

                // إضافة علامة لعملية إعادة التقديم
                if (isResubmitAction) {
                    formData.append('resubmit_action', 'true');
                }

                const response = await fetch(`/projects/${projectId}/approval-workflow/approve`, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    let errorMsg = 'حدث خطأ في الخادم';
                    try {
                        const errorData = await response.json();
                        errorMsg = errorData.message || errorMsg;
                    } catch (parseError) {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('text/html')) {
                            errorMsg = 'تم الرد من الخادم برسالة غير صحيحة';
                        }
                    }
                    showMessage(errorMsg, 'danger');
                } else {
                    const data = await response.json();

                    if (data.success) {
                        showMessage(data.message, 'success');

                        // إعادة تعيين النموذج
                        approvalForm.reset();
                        hideAllElements();

                        // إعادة تحميل الجدول الموحد بعد التأخير
                        setTimeout(() => {
                            loadUnifiedApprovalTable(projectId);
                        }, 1000);

                        // إعادة تحميل سجل الموافقات الموحد
                        setTimeout(() => {
                            loadConsolidatedTransactionLog(projectId);
                        }, 1000);
                    } else {
                        showMessage(data.message || 'حدث خطأ أثناء المعالجة', 'danger');
                    }
                }
            } catch (error) {
                showMessage('خطأ في الاتصال: ' + error.message, 'danger');
                console.error('Request error:', error);
            } finally {
                if (loadingSpinner) {
                    loadingSpinner.style.display = 'none';
                }
            }
        });
    }

    // إظهار الرسالة
    function showMessage(message, type) {
        if (!statusMessage) return;

        statusMessage.textContent = message;
        statusMessage.className = `alert alert-${type}`;
        statusMessage.style.display = 'block';

        if (type === 'success') {
            setTimeout(() => {
                statusMessage.style.display = 'none';
            }, 5000);
        }
    }

    // تحميل جدول الموافقات الموحد
    loadUnifiedApprovalTable(projectId);
}

// ============================================
// دوال المساعدة
// ============================================

function getStatusColor(status) {
    const colors = {
        'approved': '#28a745',
        'rejected': '#dc3545',
        'pending': '#ffc107',
        'need_action': '#fd7e14',
        'resubmitted': '#17a2b8',
        'financial_technical_review': '#6f42c1',
        'on_hold': '#6c757d'
    };
    return colors[status] || '#6c757d';
}

function getDropArabic(drop) {
    const drops = {
        'assembly': 'موافقة الجمعية',
        'union': 'موافقة الاتحاد',
        'committee': 'موافقة اللجنة'
    };
    return drops[drop] || drop;
}

function getStatusArabic(status) {
    const statuses = {
        'approved': '✓ موافق',
        'rejected': '✕ مرفوض',
        'pending': '⧖ قيد الانتظار',
        'need_action': '⚠ يحتاج إلى إجراء',
        'resubmitted': '⟳ إعادة تقديم',
        'financial_technical_review': '⚙ مراجعة مالية وفنية'
    };
    return statuses[status] || status;
}

function getStatusIcon(status) {
    const icons = {
        'approved': '<i class="fas fa-check-circle"></i>',
        'rejected': '<i class="fas fa-times-circle"></i>',
        'pending': '<i class="fas fa-hourglass-half"></i>',
        'need_action': '<i class="fas fa-exclamation-circle"></i>',
        'resubmitted': '<i class="fas fa-redo"></i>',
        'financial_technical_review': '<i class="fas fa-users-cog"></i>'
    };
    return icons[status] || '<i class="fas fa-circle"></i>';
}

function getTransactionTypeLabel(type) {
    const labels = {
        'movement': 'حركة المشروع',
        'audit': 'سجل الموافقات',
        'pending': 'معلقة',
        'requiring': 'بحاجة معالجة'
    };
    return labels[type] || type;
}

function getTransactionTypeBg(type) {
    const colors = {
        'movement': '#17a2b8',
        'audit': '#007bff',
        'pending': '#ffc107',
        'requiring': '#dc3545'
    };
    return colors[type] || '#6c757d';
}

// ============================================
// تحميل جدول الموافقات الموحد
// ============================================

async function loadUnifiedApprovalTable(projectId) {
    try {
        const container = document.getElementById('unifiedApprovalTable');
        if (!container) return;

        const [movementRes, auditRes, pendingRes, requiringRes] = await Promise.all([
            fetch(`/projects/${projectId}/approval-workflow/movement-log`),
            fetch(`/projects/${projectId}/approval-workflow/audit-log`),
            fetch(`/projects/${projectId}/approval-workflow/pending`),
            fetch(`/projects/${projectId}/approval-workflow/requiring-action`)
        ]);

        const movementData = await movementRes.json();
        const auditData = await auditRes.json();
        const pendingData = await pendingRes.json();
        const requiringData = await requiringRes.json();

        const allTransactions = [];

        // تجميع البيانات من جميع المصادر
        if (movementData.success && movementData.movement_log) {
            movementData.movement_log.forEach(movement => {
                allTransactions.push({
                    type: 'movement',
                    stage: movement.stage,
                    reviewer: movement.reviewer,
                    status: movement.status,
                    status_arabic: movement.status_arabic,
                    date: movement.timestamp,
                    notes: movement.notes,
                    attachment: movement.attachment,
                    is_return: movement.is_return,
                    returned_from_stage: movement.returned_from_stage
                });
            });
        }

        if (auditData.success && auditData.audit_logs) {
            auditData.audit_logs.forEach(audit => {
                allTransactions.push({
                    type: 'audit',
                    stage: audit.drop_arabic || getDropArabic(audit.drop),
                    reviewer: audit.reviewer_name,
                    status: audit.status,
                    status_arabic: audit.status_arabic || getStatusArabic(audit.status),
                    date: audit.created_at,
                    notes: audit.notes,
                    attachment: audit.attachment
                });
            });
        }

        if (pendingData.success && pendingData.pending_approvals) {
            pendingData.pending_approvals.forEach(pending => {
                allTransactions.push({
                    type: 'pending',
                    stage: pending.stage,
                    reviewer: '-',
                    status: 'pending',
                    status_arabic: 'قيد الانتظار',
                    date: pending.created_at,
                    notes: '-',
                    attachment: null
                });
            });
        }

        if (requiringData.success && requiringData.transactions) {
            requiringData.transactions.forEach(req => {
                allTransactions.push({
                    type: 'requiring',
                    stage: req.stage,
                    reviewer: req.reviewer_name,
                    status: req.status,
                    status_arabic: req.status_arabic,
                    date: req.created_at,
                    notes: req.notes,
                    attachment: req.attachment
                });
            });
        }

        // ترتيب المعاملات حسب التاريخ (الأحدث أولاً)
        allTransactions.sort((a, b) => new Date(b.date) - new Date(a.date));

        if (allTransactions.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; color: #6c757d; padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    لا توجد بيانات متعلقة بالمشروع
                </div>
            `;
            return;
        }

        let html = `
            <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #dee2e6;">
                <table style="width: 100%; border-collapse: collapse; background: white;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">رقم</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600;">نوع المعاملة</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">المرحلة</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">المراجع</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600;">الحالة</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">الملاحظات</th>
                            <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">التاريخ والوقت</th>
                            <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600;">المرفقات</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        allTransactions.forEach((transaction, index) => {
            const typeLabel = getTransactionTypeLabel(transaction.type);
            const typeBg = getTransactionTypeBg(transaction.type);
            const statusColor = getStatusColor(transaction.status);
            const rowStyle = index % 2 === 0 ? 'background: #ffffff;' : 'background: #f8f9fa;';
            const highlightReturn = transaction.is_return ? 'border-left: 4px solid #ffc107;' : '';

            const attachment = transaction.attachment
                ? `<a href="${typeof transaction.attachment === 'string' ? transaction.attachment : transaction.attachment.url}" target="_blank" style="display: inline-block; padding: 0.35rem 0.75rem; background: ${statusColor}; color: white; border-radius: 4px; text-decoration: none; font-size: 0.85rem;"><i class="fas fa-download"></i></a>`
                : '<span style="color: #adb5bd;">-</span>';

            const dateStr = transaction.date ? new Date(transaction.date).toLocaleString('ar-SA') : 'غير معروف';
            const notesDisplay = transaction.notes && transaction.notes !== '-'
                ? `<span title="${String(transaction.notes).replace(/"/g, '&quot;')}" style="display: block; max-height: 3em; overflow: hidden; text-overflow: ellipsis; word-wrap: break-word;">${transaction.notes}</span>`
                : '<span style="color: #adb5bd;">-</span>';

            html += `
                <tr style="${rowStyle} ${highlightReturn}">
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 500;">${index + 1}</td>
                    <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6;">
                        <span style="display: inline-block; padding: 0.35rem 0.75rem; background: ${typeBg}; color: white; border-radius: 4px; font-size: 0.8rem; white-space: nowrap; font-weight: 500;">
                            ${typeLabel}
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6;"><strong>${transaction.stage || 'غير معروف'}</strong></td>
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6;">${transaction.reviewer || 'غير معروف'}</td>
                    <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6;">
                        <span style="display: inline-block; padding: 0.35rem 0.75rem; background: ${statusColor}; color: white; border-radius: 4px; font-size: 0.85rem; white-space: nowrap; font-weight: 500;">
                            ${getStatusIcon(transaction.status)} ${transaction.status_arabic || 'غير معروف'}
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-size: 0.9rem; max-width: 250px;">
                        ${notesDisplay}
                    </td>
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-size: 0.9rem; white-space: nowrap; direction: ltr; text-align: left;">
                        <i class="fas fa-calendar"></i> ${dateStr}
                    </td>
                    <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6;">
                        ${attachment}
                    </td>
                </tr>
            `;
        });

        const approvedCount = allTransactions.filter(t => t.status === 'approved').length;
        const rejectedCount = allTransactions.filter(t => t.status === 'rejected').length;
        const needActionCount = allTransactions.filter(t => t.status === 'need_action').length;
        const pendingCount = allTransactions.filter(t => t.status === 'pending').length;

        html += `
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">${approvedCount}</div>
                    <div style="font-size: 0.9rem;">✓ موافقات</div>
                </div>
                <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">${rejectedCount}</div>
                    <div style="font-size: 0.9rem;">✕ رفوضات</div>
                </div>
                <div style="background: linear-gradient(135deg, #fd7e14 0%, #f0ad4e 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">${needActionCount}</div>
                    <div style="font-size: 0.9rem;">⚠ بحاجة إجراء</div>
                </div>
                <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: bold;">${pendingCount}</div>
                    <div style="font-size: 0.9rem;">⧖ معلقة</div>
                </div>
            </div>
        `;

        container.innerHTML = html;

    } catch (error) {
        console.error('Error loading unified approval table:', error);
        const container = document.getElementById('unifiedApprovalTable');
        if (container) {
            container.innerHTML = `
                <div style="text-align: center; color: #dc3545; padding: 2rem;">
                    <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    فشل تحميل جدول الموافقات الموحد
                </div>
            `;
        }
    }
}

// ============================================
// تحميل سجل المعاملات الموحد
// ============================================

function loadConsolidatedTransactionLog(projectId) {
    try {
        fetch(`/projects/${projectId}/approval-workflow/audit-log`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                const consolidatedDiv = document.getElementById('consolidatedTransactionLog');
                if (!consolidatedDiv) return;

                if (data && data.success && data.audit_logs && data.audit_logs.length > 0) {
                    const approvals = data.audit_logs;

                    approvals.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                    let html = `
                        <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #dee2e6;">
                            <table style="width: 100%; border-collapse: collapse; background: white;">
                                <thead>
                                    <tr style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
                                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">رقم</th>
                                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">المرحلة</th>
                                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">المراجع</th>
                                        <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600;">الحالة</th>
                                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">الملاحظات</th>
                                        <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #dee2e6; font-weight: 600;">التاريخ والوقت</th>
                                        <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #dee2e6; font-weight: 600;">المرفق</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    approvals.forEach((approval, index) => {
                        const statusBadge = getStatusColor(approval.status || 'pending');
                        const rowStyle = index % 2 === 0 ? 'background: #ffffff;' : 'background: #f8f9fa;';

                        const attachmentBtn = approval.attachment && approval.attachment.url ?
                            `<a href="${approval.attachment.url}" target="_blank" title="تحميل ${approval.attachment.name || 'الملف'}" style="display: inline-block; padding: 0.35rem 0.75rem; background: #007bff; color: white; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-download"></i>
                             </a>` :
                            '<span style="color: #adb5bd;">-</span>';

                        const notesDisplay = approval.notes ?
                            `<span title="${String(approval.notes).replace(/"/g, '&quot;')}" style="display: block; max-height: 3em; overflow: hidden; text-overflow: ellipsis; word-wrap: break-word;">${approval.notes}</span>` :
                            '<span style="color: #adb5bd;">-</span>';

                        const dropArabic = approval.drop_arabic || getDropArabic(approval.drop);
                        const statusArabic = approval.status_arabic || getStatusArabic(approval.status);

                        html += `
                            <tr style="${rowStyle}">
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-weight: 500;">${index + 1}</td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6;"><strong>${dropArabic || 'غير معروف'}</strong></td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6;">${approval.reviewer_name || 'غير معروف'}</td>
                                <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6;">
                                    <span style="display: inline-block; padding: 0.35rem 0.75rem; background: ${statusBadge}; color: white; border-radius: 4px; font-size: 0.85rem; white-space: nowrap;">
                                        ${statusArabic || 'غير معروف'}
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-size: 0.9rem; max-width: 300px;">
                                    ${notesDisplay}
                                </td>
                                <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #dee2e6; font-size: 0.9rem; white-space: nowrap; direction: ltr; text-align: left;">
                                    <i class="fas fa-calendar"></i> ${approval.created_at || 'غير معروف'}
                                </td>
                                <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #dee2e6;">
                                    ${attachmentBtn}
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top: 1rem; padding: 1rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 6px; font-size: 0.95rem; color: #495057; text-align: right; border-left: 4px solid #007bff;">
                            <strong style="color: #003d7a; font-size: 1.05rem;">📊 ملخص الموافقات والرفوضات</strong>
                            <ul style="margin: 0.75rem 0 0 0; padding: 0; list-style: none;">
                                <li style="margin: 0.35rem 0;">✓ <strong>إجمالي المعاملات:</strong> ${approvals.length}</li>
                                <li style="margin: 0.35rem 0;">✓ <strong>الموافقات:</strong> ${approvals.filter(a => a.status === 'approved').length}</li>
                                <li style="margin: 0.35rem 0;">✕ <strong>الرفوضات:</strong> ${approvals.filter(a => a.status === 'rejected').length}</li>
                                <li style="margin: 0.35rem 0;">⚠ <strong>بحاجة إلى إجراء:</strong> ${approvals.filter(a => a.status === 'need_action' || a.status === 'needs_revision').length}</li>
                                <li style="margin: 0.35rem 0;">⟳ <strong>إعادة تقديم:</strong> ${approvals.filter(a => a.status === 'resubmitted').length}</li>
                            </ul>
                        </div>
                    `;

                    consolidatedDiv.innerHTML = html;
                } else {
                    consolidatedDiv.innerHTML = `
                        <div style="text-align: center; color: #6c757d; padding: 2rem;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            <strong>لا توجد معاملات</strong>
                            <p style="margin-top: 0.5rem;">لم يتم تسجيل أي معاملات على هذا المشروع بعد</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading consolidated transaction log:', error);
                const consolidatedDiv = document.getElementById('consolidatedTransactionLog');
                if (consolidatedDiv) {
                    consolidatedDiv.innerHTML = `
                        <div style="text-align: center; color: #dc3545; padding: 2rem;">
                            <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            <strong>فشل تحميل السجل الموحد</strong>
                        </div>
                    `;
                }
            });
    } catch (error) {
        console.error('Error initializing consolidated transaction log:', error);
    }
}