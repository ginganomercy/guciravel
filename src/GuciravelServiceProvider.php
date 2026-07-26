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
        // Guciravel ONLY runs in local environment to guarantee zero overhead in production.
        if ($this->app->environment('local') || $this->app->environment('testing')) {
            
            // 1. Start listening to database queries
            $this->app->make(HealerEngine::class)->listen();

            // 2. Register Middleware to inject the visual alert globally
            $kernel = $this->app->make(Kernel::class);
            $kernel->pushMiddleware(InjectHealerAlert::class);

            // 3. Load Views for the alert component
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'guciravel');
        }
    }
}
