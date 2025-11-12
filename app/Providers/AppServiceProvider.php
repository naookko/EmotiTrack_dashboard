<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AnalyticsApi;
use App\Services\MockAnalyticsApi;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $useMock = (bool) env('USE_MOCK_ANALYTICS', true); // true por defecto
        if ($useMock) {
            $this->app->bind(AnalyticsApi::class, function () {
                // opcional: devolvemos Mock como si fuera el real
                return new class extends MockAnalyticsApi {};
            });
        } else {
            $this->app->bind(AnalyticsApi::class, fn() => new AnalyticsApi());
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
