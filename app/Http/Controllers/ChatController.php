<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\InternalEntity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get a list of users to chat with.
     * We'll return recent chat partners first, then a list of all active users.
     * Filtered by allowed internal entities based on user's administrative and geographic scopes.
     */
    public function getUsers(Request $request)
    {
        $currentUserId = Auth::id();
        $currentUser = auth()->user();

        // =====================================================
        // 1. FULL ACCESS CHECK (Super Admin)
        // =====================================================
        $hasFullAccess = method_exists($currentUser, 'hasRole')
            ? $currentUser->hasRole('super-admin')
            : ($currentUser->is_super_admin ?? false);

        // =====================================================
        // 2. GET ENTITY IDS (فلترة المستخدم)
        // =====================================================
        $entityIds = $this->getUserEntityIds($currentUser, $hasFullAccess);

        // =====================================================
        // 3. BUILD RECENT USERS QUERY (المستخدمين الذين تواصلنا معهم)
        // =====================================================

        // Use two indexed queries instead of full table scan with IF() expression
        $senders = ChatMessage::where('receiver_id', $currentUserId)->distinct()->pluck('sender_id');
        $receivers = ChatMessage::where('sender_id', $currentUserId)->distinct()->pluck('receiver_id');
        $recentUsersIds = $senders->merge($receivers)->unique()->filter(fn ($id) => $id != $currentUserId);

        $recentUsersQuery = User::whereIn('id', $recentUsersIds)
            ->where('status', 'Active');

        // تطبيق فلترة الكيانات إذا لم يكن سوبر أدمن
        if (! $hasFullAccess && ! empty($entityIds)) {
            $recentUsersQuery->whereIn('entity_id', $entityIds);
        } elseif (! $hasFullAccess && empty($entityIds)) {
            return response()->json([
                'recent' => [],
                'users' => [],
            ]);
        }

        // Fetch unread counts in a single aggregated query to prevent N+1
        $unreadCounts = ChatMessage::where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->selectRaw('sender_id, count(*) as count')
            ->pluck('count', 'sender_id');

        $recentUsers = $recentUsersQuery
            ->select('id', 'name', 'user_id', 'signature_path')
            ->get()
            ->map(function ($user) use ($unreadCounts) {
                $user->unread_count = $unreadCounts[$user->id] ?? 0;

                return $user;
            });

        // =====================================================
        // 4. SEARCH FOR USERS (إذا تم تقديم بحث)
        // =====================================================

        if ($request->filled('search')) {
            $search = $request->search;
            $allUsersQuery = User::where('status', 'Active')
                ->where('id', '!=', $currentUserId);

            // تطبيق فلترة الكيانات إذا لم يكن سوبر أدمن
            if (! $hasFullAccess && ! empty($entityIds)) {
                $allUsersQuery->whereIn('entity_id', $entityIds);
            } elseif (! $hasFullAccess && empty($entityIds)) {
                // إذا كانت entityIds فارغة وليس سوبر أدمن، نعيد قائمة فارغة
                return response()->json([
                    'recent' => $recentUsers, // قد يكون لديه recent من قبل ولكن بدون صلاحية؟ لكننا أعدنا أعلاه إذا كانت فارغة، لكن هنا قد تكون الحالة مختلفة لو تم الدخول للبحث بعد أن كان لديه recent؟ الأفضل توحيد المنطق: إذا كانت entityIds فارغة وليس سوبر أدمن، نرجع فارغ.
                    'users' => [],
                ]);
            }

            $allUsers = $allUsersQuery
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('user_id', 'like', "%{$search}%");
                })
                ->select('id', 'name', 'user_id', 'signature_path')
                ->limit(20)
                ->get();
        } else {
            $allUsers = collect();
        }

        return response()->json([
            'recent' => $recentUsers,
            'users' => $allUsers,
        ]);
    }

    /**
     * Get messages between authenticated user and another user.
     */
    public function getMessages($userId)
    {
        $currentUserId = Auth::id();

        // Mark messages as read
        ChatMessage::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = ChatMessage::where(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $currentUserId)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $userId)
                    ->where('receiver_id', $currentUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Poll for new messages for the current user.
     * Heavily optimized for frequent calling.
     */
    public function poll(Request $request)
    {
        $request->session()->save(); // Release session lock for long polling

        $currentUserId = Auth::id();
        $lastId = $request->get('last_id', 0);
        $activeChatUserId = $request->get('active_user_id');

        // Only fetch messages directed to current user that are strictly newer than last_id
        $query = ChatMessage::where('receiver_id', $currentUserId)
            ->where('id', '>', $lastId);

        // If polling for a specific open chat, we can mark them read automatically
        if ($activeChatUserId) {
            $query->where('sender_id', $activeChatUserId);
            $newMessages = $query->orderBy('id', 'asc')->get();

            if ($newMessages->count() > 0) {
                // Mark them as read
                ChatMessage::whereIn('id', $newMessages->pluck('id'))->update(['is_read' => true]);
            }
        } else {
            $newMessages = $query->orderBy('id', 'asc')->get();
        }

        // Get total global unread count
        $totalUnread = ChatMessage::where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->count();

        // Get unread count grouped by sender (for badges)
        $unreadBySender = ChatMessage::where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->select('sender_id', DB::raw('count(*) as count'))
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');

        return response()->json([
            'new_messages' => $newMessages,
            'total_unread' => $totalUnread,
            'unread_by_sender' => $unreadBySender,
        ]);
    }

    // =====================================================
    // 🔥 دالة الفلترة (Geo + Admin) - نفس المستخدمة في HomeController
    // =====================================================
    protected function getUserEntityIds($user, $hasFullAccess): array
    {
        // 🟢 Super Admin → يرى الكل
        if ($hasFullAccess) {
            return [];
        }

        // =====================================================
        // 1. Administrative Scope
        // =====================================================
        $entityIdsByEnt = InternalEntity::getAllChildrenIds(
            $user->administrative_scope_id
        ) ?? [];

        // =====================================================
        // 2. Geographic Scope
        // =====================================================
        $entityIdsByGeo = [];

        foreach ($user->geographicScopes as $scope) {
            if (! empty($scope->governorate_id) && empty($scope->directorate_id)) {
                $ids = InternalEntity::getAllByGovernorate($scope->governorate_id);
                if (! empty($ids)) {
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                }
            } elseif (! empty($scope->directorate_id)) {
                $ids = InternalEntity::getAllByDirectorate($scope->directorate_id);
                if (! empty($ids)) {
                    $entityIdsByGeo = array_merge($entityIdsByGeo, $ids);
                }
            }
        }

        // =====================================================
        // 3. دمج النتائج وإزالة التكرارات
        // =====================================================
        $entityIds = array_merge($entityIdsByEnt, $entityIdsByGeo);

        return array_values(array_unique($entityIds));
    }
}
