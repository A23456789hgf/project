<?php

namespace App\Providers;

use App\Services\FrappeAPIService;
use Illuminate\Support\ServiceProvider;

class FrappeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(FrappeAPIService::class, function ($app) {
            return new FrappeAPIService(
                config('services.frappe.url'),
                config('services.frappe.api_key'),
                config('services.frappe.api_secret')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
