@extends('layouts.app')

@section('content')
    <div class="dashboard-container">
        <!-- Subtle Elegant Background -->
        <div class="elegant-bg-pattern"></div>

        <!-- Page Header -->
        <div class="dashboard-header mb-5 fade-in-up" style="animation-delay: 0.1s;">
            <div class="header-gold-accent"></div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-4 position-relative z-1 header-flex-mobile">
                <div class="header-content">
                    <div class="welcome-badge mb-3">
                        <i class="fas fa-crown"></i>
                        <span>لوحة التحكم التنفيذية</span>
                    </div>
                    <h1 class="mb-2 fw-light display-6">
                        نظام إدارة المشاريع
                    </h1>
                    <p class="mb-0 text-gold-soft">
                        <i class="fas fa-user-tie me-2"></i>
                        مرحباً، <span class="fw-semibold text-white">{{ Auth::user()->name }}</span>
                    </p>
                </div>
                @can('projects.create')
                    <a href="{{ route('projects.create') }}" class="btn-premium auth-perm-projects-create">
                        <span class="btn-premium-icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="btn-premium-text">مشروع جديد</span>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="alerts-section mb-5 fade-in-up" style="animation-delay: 0.2s;">
            @if(Auth::user()->status !== 'Active')
                <div class="alert-elegant alert-danger-elegant">
                    <div class="alert-icon-elegant">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong class="d-block mb-1">تنبيه أمني</strong>
                        <span class="opacity-75">حسابك حالياً غير نشط. يرجى التواصل مع الإدارة لتفعيله.</span>
                    </div>
                </div>
            @endif

            @if(!Auth::user()->hasSignature())
                <div class="alert-elegant alert-gold-elegant">
                    <div class="alert-icon-elegant">
                        <i class="fas fa-signature"></i>
                    </div>
                    <div class="alert-content">
                        <strong class="d-block mb-1">إعداد مطلوب</strong>
                        <span class="opacity-75">لم تقم بإعداد التوقيع الإلكتروني. لن تتمكن من اعتماد المعاملات.</span>
                    </div>
                    <button class="btn-elegant-outline" onclick="window.openSignatureModal()">
                        إعداد الآن
                    </button>
                </div>
            @endif

            <div id="dashboardAlerts"></div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            @can('configuration.view')
                <div class="col-12 col-md-6 col-lg-4 auth-perm-configuration-view fade-in-up" style="animation-delay: 0.3s;">
                    <div class="stat-card-elegant stat-card-gold h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">إجمالي البرامج</div>
                                <div class="stat-value-elegant">{{ $programCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-arrow-trend-up"></i>
                                    <span>نشط ومباشر</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>
            @endcan

            @can('projects.view')
                <div class="col-12 col-md-6 col-lg-4 auth-perm-projects-view fade-in-up" style="animation-delay: 0.4s;">
                    <div class="stat-card-elegant stat-card-navy h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-diagram-project"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">إجمالي المشاريع</div>
                                <div class="stat-value-elegant">{{ $projectCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-check-circle"></i>
                                    <span>مكتمل بنجاح</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>
            @endcan

            @can('users.view')
                <div class="col-12 col-md-6 col-lg-4 auth-perm-users-view fade-in-up" style="animation-delay: 0.5s;">
                    <div class="stat-card-elegant stat-card-royal h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">إجمالي المستخدمين</div>
                                <div class="stat-value-elegant">{{ $userCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-user-plus"></i>
                                    <span>نشط في النظام</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>
            @endcan

            @can('projects.view')
                <!-- المشاريع المنجزة -->
                <div class="col-12 col-md-6 col-lg-4 auth-perm-projects-view fade-in-up" style="animation-delay: 0.6s;">
                    <div class="stat-card-elegant stat-card-emerald h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-circle-check"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">المشاريع المنجزة</div>
                                <div class="stat-value-elegant">{{ $completedProjectsCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-trophy"></i>
                                    <span>مكتملة بنجاح</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>

                <!-- المشاريع قيد المراجعة المالية والفنية -->
                <div class="col-12 col-md-6 col-lg-4 auth-perm-projects-view fade-in-up" style="animation-delay: 0.7s;">
                    <div class="stat-card-elegant stat-card-amber h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-clipboard-magnifying-glass"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">قيد المراجعة المالية والفنية</div>
                                <div class="stat-value-elegant">{{ $underReviewProjectsCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>قيد المعالجة</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>

                <!-- المشاريع قيد التنفيذ -->
                <div class="col-12 col-md-6 col-lg-4 auth-perm-projects-view fade-in-up" style="animation-delay: 0.8s;">
                    <div class="stat-card-elegant stat-card-blue h-100">
                        <div class="stat-card-inner">
                            <div class="stat-icon-elegant">
                                <i class="fas fa-gear"></i>
                            </div>
                            <div class="stat-info-elegant">
                                <div class="stat-label-elegant">المشاريع قيد التنفيذ</div>
                                <div class="stat-value-elegant">{{ $underExecutionProjectsCount ?? 0 }}</div>
                                <div class="stat-trend-elegant">
                                    <i class="fas fa-play"></i>
                                    <span>جاري العمل</span>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card-footer-elegant">
                            <span>استعراض التفاصيل</span>
                            <i class="fas fa-arrow-left-long"></i>
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        <!-- Main Content Grid -->
        <div class="row g-4 fade-in-up" style="animation-delay: 0.9s;">
            <!-- Left Column: Recent Projects -->
            <div class="col-lg-8">
                @include('home.recent_projects')
            </div>

            <!-- Right Column: Assignments & Suggestions -->
            <div class="col-lg-4">
                <!-- Assignments Card -->
                @include('home.assignments')

                <!-- Suggestions Card -->
                @include('home.suggestions')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var CAN_COMPLETE = {{ auth()->user()->can('suggestions.complete') ? 'true' : 'false' }};

            function renderItem(item) {
                const userName = item.user ? item.user.name : 'مستخدم';
                const entityName = item.entity ? item.entity.name : '-';
                const initial = userName.charAt(0).toUpperCase();
                const completed = !!item.is_completed;
                const iconClass = completed ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                const borderCls = completed ? 'border-gold-elegant' : 'border-navy-elegant';

                const actionBtn = CAN_COMPLETE
                    ? `<button class="btn-toggle-elegant" data-id="${item.id}" title="${completed ? 'مكتمل' : 'بانتظار المعالجة'}">
                           <i class="${iconClass}"></i>
                       </button>`
                    : `<i class="${iconClass}"></i>`;

                return `
                    <div class="suggestion-item-elegant mb-3 p-3 bg-white rounded-3 shadow-sm border-start border-3 ${borderCls}" data-suggestion-id="${item.id}">
                        <div class="d-flex align-items-start gap-3">
                            <div class="avatar-elegant">${initial}</div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 fw-semibold small">${userName}</h6>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted-elegant">
                                            <i class="far fa-calendar-alt me-1"></i>${item.gregorian_date}
                                        </small>
                                        ${actionBtn}
                                    </div>
                                </div>
                                <div class="text-muted-elegant small mb-2">
                                    <i class="fas fa-building me-1"></i>${entityName}
                                </div>
                                <div class="suggestion-content-elegant small">${item.content}</div>
                                <div class="mt-2 pt-2 border-top-elegant">
                                    <span class="badge-hijri-elegant">
                                        <i class="fas fa-moon me-1"></i>هجري: ${item.hijri_date}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }

            $(document).off('click', '.suggestion-toggle, .btn-toggle-elegant').on('click', '.suggestion-toggle, .btn-toggle-elegant', function () {
                const btn = $(this);
                const id = btn.data('id');
                const card = $(`[data-suggestion-id="${id}"]`);
                btn.prop('disabled', true);
                $.ajax({
                    url: `/api/suggestions/${id}/toggle-complete`,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (!res.success) return;
                        const completed = res.is_completed;
                        const iconClass = completed ? 'fas fa-check-circle text-success' : 'fas fa-times-circle text-danger';
                        btn.find('i').attr('class', iconClass);
                        btn.attr('title', completed ? 'مكتمل' : 'بانتظار المعالجة');
                        card.removeClass('border-navy-elegant border-gold-elegant').addClass(completed ? 'border-gold-elegant' : 'border-navy-elegant');
                    },
                    complete: function () { btn.prop('disabled', false); }
                });
            });

            function loadSuggestions() {
                const list = $('#suggestionsList');
                list.html('<div class="text-center py-4"><div class="spinner-elegant"><div class="spinner-item"></div><div class="spinner-item"></div><div class="spinner-item"></div></div></div>');
                $.ajax({
                    url: '{{ route("suggestions.index") }}', method: 'GET',
                    success: function (data) {
                        if (!data.length) {
                            list.html('<div class="empty-state-elegant py-4"><div class="empty-icon-elegant small"><i class="fas fa-comment-slash"></i></div><p class="text-muted-elegant mb-0 small">لا توجد مقترحات حالياً</p></div>');
                            return;
                        }
                        list.html(data.map(renderItem).join(''));
                    },
                    error: function () {
                        list.html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle fs-3 mb-2"></i><p class="mb-0 small">حدث خطأ أثناء التحميل</p></div>');
                    }
                });
            }

            var allAssignments = [];
            var currentFilter = 'all';

            function loadAssignments() {
                const container = $('#assignmentsList');
                container.html('<div class="text-center py-4"><div class="spinner-elegant"><div class="spinner-item"></div><div class="spinner-item"></div><div class="spinner-item"></div></div></div>');
                $.ajax({
                    url: '{{ route("assignments.my") }}', method: 'GET',
                    success: function (data) {
                        allAssignments = data;
                        updateAssignmentCounts();
                        renderFilteredAssignments();
                        renderDashboardAlerts();
                    },
                    error: function () {
                        container.html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle me-1"></i><small>فشل التحميل</small></div>');
                    }
                });
            }

            function updateAssignmentCounts() {
                const counts = { all: 0, overdue: 0, nearing: 0, current: 0, completed: 0 };
                allAssignments.forEach(item => {
                    counts.all++;
                    if (item.category && counts[item.category] !== undefined) counts[item.category]++;
                });
                $('#count-all').text(counts.all);
                $('#count-overdue').text(counts.overdue);
                $('#count-nearing').text(counts.nearing);
                $('#count-current').text(counts.current);
                $('#count-completed').text(counts.completed);
            }

            function renderFilteredAssignments() {
                const container = $('#assignmentsList');
                const filtered = allAssignments.filter(item => {
                    if (currentFilter === 'all') return true;
                    return item.category === currentFilter;
                });

                if (!filtered.length) {
                    let emptyMsg = 'لا توجد تكليفات أو مهام';
                    if (currentFilter === 'overdue') emptyMsg = 'لا توجد مهام متأخرة';
                    else if (currentFilter === 'nearing') emptyMsg = 'لا توجد مهام وشيكة';
                    else if (currentFilter === 'current') emptyMsg = 'لا توجد مهام جارية';
                    else if (currentFilter === 'completed') emptyMsg = 'لا توجد مهام مكتملة';

                    container.html(`<div class="empty-state-elegant py-4"><div class="empty-icon-elegant small"><i class="fas fa-clipboard-check"></i></div><p class="text-muted-elegant mb-0 small">${emptyMsg}</p></div>`);
                    return;
                }

                let html = '';
                filtered.forEach(function (item) {
                    let badgeCls = 'bg-navy-soft text-navy';
                    let borderCls = 'border-navy-elegant';
                    let timeText = 'بدون تاريخ استحقاق';
                    let iconCls = 'fa-calendar';

                    if (item.category === 'completed') {
                        badgeCls = 'bg-success-soft text-success';
                        borderCls = 'border-success';
                        iconCls = 'fa-check-circle';
                        timeText = 'مكتملة';
                    } else if (item.days_remaining !== null) {
                        if (item.days_remaining < 0) {
                            badgeCls = 'bg-danger-soft text-danger';
                            borderCls = 'border-danger';
                            iconCls = 'fa-exclamation-circle';
                            timeText = `متأخر ${Math.abs(item.days_remaining)} يوم`;
                        } else if (item.days_remaining === 0) {
                            badgeCls = 'bg-gold-soft text-gold-dark';
                            borderCls = 'border-gold-elegant';
                            iconCls = 'fa-clock';
                            timeText = 'يستحق اليوم';
                        } else if (item.days_remaining <= 3) {
                            badgeCls = 'bg-gold-soft text-gold-dark';
                            borderCls = 'border-gold-elegant';
                            iconCls = 'fa-clock';
                            timeText = `متبقي ${item.days_remaining} يوم`;
                        } else {
                            badgeCls = 'bg-primary-soft text-primary';
                            borderCls = 'border-primary';
                            iconCls = 'fa-hourglass-half';
                            timeText = `متبقي ${item.days_remaining} يوم`;
                        }
                    }

                    // Priority badge
                    let priorityBadge = '';
                    if (item.priority) {
                        const priorityLabels = {
                            'urgent': { label: 'عاجلة', cls: 'bg-danger text-white' },
                            'high': { label: 'عالية', cls: 'bg-warning text-dark' },
                            'medium': { label: 'متوسطة', cls: 'bg-info text-dark' },
                            'low': { label: 'منخفضة', cls: 'bg-secondary text-white' }
                        };
                        const pInfo = priorityLabels[item.priority] || { label: item.priority, cls: 'bg-light text-dark' };
                        priorityBadge = `<span class="badge ${pInfo.cls} me-1" style="font-size: 0.68rem;">${pInfo.label}</span>`;
                    }

                    const actionUrl = item.view_url || (item.is_standalone_task ? `/tasks/${item.task_id}` : `/projects/${item.project_id}/execution`);
                    const actionText = item.is_standalone_task ? 'عرض تفاصيل المهمة' : 'انتقال للتنفيذ';

                    html += `
                        <div class="assignment-item-elegant p-3 mb-3 bg-white rounded-3 border-start border-3 ${borderCls} shadow-sm">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge-custom-elegant ${badgeCls}">
                                    <i class="fas ${iconCls} me-1"></i>${timeText}
                                </span>
                                <div class="d-flex align-items-center gap-1">
                                    ${priorityBadge}
                                    <small class="text-muted-elegant fw-semibold">
                                        <i class="fas fa-tag me-1"></i>${item.type_label}
                                    </small>
                                </div>
                            </div>
                            <h6 class="fw-semibold mb-1 small text-dark">${item.task_name}</h6>
                            <p class="text-muted-elegant mb-2 small d-flex align-items-center gap-1">
                                <i class="fas fa-layer-group fa-xs"></i>
                                <span>${item.project_name}</span>
                            </p>
                            ${item.notes ? `<div class="note-box-elegant mb-2"><strong>ملاحظة:</strong> ${item.notes}</div>` : ''}
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                                <small class="text-muted-elegant">
                                    ${item.due_date ? `<i class="far fa-calendar-alt me-1"></i>${item.due_date}` : ''}
                                </small>
                                <a href="${actionUrl}" class="btn-link-elegant fw-semibold small">
                                    <i class="fas fa-arrow-left me-1"></i>${actionText}
                                </a>
                            </div>
                        </div>
                    `;
                });
                container.html(html);
            }

            function renderDashboardAlerts() {
                const alertsContainer = $('#dashboardAlerts');
                const overdueTasks = allAssignments.filter(item => item.category === 'overdue');
                const nearingTasks = allAssignments.filter(item => item.category === 'nearing');
                let alertsHtml = '';
                if (overdueTasks.length > 0) {
                    alertsHtml += `<div class="alert-elegant alert-danger-elegant"><div class="alert-icon-elegant"><i class="fas fa-exclamation-triangle"></i></div><div class="alert-content"><strong class="d-block mb-1">مهام متأخرة!</strong><span class="opacity-75 small">لديك <strong>${overdueTasks.length}</strong> مهمة تجاوزت موعد الاستحقاق</span></div><button class="btn-elegant-outline btn-sm" onclick="triggerFilter('overdue')">عرض</button></div>`;
                } else if (nearingTasks.length > 0) {
                    alertsHtml += `<div class="alert-elegant alert-gold-elegant"><div class="alert-icon-elegant"><i class="fas fa-bell"></i></div><div class="alert-content"><strong class="d-block mb-1">مهام وشيكة!</strong><span class="opacity-75 small">لديك <strong>${nearingTasks.length}</strong> مهمة مستحقة قريباً</span></div><button class="btn-elegant-outline btn-sm" onclick="triggerFilter('nearing')">عرض</button></div>`;
                }
                alertsContainer.html(alertsHtml);
            }

            function triggerFilter(filter) {
                currentFilter = filter;
                $('#assignmentFilters button').removeClass('active');
                $(`#assignmentFilters button[data-filter="${filter}"]`).addClass('active');
                renderFilteredAssignments();
            }

            $(document).off('click', '#assignmentFilters button').on('click', '#assignmentFilters button', function () {
                currentFilter = $(this).data('filter');
                $('#assignmentFilters button').removeClass('active');
                $(this).addClass('active');
                renderFilteredAssignments();
            });

            $(document).ready(function () {
                @if(Auth::check() && Auth::user()->must_change_password)
                    if (typeof bootstrap !== 'undefined') {
                        var forcePasswordModal = new bootstrap.Modal(document.getElementById('forcePasswordChangeModal'));
                        forcePasswordModal.show();
                    } else {
                        $('#forcePasswordChangeModal').modal({ backdrop: 'static', keyboard: false }).modal('show');
                    }
                @endif

                loadSuggestions();
                loadAssignments();

                const $form = $('#suggestionForm');
                const $textarea = $('#suggestionContent');
                const $submitBtn = $form.find('button[type="submit"]');

                $form.on('submit', function (e) {
                    e.preventDefault();
                    const content = $textarea.val().trim();
                    if (!content) return;

                    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                    $.ajax({
                        url: '{{ route("suggestions.store") }}', method: 'POST',
                        data: { content: content, _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            if (!res.success) return;
                            $textarea.val('').css('height', 'auto');
                            const list = $('#suggestionsList');
                            list.find('.empty-state-elegant').remove();
                            const $newItem = $(renderItem(res.suggestion)).hide();
                            list.prepend($newItem);
                            $newItem.slideDown(300);
                            $('#suggestionsContainer').animate({ scrollTop: 0 }, 300);
                            flasher.success('تم إرسال مقترحك بنجاح');
                        },
                        error: function () { flasher.error('عذراً، حدث خطأ أثناء الإرسال'); },
                        complete: function () { $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>'); }
                    });
                });

                $textarea.on('input', function () {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            });
        </script>
    @endpush

    <style>
        /* ═══════════════════════════════════════════════════════════════
               شريط التمرير (Scrollbar) العام الأنيق
               ═══════════════════════════════════════════════════════════════ */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-light);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(212, 175, 55, 0.6);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-gold);
        }

        /* ═══════════════════════════════════════════════════════════════
               التأثيرات الحركية (Animations)
               ═══════════════════════════════════════════════════════════════ */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes gentlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* ═══════════════════════════════════════════════════════════════
               توحيد الألوان مع صفحة تسجيل الدخول
               ═══════════════════════════════════════════════════════════════ */
        :root {
            /* الألوان الأساسية - موحدة مع صفحة الدخول */
            --primary-dark: #08214c;
            --primary-light: #1a4b8c;
            --accent-gold: #d4af37;
            --accent-gold-light: #f3d772;
            --accent-gold-dark: #b8941f;

            /* الألوان الداعمة */
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
            --bg-softer: #f1f5f9;

            /* التدرجات */
            --gradient-primary: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            --gradient-gold: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-gold-light) 100%);
            --gradient-header: linear-gradient(135deg, var(--primary-dark) 0%, #0f2d5e 50%, var(--primary-dark) 100%);

            /* الظلال */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-gold: 0 8px 24px rgba(212, 175, 55, 0.25);
            --shadow-primary: 0 8px 24px rgba(8, 33, 76, 0.2);

            /* الانتقالات */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .dashboard-container {
            padding: clamp(1rem, 3vw, 2rem);
            position: relative;
            font-family: 'Cairo', 'Tajawal', sans-serif;
            min-height: 100vh;
        }

        .elegant-bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
            background-color: var(--bg-light);
            background-image:
                radial-gradient(circle at 15% 15%, rgba(212, 175, 55, 0.04) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(8, 33, 76, 0.04) 0%, transparent 40%);
        }

        /* ═══════════════════════════════════════════════════════════════
               Premium Header
               ═══════════════════════════════════════════════════════════════ */
        .dashboard-header {
            background: var(--gradient-header);
            color: white;
            padding: clamp(1.5rem, 4vw, 2.5rem) clamp(1.5rem, 4vw, 3rem);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.6;
        }

        .header-gold-accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-gold);
            box-shadow: 0 2px 10px rgba(212, 175, 55, 0.5);
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(212, 175, 55, 0.15);
            backdrop-filter: blur(8px);
            padding: 0.5rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: var(--accent-gold-light);
            letter-spacing: 0.5px;
        }

        .welcome-badge i {
            color: var(--accent-gold);
            animation: sparkle 3s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.15);
            }
        }

        .dashboard-header h1 {
            font-weight: 300 !important;
            letter-spacing: -0.5px;
            color: var(--accent-gold-light) !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: clamp(1.5rem, 4vw, 2.5rem);
        }

        .text-gold-soft {
            color: rgba(255, 255, 255, 0.85);
            font-size: clamp(0.85rem, 1.5vw, 0.95rem);
        }

        .btn-premium {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            padding: clamp(0.7rem, 1.5vw, 0.9rem) clamp(1.2rem, 2.5vw, 2rem);
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: var(--shadow-gold);
            transition: var(--transition-smooth);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            font-size: clamp(0.85rem, 1.2vw, 1rem);
            white-space: nowrap;
        }

        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: right 0.6s ease;
        }

        .btn-premium:hover::before {
            right: 100%;
        }

        .btn-premium:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(212, 175, 55, 0.5);
            color: var(--primary-dark);
        }

        .btn-premium-icon {
            width: 28px;
            height: 28px;
            background: var(--primary-dark);
            color: var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* ═══════════════════════════════════════════════════════════════
               Elegant Alerts
               ═══════════════════════════════════════════════════════════════ */
        .alert-elegant {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-md);
            border: 1px solid transparent;
            transition: var(--transition);
            flex-wrap: wrap;
        }

        .alert-elegant:hover {
            transform: translateX(-4px);
        }

        .alert-danger-elegant {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert-gold-elegant {
            background: #fffbeb;
            border-color: rgba(212, 175, 55, 0.3);
            color: #78350f;
        }

        .alert-icon-elegant {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .alert-icon-elegant i {
            animation: gentlePulse 2.5s infinite;
        }

        .alert-danger-elegant .alert-icon-elegant {
            background: #fee2e2;
            color: #dc2626;
        }

        .alert-gold-elegant .alert-icon-elegant {
            background: rgba(212, 175, 55, 0.2);
            color: var(--accent-gold-dark);
        }

        .btn-elegant-outline {
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            border: 1px solid currentColor;
            background: transparent;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .alert-gold-elegant .btn-elegant-outline {
            color: #78350f;
            border-color: #78350f;
        }

        .alert-gold-elegant .btn-elegant-outline:hover {
            background: #78350f;
            color: #fffbeb;
        }

        /* ═══════════════════════════════════════════════════════════════
               Premium Stat Cards
               ═══════════════════════════════════════════════════════════════ */
        .stat-card-elegant {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
            border: 1px solid rgba(8, 33, 76, 0.06);
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .stat-card-elegant::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: var(--gradient-gold);
            opacity: 0;
            transition: var(--transition);
        }

        .stat-card-elegant:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .stat-card-elegant:hover::before {
            opacity: 1;
        }

        .stat-card-inner {
            padding: clamp(1.25rem, 2.5vw, 1.75rem);
            display: flex;
            align-items: center;
            gap: clamp(0.75rem, 1.5vw, 1.25rem);
        }

        .stat-icon-elegant {
            width: clamp(48px, 8vw, 64px);
            height: clamp(48px, 8vw, 64px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.2rem, 2vw, 1.5rem);
            flex-shrink: 0;
            transition: var(--transition);
            position: relative;
        }

        .stat-icon-elegant::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 16px;
            background: inherit;
            opacity: 0.2;
            transform: scale(1.2);
            z-index: -1;
            transition: var(--transition);
        }

        .stat-card-elegant:hover .stat-icon-elegant {
            transform: rotate(-8deg) scale(1.08);
        }

        .stat-card-elegant:hover .stat-icon-elegant::before {
            transform: scale(1.4);
            opacity: 0.15;
        }

        .stat-label-elegant {
            font-size: clamp(0.75rem, 1.1vw, 0.88rem);
            color: var(--text-light);
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.3px;
        }

        .stat-value-elegant {
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0.75rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-trend-elegant {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: clamp(0.65rem, 0.9vw, 0.78rem);
            font-weight: 600;
            padding: 0.3rem 0.7rem;
            border-radius: 20px;
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            flex-wrap: wrap;
        }

        .stat-card-footer-elegant {
            padding: clamp(0.75rem, 1.2vw, 1rem) clamp(1.25rem, 2vw, 1.75rem);
            background: var(--bg-softer);
            border-top: 1px solid rgba(8, 33, 76, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: clamp(0.75rem, 1vw, 0.85rem);
            color: var(--primary-light);
            font-weight: 600;
            transition: var(--transition);
        }

        .stat-card-footer-elegant i {
            transition: transform 0.3s ease;
        }

        .stat-card-elegant:hover .stat-card-footer-elegant {
            background: var(--primary-dark);
            color: var(--accent-gold);
        }

        .stat-card-elegant:hover .stat-card-footer-elegant i {
            transform: translateX(-6px);
        }

        /* 🔶 البطاقة الذهبية */
        .stat-card-gold {
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
        }

        .stat-card-gold .stat-icon-elegant {
            background: var(--gradient-gold);
            color: white;
            box-shadow: var(--shadow-gold);
        }

        .stat-card-gold .stat-value-elegant {
            background: linear-gradient(135deg, var(--accent-gold-dark) 0%, var(--accent-gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-gold:hover {
            box-shadow: 0 20px 50px rgba(212, 175, 55, 0.25);
        }

        /* 🔷 البطاقة النيلية */
        .stat-card-navy {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
        }

        .stat-card-navy .stat-icon-elegant {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-primary);
        }

        .stat-card-navy:hover {
            box-shadow: 0 20px 50px rgba(8, 33, 76, 0.25);
        }

        /* 💎 البطاقة الملكية */
        .stat-card-royal {
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
        }

        .stat-card-royal .stat-icon-elegant {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--accent-gold) 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(8, 33, 76, 0.3);
        }

        .stat-card-royal .stat-value-elegant {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-royal:hover {
            box-shadow: 0 20px 50px rgba(8, 33, 76, 0.2);
        }

        /* 🟩 البطاقة الزيتونية (المشاريع المنجزة) */
        .stat-card-emerald {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .stat-card-emerald .stat-icon-elegant {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .stat-card-emerald .stat-value-elegant {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-emerald:hover {
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.25);
        }

        /* 🟧 البطاقة العنبرية (قيد المراجعة) */
        .stat-card-amber {
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
        }

        .stat-card-amber .stat-icon-elegant {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
        }

        .stat-card-amber .stat-value-elegant {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-amber:hover {
            box-shadow: 0 20px 50px rgba(245, 158, 11, 0.25);
        }

        /* 🔷 البطاقة البحرية (قيد التنفيذ) */
        .stat-card-blue {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        }

        .stat-card-blue .stat-icon-elegant {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .stat-card-blue .stat-value-elegant {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card-blue:hover {
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.25);
        }

        /* ═══════════════════════════════════════════════════════════════
               Content Cards & Tables
               ═══════════════════════════════════════════════════════════════ */
        .content-card-elegant {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(8, 33, 76, 0.06);
            overflow: hidden;
            transition: var(--transition);
        }

        .content-card-elegant:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header-elegant {
            padding: clamp(1rem, 1.5vw, 1.25rem) clamp(1.25rem, 2vw, 1.75rem);
            background: white;
            border-bottom: 1px solid rgba(8, 33, 76, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .header-title-elegant {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .title-dot-gold,
        .title-dot-navy {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: relative;
        }

        .title-dot-gold {
            background: var(--accent-gold);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
        }

        .title-dot-navy {
            background: var(--primary-dark);
            box-shadow: 0 0 0 3px rgba(8, 33, 76, 0.2);
        }

        .card-header-elegant h5 {
            font-size: clamp(0.95rem, 1.2vw, 1.05rem);
            color: var(--primary-dark);
            font-weight: 700;
            margin: 0;
        }

        .btn-text-elegant {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-light);
            text-decoration: none;
            font-size: clamp(0.75rem, 0.9vw, 0.85rem);
            font-weight: 600;
            transition: var(--transition);
            padding: 0.4rem 0.6rem;
            border-radius: 8px;
        }

        .btn-text-elegant:hover {
            color: var(--accent-gold-dark);
            background: rgba(212, 175, 55, 0.08);
            gap: 0.7rem;
        }

        .table-responsive-elegant {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-elegant {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 480px;
        }

        .table-elegant thead th {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: clamp(0.7rem, 0.9vw, 0.8rem);
            padding: clamp(0.75rem, 1vw, 1rem) clamp(0.75rem, 1.2vw, 1.5rem);
            border-bottom: 2px solid rgba(212, 175, 55, 0.2);
            background: var(--bg-softer);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table-elegant tbody td {
            padding: clamp(0.75rem, 1vw, 1rem) clamp(0.75rem, 1.2vw, 1.5rem);
            vertical-align: middle;
            border-bottom: 1px solid rgba(8, 33, 76, 0.04);
            transition: var(--transition);
        }

        .table-elegant tbody tr {
            transition: var(--transition);
        }

        .table-elegant tbody tr:hover td {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.03) 0%, rgba(8, 33, 76, 0.03) 100%);
        }

        .project-info-elegant {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .project-avatar-elegant {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-gold);
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
            background: var(--gradient-primary);
            box-shadow: var(--shadow-sm);
        }

        .project-name-elegant {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.2rem;
            font-size: clamp(0.85rem, 1vw, 1rem);
        }

        .text-muted-elegant {
            color: var(--text-light) !important;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: clamp(0.65rem, 0.8vw, 0.75rem);
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.4;
            }
        }

        .status-gold {
            background: rgba(212, 175, 55, 0.12);
            color: var(--accent-gold-dark);
            border-color: rgba(212, 175, 55, 0.25);
        }

        .status-navy {
            background: rgba(8, 33, 76, 0.08);
            color: var(--primary-dark);
            border-color: rgba(8, 33, 76, 0.15);
        }

        .status-muted {
            background: var(--bg-softer);
            color: var(--text-light);
        }

        .date-elegant {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem;
            background: white;
            border-radius: 8px;
            font-size: clamp(0.7rem, 0.8vw, 0.8rem);
            color: var(--primary-light);
            border: 1px solid rgba(8, 33, 76, 0.1);
            font-weight: 500;
            white-space: nowrap;
        }

        .btn-action-elegant {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: white;
            color: var(--primary-light);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid rgba(8, 33, 76, 0.1);
        }

        .btn-action-elegant:hover {
            background: var(--gradient-primary);
            color: var(--accent-gold);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-primary);
        }

        /* ═══════════════════════════════════════════════════════════════
               Lists & Filters
               ═══════════════════════════════════════════════════════════════ */
        .filter-tabs-elegant {
            padding: clamp(0.75rem, 1vw, 1rem) clamp(1rem, 1.5vw, 1.5rem);
            background: var(--bg-softer);
            border-bottom: 1px solid rgba(8, 33, 76, 0.06);
        }

        .filter-tabs-elegant .nav-pills {
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .nav-link-elegant {
            padding: 0.4rem 0.8rem;
            font-size: clamp(0.7rem, 0.9vw, 0.8rem);
            font-weight: 600;
            color: var(--primary-light);
            background: white;
            border: 1px solid rgba(8, 33, 76, 0.1);
            border-radius: 20px;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-link-elegant:hover {
            border-color: var(--accent-gold);
            color: var(--accent-gold-dark);
        }

        .nav-link-elegant.active {
            background: var(--gradient-primary);
            color: var(--accent-gold);
            border-color: var(--primary-dark);
            box-shadow: var(--shadow-primary);
        }

        .badge-elegant {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-weight: 700;
        }

        .list-container-elegant {
            max-height: 380px;
            overflow-y: auto;
            padding: clamp(0.75rem, 1.5vw, 1.25rem);
            background: var(--bg-softer);
        }

        .assignment-item-elegant,
        .suggestion-item-elegant {
            transition: var(--transition);
            border-radius: 12px !important;
        }

        .assignment-item-elegant:hover,
        .suggestion-item-elegant:hover {
            transform: translateX(-4px);
            box-shadow: var(--shadow-lg) !important;
        }

        .border-navy-elegant {
            border-color: var(--primary-dark) !important;
        }

        .border-gold-elegant {
            border-color: var(--accent-gold) !important;
        }

        .badge-custom-elegant {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            font-size: clamp(0.6rem, 0.8vw, 0.7rem);
            font-weight: 700;
            white-space: nowrap;
        }

        .bg-navy-soft {
            background: rgba(8, 33, 76, 0.08);
            color: var(--primary-dark);
        }

        .bg-gold-soft {
            background: rgba(212, 175, 55, 0.12);
            color: var(--accent-gold-dark);
        }

        .text-gold-dark {
            color: var(--accent-gold-dark) !important;
        }

        .bg-danger-soft {
            background: rgba(220, 38, 38, 0.08);
            color: #dc2626;
        }

        .bg-success-soft {
            background: rgba(16, 185, 129, 0.08);
            color: #059669;
        }

        .note-box-elegant {
            background: #fffbeb;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: clamp(0.7rem, 0.8vw, 0.75rem);
            color: #78350f;
            margin-bottom: 0.75rem;
            border-right: 3px solid var(--accent-gold);
            line-height: 1.5;
            word-break: break-word;
        }

        .btn-link-elegant {
            display: inline-flex;
            align-items: center;
            color: var(--primary-light);
            text-decoration: none;
            font-size: clamp(0.7rem, 0.8vw, 0.8rem);
            font-weight: 700;
            transition: var(--transition);
            padding: 0.2rem 0.4rem;
            border-radius: 6px;
        }

        .btn-link-elegant:hover {
            color: var(--accent-gold-dark);
            background: rgba(212, 175, 55, 0.08);
            transform: translateX(-3px);
        }

        /* ═══════════════════════════════════════════════════════════════
               Suggestions & Inputs
               ═══════════════════════════════════════════════════════════════ */
        .suggestions-card-elegant {
            display: flex;
            flex-direction: column;
            height: clamp(400px, 70vh, 650px);
            min-height: 350px;
        }

        .suggestions-list-elegant {
            flex: 1;
            overflow-y: auto;
            background: var(--bg-softer);
        }

        .avatar-elegant {
            width: clamp(36px, 5vw, 42px);
            height: clamp(36px, 5vw, 42px);
            min-width: clamp(36px, 5vw, 42px);
            background: var(--gradient-primary);
            color: var(--accent-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: clamp(0.85rem, 1.2vw, 1rem);
            box-shadow: var(--shadow-sm);
        }

        .btn-toggle-elegant {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .btn-toggle-elegant:hover {
            transform: scale(1.2);
        }

        .border-top-elegant {
            border-color: rgba(8, 33, 76, 0.06) !important;
        }

        .badge-hijri-elegant {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            background: white;
            color: var(--primary-light);
            border-radius: 15px;
            font-size: clamp(0.6rem, 0.8vw, 0.7rem);
            font-weight: 600;
            border: 1px solid rgba(8, 33, 76, 0.08);
            white-space: nowrap;
        }

        .suggestion-input-elegant {
            padding: clamp(0.75rem, 1.2vw, 1.25rem) clamp(1rem, 1.5vw, 1.5rem);
            background: white;
            border-top: 1px solid rgba(8, 33, 76, 0.06);
        }

        .input-wrapper-elegant {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            border-radius: 12px;
            padding: 0.3rem 0.3rem 0.3rem 0.6rem;
            border: 2px solid rgba(8, 33, 76, 0.1);
            transition: var(--transition);
        }

        .input-wrapper-elegant:focus-within {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.12);
        }

        .form-control-elegant {
            border: none;
            background: transparent;
            padding: 0.5rem 0.5rem;
            font-size: clamp(0.85rem, 1vw, 0.9rem);
            flex: 1;
            color: var(--text-dark);
            font-family: inherit;
            min-width: 0;
        }

        .form-control-elegant:focus {
            box-shadow: none;
            outline: none;
        }

        .btn-send-elegant {
            width: clamp(38px, 5vw, 42px);
            height: clamp(38px, 5vw, 42px);
            border-radius: 10px;
            border: none;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            flex-shrink: 0;
            font-weight: 700;
            font-size: clamp(0.9rem, 1.2vw, 1.1rem);
        }

        .btn-send-elegant:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-gold);
        }

        .btn-icon-elegant {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .btn-gold-elegant {
            background: rgba(212, 175, 55, 0.1);
            color: var(--accent-gold-dark);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        .btn-gold-elegant:hover {
            background: var(--gradient-gold);
            color: var(--primary-dark);
            box-shadow: var(--shadow-gold);
        }

        .btn-navy-elegant {
            background: rgba(8, 33, 76, 0.08);
            color: var(--primary-dark);
        }

        .btn-navy-elegant:hover {
            background: var(--gradient-primary);
            color: var(--accent-gold);
            box-shadow: var(--shadow-primary);
        }

        /* ═══════════════════════════════════════════════════════════════
               Empty States & Spinners
               ═══════════════════════════════════════════════════════════════ */
        .empty-state-elegant {
            padding: 2rem 1rem;
            text-align: center;
        }

        .empty-icon-elegant {
            width: clamp(56px, 10vw, 72px);
            height: clamp(56px, 10vw, 72px);
            margin: 0 auto 1rem;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.5rem, 3vw, 1.75rem);
            color: var(--primary-light);
            border: 2px solid rgba(8, 33, 76, 0.08);
            box-shadow: var(--shadow-sm);
        }

        .empty-icon-elegant.small {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
        }

        .spinner-elegant {
            display: inline-flex;
            gap: 0.4rem;
        }

        .spinner-item {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent-gold);
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .spinner-item:nth-child(1) {
            animation-delay: -0.32s;
        }

        .spinner-item:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            }
            40% {
                transform: scale(1);
            }
        }

        /* ═══════════════════════════════════════════════════════════════
               تحسينات إضافية للشاشات الصغيرة جداً
               ═══════════════════════════════════════════════════════════════ */
        @media (max-width: 575.98px) {
            .dashboard-header {
                text-align: center;
                padding: 1.25rem 1rem;
            }

            .header-flex-mobile {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }

            .welcome-badge {
                font-size: 0.75rem;
                padding: 0.4rem 0.8rem;
                margin: 0 auto 0.5rem;
                justify-content: center;
            }

            .btn-premium {
                width: 100%;
                justify-content: center;
                padding: 0.75rem 1rem;
            }

            .stat-card-inner {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                padding: 1rem;
            }

            .stat-icon-elegant {
                margin: 0 auto;
                width: 52px;
                height: 52px;
                font-size: 1.2rem;
            }

            .stat-value-elegant {
                font-size: 1.6rem;
            }

            .stat-trend-elegant {
                justify-content: center;
                width: fit-content;
                margin: 0 auto;
                font-size: 0.7rem;
                padding: 0.2rem 0.6rem;
            }

            .stat-card-footer-elegant {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
            }

            .alert-elegant {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                gap: 0.5rem;
                padding: 1rem;
            }

            .alert-icon-elegant {
                margin: 0 auto;
                width: 40px;
                height: 40px;
            }

            .btn-elegant-outline {
                align-self: center;
                padding: 0.4rem 1rem;
                font-size: 0.8rem;
            }

            .card-header-elegant {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .header-title-elegant {
                width: 100%;
                justify-content: space-between;
            }

            .btn-text-elegant {
                align-self: flex-start;
            }

            .filter-tabs-elegant .nav-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 0.25rem;
                gap: 0.3rem;
            }

            .filter-tabs-elegant .nav-pills::-webkit-scrollbar {
                height: 2px;
            }

            .filter-tabs-elegant .nav-pills::-webkit-scrollbar-thumb {
                background: rgba(212, 175, 55, 0.4);
            }

            .nav-link-elegant {
                padding: 0.3rem 0.6rem;
                font-size: 0.65rem;
            }

            .list-container-elegant {
                padding: 0.75rem;
            }

            .assignment-item-elegant,
            .suggestion-item-elegant {
                padding: 0.75rem !important;
            }

            .suggestion-item-elegant .d-flex {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.5rem;
            }

            .avatar-elegant {
                align-self: center;
            }

            .suggestion-content-elegant {
                font-size: 0.85rem;
            }

            .input-wrapper-elegant {
                padding: 0.2rem;
            }

            .form-control-elegant {
                font-size: 0.85rem;
                padding: 0.4rem 0.4rem;
            }

            .btn-send-elegant {
                width: 36px;
                height: 36px;
                font-size: 0.9rem;
            }
        }

        /* تحسين للشاشات المتوسطة (تابلت) */
        @media (min-width: 576px) and (max-width: 991.98px) {
            .stat-card-inner {
                padding: 1.25rem;
                gap: 0.75rem;
            }

            .stat-icon-elegant {
                width: 56px;
                height: 56px;
                font-size: 1.3rem;
            }

            .stat-value-elegant {
                font-size: 2rem;
            }

            .dashboard-header {
                padding: 1.5rem 2rem;
            }

            .suggestions-card-elegant {
                height: 500px;
                min-height: 400px;
            }
        }

        /* تحسين للشاشات الكبيرة */
        @media (min-width: 1400px) {
            .dashboard-container {
                max-width: 1440px;
                margin: 0 auto;
            }

            .stat-card-elegant {
                transition-duration: 0.4s;
            }
        }

        /* تحسين لوضع RTL (العربية) */
        [dir="rtl"] .stat-card-elegant:hover .stat-card-footer-elegant i {
            transform: translateX(6px);
        }

        [dir="rtl"] .btn-premium::before {
            right: auto;
            left: -100%;
        }

        [dir="rtl"] .btn-premium:hover::before {
            left: 100%;
        }

        [dir="rtl"] .alert-elegant:hover {
            transform: translateX(4px);
        }

        [dir="rtl"] .assignment-item-elegant:hover,
        [dir="rtl"] .suggestion-item-elegant:hover {
            transform: translateX(4px);
        }

        [dir="rtl"] .btn-link-elegant:hover {
            transform: translateX(3px);
        }

        [dir="rtl"] .note-box-elegant {
            border-right: none;
            border-left: 3px solid var(--accent-gold);
        }
    </style>
@endsection