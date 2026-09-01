<style>
    /* ========================================================================= */
    /* 1. التنسيقات العامة للشريط الجانبي (Offcanvas)                           */
    /* ========================================================================= */
    .activities-offcanvas {
        width: 650px !important;
        border-left: 6px solid #f97316; /* برتقالي أكثر حيوية */
        box-shadow: -20px 0 40px rgba(0, 0, 0, 0.15);
        background-color: #f5f7fb; /* خلفية ناعمة مائلة للرمادي */
    }

    /* رأس القائمة الجانبية */
    .activities-offcanvas .offcanvas-header {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        padding: 1.75rem 1.5rem;
        color: white;
        border-bottom: 4px solid #f97316;
    }

    /* ========================================================================= */
    /* 2. تنسيق الجدول الرئيسي (الأنشطة) - بألوان مميزة                         */
    /* ========================================================================= */
    .activities-table-clean {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px; /* Increased margin between rows */
        font-family: 'Inter', sans-serif;
    }

    /* عناوين الأعمدة */
    .activities-table-clean thead th {
        color: #475569;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 0 15px 12px 15px;
        background: transparent;
        border: none;
    }

    /* صفوف الأنشطة - تدرج لوني أنيق */
    .activities-table-clean tbody tr {
        background: linear-gradient(145deg, #ffffff, #f9f9ff);
        transition: all 0.25s ease;
        box-shadow: 0 6px 14px rgba(0, 20, 40, 0.06);
        border-radius: 16px;
        border: 1px solid #e9eef5;
    }

    /* تناوب الألوان بين الصفوف */
    .activities-table-clean tbody tr:nth-child(even) {
        background: linear-gradient(145deg, #f8faff, #f0f4fe);
        border-color: #dbe7f5;
    }

    /* تأثير hover */
    .activities-table-clean tbody tr:hover {
        transform: translateY(-4px) scale(1.002);
        box-shadow: 0 20px 30px -12px rgba(249, 115, 22, 0.25);
        border-color: #fed7aa;
        background: #ffffff;
    }

    /* الخلايا */
    .activities-table-clean td {
        padding: 16px 15px;
        vertical-align: middle;
        border: none;
        background: inherit;
    }

    /* تدوير الحواف */
    .activities-table-clean td:first-child {
        border-radius: 16px 0 0 16px;
    }
    .activities-table-clean td:last-child {
        border-radius: 0 16px 16px 0;
    }

    /* ========================================================================= */
    /* 3. تنسيقات حقول الإدخال والأزرار - متناسقة مع الألوان الجديدة            */
    /* ========================================================================= */
    .activity-input-clean {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        font-weight: 500;
        color: #1e293b;
        padding: 10px 14px;
        border-radius: 14px;
        width: 100%;
        transition: all 0.2s;
        font-size: 0.9rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);
    }

    .activity-input-clean:hover {
        border-color: #f97316;
        background: #fffaf5;
    }

    .activity-input-clean:focus {
        border-color: #f97316;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15);
        outline: none;
        background: #ffffff;
    }

    /* حقل الوزن */
    .activity-weight-input {
        text-align: center;
        font-weight: 600;
        color: #1e293b;
        width: 95px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        padding: 8px 4px;
        font-size: 0.9rem;
    }

    .activity-weight-input:focus {
        border-color: #f97316;
        background: #ffffff;
    }

    /* حاوية الوزن الإجمالي */
    .weight-badge-container {
        background: #ffffff;
        border: 2px solid #e9edf2;
        border-radius: 40px;
        padding: 6px 20px;
        display: inline-flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .weight-badge-container.is-valid {
        border-color: #22c55e;
        background: #f0fdf4;
    }

    /* أزرار الإجراءات */
    .btn-manage-actions {
        background: linear-gradient(145deg, #fff4e5, #ffe8d4);
        border: 1px solid #ffd7ae;
        color: #b45309;
        border-radius: 30px;
        padding: 8px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 2px 6px rgba(249, 115, 22, 0.1);
    }

    .btn-manage-actions:hover {
        background: #f97316;
        border-color: #f97316;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -8px #f97316;
    }

    /* زر الحذف */
    .btn-remove-activity {
        color: #94a3b8;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
        background: transparent;
        border: none;
    }

    .btn-remove-activity:hover {
        background: #fee2e2;
        color: #dc2626;
        transform: scale(1.1);
    }

    /* شارة عدد الإجراءات */
    .position-absolute.badge {
        background: #f97316 !important;
        color: white;
        font-size: 0.6rem;
        padding: 0.2rem 0.45rem;
        border-radius: 30px;
        border: 2px solid white;
        top: -6px !important;
        right: -6px !important;
    }

    /* ========================================================================= */
    /* 4. تنسيقات مودال الإجراءات (إذا كان موجوداً) - بألوان مميزة              */
    /* ========================================================================= */
    .actions-modal .modal-content {
        border: none;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(0,0,0,0.3);
    }

    .actions-modal .modal-header {
        background: linear-gradient(135deg, #2d3b5a, #1e2b42);
        color: white;
        border-bottom: 4px solid #f97316;
        padding: 1.5rem;
    }

    .actions-modal .modal-title {
        font-size: 1.2rem;
        font-weight: 700;
    }

    /* جدول الإجراءات داخل المودال */
    .actions-table {
        border-collapse: separate;
        border-spacing: 0 12px; /* Increased margin between rows */
        width: 100%;
    }

    /* صفوف الإجراءات - بخلفية خضراء/نعناعية فاتحة */
    .actions-table tbody tr {
        background: linear-gradient(145deg, #f0fdf4, #e6f7ec);
        border-radius: 18px;
        border: 1px solid #bbf7d0;
        box-shadow: 0 4px 10px rgba(34, 197, 94, 0.08);
    }

    /* تناوب الألوان لصفوف الإجراءات */
    .actions-table tbody tr:nth-child(even) {
        background: linear-gradient(145deg, #e6fffa, #daf2ef);
        border-color: #99f6e4;
    }

    .actions-table tbody tr:hover {
        transform: translateY(-3px);
        border-color: #4ade80;
        box-shadow: 0 12px 24px -12px #22c55e;
    }

    .actions-table td {
        padding: 14px 12px;
        border: none;
        background: inherit;
    }

    .actions-table td:first-child {
        border-radius: 18px 0 0 18px;
    }
    .actions-table td:last-child {
        border-radius: 0 18px 18px 0;
    }

    /* حقول الإدخال داخل المودال */
    .action-input {
        border: 1px solid #cbd5e1;
        background: white;
        border-radius: 30px;
        padding: 8px 16px;
        width: 100%;
        transition: all 0.2s;
    }

    .action-input:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.15);
        outline: none;
    }

    /* أزرار الإجراءات داخل المودال */
    .btn-action-manage {
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 30px;
        padding: 5px 12px;
        font-size: 0.8rem;
        color: #334155;
    }

    .btn-action-manage:hover {
        background: #22c55e;
        border-color: #22c55e;
        color: white;
    }

    /* ========================================================================= */
    /* 5. تنسيقات إضافية */
    /* ========================================================================= */
    #emptyStateMsg {
        background: #ffffffdd;
        backdrop-filter: blur(4px);
        border-radius: 40px;
        padding: 3rem 2rem !important;
        border: 1px dashed #cbd5e1;
        box-shadow: 0 10px 25px rgba(0,0,0,0.02);
    }

    .sticky-top {
        background: rgba(255,255,255,0.8) !important;
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #eef2f6;
    }

    #addActivityBtn {
        background: linear-gradient(145deg, #1e293b, #0f172a);
        border: none;
        border-radius: 40px;
        padding: 0.6rem 1.8rem;
        font-weight: 600;
        box-shadow: 0 6px 14px rgba(0,0,0,0.1);
    }

    #addActivityBtn:hover {
        background: linear-gradient(145deg, #2d3b55, #1a2639);
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -10px #0f172a;
    }

    #saveActivitiesBtn {
        background: #f97316;
        border: none;
        border-radius: 40px;
        padding: 0.6rem 2.2rem;
        font-weight: 700;
        box-shadow: 0 8px 18px #f97316;
        transition: all 0.2s;
    }

    #saveActivitiesBtn:hover {
        background: #ea580c;
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -8px #f97316;
    }

    #totalWeightDisplay {
        font-size: 1.2rem;
        font-weight: 800;
        background: #f1f5f9;
        padding: 0.3rem 1rem;
        border-radius: 40px;
        color: #0f172a;
    }
