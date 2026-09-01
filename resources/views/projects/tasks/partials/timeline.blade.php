<div>
    {{-- ===== رأس القسم ===== --}}
    <div class="timeline-section-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="section-icon-box amber">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1" style="font-size: 1.05rem;">سجل عمليات النشاط</h5>
                    <p class="text-muted mb-0 small">تتبع جميع العمليات والتغييرات التي تمت على هذه المهمة</p>
                </div>
            </div>
            <button class="btn-refresh-timeline" onclick="loadTaskTimeline()" title="تحديث السجل">
                <i class="fas fa-sync-alt"></i>
                <span>تحديث</span>
            </button>
        </div>
    </div>

    {{-- ===== Spinner التحميل ===== --}}
    <div id="timeline-spinner" class="timeline-loader">
        <div class="loader-animation">
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
            <div class="loader-circle"></div>
        </div>
        <p class="loader-text">جاري تحميل سجل النشاطات...</p>
    </div>

    {{-- ===== محتوى الـ Timeline ===== --}}
    <div id="timeline-content" class="d-none">
        <div class="timeline-wrapper">
            <ul class="timeline-list" id="timeline-list">
                <!-- Will be dynamically filled by JS -->
            </ul>
        </div>
    </div>

    {{-- ===== حالة عدم وجود سجلات ===== --}}
    <div id="timeline-empty" class="d-none">
        <div class="timeline-empty-state">
            <div class="empty-icon-wrapper">
                <i class="fas fa-clock-rotate-left"></i>
            </div>
            <h6>لا توجد سجلات نشاط</h6>
            <p>لم يتم تسجيل أي عمليات على هذه المهمة حتى الآن.</p>
            <div class="empty-hint">
                <i class="fas fa-lightbulb"></i>
                <span>ستظهر هنا جميع التغييرات والعمليات التي تتم على المهمة</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== الأنماط ===== --}}
