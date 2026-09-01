<div class="content-section" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border: 1px solid #e9ecef; border-radius: 10px; margin-bottom: 2rem;">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 2px solid #e9ecef; background: #fff; border-radius: 10px 10px 0 0;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-history" style="color: #007bff; font-size: 1.25rem;"></i>
            <h4 style="margin: 0; color: #343a40; font-weight: 600;">سجل نشاط الطلب الكامل</h4>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" id="requestRefreshActivityLog" style="background: none; border: none; color: #007bff; cursor: pointer; font-size: 1.1rem; padding: 0.5rem; border-radius: 50%; transition: all 0.3s ease;" title="تحديث السجل">
                <i class="fas fa-sync-alt"></i>
            </button>
            <span class="badge bg-info" id="requestTotalActivitiesCount" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">0</span>
        </div>
    </div>

    <!-- Filter -->
    <div style="padding: 1rem 1.5rem 0 1.5rem; background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.85rem; color: #6c757d; font-weight: 500;">فلترة حسب النوع:</label>
                <select id="requestActivityFilter" style="padding: 0.35rem 0.75rem; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.85rem;">
                    <option value="all">الكل</option>
                    <option value="created">إنشاء</option>
                    <option value="submitted">إرسال</option>
                    <option value="approvals">موافقات</option>
                    <option value="rejections">رفوضات</option>
                    <option value="need_action">بحاجة إجراء</option>
                    <option value="transferred">تحويل</option>
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label style="font-size: 0.85rem; color: #6c757d; font-weight: 500;">الترتيب:</label>
                <select id="requestActivitySort" style="padding: 0.35rem 0.75rem; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.85rem;">
                    <option value="newest">الأحدث أولاً</option>
                    <option value="oldest">الأقدم أولاً</option>
                </select>
            </div>
            <button id="requestApplyFilter" style="padding: 0.35rem 0.75rem; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                <i class="fas fa-filter me-1"></i> تطبيق
            </button>
            <button id="requestResetFilter" style="padding: 0.35rem 0.75rem; background: #6c757d; color: white; border: none; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">
                <i class="fas fa-redo me-1"></i> إعادة تعيين
            </button>
        </div>
    </div>

    <div id="requestActivityLogContainer" style="min-height: 300px; padding: 1.5rem;">
        <div style="text-align: center; padding: 3rem 2rem;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">جاري التحميل...</span>
            </div>
            <p style="color: #6c757d; margin-top: 1.5rem; font-weight: 500;">جاري تحميل سجل النشاط...</p>
        </div>
    </div>

    <div style="padding: 1rem 1.5rem; background: #f8f9fa; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 0.85rem; border-radius: 0 0 10px 10px;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-info-circle" style="color: #0dcaf0;"></i>
            <span>السجل الشامل لجميع العمليات والمراحل على طلب المشروع مرتبة حسب وقت الإجراء</span>
        </div>
        <div id="requestActivityStats" style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;"></div>
    </div>
</div>

<script>
/* ============================================================
    أيقونات وألوان الحالات – طلب المشروع
============================================================ */
function getRequestStatusCategory(status) {
    const map = {
        'created':        'created',
        'submitted':      'submitted',
        'approved':       'approvals',
        'approval':       'approvals',
        'accepted':       'approvals',
        'rejected':       'rejections',
        'rejection':      'rejections',
        'transferred':    'transferred',
        'need_action':    'need_action',
        'requires_action':'need_action',
        'resubmitted':    'resubmitted',
        'pending':        'pending',
    };
    return map[status] || 'pending';
}

function getRequestActivityStyles(status) {
    const cat = getRequestStatusCategory(status);
    const styles = {
        'created':     { icon: 'fa-plus-circle',      color: '#20c997', badge: 'إنشاء',        bg: '#d1f0e4', border: '#a8e6d2' },
        'submitted':   { icon: 'fa-paper-plane',      color: '#007bff', badge: 'إرسال للموافقة', bg: '#cce5ff', border: '#b8daff' },
        'approvals':   { icon: 'fa-check-circle',     color: '#28a745', badge: 'موافقة',       bg: '#d4edda', border: '#c3e6cb' },
        'rejections':  { icon: 'fa-times-circle',     color: '#dc3545', badge: 'رفض',          bg: '#f8d7da', border: '#f5c6cb' },
        'transferred': { icon: 'fa-exchange-alt',     color: '#6f42c1', badge: 'تحويل',        bg: '#e2d9f3', border: '#d1c4e9' },
        'need_action': { icon: 'fa-exclamation-circle',color: '#fd7e14', badge: 'بحاجة إجراء', bg: '#fff3cd', border: '#ffeaa7' },
        'resubmitted': { icon: 'fa-redo',             color: '#17a2b8', badge: 'إعادة تقديم',  bg: '#d1ecf1', border: '#bee5eb' },
        'pending':     { icon: 'fa-hourglass-half',   color: '#6c757d', badge: 'قيد الانتظار', bg: '#e2e3e5', border: '#d3d4d5' },
    };
    return styles[cat] || styles['pending'];
}

