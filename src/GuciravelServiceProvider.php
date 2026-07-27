<?php

namespace Ginganomercy\Guciravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Ginganomercy\Guciravel\Middleware\InjectHealerAlert;

class GuciravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(HealerEngine::class, function ($app) {
            return new HealerEngine();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Always register views so they can be rendered in tests or templates
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'guciravel');

        // Guciravel ONLY runs in local/testing environment AND when debug is true, NOT in general console commands
        // This guarantees zero overhead and prevents info leaks in production.
        if (($this->app->environment('local') || $this->app->environment('testing')) 
            && config('app.debug') === true 
            && (!$this->app->runningInConsole() || $this->app->runningUnitTests())) {
            
            // 1. Start listening to database queries
            $this->app->make(HealerEngine::class)->listen();

            // 2. Register Middleware to inject the visual alert globally
            $kernel = $this->app->make(Kernel::class);
            $kernel->pushMiddleware(InjectHealerAlert::class);
        }
    }
}