</style>

<!-- بداية الشريط الجانبي (offcanvas) -->
<div class="offcanvas offcanvas-end activities-offcanvas shadow-lg" tabindex="-1" id="activitiesOffcanvas" aria-labelledby="activitiesOffcanvasLabel">

    <div class="offcanvas-header py-3">
        <div>
            <h5 class="offcanvas-title fs-6 fw-bold text-warning" id="activitiesOffcanvasLabel">
                <i class="fas fa-tasks me-2"></i> إدارة الأنشطة والمهام
            </h5>
            <div class="small text-white-50 mt-1" style="font-size: 0.8rem;">
                المشروع: <span id="displayProjectName" class="text-white fw-bold ms-1">---</span>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body bg-light p-0">

        <!-- شريط الأدوات العلوي -->
        <div class="sticky-top bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm" style="z-index: 10;">
            <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold" id="addActivityBtn">
                <i class="fas fa-plus me-1"></i> إضافة نشاط جديد
            </button>

            <div class="weight-badge-container d-flex align-items-center gap-2">
                <span class="small text-muted fw-bold">إجمالي الأوزان:</span>
                <span id="totalWeightDisplay" class="fw-bold fs-6">0%</span>
                <i id="weightStatusIcon" class="fas fa-circle-check text-success d-none"></i>
            </div>
        </div>

        <!-- محتوى الجدول -->
        <div class="p-3">
            <div class="table-responsive">
                <table class="activities-table-clean">
                    <thead>
                        <tr>
                            <th class="ps-3">اسم النشاط</th>
                            <th class="text-center" style="width: 100px;">الوزن %</th>
                            <th class="text-center" style="width: 60px;">إجراء</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="activitiesTableBody">
                        <!-- يتم تعبئتها بواسطة JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- رسالة الحالة الفارغة -->
            <div id="emptyStateMsg" class="text-center py-5 text-muted d-none">
                <div class="mb-3">
                    <span class="fa-stack fa-2x opacity-25">
                        <i class="fas fa-circle fa-stack-2x text-secondary"></i>
                        <i class="fas fa-list-check fa-stack-1x fa-inverse"></i>
                    </span>
                </div>
                <h6 class="fw-bold text-secondary">لا توجد أنشطة مضافة</h6>
                <p class="small mb-0">ابدأ بإضافة أنشطة المشروع وتوزيع الأوزان.</p>
            </div>
        </div>

        <!-- تضمين مودال الإجراءات (تأكد من وجود هذا الملف) -->
        @include('planning.plans.partials._activity_actions_modal')
    </div>

    <!-- فوتر الشريط الجانبي -->
    <div class="offcanvas-footer p-3 bg-white border-top d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light btn-sm px-4 border" data-bs-dismiss="offcanvas">إلغاء</button>
        <button type="button" class="btn btn-success btn-sm px-4 fw-bold shadow-sm" id="saveActivitiesBtn">
            <i class="fas fa-save me-1"></i> حفظ التغييرات
        </button>
    </div>
