<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewExportLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:logs 
                            {--module=* : Filter by module (e.g., Governorates, Directorates)}
                            {--type=* : Filter by log type (info, error, warning, critical)}
                            {--lines=50 : Number of lines to show}
                            {--today : Show only today\'s logs}
                            {--live : Watch logs in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View Excel export logs with filtering options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logFile = storage_path('logs/laravel.log');

        if (! File::exists($logFile)) {
            $this->error('Log file not found: '.$logFile);

            return 1;
        }

        if ($this->option('live')) {
            $this->watchLogs($logFile);

            return 0;
        }

        $this->displayLogs($logFile);

        return 0;
    }

    private function displayLogs($logFile)
    {
        $lines = $this->option('lines');
        $modules = $this->option('module');
        $types = $this->option('type');
        $todayOnly = $this->option('today');

        $this->info('📊 Excel Export Logs Viewer');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Read log file
        $logContent = File::get($logFile);
        $logLines = explode("\n", $logContent);

        // Filter export-related logs
        $exportLogs = [];
        $currentDate = now()->format('Y-m-d');

        foreach ($logLines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Check if line contains export-related keywords
            if (strpos($line, 'Excel Export') !== false ||
                strpos($line, 'GovernoratesExport') !== false ||
                strpos($line, 'DirectoratesExport') !== false ||
                strpos($line, 'Export') !== false) {

                // Filter by today if requested
                if ($todayOnly && strpos($line, $currentDate) === false) {
                    continue;
                }

                // Filter by module if specified
                if (! empty($modules)) {
                    $moduleMatch = false;
                    foreach ($modules as $module) {
                        if (stripos($line, $module) !== false) {
                            $moduleMatch = true;
                            break;
                        }
                    }
                    if (! $moduleMatch) {
                        continue;
                    }
                }

                // Filter by log type if specified
                if (! empty($types)) {
                    $typeMatch = false;
                    foreach ($types as $type) {
                        if (stripos($line, 'local.'.strtoupper($type)) !== false) {
                            $typeMatch = true;
                            break;
                        }
                    }
                    if (! $typeMatch) {
                        continue;
                    }
                }

                $exportLogs[] = $line;
            }
        }

        // Get the last N lines
        $exportLogs = array_slice($exportLogs, -$lines);

        if (empty($exportLogs)) {
            $this->warn('No export logs found matching the criteria.');

            return;
        }

        $this->info('Found '.count($exportLogs).' export log entries:');
        $this->line('');

        // Display logs with color coding
        foreach ($exportLogs as $log) {
            $this->displayFormattedLog($log);
        }

        // Show summary
        $this->line('');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->showLogSummary($exportLogs);
    }

    private function displayFormattedLog($log)
    {
        // Extract log level and message
        if (preg_match('/\[(.*?)\] local\.(\w+): (.*)/', $log, $matches)) {
            $timestamp = $matches[1];
            $level = strtoupper($matches[2]);
            $message = $matches[3];

            // Color code based on log level
            switch ($level) {
                case 'ERROR':
                    $this->line("<fg=red>🔴 [$timestamp] $level:</> $message");
                    break;
                case 'CRITICAL':
                    $this->line("<fg=red;options=bold>🚨 [$timestamp] $level:</> $message");
                    break;
                case 'WARNING':
                    $this->line("<fg=yellow>🟡 [$timestamp] $level:</> $message");
                    break;
                case 'INFO':
                    if (strpos($message, 'Success') !== false) {
                        $this->line("<fg=green>✅ [$timestamp] $level:</> $message");
                    } else {
                        $this->line("<fg=blue>ℹ️  [$timestamp] $level:</> $message");
                    }
                    break;
                default:
                    $this->line("[$timestamp] $level: $message");
            }
        } else {
            $this->line($log);
        }
    }

    private function showLogSummary($logs)
    {
        $summary = [
            'SUCCESS' => 0,
            'ERROR' => 0,
            'WARNING' => 0,
            'INFO' => 0,
            'CRITICAL' => 0,
        ];

        foreach ($logs as $log) {
            if (strpos($log, 'Excel Export Success') !== false) {
                $summary['SUCCESS']++;
            } elseif (strpos($log, 'local.ERROR') !== false) {
                $summary['ERROR']++;
            } elseif (strpos($log, 'local.CRITICAL') !== false) {
                $summary['CRITICAL']++;
            } elseif (strpos($log, 'local.WARNING') !== false) {
                $summary['WARNING']++;
            } elseif (strpos($log, 'local.INFO') !== false) {
                $summary['INFO']++;
            }
        }

        $this->info('📈 Summary:');
        $this->line('✅ Successful Exports: '.$summary['SUCCESS']);
        $this->line('🔴 Failed Exports: '.$summary['ERROR']);
        $this->line('🚨 Critical Errors: '.$summary['CRITICAL']);
        $this->line('🟡 Warnings: '.$summary['WARNING']);
        $this->line('ℹ️  Info Messages: '.$summary['INFO']);
    }

    private function watchLogs($logFile)
    {
        $this->info('👀 Watching export logs in real-time... (Press Ctrl+C to stop)');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $lastSize = filesize($logFile);

        while (true) {
            clearstatcache();
            $currentSize = filesize($logFile);

            if ($currentSize > $lastSize) {
                $handle = fopen($logFile, 'r');
                fseek($handle, $lastSize);

                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    if (strpos($line, 'Excel Export') !== false ||
                        strpos($line, 'GovernoratesExport') !== false ||
                        strpos($line, 'DirectoratesExport') !== false) {
                        $this->displayFormattedLog($line);
                    }
                }

                fclose($handle);
                $lastSize = $currentSize;
            }

            usleep(500000); // Sleep for 0.5 seconds
        }
    }
}
