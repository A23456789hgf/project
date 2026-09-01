<?php

namespace App\Console;

use App\Console\Commands\ErpSyncAllProjects;
use App\Console\Commands\FetchErpUnits;
use App\Console\Commands\SendTaskReminders;
use App\Console\Commands\SyncProjectsToErpNext;
use App\Console\Commands\SyncProjectsToFrappe;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        FetchErpUnits::class,
        ErpSyncAllProjects::class,
        SyncProjectsToErpNext::class,
        SyncProjectsToFrappe::class,
        SendTaskReminders::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // مزامنة المشاريع إلى ERPNext كل ساعة
        $schedule->command('projects:sync-to-erpnext')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/erpnext-sync.log'));

        // مزامنة المشاريع في مرحلة التنفيذ إلى Frappe API كل 30 دقيقة
        $schedule->command('frappe:sync-projects')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/frappe-sync-schedule.log'));

        // إرسال تذكيرات يومية للمهام التي اقترب موعد تسليمها أو تأخرت
        $schedule->command('tasks:send-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/task-reminders.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // تحميل جميع الأوامر من مجلد Commands
        $this->load(__DIR__.'/Commands');

        // تحميل أي تعريفات إضافية للأوامر من routes/console.php
        require base_path('routes/console.php');
    }
}
