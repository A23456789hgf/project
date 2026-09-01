<div class="section-card">
    <div class="section-card-header">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-history text-primary fs-5"></i>
            <h4 class="m-0 text-dark fw-semibold">سجل نشاط المشروع الكامل</h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="refreshActivityLog" class="icon-btn-ghost" title="تحديث السجل">
                <i class="fas fa-sync-alt"></i>
            </button>
            <span class="badge bg-info px-2 py-1" id="totalActivitiesCount">0</span>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="activity-log-filter">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted fw-medium m-0">فلترة حسب النوع:</label>
                <select id="activityFilter" class="form-select form-select-sm">
                    <option value="all">الكل</option>
                    <option value="approvals">الموافقات</option>
                    <option value="financial_review">المراجعات المالية</option>
                    <option value="rejections">الرفوضات</option>
                    <option value="need_action">بحاجة إجراء</option>
                    <option value="pending">معلقة</option>
                    <option value="phase_transition">انتقالات تلقائية</option>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted fw-medium m-0">الترتيب:</label>
                <select id="activitySort" class="form-select form-select-sm">
                    <option value="newest">الأحدث أولاً</option>
                    <option value="oldest">الأقدم أولاً</option>
                </select>
            </div>
            <button id="applyFilter" class="btn btn-primary btn-sm">
                <i class="fas fa-filter me-1"></i> تطبيق
            </button>
            <button id="resetFilter" class="btn btn-secondary btn-sm">
                <i class="fas fa-redo me-1"></i> إعادة تعيين
            </button>
        </div>
    </div>

    <div id="activityLogContainer" style="min-height: 300px; padding: 1.5rem;">
        <div style="text-align: center; padding: 3rem 2rem;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">جاري التحميل...</span>
            </div>
            <p style="color: #6c757d; margin-top: 1.5rem; font-weight: 500;">جاري تحميل سجل النشاط الكامل...</p>
        </div>
    </div>

    <!-- Phase Transitions Section (Reduced Visibility) -->
    <div id="phaseTransitionsSection" style="display: none; margin: 0 1.5rem 1.5rem 1.5rem; padding: 1rem; background: #f1f8e9; border: 1px solid #c8e6c9; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; cursor: pointer;" onclick="document.getElementById('phaseTransitionsList').style.display = document.getElementById('phaseTransitionsList').style.display === 'none' ? 'grid' : 'none'">
            <h5 style="margin: 0; color: #2e7d32; font-weight: 600; font-size: 1rem;">
                <i class="fas fa-exchange-alt me-2"></i> ملخص الانتقالات التلقائية
            </h5>
            <span class="badge bg-success" id="phaseTransitionCount">0</span>
        </div>
        <div id="phaseTransitionsList" style="display: none; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 0.75rem; margin-top: 0.75rem;">
            <!-- سيتم ملؤها ديناميكياً -->
        </div>
    </div>

    <!-- Stages Requiring Return Section -->
    <div id="stagesRequiringReturnSection" style="display: none; padding: 1.5rem; background: #fff8e5; border-top: 2px solid #ffc107; border-bottom: 2px solid #ffc107; margin-top: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h5 style="margin: 0; color: #856404; font-weight: 600;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                المراحل التي تحتاج إرجاع
            </h5>
            <span class="badge bg-warning text-dark" id="stagesRequiringReturnCount">0</span>
        </div>
        <div id="stagesRequiringReturnList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
            <!-- سيتم ملؤها ديناميكياً -->
        </div>
    </div>

    <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 0.85rem; border-radius: 0 0 10px 10px;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-info-circle" style="color: #0dcaf0;"></i>
            <span>السجل الشامل لجميع مراحل الموافقة على المشروع مع تفاصيل كل مرحلة وحالتها، مرتبة حسب وقت الإجراء</span>
        </div>
        <div id="activityStats" style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
            <!-- سيتم ملؤها ديناميكياً -->
        </div>
    </div>
</div>

