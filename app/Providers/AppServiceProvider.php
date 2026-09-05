<?php

namespace App\Providers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
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
        Carbon::serializeUsing(
            static fn (CarbonInterface $date): string => $date->toIso8601String(),
        );

        CarbonImmutable::serializeUsing(
            static fn (CarbonInterface $date): string => $date->toIso8601String(),
        );
    }
}
