<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\AuditEvent;
use App\Enums\UserStatus;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\EnumTransformer;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Schema;
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
        $this->configureOpenApi();
    }

    /**
     * Register enum schemas consumed by generated API clients.
     */
    private function configureOpenApi(): void
    {
        if (! class_exists(Scramble::class)) {
            return;
        }

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            foreach ([UserStatus::class, AuditEvent::class] as $enum) {
                $schemaName = class_basename($enum);

                if ($openApi->components->hasSchema($schemaName)) {
                    continue;
                }

                $openApi->components->addSchema(
                    $schemaName,
                    Schema::fromType(EnumTransformer::make($enum)->transform()),
                );
            }
        });
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
