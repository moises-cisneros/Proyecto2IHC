<?php

namespace App\Providers;

use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Plan;
use App\Policies\GastoPolicy;
use App\Policies\PagoPolicy;
use App\Policies\PlanPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Gasto::class, GastoPolicy::class);
        Gate::policy(Pago::class, PagoPolicy::class);
    }
}
