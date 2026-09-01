<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    public $actionType;

    public $actionGroup;

    public $message;

    public $actionUrl;

    public $icon;

    public $causer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $actionType, string $actionGroup, string $message, string $actionUrl, string $icon = 'fas fa-tasks', ?User $causer = null)
    {
        $this->task = $task;
        $this->actionType = $actionType;
        $this->actionGroup = $actionGroup;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->icon = $icon;
        $this->causer = $causer ?? auth()->user();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task',
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->project_name ?? null,
            'task_title' => $this->task->title,
            'action_type' => $this->actionType,
            'action_group' => $this->actionGroup,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
            'causer_id' => $this->causer->id ?? null,
            'causer_name' => $this->causer->name ?? 'النظام',
        ];
    }
}
