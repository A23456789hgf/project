<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Services\PermissionDiscoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PermissionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync
                            {--dry-run : Preview changes without saving to database}
                            {--module= : Sync permissions for specific module only}
                            {--remove-orphaned : Remove auto-registered permissions that no longer have routes}
                            {--force : Force sync even if auto-discovery is disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and sync permissions from routes';

    protected PermissionDiscoveryService $discoveryService;

    /**
     * Execute the console command.
     */
    public function handle(PermissionDiscoveryService $discoveryService): int
    {
        $this->discoveryService = $discoveryService;

        // Check if enabled
        if (! $discoveryService->isEnabled() && ! $this->option('force')) {
            $this->error('Permission auto-discovery is disabled. Use --force to override.');

            return self::FAILURE;
        }

        $this->info('🔍 Scanning routes for permissions...');

        // Discover permissions
        $discovered = $discoveryService->discoverPermissions();

        // Filter by module if specified
        if ($module = $this->option('module')) {
            $discovered = array_filter($discovered, fn ($p) => $p['module'] === $module);
            $this->info("Filtered to module: {$module}");
        }

        $this->info('✓ Found '.count($discovered).' permissions');
        $this->newLine();

        // Get existing permissions
        $existing = Permission::pluck('id', 'slug')->toArray();

        // Categorize changes
        $toCreate = [];
        $toUpdate = [];
        $created = 0;
        $updated = 0;

        foreach ($discovered as $permission) {
            if (! isset($existing[$permission['slug']])) {
                $toCreate[] = $permission;
            } else {
                // Check if update needed
                $existingPermission = Permission::where('slug', $permission['slug'])->first();
                if ($this->needsUpdate($existingPermission, $permission)) {
                    $toUpdate[] = [
                        'id' => $existingPermission->id,
                        'data' => $permission,
                    ];
                }
            }
        }

        // Display what will be created
        if (! empty($toCreate)) {
            $this->info('📝 New permissions to create:');
            foreach ($toCreate as $perm) {
                $this->line("  • {$perm['slug']} ({$perm['name']}) - {$perm['source']}");
            }
            $this->newLine();
        }

        // Display what will be updated
        if (! empty($toUpdate)) {
            $this->info('🔄 Permissions to update:');
            foreach ($toUpdate as $update) {
                $this->line("  • {$update['data']['slug']}");
            }
            $this->newLine();
        }

        // Handle orphaned permissions
        $removed = 0;
        if ($this->option('remove-orphaned')) {
            $discoveredSlugs = array_column($discovered, 'slug');
            $orphaned = Permission::where('auto_registered', true)
                ->whereNotIn('slug', $discoveredSlugs)
                ->get();

            if ($orphaned->count() > 0) {
                $this->warn('⚠️  Orphaned permissions (no longer in routes):');
                foreach ($orphaned as $perm) {
                    $this->line("  • {$perm->slug} ({$perm->name})");
                }
                $this->newLine();
            }
        }

        // Dry run check
        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run - no changes made');
            $this->info('Would create: '.count($toCreate));
            $this->info('Would update: '.count($toUpdate));
            if ($this->option('remove-orphaned')) {
                $this->info('Would remove: '.($orphaned->count() ?? 0));
            }

            return self::SUCCESS;
        }

        // Confirm before proceeding
        if (! $this->option('no-interaction')) {
            if (! $this->confirm('Proceed with sync?', true)) {
                $this->info('Cancelled');

                return self::SUCCESS;
            }
        }

        // Perform sync
        DB::beginTransaction();
        try {
            // Create new permissions
            foreach ($toCreate as $permission) {
                Permission::create([
                    'slug' => $permission['slug'],
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'module' => $permission['module'],
                    'auto_registered' => true,
                    'controller_class' => $permission['controller_class'],
                    'controller_method' => $permission['controller_method'],
                ]);
                $created++;
            }

            // Update existing permissions
            foreach ($toUpdate as $update) {
                $perm = Permission::find($update['id']);
                $perm->update([
                    'name' => $update['data']['name'],
                    'description' => $update['data']['description'],
                    'module' => $update['data']['module'],
                    'controller_class' => $update['data']['controller_class'],
                    'controller_method' => $update['data']['controller_method'],
                ]);
                $updated++;
            }

            // Remove orphaned if requested
            if ($this->option('remove-orphaned') && isset($orphaned)) {
                $removed = $orphaned->count();
                Permission::whereIn('id', $orphaned->pluck('id'))->delete();
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Sync complete!');
            $this->info("Created: {$created} | Updated: {$updated} | Removed: {$removed}");

            // Show statistics
            $this->displayStatistics($discoveryService);

            return self::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Sync failed: '.$e->getMessage());
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Check if permission needs update.
     */
    protected function needsUpdate(Permission $existing, array $discovered): bool
    {
        return $existing->name !== $discovered['name']
            || $existing->description !== $discovered['description']
            || $existing->module !== $discovered['module']
            || $existing->controller_class !== $discovered['controller_class']
            || $existing->controller_method !== $discovered['controller_method'];
    }

    /**
     * Display discovery statistics.
     */
    protected function displayStatistics(PermissionDiscoveryService $service): void
    {
        $stats = $service->getDiscoveryStatistics();

        $this->newLine();
        $this->info('📊 Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Permissions', $stats['total']],
                ['From Attributes', $stats['by_source']['attribute']],
                ['From Inference', $stats['by_source']['inference']],
            ]
        );

        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('By Module:');
            foreach (array_slice($stats['by_module'], 0, 10) as $module => $count) {
                $this->line("  {$module}: {$count}");
            }
        }
    }
}