<style>
    /* ===============================
       رأس القسم
       =============================== */
    .timeline-section-header {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .section-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .section-icon-box.amber {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        color: #d97706;
    }

    .btn-refresh-timeline {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 1rem;
        border-radius: 9px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .btn-refresh-timeline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #3b82f6;
    }

    .btn-refresh-timeline:hover i {
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* ===============================
       Loader التحميل
       =============================== */
    .timeline-loader {
        text-align: center;
        padding: 3.5rem 2rem;
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }

    .loader-animation {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }

    .loader-circle {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        animation: bounce 1.4s ease-in-out infinite;
    }

    .loader-circle:nth-child(1) {
        animation-delay: 0s;
    }

    .loader-circle:nth-child(2) {
        animation-delay: 0.2s;
    }

    .loader-circle:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes bounce {

        0%,
        80%,
        100% {
            transform: scale(0.6);
            opacity: 0.4;
        }

        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .loader-text {
        font-size: 0.88rem;
        color: #64748b;
        font-weight: 500;
        margin: 0;
    }

    /* ===============================
       الـ Timeline
       =============================== */
    .timeline-wrapper {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem 1.5rem 1.5rem 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    }

    .timeline-list {
        list-style: none;
        padding: 0;
        margin: 0;
        position: relative;
    }

    /* الخط العمودي */
    .timeline-list::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        right: 15px;
        width: 2px;
        background: linear-gradient(180deg, #3b82f6 0%, #e2e8f0 20%, #e2e8f0 80%, transparent 100%);
        border-radius: 2px;
    }

    /* عنصر الـ Timeline */
    .timeline-item {
        position: relative;
        padding-right: 50px;
        padding-bottom: 1.75rem;
        animation: fadeInRight 0.4s ease backwards;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item:last-child::after {
        content: '';
        position: absolute;
        top: 32px;
        bottom: 0;
        right: 14px;
        width: 4px;
        background: transparent;
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(15px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* أيقونة الـ Timeline */
    .timeline-icon {
        position: absolute;
        right: 0;
        top: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.78rem;
        color: #fff;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        z-index: 2;
        transition: transform 0.25s ease;
    }

    .timeline-item:hover .timeline-icon {
        transform: scale(1.15);
    }

    /* ألوان الأيقونات */
    .timeline-icon.created {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .timeline-icon.updated {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .timeline-icon.status {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .timeline-icon.memo-add {
        background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
    }

    .timeline-icon.memo-sign {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .timeline-icon.deleted {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .timeline-icon.attachment {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    }

    .timeline-icon.note {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .timeline-icon.discussion {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .timeline-icon.default {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
    }

    /* بطاقة الحدث */
    .timeline-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem;
        transition: all 0.25s ease;
        position: relative;
    }

    .timeline-card::before {
        content: '';
        position: absolute;
        top: 12px;
        right: -8px;
        width: 14px;
        height: 14px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        border-top: 1px solid #e2e8f0;
        transform: rotate(45deg);
    }

    .timeline-item:hover .timeline-card {
        background: #fff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .timeline-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.4rem;
        flex-wrap: wrap;
    }

    .timeline-card-title {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex: 1;
        min-width: 0;
    }

    .timeline-card-title .status-change-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.5rem;
        border-radius: 5px;
        font-size: 0.72rem;
        font-weight: 600;
        background: #dbeafe;
        color: #1e40af;
    }

    .timeline-card-date {
        font-size: 0.72rem;
        color: #94a3b8;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .timeline-card-date i {
        font-size: 0.65rem;
    }

    .timeline-card-user {
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .timeline-card-user i {
        font-size: 0.7rem;
        color: #94a3b8;
    }

    .timeline-card-user strong {
        color: #334155;
        font-weight: 600;
    }

    .timeline-card-details {
        margin-top: 0.6rem;
        padding: 0.6rem 0.85rem;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .timeline-card-details i {
        color: #3b82f6;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .timeline-card-details.file-detail i {
        color: #14b8a6;
    }

    .timeline-card-details.memo-detail i {
        color: #a855f7;
    }

    /* ===============================
       حالة عدم وجود سجلات
       =============================== */
    .timeline-empty-state {
        text-align: center;
        padding: 3.5rem 2rem;
        background: #fff;
        border-radius: 16px;
        border: 2px dashed #e2e8f0;
    }

    .empty-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 1.25rem;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #d97706;
    }

    .timeline-empty-state h6 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.4rem;
    }

    .timeline-empty-state p {
        font-size: 0.88rem;
        color: #94a3b8;
        margin-bottom: 1.25rem;
    }

    .empty-hint {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1rem;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        font-size: 0.82rem;
        color: #92400e;
    }

    .empty-hint i {
        color: #f59e0b;
    }
</style>

<script>
    function loadTaskTimeline() {
        const spinner = document.getElementById('timeline-spinner');
        const content = document.getElementById('timeline-content');
        const empty = document.getElementById('timeline-empty');
        const list = document.getElementById('timeline-list');

        spinner.classList.remove('d-none');
        content.classList.add('d-none');
        empty.classList.add('d-none');
        list.innerHTML = '';

        fetch('{{ route("projects.tasks.activities.index", [$project->id, $task->id]) }}')
            .then(response => response.json())
            .then(data => {
                spinner.classList.add('d-none');

                if (data.length === 0) {
                    empty.classList.remove('d-none');
                    return;
                }

                data.forEach((activity, index) => {
                    const li = document.createElement('li');
                    li.className = 'timeline-item';
                    li.style.animationDelay = `${index * 0.08}s`;

                    let icon = 'fa-info-circle';
                    let iconClass = 'default';
                    let title = '';
                    let details = '';
                    let detailClass = '';

                    switch (activity.event_type) {
                        case 'task_created':
                            icon = 'fa-plus';
                            iconClass = 'created';
                            title = 'تم إنشاء المهمة';
                            break;
                        case 'task_updated':
                            icon = 'fa-pen';
                            iconClass = 'updated';
                            title = 'تم تحديث تفاصيل المهمة';
                            break;
                        case 'status_changed':
                            icon = 'fa-circle-dot';
                            iconClass = 'status';
                            title = 'تغيير حالة المهمة';
                            if (activity.properties && activity.properties.new) {
                                let statusName = activity.properties.new.status;
                                const statusLabels = {
                                    'todo': '⏳ قيد الانتظار',
                                    'in_progress': '🔄 قيد التنفيذ',
                                    'completed': '✅ مكتملة',
                                    'cancelled': '❌ ملغاة'
                                };
                                statusName = statusLabels[statusName] || statusName;
                                title += ` <span class="status-change-badge">${statusName}</span>`;
                            }
                            break;
                        case 'memo_added':
                            icon = 'fa-file-circle-plus';
                            iconClass = 'memo-add';
                            title = 'إضافة مذكرة / قرار';
                            if (activity.properties && activity.properties.subject) {
                                details = activity.properties.subject;
                                detailClass = 'memo-detail';
                            }
                            break;
                        case 'memo_deleted':
                            icon = 'fa-file-circle-xmark';
                            iconClass = 'deleted';
                            title = 'حذف مذكرة / قرار';
                            break;
                        case 'memo_signed':
                            icon = 'fa-signature';
                            iconClass = 'memo-sign';
                            title = 'توقيع واعتماد مذكرة';
                            break;
                        case 'attachment_uploaded':
                            icon = 'fa-paperclip';
                            iconClass = 'attachment';
                            title = 'رفع مرفق جديد';
                            if (activity.properties && activity.properties.file_name) {
                                details = activity.properties.file_name;
                                detailClass = 'file-detail';
                            }
                            break;
                        case 'attachment_deleted':
                            icon = 'fa-trash-alt';
                            iconClass = 'deleted';
                            title = 'حذف مرفق';
                            if (activity.properties && activity.properties.file_name) {
                                details = activity.properties.file_name;
                                detailClass = 'file-detail';
                            }
                            break;
                        case 'execution_note_added':
                            icon = 'fa-tasks';
                            iconClass = 'note';
                            title = 'إضافة ملاحظة تنفيذ';
                            if (activity.properties && activity.properties.progress_percentage !== undefined) {
                                title += ` <span class="status-change-badge">${activity.properties.progress_percentage}%</span>`;
                            }
                            break;
                        case 'document_note_added':
                            icon = 'fa-note-sticky';
                            iconClass = 'note';
                            title = 'إضافة ملاحظة مستند';
                            break;
                        case 'discussion_message_added':
                            icon = 'fa-comments';
                            iconClass = 'discussion';
                            title = 'رسالة نقاش جديدة';
                            break;
                        default:
                            title = activity.event_type;
                            break;
                    }

                    // Format date
                    const date = new Date(activity.created_at);
                    const formattedDate = date.toLocaleDateString('ar-YE', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const causer = activity.causer ? activity.causer.name : 'النظام';

                    li.innerHTML = `
                        <div class="timeline-icon ${iconClass}">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="timeline-card">
                            <div class="timeline-card-header">
                                <h6 class="timeline-card-title">${title}</h6>
                                <span class="timeline-card-date">
                                    <i class="far fa-clock"></i>
                                    ${formattedDate}
                                </span>
                            </div>
                            <p class="timeline-card-user">
                                <i class="fas fa-user"></i>
                                بواسطة: <strong>${causer}</strong>
                            </p>
                            ${details ? `
                                <div class="timeline-card-details ${detailClass}">
                                    <i class="fas ${detailClass === 'file-detail' ? 'fa-file' : (detailClass === 'memo-detail' ? 'fa-file-lines' : 'fa-info-circle')}"></i>
                                    <span>${details}</span>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    list.appendChild(li);
                });

                content.classList.remove('d-none');
            })
            .catch(error => {
                console.error('Error loading task activities:', error);
                spinner.classList.add('d-none');
                empty.classList.remove('d-none');
                empty.querySelector('p').innerText = 'حدث خطأ أثناء تحميل سجل النشاطات.';
            });
    }

    // Load timeline dynamically if the trigger button is clicked
    document.addEventListener('DOMContentLoaded', () => {
        const trigger = document.getElementById('tab-timeline-trigger');
        if (trigger) {
            trigger.addEventListener('click', loadTaskTimeline);
        }
    });
</script>