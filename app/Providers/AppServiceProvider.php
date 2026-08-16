<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Listeners in app/Listeners are auto-discovered by Laravel via their
        // typed `handle()` event parameter — no manual Event::listen() needed.

        // Throttle brute-force attempts against the public Radius endpoint.
        RateLimiter::for('radius', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
