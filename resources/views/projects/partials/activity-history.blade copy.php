<div class="content-section" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 1px solid #e9ecef;">
    <div class="section-header">
        <i class="fas fa-history" style="color: #007bff;"></i>
        سجل نشاط المشروع الكامل
        <button type="button" id="refreshActivityLog" style="float: left; background: none; border: none; color: #007bff; cursor: pointer; font-size: 1.2rem; padding: 0.5rem; transition: transform 0.3s ease;" title="تحديث السجل" onmouseover="this.style.transform='rotate(180deg)';" onmouseout="this.style.transform='rotate(0)';">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>

    <div id="activityLogContainer" style="overflow-x: auto; margin: 1.5rem 0; padding: 0.5rem;">
        <div style="text-align: center; padding: 3rem 2rem;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">جاري التحميل...</span>
            </div>
            <p style="color: #6c757d; margin-top: 1.5rem; font-weight: 500;">جاري تحميل سجل النشاط الكامل...</p>
        </div>
    </div>

    <div id="activitySummary" style="margin-top: 1.5rem; display: none;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 1.2rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;" id="summaryApproved">0</div>
                <div style="font-size: 0.85rem; margin-top: 0.3rem;">✓ موافقات</div>
            </div>
            <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 1.2rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;" id="summaryRejected">0</div>
                <div style="font-size: 0.85rem; margin-top: 0.3rem;">✕ رفوضات</div>
            </div>
            <div style="background: linear-gradient(135deg, #fd7e14 0%, #f0ad4e 100%); color: white; padding: 1.2rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;" id="summaryNeedAction">0</div>
                <div style="font-size: 0.85rem; margin-top: 0.3rem;">⚠ بحاجة إجراء</div>
            </div>
            <div style="background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); color: white; padding: 1.2rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;" id="summaryPending">0</div>
                <div style="font-size: 0.85rem; margin-top: 0.3rem;">⧖ قيد الانتظار</div>
            </div>
        </div>
    </div>

    <div style="padding: 0 1.5rem 1rem 1.5rem; color: #6c757d; font-size: 0.85rem;">
        <i class="fas fa-info-circle" style="margin-left: 0.5rem; color: #0dcaf0;"></i>
        <span>السجل الشامل لجميع مراحل الموافقة على المشروع مع تفاصيل كل مرحلة وحالتها، مرتبة حسب وقت الإجراء</span>
    </div>
</div>

<script>
function getActivityTypeStyles(type, status) {
    const typeMap = {
        'movement': 'movement',
        'audit': 'approval',
        'pending': 'pending',
        'requiring': 'rejection',
        'approved': 'approval',
        'rejected': 'rejection',
        'need_action': 'need_action',
        'pending': 'pending'
    };
    
    const mappedType = typeMap[type] || typeMap[status] || 'pending';
    
    const styles = {
        'submission': { icon: 'fa-paper-plane', color: '#0dcaf0', badge: 'تقديم', bg: '#cfe2ff', border: '#b6d4fe' },
        'resubmission': { icon: 'fa-redo', color: '#17a2b8', badge: 'إعادة تقديم', bg: '#d1f2eb', border: '#a8e6da' },
        'approval': { icon: 'fa-check-circle', color: '#28a745', badge: 'موافق', bg: '#d4edda', border: '#c3e6cb' },
        'movement': { icon: 'fa-project-diagram', color: '#17a2b8', badge: 'حركة المشروع', bg: '#d1f2eb', border: '#a8e6da' },
        'need_action': { icon: 'fa-exclamation-circle', color: '#fd7e14', badge: 'بحاجة إجراء', bg: '#fff3cd', border: '#ffeaa7' },
        'return': { icon: 'fa-undo', color: '#fd7e14', badge: 'تم الإرجاع', bg: '#fff3cd', border: '#ffeaa7' },
        'rejection': { icon: 'fa-times-circle', color: '#dc3545', badge: 'مرفوض', bg: '#f8d7da', border: '#f5c6cb' },
        'pending': { icon: 'fa-hourglass-half', color: '#6c757d', badge: 'قيد الانتظار', bg: '#e2e3e5', border: '#d3d4d5' }
    };
    return styles[mappedType] || styles['pending'];
}

function getActivityTypeLabel(type) {
    const labels = {
        'movement': 'حركة المشروع',
        'audit': 'سجل الموافقات',
        'pending': 'معلقة',
        'requiring': 'بحاجة معالجة'
    };
    return labels[type] || type;
}

function getActivityTypeBg(type) {
    const colors = {
        'movement': '#17a2b8',
        'audit': '#007bff',
        'pending': '#ffc107',
        'requiring': '#dc3545'
    };
    return colors[type] || '#6c757d';
}

