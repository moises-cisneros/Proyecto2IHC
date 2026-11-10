<?php

namespace App\Providers;

use App\Contracts\ComprobanteScannerInterface;
use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Plan;
use App\Policies\GastoPolicy;
use App\Policies\PagoPolicy;
use App\Policies\PlanPolicy;
use App\Services\Comprobante\FakeComprobanteScanner;
use App\Services\Comprobante\GeminiComprobanteScanner;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ComprobanteScannerInterface::class, function ($app) {
            $driver = config('services.comprobante_scanner.driver', 'fake');

            if ($driver === 'gemini' && ! empty(config('services.gemini.key'))) {
                return new GeminiComprobanteScanner;
            }

            return new FakeComprobanteScanner;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Gasto::class, GastoPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);
    }
}
