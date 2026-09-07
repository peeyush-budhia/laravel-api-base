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
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limits for public authentication endpoints.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for(
            'auth.login',
            fn (Request $request): array => [
                Limit::perMinute(5)->by(
                    'login:account:'.$this->accountKey($request, 'login'),
                ),
                Limit::perMinute(20)->by('login:ip:'.$request->ip()),
            ],
        );

        RateLimiter::for(
            'auth.forgot-password',
            fn (Request $request): array => [
                Limit::perMinute(3)->by(
                    'forgot-password:account:'.$this->accountKey($request, 'email'),
                ),
                Limit::perMinute(10)->by('forgot-password:ip:'.$request->ip()),
            ],
        );

        RateLimiter::for(
            'auth.reset-password',
            fn (Request $request): array => [
                Limit::perMinute(5)->by(
                    'reset-password:account:'.$this->accountKey($request, 'email'),
                ),
                Limit::perMinute(10)->by('reset-password:ip:'.$request->ip()),
            ],
        );
    }

    /**
     * Build a privacy-safe rate-limit key for an account identifier.
     */
    private function accountKey(Request $request, string $field): string
    {
        $value = $request->input($field);
        $identifier = is_string($value)
            ? mb_strtolower(trim($value))
            : '';

        return hash('sha256', $identifier);
    }
}
