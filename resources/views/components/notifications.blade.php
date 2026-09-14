@php
    // We only need the unread count initially to show the badge.
    // Offloaded to AJAX for maximum performance.
@endphp

<div class="notification-wrapper">
    <button class="notification-trigger" id="notificationToggle" aria-label="الإشعارات" title="الإشعارات والتنبيهات">
        <div class="bell-container" style="pointer-events: none;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="16" height="16" fill="currentColor" style="pointer-events: none;">
                <path d="M224 0c-17.7 0-32 14.3-32 32V51.2C119 66 64 130.6 64 208v118.8c0 47-17.3 92.4-48.5 127.6l-7.4 8.3c-8.4 9.4-10.4 22.9-5.3 34.4S19.4 512 32 512H416c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-7.4-8.3C401.3 419.2 384 373.8 384 326.8V208c0-77.4-55-142-128-156.8V32c0-17.7-14.3-32-32-32zm45.3 493.3c12-12 18.7-28.3 18.7-45.3H160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7z" />
            </svg>
            <span class="notification-counter" id="notificationCount" style="display: none; pointer-events: none;"></span>
        </div>
    </button>

    <div class="notification-dropdown" id="notificationDropdown">
        <div class="panel-header">
            <div class="header-content">
                <span class="header-title">مركز الإشعارات والتنبيهات</span>
                <span class="unread-count-label" id="unreadCountText">جاري التحميل...</span>
            </div>
            <button class="mark-read-all" id="markAllReadBtn" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="13" height="13" fill="currentColor">
                    <path d="M342.6 86.6c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0L74.3 183.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L205.3 224l137.3-137.4zM438.6 278.6c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0l-160-160c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L278.6 438.6l160-160zM122.3 294.3c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z" />
                </svg>
                تحديد الكل كمقروء
            </button>
        </div>

        <!-- Filter Chips -->
        <div class="notification-filters" id="notifFilterChips">
            <button class="filter-chip active" data-filter="all">الكل</button>
            <button class="filter-chip" data-filter="unread">غير المقروءة</button>
            <button class="filter-chip" data-filter="projects">المشاريع والاعتمادات</button>
            <button class="filter-chip" data-filter="correspondence">المراسلات</button>
            <button class="filter-chip" data-filter="tasks">المهام</button>
            <button class="filter-chip" data-filter="other">عمليات إدارية</button>
        </div>

        <div class="notification-scroll-area" id="notificationList">
            <div class="notification-loading-state">
                <div class="shimmer-item"></div>
                <div class="shimmer-item"></div>
                <div class="shimmer-item"></div>
            </div>
        </div>

        <div class="panel-footer">
            <a href="{{ route('projects.index') }}" class="view-all-link">
                الانتقال للرئيسية ولوحة المشاريع
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="12" height="12" fill="currentColor">
                    <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z" />
                </svg>
            </a>
        </div>
    </div>
</div>

