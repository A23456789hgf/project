<?php

namespace App\Http\Controllers;

use App\Models\ProjectActivityHistory;
use App\Models\User;
use App\Models\UserNotificationRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read
     */
    public function markAsRead(string|int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // 1. Check if it's a UUID (Laravel Database Notification)
            $dbNotification = $user->notifications()->where('id', $id)->first();
            if ($dbNotification) {
                $dbNotification->markAsRead();
            } else {
                // 2. Or numeric ID (ProjectActivityHistory)
                if (is_numeric($id)) {
                    UserNotificationRead::firstOrCreate([
                        'user_id' => $user->id,
                        'activity_history_id' => (int) $id,
                    ], [
                        'read_at' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديد الإشعار كمقروء',
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الإشعار',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // 1. Mark Laravel Database Notifications as read
            $user->unreadNotifications->markAsRead();

            // 2. Mark custom ProjectActivityHistory events as read
            $activityIds = ProjectActivityHistory::latest()
                ->take(50)
                ->pluck('id');

            foreach ($activityIds as $activityId) {
                UserNotificationRead::firstOrCreate([
                    'user_id' => $user->id,
                    'activity_history_id' => $activityId,
                ], [
                    'read_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديد جميع الإشعارات كمقروءة',
                'unread_count' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الإشعارات',
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(): JsonResponse
    {
        request()->session()->save(); // Release session lock for fast polling

        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'unread_count' => 0], 401);
            }

            // Get recent activity history (filtered by user permissions)
            $activityQuery = ProjectActivityHistory::query();
            if (! $user->isAdmin()) {
                $activityQuery->whereHas('project', function ($query) {
                    $query->visibleToUser('projects');
                });
            }
            $recentActivityIds = $activityQuery->latest()->take(50)->pluck('id');

            // Get read activity IDs for this user
            $readActivityIds = UserNotificationRead::where('user_id', $user->id)
                ->whereIn('activity_history_id', $recentActivityIds)
                ->pluck('activity_history_id');

            // Calculate unread count (Project Activities + Database Notifications)
            $activityUnreadCount = $recentActivityIds->diff($readActivityIds)->count();
            $dbUnreadCount = $user->unreadNotifications()->count();
            $unreadCount = $activityUnreadCount + $dbUnreadCount;

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting unread notification count: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب عدد الإشعارات',
            ], 500);
        }
    }

    /**
     * Get the latest notifications (combining project activities and database notifications)
     */
    public function getLatest(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $userId = $user->id;

            // 1. Get ProjectActivityHistory (filtered by user permissions)
            $systemActivitiesQuery = ProjectActivityHistory::with(['project', 'user']);
            if (! $user->isAdmin()) {
                $systemActivitiesQuery->whereHas('project', function ($query) {
                    $query->visibleToUser('projects');
                });
            }
            $systemActivities = $systemActivitiesQuery->latest()->take(30)->get();

            // Get read activity IDs for this user
            $readActivityIds = UserNotificationRead::where('user_id', $userId)
                ->whereIn('activity_history_id', $systemActivities->pluck('id'))
                ->pluck('activity_history_id')
                ->toArray();

            // 2. Get Laravel Database Notifications (all modules)
            $dbNotifications = $user->notifications()->take(30)->get();

            // 3. Unify, map, and translate
            $allNotifications = collect();

            $translateMessage = function (?string $text): string {
                if (! $text) {
                    return 'تنبيه جديد';
                }

                $translations = [
                    'Task created' => 'تم إنشاء مهمة جديدة',
                    'Task updated' => 'تم تحديث المهمة',
                    'Task deleted' => 'تم حذف المهمة',
                    'Task assigned' => 'تم تكليف المهمة',
                    'A new task has been assigned to you' => 'تم إسناد مهمة جديدة لك',
                    'Stage transition' => 'انتقال لمرحلة جديدة',
                    'Project approved' => 'تمت الموافقة على المشروع',
                    'Project rejected' => 'تم رفض المشروع',
                    'Project referral' => 'إحالة المشروع',
                    'Referral response' => 'الرد على الإحالة',
                    'approval' => 'وافق على مرحلة الاعتماد',
                    'approved' => 'وافق على مرحلة الاعتماد',
                    'project_completion' => 'اكتملت جميع مراحل الاعتماد بنجاح',
                    'completed' => 'اكتملت جميع مراحل الاعتماد بنجاح',
                    'stage_progression' => 'الانتقال للمرحلة التالية',
                    'stage_regression' => 'إرجاع المشروع إلى مرحلة سابقة',
                    'reverted_to_draft' => 'إعادة المشروع إلى المسودة',
                    'rejected' => 'رفض المشروع',
                    'requires_action' => 'طلب إجراء على المشروع',
                    'need_action' => 'طلب إجراء وإرجاع مرحلي',
                    'resubmitted' => 'أعاد تقديم المشروع للاعتماد',
                    'sent_for_review' => 'أرسل للمراجعة المالية والفنية',
                    'financial_technical_review' => 'أرسل للمراجعة المالية والفنية',
                    'review_completed' => 'أكمل المراجعة المالية والفنية',
                    'financial_review_completed' => 'أكمل المراجعة المالية',
                    'technical_review_completed' => 'أكمل المراجعة الفنية',
                    'created' => 'إنشاء سجل جديد',
                    'updated' => 'تحديث البيانات',
                    'deleted' => 'حذف السجل',
                    'assigned' => 'تكليف بمهمة جديدة',
                    'responded' => 'الرد على الإحالة',
                    'referral' => 'إحالة جديدة',
                    'submitted' => 'تقديم للاعتماد',
                    'transferred' => 'تحويل إلى مشروع رسمي',
                    'reply' => 'إضافة رد جديد',
                    'forward' => 'توجيه المعاملة',
                    'close' => 'إغلاق المعاملة',
                    'printed' => 'طباعة وتوقيع المعاملة',
                    'financial_updated' => 'تحديث الحالة المالية',
                    'enabled' => 'تفعيل الحساب',
                    'disabled' => 'تعطيل الحساب',
                    'password_reset' => 'إعادة تعيين كلمة المرور',
                    'stopped' => 'إيقاف المهمة',
                    'resumed' => 'استئناف المهمة',
                ];

                $trimmed = trim($text);

                return $translations[$text] ?? $translations[$trimmed] ?? $translations[strtolower($trimmed)] ?? $text;
            };

            $getDeepLinkParams = function ($actionType) {
                return match ($actionType) {
                    'created', 'project_created' => ['step' => 1, 'section' => 'basic-data-section'],
                    'updated', 'project_updated' => ['step' => 2, 'section' => 'technical-details-section'],
                    'approval', 'approved', 'rejected', 'returned', 'need_action', 'requires_action',
                    'resubmitted', 'sent_for_review', 'financial_technical_review', 'review_completed',
                    'financial_review_completed', 'technical_review_completed', 'initiated', 'finalized',
                    'reverted_to_draft', 'stage_progression', 'stage_regression', 'returned_for_revision',
                    'returned_to_previous', 'completed', 'project_completion', 'approved_to_implementation',
                    'project_referral', 'referral', 'responded', 'referral_response' => ['step' => 3, 'section' => 'approval-workflow'],
                    default => ['step' => 1, 'section' => '']
                };
            };

            $getPageName = function (?string $url, ?string $actionType = null, ?string $notifType = null, ?string $customPage = null): string {
                if ($customPage) {
                    return $customPage;
                }

                if (! $url || $url === '#') {
                    return match ($notifType) {
                        'task' => 'إدارة المهام',
                        'correspondence' => 'المراسلات الإدارية',
                        'project_request' => 'طلبات المشاريع',
                        'memoir' => 'المذكرات الإدارية',
                        'plan' => 'الخطط والمشاريع',
                        'execution' => 'متابعة التنفيذ',
                        'suggestion' => 'الاقتراحات والتطوير',
                        'request_descend' => 'طلبات النزول الميداني',
                        'value_chain' => 'سلاسل القيمة',
                        'user' => 'إدارة المستخدمين',
                        default => 'لوحة التحكم',
                    };
                }

                if (str_contains($url, 'correspondence')) {
                    return 'المراسلات الإدارية';
                }
                if (str_contains($url, 'project-requests')) {
                    return 'طلبات المشاريع';
                }
                if (str_contains($url, 'memoirs')) {
                    return 'المذكرات الإدارية';
                }
                if (str_contains($url, 'requests_descend')) {
                    return 'طلبات النزول الميداني';
                }
                if (str_contains($url, 'financial-justifications')) {
                    return 'المبررات المالية';
                }
                if (str_contains($url, 'projects.tasks') || str_contains($url, '/tasks') || $notifType === 'task') {
                    return 'إدارة المهام';
                }
                if (str_contains($url, 'project-referrals') || str_contains($url, 'referrals')) {
                    return 'إحالات المشاريع';
                }
                if (str_contains($url, 'execution') || str_contains($url, 'projects/execution')) {
                    return 'متابعة التنفيذ';
                }
                if (str_contains($url, 'complete-data')) {
                    return 'استكمال بيانات المشروع';
                }
                if (str_contains($url, 'budgets')) {
                    return 'الموازنات';
                }
                if (str_contains($url, 'achievements')) {
                    return 'إنجازات المشاريع السابقة';
                }
                if (str_contains($url, 'reports')) {
                    return 'لوحة التقارير';
                }
                if (str_contains($url, 'empowerment')) {
                    return 'إدارة التمكين';
                }
                if (str_contains($url, 'planning') || str_contains($url, 'plans')) {
                    return 'الخطط التنموية';
                }
                if (str_contains($url, 'value-chains') || str_contains($url, 'chain-plans')) {
                    return 'سلاسل القيمة';
                }
                if (str_contains($url, 'users') || str_contains($url, 'profile')) {
                    return 'الملف الشخصي والحساب';
                }
                if (str_contains($url, 'basic-data-section') || str_contains($url, 'step=1')) {
                    return 'تفاصيل المشروع (البيانات الأساسية)';
                }
                if (str_contains($url, 'technical-details-section') || str_contains($url, 'step=2')) {
                    return 'تفاصيل المشروع (التفاصيل الفنية والمالية)';
                }
                if (str_contains($url, 'approval-workflow') || str_contains($url, 'step=3')) {
                    return 'تفاصيل المشروع (سير عمل الاعتماد)';
                }
                if (str_contains($url, 'projects/') || str_contains($url, 'projects.show')) {
                    return 'تفاصيل المشروع';
                }
                if (str_contains($url, 'projects')) {
                    return 'قائمة المشاريع';
                }

                return 'النظام';
            };

            // Map Activity History
            foreach ($systemActivities as $activity) {
                $isRead = in_array($activity->id, $readActivityIds);
                $linkParams = $getDeepLinkParams($activity->action_type);
                $url = route('projects.show', $activity->project_id).'?'.http_build_query($linkParams);

                $allNotifications->push([
                    'id' => $activity->id,
                    'type' => 'activity',
                    'category' => 'projects',
                    'action_type' => $activity->action_type,
                    'title' => $activity->project->project_name ?? 'مشروع',
                    'desc' => $activity->getActionDescription(),
                    'page_name' => $activity->getPageName(),
                    'user' => $activity->user->name ?? 'مستخدم',
                    'date_raw' => $activity->created_at,
                    'date' => $activity->created_at->locale('ar')->diffForHumans(),
                    'url' => $url,
                    'is_unread' => ! $isRead,
                    'icon' => $activity->getIconClass() ?? 'fas fa-info-circle',
                ]);
            }

            // Map Database Notifications
            foreach ($dbNotifications as $notif) {
                $data = $notif->data;
                $notifType = $data['type'] ?? 'general';

                $category = match ($notifType) {
                    'correspondence', 'memoir' => 'correspondence',
                    'task' => 'tasks',
                    'project_request', 'activity', 'approval', 'stage_transition', 'database', 'project_referral', 'execution' => 'projects',
                    default => 'other',
                };

                if ($notifType === 'task') {
                    // Task Notification
                    $taskTitle = $data['task_title'] ?? 'مهمة جديدة';
                    if (! empty($data['project_name'])) {
                        $taskTitle .= ' ('.$data['project_name'].')';
                    }
                    $message = $translateMessage($data['message'] ?? 'تم إسناد مهمة جديدة لك');
                    $actionUrl = $data['action_url'] ?? '#';
                    $causerName = $data['causer_name'] ?? 'النظام';
                    if ($causerName === 'النظام' && ! empty($data['causer_id'])) {
                        $causer = User::find($data['causer_id']);
                        if ($causer) {
                            $causerName = $causer->name;
                        }
                    }

                    $actionType = $data['action_type'] ?? 'created';
                    $allNotifications->push([
                        'id' => $notif->id,
                        'type' => 'task',
                        'category' => 'tasks',
                        'action_type' => $actionType,
                        'title' => $taskTitle,
                        'desc' => $message,
                        'page_name' => $getPageName($actionUrl, $actionType, 'task', $data['page_name'] ?? null),
                        'user' => $causerName,
                        'date_raw' => $notif->created_at,
                        'date' => $notif->created_at->locale('ar')->diffForHumans(),
                        'url' => $actionUrl,
                        'is_unread' => $notif->unread(),
                        'icon' => $data['icon'] ?? 'fas fa-tasks',
                    ]);
                } elseif (in_array($notifType, ['correspondence', 'project_request', 'memoir', 'plan', 'execution', 'suggestion', 'request_descend', 'value_chain', 'lookup', 'user', 'general'])) {
                    // Universal Notification from GeneralNotification
                    $actionType = $data['action_type'] ?? 'info';
                    $actionUrl = $data['action_url'] ?? '#';
                    $title = $data['title'] ?? 'إشعار جديد';
                    $message = $translateMessage($data['message'] ?? '');
                    $causerName = $data['causer_name'] ?? 'النظام';

                    $allNotifications->push([
                        'id' => $notif->id,
                        'type' => $notifType,
                        'category' => $category,
                        'action_type' => $actionType,
                        'title' => $title,
                        'desc' => $message,
                        'page_name' => $getPageName($actionUrl, $actionType, $notifType, $data['page_name'] ?? null),
                        'user' => $causerName,
                        'date_raw' => $notif->created_at,
                        'date' => $notif->created_at->locale('ar')->diffForHumans(),
                        'url' => $actionUrl,
                        'is_unread' => $notif->unread(),
                        'icon' => $data['icon'] ?? 'fas fa-bell',
                    ]);
                } else {
                    // Project Referral / Approval Notification
                    $actionType = $data['status'] ?? ($data['type'] ?? 'referral');
                    $linkParams = $getDeepLinkParams($actionType);

                    $baseUrl = $data['action_url'] ?? '#';
                    $url = $baseUrl;
                    if ($baseUrl !== '#' && ! str_contains($baseUrl, '?')) {
                        $url .= '?'.http_build_query($linkParams);
                    } elseif ($baseUrl !== '#' && str_contains($baseUrl, '?')) {
                        $url .= '&'.http_build_query($linkParams);
                    }

                    $userName = $data['referring_user'] ?? ($data['responding_user'] ?? ($data['approved_by'] ?? ($data['assigned_by'] ?? ($data['requested_by'] ?? ($data['resubmitted_by'] ?? ($data['causer_name'] ?? 'النظام'))))));

                    $allNotifications->push([
                        'id' => $notif->id,
                        'type' => 'database',
                        'category' => 'projects',
                        'action_type' => $actionType,
                        'title' => $data['project_name'] ?? ($data['form_number'] ?? 'إحالة مشروع'),
                        'desc' => $translateMessage($data['message'] ?? 'تنبيه جديد'),
                        'page_name' => $getPageName($url, $actionType, 'database', $data['page_name'] ?? null),
                        'user' => $userName,
                        'date_raw' => $notif->created_at,
                        'date' => $notif->created_at->locale('ar')->diffForHumans(),
                        'url' => $url,
                        'is_unread' => $notif->unread(),
                        'icon' => $data['icon'] ?? 'fas fa-exchange-alt',
                    ]);
                }
            }

            // Optional category filter
            $filter = $request->query('filter', 'all');
            $filteredNotifications = $allNotifications;

            if ($filter === 'unread') {
                $filteredNotifications = $filteredNotifications->where('is_unread', true);
            } elseif (in_array($filter, ['projects', 'correspondence', 'tasks', 'other'])) {
                $filteredNotifications = $filteredNotifications->where('category', $filter);
            }

            // Sort by date desc and take top 25
            $sorted = $filteredNotifications->sortByDesc('date_raw')->take(25)->values();
            $totalUnreadCount = $allNotifications->where('is_unread', true)->count();

            return response()->json([
                'success' => true,
                'notifications' => $sorted,
                'unread_count' => $totalUnreadCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading latest notifications: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الإشعارات',
            ], 500);
        }
    }
}
