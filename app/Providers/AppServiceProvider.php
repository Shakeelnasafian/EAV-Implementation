<?php

namespace App\Providers;

use App\Support\ApiResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Passport::loadKeysFrom(storage_path('oauth'));

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        // 60 requests/minute per authenticated user or IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(fn () => ApiResponse::error('Too many requests. Please slow down.', 429));
        });

        // Strict limit for auth endpoints — 5 attempts/minute per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn () => ApiResponse::error('Too many login attempts. Try again in a minute.', 429));
        });
    }
}