<style>
    :root {
        --notif-primary: #1e3c72;
        --notif-primary-hover: #2a5298;
        --notif-bg: #ffffff;
        --notif-unread-bg: #f3f7fe;
        --notif-hover: #f1f5fa;
        --notif-border: #e9ecef;
        --notif-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .notification-wrapper {
        position: relative;
        display: inline-block;
    }

    .notification-trigger {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 8px 10px;
        cursor: pointer;
        transition: all 0.25s ease;
        border-radius: 10px;
        color: inherit;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-trigger:hover {
        background: rgba(255, 255, 255, 0.22);
        transform: translateY(-1px);
    }

    .bell-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-counter {
        position: absolute;
        top: -8px;
        right: -10px;
        background: #e74c3c;
        color: #ffffff;
        font-size: 0.65rem;
        padding: 2px 5px;
        border-radius: 10px;
        font-weight: 700;
        border: 2px solid #fff;
        min-width: 18px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(231, 76, 60, 0.4);
        animation: pulseBadge 2s infinite;
    }

    @keyframes pulseBadge {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .notification-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        left: 0;
        width: 410px;
        max-width: 92vw;
        background: var(--notif-bg);
        border-radius: 16px;
        box-shadow: var(--notif-shadow);
        display: none;
        z-index: 1050;
        overflow: hidden;
        animation: slideDownFade 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        transform-origin: top left;
        border: 1px solid rgba(0, 0, 0, 0.08);
        direction: rtl;
        text-align: right;
    }

    @keyframes slideDownFade {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.97);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .notification-dropdown.show {
        display: block;
    }

    .panel-header {
        padding: 16px 20px;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-size: 1rem;
        font-weight: 700;
        display: block;
    }

    .unread-count-label {
        display: block;
        font-size: 0.75rem;
        opacity: 0.85;
        margin-top: 2px;
    }

    .mark-read-all {
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        font-size: 0.72rem;
        padding: 5px 11px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
    }

    .mark-read-all:hover {
        background: rgba(255, 255, 255, 0.32);
    }

    .notification-filters {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 10px 15px;
        background: #f8fafc;
        border-bottom: 1px solid var(--notif-border);
        overflow-x: auto;
        scrollbar-width: none;
    }

    .notification-filters::-webkit-scrollbar {
        display: none;
    }

    .filter-chip {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 12px;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .filter-chip:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .filter-chip.active {
        background: #1e3c72;
        color: #ffffff;
        border-color: #1e3c72;
        font-weight: 600;
    }

    .notification-scroll-area {
        max-height: 440px;
        overflow-y: auto;
    }

    .modern-notification-item {
        display: flex;
        padding: 14px 18px;
        text-decoration: none !important;
        border-bottom: 1px solid var(--notif-border);
        transition: all 0.2s ease;
        position: relative;
        color: inherit;
    }

    .modern-notification-item:hover {
        background: var(--notif-hover);
        transform: translateX(-3px);
    }

    .modern-notification-item.unread {
        background: var(--notif-unread-bg);
    }

    .notification-icon-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        margin-left: 14px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .notification-details {
        flex-grow: 1;
        overflow: hidden;
    }

    .user-action {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        margin-bottom: 3px;
    }

    .username {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.85rem;
    }

    .action-desc {
        color: #475569;
        font-size: 0.82rem;
    }

    .project-title {
        color: #2563eb;
        font-weight: 600;
        font-size: 0.83rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .notification-page-name {
        font-size: 0.7rem;
        color: #475569;
        background: #e2e8f0;
        border: 1px solid #cbd5e1;
        padding: 2px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 6px;
        font-weight: 600;
        width: fit-content;
    }

    .meta-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2px;
    }

    .time-ago {
        font-size: 0.68rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        background: #2563eb;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .empty-notifications {
        padding: 50px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .empty-icon {
        margin-bottom: 12px;
        opacity: 0.4;
    }

    .panel-footer {
        padding: 12px;
        text-align: center;
        background: #f8fafc;
        border-top: 1px solid var(--notif-border);
    }

    .view-all-link {
        color: #1e3c72;
        font-weight: 600;
        font-size: 0.82rem;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .view-all-link:hover {
        color: #2563eb;
    }

    .notification-loading-state {
        padding: 20px;
    }

    .shimmer-item {
        height: 65px;
        background: #f1f5f9;
        background-image: linear-gradient(to right, #f1f5f9 0%, #e2e8f0 20%, #f1f5f9 40%, #f1f5f9 100%);
        background-repeat: no-repeat;
        background-size: 800px 104px;
        display: block;
        animation: shimmerAnim 1.5s infinite linear;
        margin-bottom: 10px;
        border-radius: 10px;
    }

    @keyframes shimmerAnim {
        0% { background-position: -468px 0; }
        100% { background-position: 468px 0; }
    }
</style>

<script>
    (function () {
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationCount = document.getElementById('notificationCount');
        const notificationList = document.getElementById('notificationList');
        const unreadCountText = document.getElementById('unreadCountText');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const filterChips = document.querySelectorAll('#notifFilterChips .filter-chip');

        let rawNotifications = [];
        let activeFilter = 'all';

        // Sound player
        function playNotificationSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(440, audioCtx.currentTime + 0.1);
                
                gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.1, audioCtx.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                
                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.3);
            } catch (e) {
                console.error('Audio playback failed', e);
            }
        }

        // Fetch unread count
        function fetchUnreadCount() {
            fetch('{{ route('notifications.unread-count') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const lastUnreadCount = parseInt(localStorage.getItem('notification_unread_count') || '0');
                        
                        if (data.unread_count > 0) {
                            notificationCount.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            notificationCount.style.display = 'block';
                            unreadCountText.textContent = data.unread_count + ' غير مقروءة';
                            markAllReadBtn.style.display = 'inline-flex';
                            
                            if (data.unread_count > lastUnreadCount) {
                                playNotificationSound();
                            }
                        } else {
                            notificationCount.style.display = 'none';
                            unreadCountText.textContent = 'لا توجد إشعارات غير مقروءة';
                            markAllReadBtn.style.display = 'none';
                        }
                        
                        localStorage.setItem('notification_unread_count', data.unread_count);
                    }
                })
                .catch(err => console.error('Error fetching unread count:', err));
        }

        fetchUnreadCount();
        setInterval(fetchUnreadCount, 30000); // Check every 30 seconds

        // Toggle dropdown
        notificationToggle?.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');

            if (notificationDropdown.classList.contains('show')) {
                loadNotifications();
            }
        });

        // Load notifications
        function loadNotifications() {
            fetch('{{ route('notifications.latest') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        rawNotifications = data.notifications || [];
                        renderFilteredNotifications();

                        if (data.unread_count > 0) {
                            notificationCount.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                            notificationCount.style.display = 'block';
                            unreadCountText.textContent = data.unread_count + ' غير مقروءة';
                            markAllReadBtn.style.display = 'inline-flex';
                        } else {
                            notificationCount.style.display = 'none';
                            unreadCountText.textContent = 'لا توجد إشعارات غير مقروءة';
                            markAllReadBtn.style.display = 'none';
                        }
                        
                        localStorage.setItem('notification_unread_count', data.unread_count);
                    }
                })
                .catch(err => {
                    notificationList.innerHTML = '<div class="empty-notifications"><p>فشل تحميل الإشعارات</p></div>';
                });
        }

        // Filter chips logic
        filterChips.forEach(chip => {
            chip.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                filterChips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.getAttribute('data-filter');
                renderFilteredNotifications();
            });
        });

        function renderFilteredNotifications() {
            let filtered = rawNotifications;
            if (activeFilter === 'unread') {
                filtered = rawNotifications.filter(n => n.is_unread);
            } else if (activeFilter === 'projects') {
                filtered = rawNotifications.filter(n => n.category === 'projects' || n.type === 'activity');
            } else if (activeFilter === 'correspondence') {
                filtered = rawNotifications.filter(n => n.category === 'correspondence' || n.type === 'correspondence');
            } else if (activeFilter === 'tasks') {
                filtered = rawNotifications.filter(n => n.category === 'tasks' || n.type === 'task');
            } else if (activeFilter === 'other') {
                filtered = rawNotifications.filter(n => n.category === 'other' || ['memoir', 'plan', 'execution', 'suggestion', 'request_descend', 'value_chain', 'lookup', 'user'].includes(n.type));
            }

            renderNotifications(filtered);
        }

        function renderNotifications(notifications) {
            if (!notifications || notifications.length === 0) {
                notificationList.innerHTML = `
                    <div class="empty-notifications">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="44" height="44" fill="currentColor">
                                <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V137.2c0-77.4-55-142-128-156.8V-12C368-29.7 353.7-44 336-44s-32 14.3-32 32V-19.6C231-4.8 176 59.8 176 137.2v69.6L38.8 5.1zM116 235.8l-52 41c-8.4 9.4-10.4 22.9-5.3 34.4S75.4 332 88 332h306.9L116 117.8V235.8zM440 332c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-31.5-35.4c-6.8-7.7-18.8-8.2-26.2-.9s-8.1 19.5-.7 27.9l20.4 22.9H300.9l-61.9-48.5V137.2c0-47 17.3-92.4 48.5-127.6l7.4-8.3c8.4-9.4 10.4-22.9 5.3-34.4S281.4-44 268.8-44H176c-12.6 0-24 7.4-29.2 18.9s-3.1 25 5.3 34.4l7.4 8.3c10.3 11.6 18.2 24.8 23.5 38.9l-42 32.9L116 235.8z"/>
                            </svg>
                        </div>
                        <p>لا توجد إشعارات حالياً</p>
                    </div>`;
                return;
            }

            let html = '';
            notifications.forEach(n => {
                const iconColor = getIconGradient(n.type, n.action_type);
                html += `
                    <a href="${n.url}" class="modern-notification-item ${n.is_unread ? 'unread' : 'read'}" data-notification-id="${n.id}" data-unread="${n.is_unread}">
                        <div class="notification-icon-box" style="background: ${iconColor};">
                            <i class="${n.icon}"></i>
                        </div>
                        <div class="notification-details">
                            <div class="user-action">
                                <span class="username">${n.user}</span>
                                <span class="action-desc">${n.desc}</span>
                            </div>
                            <div class="project-title">${n.title}</div>
                            ${n.page_name ? `
                                <div class="notification-page-name">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="10" height="10" fill="currentColor">
                                        <path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128z"/>
                                    </svg>
                                    <span>${n.page_name}</span>
                                </div>
                            ` : ''}
                            <div class="meta-info">
                                <span class="time-ago">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="10" height="10" fill="currentColor"><path d="M256 0a256 256 0 1 1 0 512A256 256 0 1 1 256 0zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/></svg>
                                    ${n.date}
                                </span>
                                ${n.is_unread ? '<span class="unread-dot"></span>' : ''}
                            </div>
                        </div>
                    </a>`;
            });
            notificationList.innerHTML = html;

            // Bind click handler on notification items
            notificationList.querySelectorAll('.modern-notification-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    const notifId = this.getAttribute('data-notification-id');
                    const isUnread = this.getAttribute('data-unread') === 'true';
                    const targetUrl = this.getAttribute('href');

                    if (isUnread && notifId) {
                        e.preventDefault();
                        markSingleAsRead(notifId, targetUrl);
                    }
                });
            });
        }

        function markSingleAsRead(notifId, targetUrl) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            fetch(`/notifications/${notifId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).finally(() => {
                if (targetUrl && targetUrl !== '#') {
                    window.location.href = targetUrl;
                }
            });
        }

        function getIconGradient(type, actionType) {
            if (type === 'correspondence') {
                return 'linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%)';
            }
            if (type === 'memoir') {
                return 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)';
            }
            if (type === 'project_request') {
                return 'linear-gradient(135deg, #00b4db 0%, #0083b0 100%)';
            }
            if (type === 'execution') {
                return 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)';
            }
            if (type === 'plan') {
                return 'linear-gradient(135deg, #0ba360 0%, #3cba92 100%)';
            }
            if (type === 'suggestion') {
                return 'linear-gradient(135deg, #f39c12 0%, #d35400 100%)';
            }
            if (type === 'request_descend') {
                return 'linear-gradient(135deg, #e65c00 0%, #F9D423 100%)';
            }
            if (type === 'value_chain') {
                return 'linear-gradient(135deg, #56ab2f 0%, #a8e063 100%)';
            }
            if (type === 'user') {
                return 'linear-gradient(135deg, #434343 0%, #000000 100%)';
            }

            const colors = {
                'created': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'project_created': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'responded': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'referral_response': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'updated': 'linear-gradient(45deg, #2193b0, #6dd5ed)',
                'project_updated': 'linear-gradient(45deg, #2193b0, #6dd5ed)',
                'stage_progression': 'linear-gradient(45deg, #2193b0, #6dd5ed)',
                'approval': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'approved': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'completed': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'project_completion': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'review_completed': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'rejected': 'linear-gradient(45deg, #cb2d3e, #ef473a)',
                'returned': 'linear-gradient(45deg, #cb2d3e, #ef473a)',
                'stage_regression': 'linear-gradient(45deg, #cb2d3e, #ef473a)',
                'need_action': 'linear-gradient(45deg, #f83600, #f9d423)',
                'requires_action': 'linear-gradient(45deg, #f83600, #f9d423)',
                'reverted_to_draft': 'linear-gradient(45deg, #f83600, #f9d423)',
                'project_referral': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'referral': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'sent_for_review': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'financial_technical_review': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'initiated': 'linear-gradient(45deg, #6f42c1, #a18cd1)'
            };
            return colors[actionType] || 'linear-gradient(45deg, #1e3c72, #2a5298)';
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
                notificationDropdown.classList.remove('show');
            }
        });

        // Mark all as read button
        markAllReadBtn?.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            markNotificationsAsRead();
        });

        function markNotificationsAsRead() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) return;

            fetch('{{ route('notifications.mark-all-read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationCount.style.display = 'none';
                        unreadCountText.textContent = '0 غير مقروءة';
                        markAllReadBtn.style.display = 'none';

                        rawNotifications.forEach(n => n.is_unread = false);
                        renderFilteredNotifications();
                        localStorage.setItem('notification_unread_count', '0');
                    }
                })
                .catch(error => console.error('Error marking notifications as read:', error));
        }
    })();
</script>