<?php

namespace App\Services;

use App\Models\InternalEntity;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Log a task activity.
     */
    public function logActivity(Task $task, string $eventType, ?object $subject = null, ?array $properties = null): void
    {
        TaskActivity::create([
            'task_id' => $task->id,
            'event_type' => $eventType,
            'causer_id' => auth()->id(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
        ]);
    }

    /**
     * Notify assignees about a task event.
     */
    public function notifyAssignees(Task $task, string $actionType, string $message, string $icon = 'fas fa-tasks'): void
    {
        $usersToNotify = collect();

        // 1. Notify directly assigned users
        if ($task->assignees()->exists()) {
            foreach ($task->assignees as $user) {
                if ($user && $user->hasPermission('task.view')) {
                    $usersToNotify->push($user);
                }
            }
        }
        // 2. Notify assigned entity users
        elseif ($task->assigned_entity_id) {
            $entityIds = InternalEntity::getAllChildrenIds($task->assigned_entity_id);
            $entityUsers = User::active()
                ->whereIn('entity_id', $entityIds)
                ->get()
                ->filter(function ($u) {
                    return $u->hasPermission('task.view');
                });
            $usersToNotify = $usersToNotify->merge($entityUsers);
        }
        // 3. Notify department if no direct user is assigned
        elseif ($task->project_entities_id) {
            // Determine assignable users in the creator's administrative scope
            $creator = User::find($task->created_by ?? auth()->id());
            if ($creator && $creator->entity_id) {
                $entityIds = InternalEntity::getAllChildrenIds($creator->entity_id);
                $deptUsers = User::active()
                    ->whereIn('entity_id', $entityIds)
                    ->get()
                    ->filter(function ($u) {
                        return $u->hasPermission('task.view');
                    });

                $usersToNotify = $usersToNotify->merge($deptUsers);
            }
        }

        $usersToNotify = $usersToNotify->unique('id');
        $actionUrl = $task->project_id ? route('projects.tasks.show', [$task->project_id, $task->id]) : route('tasks.index');

        $actualUsersToNotify = $usersToNotify->filter(function ($user) {
            return app()->runningInConsole() || $user->id !== auth()->id();
        });

        foreach ($actualUsersToNotify as $user) {
            $user->notify(new TaskNotification(
                $task,
                $actionType,
                'task_assignment',
                $message,
                $actionUrl,
                $icon
            ));
        }

        // Send SMS notifications for creation and updates
        if ($actionType === 'created' || $actionType === 'updated') {
            try {
                $smsService = app(SmppSmsService::class);
                $smsService->sendToMultiple($actualUsersToNotify, $message, 'task_assignment');
            } catch (\Exception $e) {
                Log::error('Failed to send task assignment SMS: '.$e->getMessage());
            }
        }
    }

    /**
     * Parse mentions in text and notify users.
     */
    public function parseAndNotifyMentions(Task $task, string $text, string $actionType, string $actionUrl): void
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $text, $matches);
        $usernames = array_unique($matches[1]);

        if (empty($usernames)) {
            return;
        }

        $users = User::whereIn('username', $usernames)->get();

        foreach ($users as $user) {
            $user->notify(new TaskNotification(
                $task,
                $actionType,
                'user_mention',
                "تمت الإشارة إليك في المهمة: {$task->title}",
                $actionUrl,
                'fas fa-at'
            ));
        }
    }

    /**
     * Store signature file.
     */
    public function storeSignature(UploadedFile $file): string
    {
        return $file->store('task_signatures', 'public');
    }

    /**
     * Store task attachment.
     */
    public function storeAttachment(UploadedFile $file): array
    {
        $path = $file->store('task_attachments', 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
        ];
    }
}
