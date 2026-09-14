<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    /**
     * Create a new notification instance.
     *
     * Expected array keys:
     * - type: string (e.g. 'correspondence', 'project_request', 'memoir', 'plan', 'execution', 'task', 'user', 'suggestion', 'request_descend', 'value_chain', 'lookup')
     * - action_type: string (e.g. 'created', 'updated', 'deleted', 'approved', 'rejected', 'replied', 'forwarded', 'transferred', 'assigned', 'completed', 'status_changed')
     * - title: string (e.g. subject or record title)
     * - message: string (detailed Arabic message)
     * - action_url: string (URL to view the item)
     * - page_name: string|null (Arabic screen name)
     * - icon: string|null (FontAwesome icon class)
     * - causer_id: int|null
     * - causer_name: string|null
     * - extra: array|null
     */
    public function __construct(array $data)
    {
        $this->data = array_merge([
            'type' => 'general',
            'action_type' => 'info',
            'title' => 'إشعار جديد',
            'message' => 'يوجد تحديث جديد في النظام',
            'action_url' => '#',
            'page_name' => null,
            'icon' => 'fas fa-bell',
            'causer_id' => auth()->id(),
            'causer_name' => auth()->user()?->name ?? 'النظام',
            'created_at' => now()->toIso8601String(),
        ], $data);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->data['title'] ?? 'إشعار من نظام إدارة المشاريع')
            ->greeting('مرحباً '.($notifiable->name ?? 'المستخدم'))
            ->line($this->data['message'] ?? '');

        if (! empty($this->data['action_url']) && $this->data['action_url'] !== '#') {
            $mail->action('عرض التفاصيل', $this->data['action_url']);
        }

        $mail->line('شكراً لاستخدامك نظام متابعة المشاريع والمراسلات.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->data;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->data;
    }
}
