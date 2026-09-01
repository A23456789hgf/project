@php
    // We only need the unread count initially to show the badge.
    // Offloaded to AJAX for maximum performance.
@endphp

<div class="notification-wrapper">
    <button class="notification-trigger" id="notificationToggle" aria-label="الإشعارات">
        <div class="bell-container" style="pointer-events: none;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="12" height="12" fill="#002147" style="pointer-events: none;">
                <path
                    d="M224 0c-17.7 0-32 14.3-32 32V51.2C119 66 64 130.6 64 208v118.8c0 47-17.3 92.4-48.5 127.6l-7.4 8.3c-8.4 9.4-10.4 22.9-5.3 34.4S19.4 512 32 512H416c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-7.4-8.3C401.3 419.2 384 373.8 384 326.8V208c0-77.4-55-142-128-156.8V32c0-17.7-14.3-32-32-32zm45.3 493.3c12-12 18.7-28.3 18.7-45.3H160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7z" />
            </svg>
            <span class="notification-counter" id="notificationCount" style="display: none; pointer-events: none;"></span>
        </div>
    </button>

    <div class="notification-dropdown" id="notificationDropdown">
        <div class="panel-header">
            <div class="header-content">
                <span class="header-title">الإشعارات</span>
                <span class="unread-count-label" id="unreadCountText">جاري التحميل...</span>
            </div>
            <button class="mark-read-all" id="markAllReadBtn"
                style="display: none; display: flex; align-items: center; gap: 5px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="14" height="14"
                    fill="currentColor">
                    <path
                        d="M342.6 86.6c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0L74.3 183.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L205.3 224l137.3-137.4zM438.6 278.6c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0l-160-160c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L278.6 438.6l160-160zM122.3 294.3c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-160-160z" />
                </svg>
                تحديد الكل
            </button>
        </div>

        <div class="notification-scroll-area" id="notificationList">
            <div class="notification-loading-state">
                <div class="shimmer-item"></div>
                <div class="shimmer-item"></div>
                <div class="shimmer-item"></div>
            </div>
        </div>

        <div class="panel-footer">
            <a href="{{ route('projects.index') }}"
                class="view-all-link d-flex align-items-center justify-content-center gap-2">
                عرض جميع المشاريع
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="14" height="14"
                    fill="currentColor">
                    <path
                        d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z" />
                </svg>
            </a>
        </div>
    </div>
</div>