/* ============================================================
    رسم الجدول
============================================================ */
function renderRequestActivityLog(activities, filterType = 'all', sortOrder = 'newest') {
    if (!activities || activities.length === 0) {
        return `<div style="padding: 3rem 2rem; text-align: center; background: #f8f9fa; border-radius: 8px; border: 2px dashed #dee2e6;">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #adb5bd; margin-bottom: 1rem; opacity: 0.5;"></i>
            <h5 style="color: #6c757d; margin-bottom: 0.5rem;">لا توجد سجلات نشاط</h5>
            <p style="color: #adb5bd; font-size: 0.9rem;">لم يتم تسجيل أي نشاط على هذا الطلب بعد</p>
        </div>`;
    }

    let filtered = [...activities];

    if (filterType !== 'all') {
        filtered = filtered.filter(log => {
            const cat = getRequestStatusCategory(log.status || log.activity_type);
            if (filterType === 'created')    return cat === 'created';
            if (filterType === 'submitted')  return cat === 'submitted';
            if (filterType === 'transferred')return cat === 'transferred';
            return cat === filterType;
        });
    }

    filtered.sort((a, b) => {
        const da = new Date(a.timestamp || a.date), db = new Date(b.timestamp || b.date);
        return sortOrder === 'newest' ? db - da : da - db;
    });

    document.getElementById('requestTotalActivitiesCount').textContent = filtered.length;

    // Stats
    const counts = { approvals:0, rejections:0, need_action:0, created:0, submitted:0, transferred:0 };
    filtered.forEach(log => {
        const cat = getRequestStatusCategory(log.status || log.activity_type);
        if (counts.hasOwnProperty(cat)) counts[cat]++;
    });

    const statsEl = document.getElementById('requestActivityStats');
    if (statsEl) {
        statsEl.innerHTML = `
            <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#e9ecef;border-radius:4px;font-size:0.8rem;"><i class="fas fa-list-alt"></i> إجمالي: ${filtered.length}</span>
            ${counts.created > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#d1f0e4;color:#0a5740;border-radius:4px;font-size:0.8rem;"><i class="fas fa-plus-circle"></i> إنشاء: ${counts.created}</span>` : ''}
            ${counts.submitted > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#cce5ff;color:#004085;border-radius:4px;font-size:0.8rem;"><i class="fas fa-paper-plane"></i> إرسال: ${counts.submitted}</span>` : ''}
            ${counts.approvals > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#d4edda;color:#155724;border-radius:4px;font-size:0.8rem;"><i class="fas fa-check-circle"></i> موافقات: ${counts.approvals}</span>` : ''}
            ${counts.rejections > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#f8d7da;color:#721c24;border-radius:4px;font-size:0.8rem;"><i class="fas fa-times-circle"></i> رفوضات: ${counts.rejections}</span>` : ''}
            ${counts.need_action > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#fff3cd;color:#856404;border-radius:4px;font-size:0.8rem;"><i class="fas fa-exclamation-circle"></i> إجراء: ${counts.need_action}</span>` : ''}
            ${counts.transferred > 0 ? `<span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.25rem 0.5rem;background:#e2d9f3;color:#382e4d;border-radius:4px;font-size:0.8rem;"><i class="fas fa-exchange-alt"></i> تحويل: ${counts.transferred}</span>` : ''}
        `;
    }

    let html = `
        <div style="overflow-x: auto; border-radius: 8px; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <table style="width: 100%; border-collapse: collapse; background: white; min-width: 900px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
                    <th style="padding: 1rem; text-align: center; width: 50px; font-weight: 600;">#</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600;">نوع الإجراء / المرحلة</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600;">الحالة</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600;">المستخدم</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600;">الملاحظات</th>
                    <th style="padding: 1rem; text-align: right; font-weight: 600;">التاريخ والوقت</th>
                    <th style="padding: 1rem; text-align: center; font-weight: 600;">الوقت المنقضي</th>
                </tr>
            </thead>
            <tbody>
    `;

    filtered.forEach((log, index) => {
        const status = log.status || log.activity_type;
        const styles = getRequestActivityStyles(status);
        const bg = index % 2 === 0 ? '#f8f9fa' : 'white';

        const notes = log.notes
            ? `<button class="req-view-notes btn btn-link p-0 text-decoration-none" data-notes="${(log.notes+'').replace(/"/g, '&quot;')}" style="color:#fd7e14;font-size:0.85rem;"><i class="fas fa-sticky-note me-1"></i>عرض</button>`
            : '<span class="text-muted" style="font-size:0.85rem;">-</span>';

        const dateStr = new Date(log.timestamp || log.date).toLocaleString('ar-SA', {
            year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
        });

        const elapsed = log.elapsed_string
            ? `<span class="badge rounded-pill bg-light text-dark border" style="font-size:0.75rem;"><i class="fas fa-hourglass-half me-1 text-primary"></i>${log.elapsed_string}</span>`
            : '<span class="text-muted small">-</span>';

        const stageLine = log.from_stage && log.to_stage
            ? `<div style="font-size:0.78rem;color:#6c757d;margin-top:0.25rem;"><i class="fas fa-arrow-left me-1"></i>${log.from_stage} → ${log.to_stage}</div>`
            : '';

        html += `
            <tr style="background:${bg}; border-bottom: 1px solid #e9ecef;">
                <td style="padding:0.75rem; text-align:center; font-weight:600; color:#495057;">${index + 1}</td>
                <td style="padding:0.75rem;">
                    <div style="font-weight:600;color:#343a40;">${log.stage || log.activity_type}</div>
                    ${stageLine}
                </td>
                <td style="padding:0.75rem; text-align:center;">
                    <span style="padding:0.35rem 0.75rem;border-radius:20px;color:${styles.color};background:${styles.bg};border:1px solid ${styles.border};font-weight:600;font-size:0.85rem;display:inline-flex;align-items:center;gap:0.3rem;">
                        <i class="fas ${styles.icon}"></i> ${styles.badge}
                    </span>
                </td>
                <td style="padding:0.75rem;color:#495057;">${log.reviewer || log.authority || '<span class="text-muted">-</span>'}</td>
                <td style="padding:0.75rem;">${notes}</td>
                <td style="padding:0.75rem;color:#6c757d;font-size:0.85rem;white-space:nowrap;">
                    <i class="fas fa-clock me-1" style="color:#6c757d;"></i>${dateStr}
                </td>
                <td style="padding:0.75rem;text-align:center;">${elapsed}</td>
            </tr>
        `;
    });

    html += '</tbody></table></div>';
    return html;
}

