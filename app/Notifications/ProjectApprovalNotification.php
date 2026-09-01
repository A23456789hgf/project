<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('مرحباً '.$notifiable->name)
            ->line($this->data['message']);

        if (isset($this->data['action_url'])) {
            $message->action('عرض المشروع', $this->data['action_url']);
        }

        // Add additional details based on notification type
        if (isset($this->data['required_action'])) {
            $message->line('**الإجراء المطلوب:** '.$this->data['required_action']);
        }

        if (isset($this->data['rejection_reason'])) {
            $message->line('**سبب الرفض:** '.$this->data['rejection_reason']);
        }

        if (isset($this->data['notes'])) {
            $message->line('**ملاحظات:** '.$this->data['notes']);
        }

        $message->line('شكراً لاستخدامك نظام إدارة المشاريع.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return $this->data;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    /**
     * Get notification subject based on type
     */
    private function getSubject(): string
    {
        $subjects = [
            'stage_transition' => 'انتقال المشروع إلى مرحلة جديدة',
            'review_assigned' => 'تعيين مراجعة جديدة',
            'action_required' => 'مطلوب إجراء على مشروع',
            'rejected' => 'تم رفض مشروع',
            'resubmitted' => 'تم إعادة تقديم مشروع',
        ];

        return $subjects[$this->data['type']] ?? 'إشعار من نظام المشاريع';
    }
}