function renderActivityLog(activities) {
    if (!activities || activities.length === 0) {
        return '<div class="no-data" style="padding: 2rem; text-align: center; background: #f8f9fa; border-radius: 8px; border: 1px dashed #dee2e6;"><i class="fas fa-inbox" style="font-size: 2rem; color: #adb5bd; margin-bottom: 0.5rem; display: block;"></i>لا توجد سجلات نشاط للمشروع</div>';
    }

    let html = '<table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.1);">';
    
    html += '<thead>';
    html += '<tr style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; border-right: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-layer-group" style="margin-left: 0.5rem;"></i>نوع النشاط</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; border-right: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-layer-group" style="margin-left: 0.5rem;"></i>المرحلة</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; border-right: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-tasks" style="margin-left: 0.5rem;"></i>الحالة</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; border-right: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-user-tie" style="margin-left: 0.5rem;"></i>المستخدم</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; border-right: 1px solid rgba(255,255,255,0.1); min-width: 280px;"><i class="fas fa-message" style="margin-left: 0.5rem;"></i>الملاحظات / الأسباب</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; min-width: 150px;"><i class="fas fa-file-download" style="margin-left: 0.5rem;"></i>المرفقات</th>';
    html += '<th style="padding: 1.2rem 1rem; text-align: right; font-weight: 700; font-size: 0.95rem; min-width: 140px;"><i class="fas fa-clock" style="margin-left: 0.5rem;"></i>الوقت</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';

    activities.forEach((log, index) => {
        const styles = getActivityTypeStyles(log.activity_type, log.status);
        const activityTypeLabel = getActivityTypeLabel(log.activity_type);
        const activityTypeBg = getActivityTypeBg(log.activity_type);
        const hasNotes = log.notes && log.notes.trim() !== '';
        const notesText = log.notes || '-';
        const timestamp = log.date ? new Date(log.date).toLocaleString('ar-SA') : 'غير معروف';
        
        html += '<tr style="border-bottom: 1px solid #e9ecef; transition: all 0.2s ease; background: white;" onmouseover="this.style.background=\'#f8f9fa\'; this.style.boxShadow=\'inset 0 0 8px rgba(0,123,255,0.05)\';" onmouseout="this.style.background=\'white\'; this.style.boxShadow=\'none\';">';
        
        html += '<td style="padding: 1rem; text-align: center; font-weight: 600; color: #333; vertical-align: middle;">';
        html += '<span style="display: inline-block; padding: 0.4rem 0.8rem; background: ' + activityTypeBg + '; color: white; border-radius: 4px; font-size: 0.8rem; white-space: nowrap; font-weight: 500;">' + activityTypeLabel + '</span>';
        html += '</td>';

        html += '<td style="padding: 1rem; text-align: right; font-weight: 600; color: #333; vertical-align: middle;">' + (log.stage || 'غير محدد') + '</td>';
        
        html += '<td style="padding: 1rem; text-align: center; vertical-align: middle;">';
        html += '<span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.9rem; background: ' + styles.bg + '; color: ' + styles.color + '; font-size: 0.85rem; font-weight: 700; border-radius: 20px; border: 1.5px solid ' + styles.border + '; white-space: nowrap; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        html += '<i class="fas ' + styles.icon + '" style="font-size: 0.95rem;"></i>' + styles.badge;
        html += '</span>';
        html += '</td>';
        
        html += '<td style="padding: 1rem; text-align: right; color: #333; vertical-align: middle;">';
        html += '<div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.6rem;">';
        html += '<div style="text-align: right; line-height: 1.4;">';
        html += '<div style="font-weight: 600; font-size: 0.95rem;">' + (log.reviewer || 'غير معروف') + '</div>';
        html += '</div>';
        html += '<i class="fas fa-user-circle" style="font-size: 1.8rem; color: #007bff;"></i>';
        html += '</div>';
        html += '</td>';
        
        html += '<td style="padding: 1rem; text-align: right; color: #6c757d; font-size: 0.9rem; vertical-align: middle;">';
        if (hasNotes) {
            html += '<button type="button" class="btn-view-notes-modal" data-index="' + index + '" data-notes="' + notesText.replace(/"/g, '&quot;') + '" style="background: none; border: none; color: #fd7e14; cursor: pointer; font-weight: 600; text-decoration: underline; padding: 0.3rem 0.6rem; font-size: 0.9rem; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.background=\'rgba(253,126,20,0.1)\';" onmouseout="this.style.background=\'transparent\';"><i class="fas fa-eye" style="margin-left: 0.3rem;"></i>عرض</button>';
        } else {
            html += '<span style="color: #adb5bd; font-style: italic;">-</span>';
        }
        html += '</td>';
        
        html += '<td style="padding: 1rem; text-align: right; vertical-align: middle;">';
        if (log.attachment) {
            const fileName = typeof log.attachment === 'object' ? (log.attachment.name || 'ملف') : 'ملف';
            const attachmentUrl = typeof log.attachment === 'object' ? log.attachment.url : log.attachment;
            html += '<div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.8rem;">';
            html += '<div style="text-align: right;">';
            html += '<div style="font-size: 0.85rem; color: #333; font-weight: 500;">' + fileName + '</div>';
            html += '</div>';
            html += '<div style="display: flex; gap: 0.5rem;">';
            html += '<a href="' + attachmentUrl + '" download title="تحميل" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #e7f3ff; color: #007bff; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background=\'#cfe2ff\';" onmouseout="this.style.background=\'#e7f3ff\';"><i class="fas fa-download"></i></a>';
            html += '<a href="' + attachmentUrl + '" target="_blank" title="عرض" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #d1f2eb; color: #17a2b8; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background=\'#a8e6da\';" onmouseout="this.style.background=\'#d1f2eb\';"><i class="fas fa-eye"></i></a>';
            html += '</div>';
            html += '</div>';
        } else {
            html += '<div style="text-align: center; color: #adb5bd; font-style: italic;">-</div>';
        }
        html += '</td>';

        html += '<td style="padding: 1rem; text-align: right; font-size: 0.85rem; color: #6c757d; vertical-align: middle;">';
        html += '<div style="white-space: nowrap;">' + timestamp + '</div>';
        html += '</td>';
        
        html += '</tr>';
    });

    html += '</tbody></table>';
    return html;
}

async function loadActivityLog() {
    const projectId = {{ $project->id }};
    if (!projectId) return;

    try {
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

        let allActivities = [];

        if (movementData.success && movementData.movement_log) {
            movementData.movement_log.forEach(item => {
                allActivities.push({
                    activity_type: 'movement',
                    stage: item.stage,
                    status: item.status,
                    reviewer: item.reviewer,
                    notes: item.notes,
                    attachment: item.attachment,
                    date: item.timestamp
                });
            });
        }

        if (auditData.success && auditData.audit_logs) {
            auditData.audit_logs.forEach(item => {
                allActivities.push({
                    activity_type: 'audit',
                    stage: item.drop_arabic || item.drop,
                    status: item.status,
                    reviewer: item.reviewer_name,
                    notes: item.notes,
                    attachment: item.attachment,
                    date: item.created_at
                });
            });
        }

        if (pendingData.success && pendingData.pending_approvals) {
            pendingData.pending_approvals.forEach(item => {
                allActivities.push({
                    activity_type: 'pending',
                    stage: item.stage,
                    status: 'pending',
                    reviewer: '-',
                    notes: null,
                    attachment: null,
                    date: item.created_at
                });
            });
        }

        if (requiringData.success && requiringData.transactions) {
            requiringData.transactions.forEach(item => {
                allActivities.push({
                    activity_type: 'requiring',
                    stage: item.stage,
                    status: item.status,
                    reviewer: item.reviewer_name,
                    notes: item.notes,
                    attachment: item.attachment,
                    date: item.created_at
                });
            });
        }

        allActivities.sort((a, b) => new Date(b.date) - new Date(a.date));

        const approvedCount = allActivities.filter(a => a.status === 'approved').length;
        const rejectedCount = allActivities.filter(a => a.status === 'rejected').length;
        const needActionCount = allActivities.filter(a => a.status === 'need_action').length;
        const pendingCount = allActivities.filter(a => a.status === 'pending').length;

        document.getElementById('summaryApproved').textContent = approvedCount;
        document.getElementById('summaryRejected').textContent = rejectedCount;
        document.getElementById('summaryNeedAction').textContent = needActionCount;
        document.getElementById('summaryPending').textContent = pendingCount;
        document.getElementById('activitySummary').style.display = 'block';

        document.getElementById('activityLogContainer').innerHTML = renderActivityLog(allActivities);
        attachNotesModalListeners();
    } catch (error) {
        console.error('Error loading activity log:', error);
        document.getElementById('activityLogContainer').innerHTML = '<div style="padding: 2rem; text-align: center; color: #dc3545;"><i class="fas fa-exclamation-circle"></i> حدث خطأ في تحميل السجل</div>';
    }
}

function attachNotesModalListeners() {
    document.querySelectorAll('.btn-view-notes-modal').forEach(button => {
        button.addEventListener('click', function() {
            const notes = this.getAttribute('data-notes');
            const index = this.getAttribute('data-index');
            showNotesModal(notes, index);
        });
    });
}

function showNotesModal(notes, index) {
    let modal = document.getElementById('notesModalDynamic');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'notesModalDynamic';
        document.body.appendChild(modal);
    }
    
    modal.innerHTML = '<div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;" onclick="closeNotesModal(event)"><div style="background: white; border-radius: 12px; padding: 2rem; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);" onclick="event.stopPropagation()"><div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;"><h5 style="margin: 0; font-weight: 700; color: #333;">الملاحظات والأسباب</h5><button type="button" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6c757d;" onclick="closeNotesModal()"><i class="fas fa-times"></i></button></div><div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 1rem; margin-bottom: 1.5rem;"><p style="margin: 0; color: #856404; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word;">' + notes + '</p></div><div style="text-align: left;"><button type="button" style="padding: 0.5rem 1.5rem; border: none; background: #6c757d; color: white; border-radius: 6px; cursor: pointer; font-weight: 600;" onclick="closeNotesModal()">إغلاق</button></div></div></div>';
    
    modal.style.display = 'block';
}

function closeNotesModal(event) {
    if (event && event.target.id !== 'notesModalDynamic') return;
    const modal = document.getElementById('notesModalDynamic');
    if (modal) modal.remove();
}

document.addEventListener('DOMContentLoaded', function() {
    loadActivityLog();
    const refreshBtn = document.getElementById('refreshActivityLog');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadActivityLog);
    }
});
</script>
