<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Role directive
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        // Has Any Role directive
        Blade::if('hasanyrole', function ($roles) {
            return auth()->check() && auth()->user()->hasAnyRole($roles);
        });

        // Has All Roles directive
        Blade::if('hasallroles', function ($roles) {
            if (! auth()->check()) {
                return false;
            }
            $user = auth()->user();

            foreach ((array) $roles as $role) {
                if (! $user->hasRole($role)) {
                    return false;
                }
            }

            return true;
        });

        // Permission directives removed here as they are already defined in AppServiceProvider

        // Has Any Permission
        Blade::if('anypermission', function ($permissions) {
            return auth()->check() && auth()->user()->hasAnyPermission($permissions);
        });
    }
}
