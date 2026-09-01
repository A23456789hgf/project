<?php

namespace App\Providers;

use App\Console\Commands\PermissionsListCommand;
use App\Console\Commands\PermissionsSyncCommand;
use App\Services\PermissionDiscoveryService;
use App\Services\PermissionInferenceRules;
use App\Services\PermissionService;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register inference rules as singleton
        $this->app->singleton(PermissionInferenceRules::class);

        // Register discovery service as singleton
        $this->app->singleton(PermissionDiscoveryService::class, function ($app) {
            return new PermissionDiscoveryService(
                $app->make(PermissionInferenceRules::class)
            );
        });

        // Register commands
        $this->commands([
            PermissionsSyncCommand::class,
            PermissionsListCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/permissions.php' => config_path('permissions.php'),
        ], 'permissions-config');

        // Auto-sync on boot if enabled (development only)
        if (config('permissions.auto_discovery.sync_on_boot', false) && $this->app->environment('local')) {
            $this->autoSync();
        }
    }

    /**
     * Auto-sync permissions on boot (development only).
     */
    protected function autoSync(): void
    {
        try {
            // Only sync if routes are cached or in development
            if ($this->app->routesAreCached() || $this->app->environment('local')) {
                $discovery = $this->app->make(PermissionDiscoveryService::class);

                if ($discovery->isEnabled()) {
                    // Perform silent sync in background
                    // This is simplified - in production you'd want to queue this
                    $discovered = $discovery->discoverPermissions();

                    $service = $this->app->make(PermissionService::class);
                    foreach ($discovered as $permission) {
                        $service->syncPermission($permission);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the application
            logger()->error('Permission auto-sync failed: '.$e->getMessage());
        }
    }
}
