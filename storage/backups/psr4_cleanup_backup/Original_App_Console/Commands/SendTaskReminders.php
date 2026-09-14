<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminders for tasks with approaching deadlines';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService)
    {
        $this->info('Starting task reminders check...');

        // Fetch tasks that are not completed/cancelled and have a due date
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_date')
            ->get();

        $notificationsSent = 0;

        foreach ($tasks as $task) {
            $dueDate = Carbon::parse($task->due_date)->startOfDay();
            $today = Carbon::now()->startOfDay();

            if ($dueDate->lt($today)) {
                // Task is overdue
                $daysOverdue = $today->diffInDays($dueDate);
                $message = "تذكير: المهمة '{$task->title}' متأخرة عن موعدها بـ {$daysOverdue} يوم/أيام.";
                $taskService->notifyAssignees($task, 'need_action', $message, 'fas fa-exclamation-triangle');
                $notificationsSent++;
            } elseif ($dueDate->diffInDays($today) <= 3) {
                // Task is due within 3 days
                $daysRemaining = $dueDate->diffInDays($today);

                if ($daysRemaining == 0) {
                    $message = "تذكير: المهمة '{$task->title}' موعد تسليمها اليوم!";
                } else {
                    $message = "تذكير: يتبقى {$daysRemaining} يوم/أيام على موعد تسليم المهمة '{$task->title}'.";
                }

                $taskService->notifyAssignees($task, 'requires_action', $message, 'fas fa-clock');
                $notificationsSent++;
            }
        }

        $this->info("Task reminders check completed. {$notificationsSent} task reminders processed.");

        return Command::SUCCESS;
    }
}