</div>

@push('scripts')
<script>
    // =========================================================================
    // مدير الأنشطة (Activities Manager) - محدث مع دعم الألوان والتكامل
    // =========================================================================
    window.ActivitiesManager = {
        currentProjectRow: null,
        activities: [],
        bsOffcanvas: null,

        init: function() {
            const el = document.getElementById('activitiesOffcanvas');
            if (el) {
                this.bsOffcanvas = new bootstrap.Offcanvas(el);
            }
            document.getElementById('addActivityBtn')?.addEventListener('click', () => this.addActivity());
            document.getElementById('saveActivitiesBtn')?.addEventListener('click', () => this.saveActivities());
        },

        open: function(projectRow) {
            this.currentProjectRow = projectRow;
            const nameInput = projectRow.querySelector('input[name*="[name]"]');
            const jsonInput = projectRow.querySelector('.project-activities-input');
            const projectName = nameInput ? nameInput.value : 'مشروع جديد';
            document.getElementById('displayProjectName').textContent = projectName;

            try {
                this.activities = jsonInput ? JSON.parse(jsonInput.value) : [];
            } catch (e) {
                this.activities = [];
            }

            this.render();
            if (this.bsOffcanvas) this.bsOffcanvas.show();
        },

        addActivity: function() {
            this.activities.push({ name: '', weight: 0, actions: [] });
            this.render();
            setTimeout(() => {
                const inputs = document.querySelectorAll('.activity-name-input');
                if (inputs.length) inputs[inputs.length - 1].focus();
            }, 100);
        },

        render: function() {
            const container = document.getElementById('activitiesTableBody');
            const emptyState = document.getElementById('emptyStateMsg');
            if (!container) return;
            container.innerHTML = '';

            if (this.activities.length === 0) {
                emptyState?.classList.remove('d-none');
            } else {
                emptyState?.classList.add('d-none');
                this.activities.forEach((activity, index) => {
                    const tr = document.createElement('tr');
                    const actionsCount = activity.actions?.length || 0;
                    const badge = actionsCount > 0
                        ? `<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size:0.6rem">${actionsCount}</span>`
                        : '';

                    tr.innerHTML = `
                        <td class="ps-3">
                            <input type="text" class="activity-input-clean activity-name-input"
                                value="${this.escapeHtml(activity.name || '')}" placeholder="أدخل اسم النشاط..." required>
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control text-center fw-bold activity-weight-input"
                                    value="${activity.weight || ''}" placeholder="0" min="0" max="100" step="0.01">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-manage-actions position-relative"
                                data-index="${index}" title="الإجراءات الفرعية">
                                <i class="fas fa-bolt me-1"></i> إجراءات
                                ${badge}
                            </button>
                        </td>
                        <td class="text-center pe-2">
                            <button type="button" class="btn-remove-activity" data-index="${index}" title="حذف">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    container.appendChild(tr);
                });
            }
            this.calculateTotal();
            this.bindEvents(container);
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        bindEvents: function(container) {
            container.querySelectorAll('.btn-remove-activity').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = e.currentTarget.getAttribute('data-index');
                    Swal.fire({
                        title: 'تأكيد العملية',
                        text: 'هل أنت متأكد من حذف هذا النشاط؟',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'نعم',
                        cancelButtonText: 'لا',
                        reverseButtons: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.activities.splice(idx, 1);
                            this.render();
                        }
                    });
                });
            });

            container.querySelectorAll('.btn-manage-actions').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = e.currentTarget.getAttribute('data-index');
                    if (window.ActivityActionsManager) {
                        window.ActivityActionsManager.open(idx, this.activities[idx].name, this.activities[idx].actions || []);
                    } else {
                        alert('نظام إدارة الإجراءات غير مضمن!');
                    }
                });
            });

            container.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', (e) => {
                    const tr = input.closest('tr');
                    const idx = Array.from(container.children).indexOf(tr);
                    if (input.classList.contains('activity-name-input')) {
                        this.activities[idx].name = input.value;
                    } else if (input.classList.contains('activity-weight-input')) {
                        this.activities[idx].weight = parseFloat(input.value) || 0;
                        this.calculateTotal();
                    }
                });
            });
        },

        calculateTotal: function() {
            const total = this.activities.reduce((sum, act) => sum + (parseFloat(act.weight) || 0), 0);
            const display = document.getElementById('totalWeightDisplay');
            const icon = document.getElementById('weightStatusIcon');
            if (!display) return;
            display.textContent = total.toFixed(1) + '%';

            const isComplete = Math.abs(total - 100) < 0.1;
            const isOver = total > 100.1;

            display.className = 'fw-bold fs-6';
            icon?.classList.add('d-none');

            if (isComplete) {
                display.classList.add('text-success');
                icon?.classList.remove('d-none');
            } else if (isOver) {
                display.classList.add('text-danger');
            } else {
                display.classList.add('text-warning');
            }
        },

        saveActivities: function() {
            if (!this.currentProjectRow) return;

            const total = this.activities.reduce((sum, act) => sum + (parseFloat(act.weight) || 0), 0);
            
            // Check for 100% weight if there are activities
            if (this.activities.length > 0 && Math.abs(total - 100) > 0.1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'يجب أن يكون مجموع أوزان الأنشطة 100% (المجموع الحالي: ' + total.toFixed(1) + '%)',
                    confirmButtonText: 'حسناً'
                });
                return;
            }

            const emptyNames = this.activities.some(a => !a.name?.trim());
            if (emptyNames) {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'يرجى التأكد من كتابة أسماء لجميع الأنشطة.', confirmButtonText: 'حسناً' });
                return;
            }

            // Check if all activities with actions have theirs at 100%
            // (Note: ActivityActionsManager already validates on its save, 
            // but this is a safety check if we want it)

            const hiddenInput = this.currentProjectRow.querySelector('.project-activities-input');
            if (hiddenInput) {
                hiddenInput.value = JSON.stringify(this.activities);
                hiddenInput.dispatchEvent(new Event('change'));
            }

            const countBadge = this.currentProjectRow.querySelector('.activity-count-badge');
            if (countBadge) {
                countBadge.textContent = this.activities.length;
                countBadge.classList.toggle('bg-secondary', this.activities.length === 0);
                countBadge.classList.toggle('bg-primary', this.activities.length > 0);
            }

            // وميض تأكيد
            this.currentProjectRow.style.transition = 'background-color 0.5s';
            this.currentProjectRow.style.backgroundColor = '#dcfce7';
            setTimeout(() => {
                this.currentProjectRow.style.backgroundColor = '';
            }, 1000);

            this.bsOffcanvas?.hide();
        },

        updateActivityActions: function(index, actions) {
            if (this.activities[index]) {
                this.activities[index].actions = actions;
                this.render();
            }
        }
    };

    // تشغيل المدير بعد تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        ActivitiesManager.init();
    });
</script>
@endpush