<?php

namespace App\Console\Commands;

use App\Services\PermissionAuditService;
use App\Services\PermissionAutomationService;
use Illuminate\Console\Command;

class AuditPermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'permissions:audit {--fix : Auto-register missing permissions}';

    /**
     * The console command description.
     */
    protected $description = 'Audit the codebase for missing permissions and optionally fix them.';

    /**
     * Execute the console command.
     */
    public function handle(PermissionAuditService $auditService, PermissionAutomationService $autoService)
    {
        $this->info('Scanning controllers for missing permissions...');

        $gaps = $auditService->auditControllerMethods();

        if (empty($gaps)) {
            $this->info('No missing permissions found. System is fully protected.');

            return Command::SUCCESS;
        }

        $this->warn(count($gaps).' missing permissions identified:');

        $headers = ['Controller', 'Method', 'Suggested Slug'];
        $data = [];
        foreach ($gaps as $gap) {
            $data[] = [
                str_replace('App\\Http\\Controllers\\', '', $gap['controller']),
                $gap['method'],
                $gap['suggested_slug'],
            ];
        }

        $this->table($headers, $data);

        if ($this->option('fix')) {
            if ($this->confirm('Do you want to auto-register these permissions?')) {
                $count = $autoService->autoRegisterGaps($gaps);
                $this->info("Successfully registered {$count} permissions.");
            }
        } else {
            $this->info('Run with --fix to automatically register these permissions.');
        }

        return Command::SUCCESS;
    }
}
