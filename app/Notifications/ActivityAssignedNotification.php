<?php

namespace App\Notifications;

use App\Models\ActivityAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ActivityAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $assignment;

    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(ActivityAssignment $assignment, string $message)
    {
        $this->assignment = $assignment;
        $this->message = $message;
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
            'task_id' => $this->assignment->id,
            'project_id' => $this->assignment->project_id,
            'task_title' => 'تكليف بمهمة جديدة',
            'action_type' => 'assigned',
            'action_group' => 'execution',
            'message' => $this->message,
            'action_url' => route('projects.execution', $this->assignment->project_id),
            'icon' => 'fas fa-user-check',
            'causer_id' => auth()->id(),
        ];
    }
}