<style>
    :root {
        --notif-primary: #4e73df;
        --notif-bg: #ffffff;
        --notif-unread-bg: #f8faff;
        --notif-hover: #f1f4f9;
        --notif-border: #eef1f6;
        --notif-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .notification-wrapper {
        position: relative;
        display: inline-block;
    }

    .notification-trigger {
        background: transparent;
        border: none;
        padding: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 50%;
        color: #fff;
    }

    .notification-trigger:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .bell-container {
        position: relative;
        font-size: 1.25rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-counter {
        position: absolute;
        top: -6px;
        right: -8px;
        background: #e74a3b;
        color: white;
        font-size: 0.65rem;
        padding: 2px 5px;
        border-radius: 10px;
        font-weight: bold;
        border: 2px solid #fff;
        min-width: 18px;
        text-align: center;
    }

    .notification-dropdown {
        position: absolute;
        top: 50px;
        left: 0;
        width: 380px;
        background: var(--notif-bg);
        border-radius: 16px;
        box-shadow: var(--notif-shadow);
        display: none;
        z-index: 1000;
        overflow: hidden;
        animation: slideDown 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        transform-origin: top left;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
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
        padding: 20px;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-size: 1.1rem;
        font-weight: 700;
    }

    .unread-count-label {
        display: block;
        font-size: 0.75rem;
        opacity: 0.8;
    }

    .mark-read-all {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 0.75rem;
        padding: 6px 12px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .mark-read-all:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .notification-scroll-area {
        max-height: 450px;
        overflow-y: auto;
    }

    .modern-notification-item {
        display: flex;
        padding: 16px 20px;
        text-decoration: none !important;
        border-bottom: 1px solid var(--notif-border);
        transition: all 0.2s ease;
        position: relative;
    }

    .modern-notification-item:hover {
        background: var(--notif-hover);
        transform: translateX(-4px);
    }

    .modern-notification-item.unread {
        background: var(--notif-unread-bg);
    }

    .notification-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
        margin-left: 15px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .notification-details {
        flex-grow: 1;
        overflow: hidden;
    }

    .user-action {
        display: block;
        margin-bottom: 2px;
    }

    .username {
        font-weight: 700;
        color: #333;
        font-size: 0.9rem;
    }

    .action-desc {
        color: #666;
        font-size: 0.85rem;
        margin-right: 4px;
    }

    .project-title {
        color: var(--notif-primary);
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .meta-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .time-ago {
        font-size: 0.7rem;
        color: #999;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        background: #4e73df;
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
    }

    .empty-notifications {
        padding: 60px 20px;
        text-align: center;
        color: #999;
    }

    .empty-icon {
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .panel-footer {
        padding: 15px;
        text-align: center;
        background: #f8f9fc;
        border-top: 1px solid var(--notif-border);
    }

    .view-all-link {
        color: #4e73df;
        font-weight: 600;
        font-size: 0.85rem;
        text-decoration: none;
        transition: all 0.2s;
    }

    .view-all-link:hover {
        color: #224abe;
    }

    .notification-loading-state {
        padding: 20px;
    }

    .shimmer-item {
        height: 70px;
        background: #f6f7f8;
        background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-repeat: no-repeat;
        background-size: 800px 104px;
        display: block;
        position: relative;
        animation: shimmer 1.5s infinite linear;
        margin-bottom: 10px;
        border-radius: 8px;
    }

    @keyframes shimmer {
        0% {
            background-position: -468px 0;
        }

        100% {
            background-position: 468px 0;
        }
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

        let notificationsLoaded = false;

        // Notification Sound function
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
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
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

        // 1. Initial Unread Count Fetch
        function fetchUnreadCount() {
            fetch('{{ route('notifications.unread-count') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const lastUnreadCount = parseInt(localStorage.getItem('notification_unread_count') || '0');
                        
                        if (data.unread_count > 0) {
                            notificationCount.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                            notificationCount.style.display = 'block';
                            unreadCountText.textContent = data.unread_count + ' غير مقروءة';
                            markAllReadBtn.style.display = 'flex'; // Changed to flex for alignment
                            
                            // Play sound only if unread count increased (new notifications arrived)
                            if (data.unread_count > lastUnreadCount) {
                                playNotificationSound();
                            }
                        } else {
                            notificationCount.style.display = 'none';
                            unreadCountText.textContent = 'لا توجد إشعارات غير مقروءة';
                            markAllReadBtn.style.display = 'none';
                        }
                        
                        // Update stored count
                        localStorage.setItem('notification_unread_count', data.unread_count);
                        }
                })
                .catch(err => console.error('Error fetching unread count:', err));
        }

        fetchUnreadCount();

        // 2. Load Notifications on Toggle
        notificationToggle?.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');

            if (notificationDropdown.classList.contains('show')) {
                loadNotifications();
            }
        });

        function loadNotifications() {
            fetch('{{ route('notifications.latest') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderNotifications(data.notifications);
                        notificationsLoaded = true;

                        // Update counts based on latest data
                        if (data.unread_count > 0) {
                            notificationCount.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                            notificationCount.style.display = 'block';
                            unreadCountText.textContent = data.unread_count + ' غير مقروءة';
                            markAllReadBtn.style.display = 'flex';
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

        function renderNotifications(notifications) {
            if (notifications.length === 0) {
                // تم استبدال fa-bell-slash بـ SVG
                notificationList.innerHTML = `
                    <div class="empty-notifications">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" width="48" height="48" fill="currentColor">
                                <path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V137.2c0-77.4-55-142-128-156.8V-12C368-29.7 353.7-44 336-44s-32 14.3-32 32V-19.6C231-4.8 176 59.8 176 137.2v69.6L38.8 5.1zM116 235.8l-52 41c-8.4 9.4-10.4 22.9-5.3 34.4S75.4 332 88 332h306.9L116 117.8V235.8zM440 332c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-31.5-35.4c-6.8-7.7-18.8-8.2-26.2-.9s-8.1 19.5-.7 27.9l20.4 22.9H300.9l-61.9-48.5V137.2c0-47 17.3-92.4 48.5-127.6l7.4-8.3c8.4-9.4 10.4-22.9 5.3-34.4S281.4-44 268.8-44H176c-12.6 0-24 7.4-29.2 18.9s-3.1 25 5.3 34.4l7.4 8.3c10.3 11.6 18.2 24.8 23.5 38.9l-42 32.9L116 235.8z"/>
                            </svg>
                        </div>
                        <p>لا توجد إشعارات حالياً</p>
                    </div>`;
                return;
            }

            let html = '';
            notifications.forEach(n => {
                const iconColor = getIconColor(n.action_type);
                // استخدام أيقونات n.icon إذا كانت SVG جاهزة من الـ Backend، 
                // أو سيتم جلبها كما هي (يُفضل تعديل الـ Backend ليرسل SVGs أو تركها كـ FontAwesome إذا كانت تعمل داخلياً).
                html += `
                    <a href="${n.url}" class="modern-notification-item ${n.is_unread ? 'unread' : 'read'}" data-notification-id="${n.id}">
                        <div class="notification-icon-box" style="background: ${iconColor};">
                            <i class="${n.icon}"></i>
                        </div>
                        <div class="notification-details">
                            <div class="user-action">
                                <span class="username">${n.user}</span>
                                <span class="action-desc">${n.desc}</span>
                            </div>
                            <div class="project-title">${n.title}</div>
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
        }

        function getIconColor(type) {
            const colors = {
                'created': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'responded': 'linear-gradient(45deg, #11998e, #38ef7d)',
                'updated': 'linear-gradient(45deg, #2193b0, #6dd5ed)',
                'approved': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'completed': 'linear-gradient(45deg, #1D976C, #93F9B9)',
                'rejected': 'linear-gradient(45deg, #cb2d3e, #ef473a)',
                'returned': 'linear-gradient(45deg, #cb2d3e, #ef473a)',
                'need_action': 'linear-gradient(45deg, #f83600, #f9d423)',
                'requires_action': 'linear-gradient(45deg, #f83600, #f9d423)',
                'project_referral': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'referral': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'sent_for_review': 'linear-gradient(45deg, #6f42c1, #a18cd1)',
                'initiated': 'linear-gradient(45deg, #6f42c1, #a18cd1)'
            };
            return colors[type] || 'linear-gradient(45deg, #4e73df, #224abe)';
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

                        document.querySelectorAll('.modern-notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                            item.classList.add('read');
                            const dot = item.querySelector('.unread-dot');
                            if (dot) dot.remove();
                        });
                    }
                })
                .catch(error => console.error('Error marking notifications as read:', error));
        }
    })();
</script>