<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use App\Observers\TenantObserver;
use App\Observers\UserObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /** @var array<string, array<int, class-string>> */
    protected $listen = [
        // 'App\Events\TenantSwitched' => [
        //     \App\Listeners\ClearFilamentCache::class,
        // ],
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Реєструємо Observer
        User::observe(UserObserver::class);
        Tenant::observe(TenantObserver::class);
    }
}
