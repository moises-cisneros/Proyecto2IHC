<?php

namespace App\Providers;

use App\Contracts\ComprobanteScannerInterface;
use App\Mail\Transport\CloudflareMailTransport;
use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Plan;
use App\Policies\GastoPolicy;
use App\Policies\PagoPolicy;
use App\Policies\PlanPolicy;
use App\Services\Comprobante\GeminiComprobanteScanner;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ComprobanteScannerInterface::class, function ($app) {
            return new GeminiComprobanteScanner;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Gasto::class, GastoPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);

        Mail::extend('cloudflare', function (array $config = []) {
            return new CloudflareMailTransport(
                $config['endpoint'] ?? env('MAIL_BRIDGE_ENDPOINT', 'https://planazo-mail-bridge.moisescisnerosdl.workers.dev'),
                $config['secret'] ?? env('MAIL_BRIDGE_SECRET', 'planazo-secure-key-2026')
            );
        });
    }
}
