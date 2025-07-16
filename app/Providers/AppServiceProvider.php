<?php

namespace App\Providers;

use App\Services\CurrentTenantService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;

use function globalTeamId;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            CurrentTenantService::class,
            fn ($app) => new CurrentTenantService($app->make(Request::class))
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // усі операції Spatie за замовчанням працюватимуть у «глобальній команді» (=0)
        app(PermissionRegistrar::class)->setPermissionsTeamId(globalTeamId());
    }
}