/* ============================================================
    تحميل البيانات من API
============================================================ */
async function loadRequestActivityLog() {
    const requestId = {{ $request->id }};
    const container = document.getElementById('requestActivityLogContainer');

    try {
        container.innerHTML = `<div style="text-align: center; padding: 3rem 2rem;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
            <p style="color: #6c757d; margin-top: 1.5rem; font-weight: 500;">جاري تحميل سجل النشاط...</p>
        </div>`;

        const response = await fetch(`/project-requests/${requestId}/movement-log`);
        const data = await response.json();

        if (!data.success) throw new Error(data.message || 'فشل تحميل السجل');

        const activities = data.movement_log || data.log || [];
        window.requestCurrentActivities = activities;

        const filterType = document.getElementById('requestActivityFilter').value;
        const sortOrder  = document.getElementById('requestActivitySort').value;
        container.innerHTML = renderRequestActivityLog(activities, filterType, sortOrder);

        // Notes modal
        container.querySelectorAll('.req-view-notes').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                if (typeof showNotesModal === 'function') {
                    showNotesModal(btn.dataset.notes);
                } else {
                    alert(btn.dataset.notes);
                }
            });
        });

    } catch (error) {
        console.error('Error loading request activity log:', error);
        container.innerHTML = `<div style="padding: 3rem 2rem; text-align: center; background: #f8d7da; border-radius: 8px; border: 2px solid #f5c6cb;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #721c24; margin-bottom: 1rem;"></i>
            <h5 style="color: #721c24; margin-bottom: 0.5rem;">حدث خطأ في تحميل السجل</h5>
            <p style="color: #721c24;">${error.message}</p>
            <button onclick="loadRequestActivityLog()" style="padding: 0.5rem 1rem; background: #dc3545; color: white; border: none; border-radius: 4px; margin-top: 1rem; cursor: pointer;">
                <i class="fas fa-redo me-1"></i> إعادة المحاولة
            </button>
        </div>`;
    }
}

/* ============================================================
    تهيئة الأحداث
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    loadRequestActivityLog();

    document.getElementById('requestRefreshActivityLog').addEventListener('click', loadRequestActivityLog);

    document.getElementById('requestApplyFilter').addEventListener('click', function() {
        const filterType = document.getElementById('requestActivityFilter').value;
        const sortOrder  = document.getElementById('requestActivitySort').value;
        if (window.requestCurrentActivities) {
            const container = document.getElementById('requestActivityLogContainer');
            container.innerHTML = renderRequestActivityLog(window.requestCurrentActivities, filterType, sortOrder);
            container.querySelectorAll('.req-view-notes').forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    if (typeof showNotesModal === 'function') { showNotesModal(btn.dataset.notes); } else { alert(btn.dataset.notes); }
                });
            });
        }
    });

    document.getElementById('requestResetFilter').addEventListener('click', function() {
        document.getElementById('requestActivityFilter').value = 'all';
        document.getElementById('requestActivitySort').value  = 'newest';
        if (window.requestCurrentActivities) {
            const container = document.getElementById('requestActivityLogContainer');
            container.innerHTML = renderRequestActivityLog(window.requestCurrentActivities, 'all', 'newest');
        }
    });
});
</script>