<script>
/* ============================================================
    دوال الألوان والأيقونات للحالات
============================================================ */
function getStatusCategory(status) {
    const statusMap = {
        'approved': 'approvals',
        'approval': 'approvals',
        'accepted': 'approvals',
        'rejected': 'rejections',
        'rejection': 'rejections',
        'need_action': 'need_action',
        'rolled_back_for_review': 'need_action',
        'requiring_action': 'need_action',
        'needs_revision': 'need_action',
        'returned_for_revision': 'need_action',
        'returned': 'returned',
        'resubmitted': 'resubmitted',
        'financial_review': 'financial_review',
        'technical_review': 'technical_review',
        'financial_technical_review': 'financial_review', // Map joint status to financial category for styling
        'pending': 'pending'
    };
    return statusMap[status] || 'pending';
}

function getActivityTypeStyles(type, status) {
    const category = getStatusCategory(status);
    
    const styles = {
        'approvals': { 
            icon: 'fa-check-circle', 
            color: '#28a745', 
            badge: 'موافق', 
            bg: '#d4edda', 
            border: '#c3e6cb',
            label: 'موافقة'
        },
        'rejections': { 
            icon: 'fa-times-circle', 
            color: '#dc3545', 
            badge: 'مرفوض', 
            bg: '#f8d7da', 
            border: '#f5c6cb',
            label: 'رفض'
        },
        'need_action': { 
            icon: 'fa-exclamation-circle', 
            color: '#fd7e14', 
            badge: 'بحاجة إجراء', 
            bg: '#fff3cd', 
            border: '#ffeaa7',
            label: 'يحتاج إجراء'
        },
        'returned': { 
            icon: 'fa-undo', 
            color: '#17a2b8', 
            badge: 'مرتجعة', 
            bg: '#d1ecf1', 
            border: '#bee5eb',
            label: 'مرتجعة'
        },
        'resubmitted': { 
            icon: 'fa-paper-plane', 
            color: '#6f42c1', 
            badge: 'معاد تقديمها', 
            bg: '#e2d9f3', 
            border: '#d1c4e9',
            label: 'معاد تقديمها'
        },
        'financial_review': {
            icon: 'fa-dollar-sign',
            color: '#17a2b8',
            badge: '💰 مراجعة مالية',
            bg: '#d1ecf1',
            border: '#bee5eb',
            label: 'مراجعة مالية'
        },
        'technical_review': {
            icon: 'fa-tools',
            color: '#6f42c1',
            badge: '🔧 مراجعة فنية',
            bg: '#e2d9f3',
            border: '#d1c4e9',
            label: 'مراجعة فنية'
        },
        'phase_transition': {
            icon: 'fa-exchange-alt',
            color: '#20c997',
            badge: 'انتقال تلقائي',
            bg: '#d1f0e4',
            border: '#a8e6d2',
            label: 'انتقال بين المراحل'
        },
        'pending': { 
            icon: 'fa-hourglass-half', 
            color: '#6c757d', 
            badge: 'قيد الانتظار', 
            bg: '#e2e3e5', 
            border: '#d3d4d5',
            label: 'قيد الانتظار'
        }
    };

    // Handle phase_transition type directly
    if (type === 'phase_transition') {
        return styles['phase_transition'];
    }

    return styles[category] || styles['pending'];
}

function getStatusSortOrder(status) {
    const orderMap = {
        'approvals': 0,
        'need_action': 1,
        'returned': 2,
        'resubmitted': 3,
        'rejections': 4,
        'pending': 5
    };
    const category = getStatusCategory(status);
    return orderMap[category] !== undefined ? orderMap[category] : 5;
}

/* ============================================================
    إزالة التكرارات من السجلات
============================================================ */
function deduplicateActivities(activities) {
    const seen = new Set();
    const deduplicated = [];

    activities.forEach(log => {
        // Normalize stage and status for consistent comparison
        const normalizedStage = (log.stage || '').trim();
        const normalizedStatus = (log.status || '').trim();
        const normalizedReviewer = (log.reviewer || '').trim();
        
        // Use date rounded to nearest 5 minutes to handle timestamp variations across APIs
        const dateObj = new Date(log.date);
        const roundedDate = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), dateObj.getHours(), Math.floor(dateObj.getMinutes() / 5) * 5);
        
        const key = `${normalizedStage}|${normalizedStatus}|${normalizedReviewer}|${roundedDate.getTime()}`;
        
        if (!seen.has(key)) {
            seen.add(key);
            deduplicated.push(log);
        }
    });

    return deduplicated;
}

