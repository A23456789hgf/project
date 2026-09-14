<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFinished extends Notification
{
    use Queueable;

    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('تم الانتهاء من عملية الاستيراد')
            ->line('تفاصيل عملية الاستيراد:')
            ->line('إجمالي السجلات: '.$this->report['total'])
            ->line('نجحت: '.$this->report['successful'])
            ->line('فشلت: '.$this->report['failed']);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'تم الانتهاء من استيراد المحافظات',
            'total' => $this->report['total'],
            'successful' => $this->report['successful'],
            'failed' => $this->report['failed'],
        ];
    }
}
