<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Services\PermissionDiscoveryService;
use Illuminate\Console\Command;

class PermissionsListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:list
                            {--module= : Filter by module}
                            {--auto-only : Show only auto-registered permissions}
                            {--manual-only : Show only manually created permissions}
                            {--format=table : Output format (table, json, csv)}
                            {--search= : Search permissions by slug or name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all permissions in the system';

    /**
     * Execute the console command.
     */
    public function handle(PermissionDiscoveryService $discoveryService): int
    {
        $this->info('📋 Listing Permissions');
        $this->newLine();

        // Build query
        $query = Permission::query()->orderBy('module')->orderBy('slug');

        // Apply filters
        if ($module = $this->option('module')) {
            $query->where('module', $module);
            $this->info("Filtered by module: {$module}");
        }

        if ($this->option('auto-only')) {
            $query->where('auto_registered', true);
            $this->info('Showing only auto-registered permissions');
        }

        if ($this->option('manual-only')) {
            $query->where('auto_registered', false);
            $this->info('Showing only manually created permissions');
        }

        if ($search = $this->option('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
            $this->info("Search: {$search}");
        }

        $permissions = $query->get();

        if ($permissions->isEmpty()) {
            $this->warn('No permissions found matching criteria');

            return self::SUCCESS;
        }

        $this->info("Found {$permissions->count()} permissions");
        $this->newLine();

        // Output based on format
        $format = $this->option('format');

        switch ($format) {
            case 'json':
                $this->outputJson($permissions);
                break;
            case 'csv':
                $this->outputCsv($permissions);
                break;
            default:
                $this->outputTable($permissions);
        }

        // Show statistics
        $this->displayStatistics($permissions, $discoveryService);

        return self::SUCCESS;
    }

    /**
     * Output permissions as table.
     */
    protected function outputTable($permissions): void
    {
        $grouped = $permissions->groupBy('module');

        foreach ($grouped as $module => $modulePermissions) {
            $moduleTranslation = config("permissions.module_translations.{$module}", $module);
            $this->info("📦 {$moduleTranslation} ({$module})");

            $rows = $modulePermissions->map(function ($perm) {
                return [
                    'slug' => $perm->slug,
                    'name' => $perm->name,
                    'source' => $perm->auto_registered ? '🤖 Auto' : '✋ Manual',
                    'controller' => $perm->controller_class
                        ? class_basename($perm->controller_class).'@'.$perm->controller_method
                        : '-',
                ];
            })->toArray();

            $this->table(
                ['Slug', 'Name', 'Source', 'Controller'],
                $rows
            );
            $this->newLine();
        }
    }

    /**
     * Output permissions as JSON.
     */
    protected function outputJson($permissions): void
    {
        $data = $permissions->map(function ($perm) {
            return [
                'slug' => $perm->slug,
                'name' => $perm->name,
                'description' => $perm->description,
                'module' => $perm->module,
                'auto_registered' => $perm->auto_registered,
                'controller_class' => $perm->controller_class,
                'controller_method' => $perm->controller_method,
            ];
        });

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Output permissions as CSV.
     */
    protected function outputCsv($permissions): void
    {
        // Output header
        $this->line('slug,name,description,module,auto_registered,controller_class,controller_method');

        // Output rows
        foreach ($permissions as $perm) {
            $this->line(implode(',', [
                $this->escapeCsv($perm->slug),
                $this->escapeCsv($perm->name),
                $this->escapeCsv($perm->description),
                $this->escapeCsv($perm->module),
                $perm->auto_registered ? '1' : '0',
                $this->escapeCsv($perm->controller_class ?? ''),
                $this->escapeCsv($perm->controller_method ?? ''),
            ]));
        }
    }

    /**
     * Escape CSV field.
     */
    protected function escapeCsv(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    /**
     * Display statistics.
     */
    protected function displayStatistics($permissions, PermissionDiscoveryService $service): void
    {
        $total = $permissions->count();
        $auto = $permissions->where('auto_registered', true)->count();
        $manual = $permissions->where('auto_registered', false)->count();

        $this->info('📊 Summary:');
        $this->table(
            ['Category', 'Count', 'Percentage'],
            [
                ['Total', $total, '100%'],
                ['Auto-registered', $auto, round(($auto / max($total, 1)) * 100, 1).'%'],
                ['Manual', $manual, round(($manual / max($total, 1)) * 100, 1).'%'],
            ]
        );

        // Module breakdown
        if ($this->option('verbose')) {
            $byModule = $permissions->groupBy('module')
                ->map->count()
                ->sortDesc();

            $this->newLine();
            $this->info('By Module:');
            $this->table(
                ['Module', 'Count'],
                $byModule->map(fn ($count, $module) => [$module, $count])->values()->toArray()
            );
        }

        // Show discovered vs database count
        $discoveredCount = $service->getDiscoveryStatistics()['total'];
        $databaseCount = Permission::where('auto_registered', true)->count();

        if ($discoveredCount !== $databaseCount) {
            $this->newLine();
            $this->warn("⚠️  Mismatch: {$discoveredCount} permissions discovered from routes, but {$databaseCount} auto-registered in database");
            $this->info('Run "php artisan permissions:sync" to update');
        }
    }
}