/* ============================================================
    دالة رسم الجدول
============================================================ */
function renderActivityLog(activities, filterType = 'all', sortOrder = 'newest') {
    if (!activities || activities.length === 0) {
        return `
        <div style="padding: 3rem 2rem; text-align: center; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #adb5bd; margin-bottom: 1rem; opacity: 0.5;"></i>
            <h5 style="color: #6c757d; margin-bottom: 0.5rem;">لا توجد سجلات نشاط</h5>
            <p style="color: #adb5bd; font-size: 0.9rem;">لم يتم تسجيل أي نشاط على هذا المشروع بعد</p>
        </div>`;
    }

    // تطبيق الفلترة
    let filteredActivities = activities;
    if (filterType !== 'all') {
        filteredActivities = activities.filter(log => {
            const category = getStatusCategory(log.status);
            if (filterType === 'returned') return category === 'returned';
            if (filterType === 'resubmitted') return category === 'resubmitted';
            return category === filterType;
        });
    }

    // تطبيق الترتيب
    filteredActivities.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return sortOrder === 'newest' ? dateB - dateA : dateA - dateB;
    });

    // احتساب الإحصائيات
    const statusCounts = {
        'approvals': 0,
        'need_action': 0,
        'rejections': 0,
        'returned': 0,
        'resubmitted': 0,
        'pending': 0,
        'total': filteredActivities.length
    };

    filteredActivities.forEach(log => {
        const category = getStatusCategory(log.status);
        if (statusCounts.hasOwnProperty(category)) {
            statusCounts[category]++;
        }
    });

    // تحديث العداد
    document.getElementById('totalActivitiesCount').textContent = statusCounts.total;

    // تحديث الإحصائيات
    updateActivityStats(statusCounts);

    let html = `
        <div style="margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding: 0.75rem; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #007bff;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.85rem; color: #495057; font-weight: 500;">
                        <i class="fas fa-filter me-1"></i>
                        تم فلترة ${statusCounts.total} سجل
                    </span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button onclick="exportToCSV(filteredActivities)" style="padding: 0.4rem 0.8rem; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                        <i class="fas fa-download me-1"></i> تصدير CSV
                    </button>
                    <button onclick="printActivityLog()" style="padding: 0.4rem 0.8rem; background: #6c757d; color: white; border: none; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                        <i class="fas fa-print me-1"></i> طباعة
                    </button>
                </div>
            </div>
        </div>

        <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse; background: white; min-width: 1000px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
                    <th style="padding: 1rem; text-align: center; width: 60px; font-weight: 600; border-bottom: 2px solid #0056b3;">#</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600; border-bottom: 2px solid #0056b3;">المرحلة</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600; border-bottom: 2px solid #0056b3;">الحالة</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600; border-bottom: 2px solid #0056b3;">المستخدم</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600; border-bottom: 2px solid #0056b3;">الملاحظات</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600; border-bottom: 2px solid #0056b3;">المرفقات</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600; border-bottom: 2px solid #0056b3;">التاريخ والوقت</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600; border-bottom: 2px solid #0056b3;">الوقت المنقضي</th>
                </tr>
            </thead>
            <tbody>
    `;

    filteredActivities.forEach((log, index) => {
        const styles = getActivityTypeStyles(log.activity_type, log.status);
        const rowClass = index % 2 === 0 ? 'bg-light' : 'bg-white';
        
        const notesDisplay = log.notes ? 
            `<button class="btn-view-notes-modal btn btn-link p-0 text-decoration-none" 
                    data-notes="${log.notes.replace(/"/g, '&quot;')}" 
                    style="color: #fd7e14; font-size: 0.85rem;">
                <i class="fas fa-sticky-note me-1"></i>عرض الملاحظات
            </button>` 
            : '<span class="text-muted" style="font-size: 0.85rem;">-</span>';

        const attachmentDisplay = log.attachment ? 
            `<a href="${log.attachment}" target="_blank" 
               class="btn btn-sm btn-outline-primary" 
               style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                <i class="fas fa-download me-1"></i>تحميل
            </a>` 
            : '<span class="text-muted" style="font-size: 0.85rem;">-</span>';

        const dateFormatted = new Date(log.date).toLocaleString('ar-SA', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Get elapsed time from metadata
        const elapsedDisplay = log.elapsed_string ? 
            `<span class="badge rounded-pill bg-light text-dark border" style="font-size: 0.75rem; font-weight: 500;">
                <i class="fas fa-hourglass-half me-1 text-primary"></i> ${log.elapsed_string}
            </span>` : 
            '<span class="text-muted small">-</span>';

        html += `
            <tr style="${rowClass === 'bg-light' ? 'background: #f8f9fa;' : 'background: white;'} border-bottom: 1px solid #e9ecef; transition: background-color 0.2s ease;">
                <td style="padding: 0.75rem; text-align: center; font-weight: 600; color: #495057; border-left: 1px solid #e9ecef;">
                    ${index + 1}
                </td>
                <td style="padding: 0.75rem; font-weight: 600; color: #007bff;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-layer-group" style="color: #6c757d;"></i>
                        ${log.stage}
                    </div>
                </td>
                <td style="padding: 0.75rem; text-align: center;">
                    <span style="padding: 0.35rem 0.75rem; border-radius: 20px; color: ${styles.color}; background: ${styles.bg}; border: 1px solid ${styles.border}; font-weight: 600; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <i class="fas ${styles.icon}"></i> ${styles.badge}
                    </span>
                </td>
                <td style="padding: 0.75rem; color: #495057; font-size: 0.9rem;">
                    ${log.reviewer || '<span class="text-muted">غير محدد</span>'}
                    ${log.reviewer_type === 'financial' ? '<span class="badge bg-info ms-1" style="font-size: 0.75rem;">💰 مالي</span>' : ''}
                    ${log.reviewer_type === 'technical' ? '<span class="badge ms-1" style="background-color: #6f42c1; font-size: 0.75rem;">🔧 فني</span>' : ''}
                </td>
                <td style="padding: 0.75rem; max-width: 300px;">
                    ${notesDisplay}
                </td>
                <td style="padding: 0.75rem; text-align: center;">
                    ${attachmentDisplay}
                </td>
                <td style="padding: 0.75rem; color: #6c757d; font-size: 0.85rem; white-space: nowrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; justify-content: flex-end;">
                        <i class="fas fa-clock" style="color: #6c757d;"></i>
                        ${dateFormatted}
                    </div>
                </td>
                <td style="padding: 0.75rem; text-align: center;">
                    ${elapsedDisplay}
                </td>
            </tr>`;
    });

    html += `</tbody></table></div>`;
    
    // إضافة ملخص النتائج
    html += `
        <div style="padding: 1rem; margin-top: 1.5rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px; border: 1px solid #dee2e6;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fas fa-chart-bar" style="color: #007bff; font-size: 1.2rem;"></i>
                    <h6 style="margin: 0; color: #495057;">ملخص النتائج</h6>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="padding: 0.4rem 0.8rem; background: #d4edda; color: #155724; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-check-circle"></i> ${statusCounts.approvals}
                    </span>
                    <span style="padding: 0.4rem 0.8rem; background: #fff3cd; color: #856404; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-exclamation-circle"></i> ${statusCounts.need_action}
                    </span>
                    <span style="padding: 0.4rem 0.8rem; background: #f8d7da; color: #721c24; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-times-circle"></i> ${statusCounts.rejections}
                    </span>
                    <span style="padding: 0.4rem 0.8rem; background: #d1ecf1; color: #0c5460; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-undo"></i> ${statusCounts.returned}
                    </span>
                    <span style="padding: 0.4rem 0.8rem; background: #e2d9f3; color: #382e4d; border-radius: 20px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fas fa-paper-plane"></i> ${statusCounts.resubmitted}
                    </span>
                </div>
            </div>
        </div>
    `;

    return html;
}

