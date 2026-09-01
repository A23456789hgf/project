@php
    $user = auth()->user();
    $userId = $user->id;
    
    // 1. Get ProjectActivityHistory (filtered by user permissions)
    // Only show notifications for projects the user has permission to view
    $systemActivities = \App\Models\ProjectActivityHistory::with(['project', 'user'])
        ->whereHas('project', function($query) {
            // Apply the same visibility scope used in Project model
            // This respects: view-all, view-parallel, view-own, view-user permissions
            $query->visibleToUser('projects');
        })
        ->latest()
        ->take(15)
        ->get();
    
    // Get manual read IDs for activity history
    $readActivityIds = \App\Models\UserNotificationRead::where('user_id', $userId)
        ->whereIn('activity_history_id', $systemActivities->pluck('id'))
        ->pluck('activity_history_id')
        ->toArray();
    
    // 2. Get Laravel Database Notifications (referrals/responses)
    $dbNotifications = $user->notifications()->take(15)->get();
    
    // 3. Unify and Sort
    $allNotifications = collect();
    
    $getDeepLinkParams = function($actionType) {
        return match($actionType) {
            'created' => ['step' => 1, 'section' => 'basic-data-section'],
            'updated' => ['step' => 2, 'section' => 'technical-details-section'],
            'approved', 'rejected', 'returned', 'need_action', 'requires_action', 
            'resubmitted', 'sent_for_review', 'review_completed', 'initiated', 
            'completed', 'project_referral', 'referral' => ['step' => 3, 'section' => 'approval-workflow'],
            default => ['step' => 1, 'section' => '']
        };
    };
    
    foreach ($systemActivities as $activity) {
        $isRead = in_array($activity->id, $readActivityIds);
        $linkParams = $getDeepLinkParams($activity->action_type);
        
        $allNotifications->push([
            'id' => $activity->id,
            'type' => 'activity',
            'action_type' => $activity->action_type,
            'title' => $activity->project->project_name ?? 'مشروع',
            'desc' => $activity->getActionDescription(),
            'user' => $activity->user->name ?? 'مستخدم',
            'date' => $activity->created_at,
            'url' => route('projects.show', $activity->project_id) . '?' . http_build_query($linkParams),
            'is_unread' => !$isRead,
            'icon' => $activity->getIconClass()
        ]);
    }
    
    foreach ($dbNotifications as $notif) {
        $data = $notif->data;
        $notifType = $data['type'] ?? 'database';
        
        if ($notifType === 'task') {
            $actionType = $data['action_type'] ?? 'created';
            $url = $data['action_url'] ?? '#';
            $icon = $data['icon'] ?? 'fas fa-tasks';
            $title = $data['task_title'] ?? 'مهمة جديدة';
            if (!empty($data['project_name'])) {
                $title .= ' (' . $data['project_name'] . ')';
            }
            
            $causerName = $data['causer_name'] ?? 'النظام';
            if ($causerName === 'النظام' && !empty($data['causer_id'])) {
                $causer = \App\Models\User::find($data['causer_id']);
                if ($causer) {
                    $causerName = $causer->name;
                }
            }
            
            $allNotifications->push([
                'id' => $notif->id,
                'type' => 'database',
                'action_type' => $actionType,
                'title' => $title,
                'desc' => $data['message'] ?? 'تكليف بمهمة جديدة',
                'user' => $causerName,
                'date' => $notif->created_at,
                'url' => $url,
                'is_unread' => $notif->unread(),
                'icon' => $icon
            ]);
        } else {
            $actionType = $data['status'] ?? 'referral';
            $linkParams = $getDeepLinkParams($actionType);
            
            $baseUrl = $data['action_url'] ?? '#';
            $url = $baseUrl;
            if ($baseUrl !== '#' && !str_contains($baseUrl, '?')) {
                $url .= '?' . http_build_query($linkParams);
            } elseif ($baseUrl !== '#' && str_contains($baseUrl, '?')) {
                $url .= '&' . http_build_query($linkParams);
            }

            $allNotifications->push([
                'id' => $notif->id,
                'type' => 'database',
                'action_type' => $actionType,
                'title' => $data['project_name'] ?? ($data['form_number'] ?? 'إحالة مشروع'),
                'desc' => $data['message'] ?? 'تنبيه جديد',
                'user' => $data['referring_user'] ?? ($data['responding_user'] ?? 'النظام'),
                'date' => $notif->created_at,
                'url' => $url,
                'is_unread' => $notif->unread(),
                'icon' => 'fas fa-exchange-alt'
            ]);
        }
    }
    
    // Sort by date descending
    $sortedNotifications = $allNotifications->sortByDesc('date')->take(20);
    $unreadCount = $sortedNotifications->where('is_unread', true)->count();

    $getIconColor = function($type) {
        return match($type) {
            'created', 'responded' => 'linear-gradient(45deg, #11998e, #38ef7d)',
            'updated' => 'linear-gradient(45deg, #2193b0, #6dd5ed)',
            'approved', 'completed' => 'linear-gradient(45deg, #1D976C, #93F9B9)',
            'rejected', 'returned' => 'linear-gradient(45deg, #cb2d3e, #ef473a)',
            'need_action', 'requires_action' => 'linear-gradient(45deg, #f83600, #f9d423)',
            'project_referral', 'referral', 'sent_for_review', 'initiated' => 'linear-gradient(45deg, #6f42c1, #a18cd1)',
            default => 'linear-gradient(45deg, #4e73df, #224abe)',
        };
    };
