<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('answers', fn (Request $request): Limit => Limit::perMinute(120)
            ->by('answers:'.($request->user()?->id ?? $request->ip())));

        RateLimiter::for('violations', fn (Request $request): Limit => Limit::perMinute(60)
            ->by('violations:'.($request->user()?->id ?? $request->ip())));
    }
}