/* ============================================================
    تحديث إحصائيات النشاط
============================================================ */
function updateActivityStats(counts) {
    const statsContainer = document.getElementById('activityStats');
    if (!statsContainer) return;

    statsContainer.innerHTML = `
        <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.5rem; background: #e9ecef; border-radius: 4px; font-size: 0.8rem;">
            <i class="fas fa-list-alt"></i> إجمالي السجلات: ${counts.total}
        </span>
        ${counts.approvals > 0 ? `
            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.5rem; background: #d4edda; color: #155724; border-radius: 4px; font-size: 0.8rem;">
                <i class="fas fa-check-circle"></i> موافقات: ${counts.approvals}
            </span>
        ` : ''}
        ${counts.need_action > 0 ? `
            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.5rem; background: #fff3cd; color: #856404; border-radius: 4px; font-size: 0.8rem;">
                <i class="fas fa-exclamation-circle"></i> بحاجة إجراء: ${counts.need_action}
            </span>
        ` : ''}
        ${counts.returned > 0 ? `
            <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.5rem; background: #d1ecf1; color: #0c5460; border-radius: 4px; font-size: 0.8rem;">
                <i class="fas fa-undo"></i> مرتجعة: ${counts.returned}
            </span>
        ` : ''}
    `;
}

/* ============================================================
    عرض المراحل التي تحتاج إرجاع
============================================================ */
function showStagesRequiringReturn(stages) {
    const section = document.getElementById('stagesRequiringReturnSection');
    const listContainer = document.getElementById('stagesRequiringReturnList');
    const countElement = document.getElementById('stagesRequiringReturnCount');

    if (!stages || stages.length === 0) {
        section.style.display = 'none';
        return;
    }

    countElement.textContent = stages.length;
    
    let html = '';
    stages.forEach((stage, index) => {
        const statusBadge = stage.status === 'needs_revision' ? 
            '<span class="badge bg-danger">يحتاج تعديل</span>' :
            '<span class="badge bg-warning text-dark">قيد الانتظار</span>';

        const date = stage.created_at ? 
            new Date(stage.created_at).toLocaleString('ar-SA') : 
            'غير محدد';

        html += `
            <div class="card" style="border: 1px solid #ffeaa7; background: #fff3cd;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0" style="color: #856404;">
                            <i class="fas fa-layer-group me-2"></i>
                            ${stage.stage_name}
                        </h6>
                        ${statusBadge}
                    </div>
                    <p class="mb-2 small" style="color: #996515;">
                        ${stage.notes || 'لا توجد ملاحظات'}
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i> ${date}
                        </small>
                        <button onclick="showReturnToStageModal(${stage.stage_id}, {{ $project->id }}, '${stage.stage_name.replace(/'/g, "\\'")}')" 
                                class="btn btn-sm btn-warning">
                            <i class="fas fa-undo me-1"></i> العودة للمرحلة
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
    section.style.display = 'block';
}

/* ===========================================================
    عرض الانتقالات التلقائية بين المراحل
=========================================================== */
function showPhaseTransitions(transitions) {
    const section = document.getElementById('phaseTransitionsSection');
    const listContainer = document.getElementById('phaseTransitionsList');
    const countElement = document.getElementById('phaseTransitionCount');

    if (!transitions || transitions.length === 0) {
        section.style.display = 'none';
        return;
    }

    countElement.textContent = transitions.length;
    
    let html = '';
    transitions.forEach((transition, index) => {
        const date = transition.date || transition.timestamp || new Date().toLocaleString('ar-SA');
        const dateObj = new Date(date);
        const dateFormatted = dateObj.toLocaleString('ar-SA', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Extract transition metadata if available
        const metadata = transition.transition_metadata || {};
        const fromStage = metadata.from_stage || 'غير محدد';
        const toStage = metadata.to_stage || 'غير محدد';
        const transitionType = metadata.transition_type || 'auto_advance';
        
        // Determine transition display based on type
        let transitionDisplay = '';
        if (transitionType === 'completion') {
            transitionDisplay = `
                <div style="text-align: center; padding: 1rem; background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%); border-radius: 6px; margin-bottom: 0.75rem;">
                    <i class="fas fa-trophy" style="font-size: 2rem; color: #f57f17; margin-bottom: 0.5rem;"></i>
                    <div style="font-weight: 700; color: #f57f17; font-size: 1.1rem;">إكمال المشروع</div>
                    <div style="font-size: 0.85rem; color: #827717; margin-top: 0.25rem;">تم الانتهاء من جميع مراحل الموافقة</div>
                </div>
            `;
        } else {
            transitionDisplay = `
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 0.75rem; padding: 0.75rem; background: rgba(255, 255, 255, 0.6); border-radius: 6px;">
                    <div style="flex: 1; text-align: center; padding: 0.5rem; background: white; border-radius: 4px; border-right: 3px solid #ff9800;">
                        <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">من</div>
                        <div style="font-weight: 600; color: #e65100;">${fromStage}</div>
                    </div>
                    <i class="fas fa-arrow-left" style="font-size: 1.5rem; color: #4caf50;"></i>
                    <div style="flex: 1; text-align: center; padding: 0.5rem; background: white; border-radius: 4px; border-right: 3px solid #4caf50;">
                        <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">إلى</div>
                        <div style="font-weight: 600; color: #2e7d32;">${toStage}</div>
                    </div>
                </div>
            `;
        }

        html += `
            <div class="card" style="border: 2px solid #4caf50; background: linear-gradient(135deg, #ffffff 0%, #f1f8e9 100%); box-shadow: 0 4px 6px rgba(76, 175, 80, 0.15); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 12px rgba(76, 175, 80, 0.25)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(76, 175, 80, 0.15)';">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0" style="color: #2e7d32; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 30px; height: 30px; background: #4caf50; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-exchange-alt" style="font-size: 0.9rem; color: white;"></i>
                            </div>
                            انتقال تلقائي #${index + 1}
                        </h6>
                        <span class="badge" style="background: #4caf50; font-size: 0.8rem; padding: 0.35rem 0.7rem; border-radius: 12px;">✓ مكتمل</span>
                    </div>
                    
                    ${transitionDisplay}
                    
                    <p class="mb-2 small" style="color: #33691e; line-height: 1.6; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); border-radius: 4px;">
                        <i class="fas fa-comment-dots me-1"></i>
                        ${transition.notes || transition.stage || 'انتقال تلقائي بين المراحل'}
                    </p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-top: 0.75rem;">
                        <div style="padding: 0.5rem; background: white; border-radius: 4px; border-right: 2px solid #66bb6a;">
                            <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">
                                <i class="fas fa-calendar"></i> التاريخ
                            </div>
                            <div style="font-size: 0.85rem; color: #2e7d32; font-weight: 600;">${dateFormatted}</div>
                        </div>
                        <div style="padding: 0.5rem; background: white; border-radius: 4px; border-right: 2px solid #66bb6a;">
                            <div style="font-size: 0.75rem; color: #666; margin-bottom: 0.25rem;">
                                <i class="fas fa-user"></i> المستخدم
                            </div>
                            <div style="font-size: 0.85rem; color: #2e7d32; font-weight: 600;">${transition.reviewer || 'النظام'}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
    section.style.display = 'block';
}

/* ===========================================================
    استدعاء الـ API الموحد وسجل الحركة
=========================================================== */
async function loadActivityLog() {
    const projectId = {{ $project->id }};
    const container = document.getElementById('activityLogContainer');

    try {
        // عرض حالة التحميل
        container.innerHTML = `
            <div style="text-align: center; padding: 3rem 2rem;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <p style="color: #6c757d; margin-top: 1.5rem; font-weight: 500;">جاري تحميل سجل النشاط الموحد...</p>
            </div>
        `;

        // جلب البيانات من المصدر الموحد
        const response = await fetch(`/projects/${projectId}/approval-workflow/movement-log`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'فشل تحميل سجل الحركة');
        }

        let allActivities = (data.movement_log || []).map(item => ({
            activity_type: item.activity_type,
            stage: item.stage,
            status: item.status,
            reviewer: item.reviewer,
            notes: item.notes,
            attachment: item.attachment ? item.attachment.url : null,
            date: item.timestamp,
            is_phase_transition: item.is_phase_transition,
            transition_metadata: item.transition_metadata
        }));

        // عرض المراحل التي تحتاج إرجاع إذا وجدت
        if (data.stages_requiring_return) {
            showStagesRequiringReturn(data.stages_requiring_return);
        }

        // استخراج الانتقالات التلقائية بين المراحل
        const phaseTransitions = allActivities.filter(item => item.is_phase_transition);
        if (phaseTransitions.length > 0) {
            showPhaseTransitions(phaseTransitions);
        }

        // حفظ البيانات للمعالجة اللاحقة
        window.currentActivities = allActivities;

        // الحصول على إعدادات الفلترة الحالية
        const filterType = document.getElementById('activityFilter').value;
        const sortOrder = document.getElementById('activitySort').value;

        // عرض البيانات
        container.innerHTML = renderActivityLog(allActivities, filterType, sortOrder);

        // إضافة مستمعي الأحداث للملاحظات
        document.querySelectorAll('.btn-view-notes-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (typeof showNotesModal === 'function') {
                    showNotesModal(btn.dataset.notes);
                } else {
                    alert(btn.dataset.notes);
                }
            });
        });

    } catch (error) {
        console.error('Error loading activity log:', error);
        container.innerHTML = `
            <div style="padding: 3rem 2rem; text-align: center; background: #f8d7da; border-radius: 8px; border: 2px solid #f5c6cb;">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #721c24; margin-bottom: 1rem;"></i>
                <h5 style="color: #721c24; margin-bottom: 0.5rem;">حدث خطأ في تحميل السجل</h5>
                <p style="color: #721c24;">${error.message}</p>
                <button onclick="loadActivityLog()" style="padding: 0.5rem 1rem; background: #dc3545; color: white; border: none; border-radius: 4px; margin-top: 1rem; cursor: pointer;">
                    <i class="fas fa-redo me-1"></i> إعادة المحاولة
                </button>
            </div>
        `;
    }
}