@endphp

<div class="notification-wrapper">
    <button class="notification-trigger" id="notificationToggle" aria-label="الإشعارات">
        <div class="bell-container">
            <i class="fas fa-bell"></i>
            @if($unreadCount > 0)
                <span class="notification-counter" id="notificationCount">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            @endif
        </div>
    </button>
    
    <div class="notification-panel" id="notificationDropdown">
        <div class="panel-header">
            <div class="header-content">
                <span class="header-title">الإشعارات</span>
                <span class="unread-count-label" id="unreadCountText">{{ $unreadCount }} غير مقروءة</span>
            </div>
            @if($unreadCount > 0)
                <button class="mark-read-all" id="markAllReadBtn">
                    <i class="fas fa-check-double"></i> تحديد الكل
                </button>
            @endif
        </div>
        
        <div class="notification-scroll-area">
            @forelse $sortedNotifications as $notif
                <a href="{{ $notif['url'] }}" 
                   class="modern-notification-item {{ $notif['is_unread'] ? 'unread' : 'read' }}"
                   data-notification-id="{{ $notif['id'] }}">
                    <div class="notification-icon-box" style="background: {{ $getIconColor($notif['action_type']) }};">
                        <i class="{{ $notif['icon'] }}"></i>
                    </div>
                    <div class="notification-details">
                        <div class="user-action">
                            <span class="username">{{ $notif['user'] }}</span>
                            <span class="action-desc">{{ $notif['desc'] }}</span>
                        </div>
                        <div class="project-title">{{ $notif['title'] }}</div>
                        <div class="meta-info">
                            <span class="time-ago"><i class="far fa-clock"></i> {{ $notif['date']->diffForHumans() }}</span>
                            @if($notif['is_unread'])
                                <span class="unread-dot"></span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-notifications">
                    <div class="empty-icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <p>لا توجد إشعارات أو تنبيهات حالياً</p>
                </div>
            @endforelse
        </div>
        
        <div class="panel-footer">
            <a href="{{ route('projects.index') }}" class="view-all-link">
                عرض جميع المشاريع <i class="fas fa-arrow-left"></i>
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
    padding: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
    border-radius: 50%;
    color: #ffffff;
}

.notification-trigger:hover {
    background: rgba(255, 255, 255, 0.15);
}

.bell-container {
    position: relative;
    font-size: 1.15rem;
    color: #ffffff;
}

.notification-counter {
    position: absolute;
    top: -5px;
    right: -5px;
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

.notification-panel {
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
    from { opacity: 0; transform: translateY(-10px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.notification-panel.show {
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
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
    font-size: 3rem;
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

.notification-scroll-area::-webkit-scrollbar {
    width: 6px;
}

.notification-scroll-area::-webkit-scrollbar-thumb {
    background: #dde2ef;
    border-radius: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationCount = document.getElementById('notificationCount');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    
    // Toggle dropdown
    notificationToggle?.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationDropdown.classList.toggle('show');
        
        // When opening, we don't automatically mark all as read. 
        // We'll let the user see them first.
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && !notificationToggle.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
    
    // Mark all as read button
    markAllReadBtn?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        markNotificationsAsRead();
    });
    
    // Individual item click handling
    document.querySelectorAll('.modern-notification-item.unread').forEach(item => {
        item.addEventListener('click', function(e) {
            // Optionally mark individual as read via AJAX here before navigating
            // For now, let's keep it simple
        });
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
                // Hide notification badge with animation
                if (notificationCount) {
                    notificationCount.style.transform = 'scale(0)';
                    setTimeout(() => notificationCount.style.display = 'none', 200);
                }
                
                // Remove unread markers from items
                document.querySelectorAll('.modern-notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const dot = item.querySelector('.unread-dot');
                    if (dot) dot.remove();
                });
                
                // Hide mark all read button
                if (markAllReadBtn) {
                    markAllReadBtn.style.opacity = '0';
                    setTimeout(() => markAllReadBtn.style.display = 'none', 300);
                }

                // Update unread count label
                const label = document.querySelector('.unread-count-label');
                if (label) label.textContent = '0 غير مقروءة';
            }
        })
        .catch(error => console.error('Error marking notifications as read:', error));
    }
});
</script>
