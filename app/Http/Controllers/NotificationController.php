<?php

namespace App\Http\Controllers;

use App\Models\ProjectActivityHistory;
use App\Models\User;
use App\Models\UserNotificationRead;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = auth()->user();

            // 1. Mark Laravel Database Notifications as read
            $user->unreadNotifications->markAsRead();

            // 2. Mark custom ProjectActivityHistory events as read
            $activityIds = ProjectActivityHistory::latest()
                ->take(20)
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
            \Log::error('Error marking all notifications as read: '.$e->getMessage());

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
        request()->session()->save(); // Release session lock for long polling

        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'unread_count' => 0], 401);
            }

            // Get recent activity history (filtered by user permissions)
            $recentActivityIds = ProjectActivityHistory::whereHas('project', function ($query) {
                $query->visibleToUser('projects');
            })
                ->latest()
                ->take(20)
                ->pluck('id');

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
            \Log::error('Error getting unread notification count: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب عدد الإشعارات',
            ], 500);
        }
    }

    /**
     * Get the latest notifications (combining project activities and database notifications)
     */
    public function getLatest(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $userId = $user->id;

            // 1. Get ProjectActivityHistory (filtered by user permissions)
            $systemActivities = ProjectActivityHistory::with(['project', 'user'])
                ->whereHas('project', function ($query) {
                    $query->visibleToUser('projects');
                })
                ->latest()
                ->take(15)
                ->get();

            // Get read activity IDs for this user
            $readActivityIds = UserNotificationRead::where('user_id', $userId)
                ->whereIn('activity_history_id', $systemActivities->pluck('id'))
                ->pluck('activity_history_id')
                ->toArray();

            // 2. Get Laravel Database Notifications (referrals/responses/tasks)
            $dbNotifications = $user->notifications()->take(15)->get();

            // 3. Unify and Sort
            $allNotifications = collect();

            $getDeepLinkParams = function ($actionType) {
                return match ($actionType) {
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
                    'date_raw' => $activity->created_at,
                    'date' => $activity->created_at->diffForHumans(),
                    'url' => route('projects.show', $activity->project_id).'?'.http_build_query($linkParams),
                    'is_unread' => ! $isRead,
                    'icon' => $activity->getIconClass() ?? 'fas fa-info-circle',
                ]);
            }

            foreach ($dbNotifications as $notif) {
                $data = $notif->data;
                $notifType = $data['type'] ?? 'database';

                if ($notifType === 'task') {
                    // Task Notification
                    $taskTitle = $data['task_title'] ?? 'مهمة جديدة';
                    if (! empty($data['project_name'])) {
                        $taskTitle .= ' ('.$data['project_name'].')';
                    }
                    $message = $data['message'] ?? 'تم إسناد مهمة جديدة لك';
                    $actionUrl = $data['action_url'] ?? '#';
                    $causerName = $data['causer_name'] ?? 'النظام';
                    if ($causerName === 'النظام' && ! empty($data['causer_id'])) {
                        $causer = User::find($data['causer_id']);
                        if ($causer) {
                            $causerName = $causer->name;
                        }
                    }

                    $allNotifications->push([
                        'id' => $notif->id,
                        'type' => 'task',
                        'action_type' => $data['action_type'] ?? 'created',
                        'title' => $taskTitle,
                        'desc' => $message,
                        'user' => $causerName,
                        'date_raw' => $notif->created_at,
                        'date' => $notif->created_at->diffForHumans(),
                        'url' => $actionUrl,
                        'is_unread' => $notif->unread(),
                        'icon' => $data['icon'] ?? 'fas fa-tasks',
                    ]);
                } else {
                    // Project Referral / Approval Notification
                    $actionType = $data['status'] ?? 'referral';
                    $linkParams = $getDeepLinkParams($actionType);

                    $baseUrl = $data['action_url'] ?? '#';
                    $url = $baseUrl;
                    if ($baseUrl !== '#' && ! str_contains($baseUrl, '?')) {
                        $url .= '?'.http_build_query($linkParams);
                    } elseif ($baseUrl !== '#' && str_contains($baseUrl, '?')) {
                        $url .= '&'.http_build_query($linkParams);
                    }

                    $allNotifications->push([
                        'id' => $notif->id,
                        'type' => 'database',
                        'action_type' => $actionType,
                        'title' => $data['project_name'] ?? ($data['form_number'] ?? 'إحالة مشروع'),
                        'desc' => $data['message'] ?? 'تنبيه جديد',
                        'user' => $data['referring_user'] ?? ($data['responding_user'] ?? 'النظام'),
                        'date_raw' => $notif->created_at,
                        'date' => $notif->created_at->diffForHumans(),
                        'url' => $url,
                        'is_unread' => $notif->unread(),
                        'icon' => 'fas fa-exchange-alt',
                    ]);
                }
            }

            // Sort and take 20
            $sorted = $allNotifications->sortByDesc('date_raw')->take(20)->values();
            $unreadCount = $sorted->where('is_unread', true)->count();

            return response()->json([
                'success' => true,
                'notifications' => $sorted,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading latest notifications: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل الإشعارات',
            ], 500);
        }
    }
}