/* ============================================================
    تصدير البيانات إلى CSV
============================================================ */
function exportToCSV(activities) {
    if (!activities || activities.length === 0) {
        alert('لا توجد بيانات للتصدير');
        return;
    }

    const headers = ['#', 'المرحلة', 'الحالة', 'المستخدم', 'الملاحظات', 'التاريخ', 'النوع'];
    const csvData = activities.map((log, index) => {
        const styles = getActivityTypeStyles(log.activity_type, log.status);
        return [
            index + 1,
            log.stage,
            styles.label,
            log.reviewer || '-',
            log.notes || '-',
            new Date(log.date).toLocaleString('ar-SA'),
            log.activity_type
        ];
    });

    const csvContent = [
        headers.join(','),
        ...csvData.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `سجل_النشاط_المشروع_{{ $project->id }}_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ============================================================
    طباعة السجل
============================================================ */
function printActivityLog() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>سجل نشاط المشروع</title>
                <style>
                    body { font-family: Arial, sans-serif; direction: rtl; padding: 20px; }
                    h1 { color: #007bff; text-align: center; margin-bottom: 30px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th { background: #007bff; color: white; padding: 10px; text-align: right; }
                    td { padding: 8px; border: 1px solid #ddd; }
                    .badge { padding: 3px 8px; border-radius: 4px; font-size: 12px; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <h1>سجل نشاط المشروع</h1>
                <div style="margin-bottom: 20px;">
                    <p><strong>تاريخ الطباعة:</strong> ${new Date().toLocaleString('ar-SA')}</p>
                </div>
                ${document.getElementById('activityLogContainer').innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

/* ============================================================
    تهيئة الأحداث
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    // تحميل البيانات أول مرة
    loadActivityLog();

    // حدث تحديث السجل
    document.getElementById('refreshActivityLog').addEventListener('click', function() {
        this.style.transform = 'rotate(360deg)';
        setTimeout(() => {
            this.style.transform = 'rotate(0deg)';
        }, 300);
        loadActivityLog();
    });

    // حدث تطبيق الفلترة
    document.getElementById('applyFilter').addEventListener('click', function() {
        if (window.currentActivities) {
            const filterType = document.getElementById('activityFilter').value;
            const sortOrder = document.getElementById('activitySort').value;
            document.getElementById('activityLogContainer').innerHTML = 
                renderActivityLog(window.currentActivities, filterType, sortOrder);
            
            // إعادة ربط أحداث عرض الملاحظات
            document.querySelectorAll('.btn-view-notes-modal').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showNotesModal(btn.dataset.notes);
                });
            });
        }
    });

    // حدث إعادة تعيين الفلترة
    document.getElementById('resetFilter').addEventListener('click', function() {
        document.getElementById('activityFilter').value = 'all';
        document.getElementById('activitySort').value = 'newest';
        if (window.currentActivities) {
            document.getElementById('activityLogContainer').innerHTML = 
                renderActivityLog(window.currentActivities, 'all', 'newest');
            
            // إعادة ربط أحداث عرض الملاحظات
            document.querySelectorAll('.btn-view-notes-modal').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showNotesModal(btn.dataset.notes);
                });
            });
        }
    });

    // تأثير hover على زر التحديث
    const refreshBtn = document.getElementById('refreshActivityLog');
    refreshBtn.addEventListener('mouseenter', function() {
        this.style.background = '#e9ecef';
        this.style.transform = 'rotate(180deg)';
    });
    refreshBtn.addEventListener('mouseleave', function() {
        this.style.background = 'none';
        this.style.transform = 'rotate(0deg)';
    });
});
</script>