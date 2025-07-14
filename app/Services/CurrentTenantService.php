<?php

namespace App\Services;

use App\Events\TenantSwitched;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CurrentTenantService
{
    public function __construct(
        protected Request $request
    ) {}

    /* ------------ getter / helper ------------ */

    public function tenant(): ?Tenant
    {
        return $this->request->attributes->get('current_tenant');
    }

    public function hasTenant(): bool
    {
        return (bool) $this->tenant();
    }

    public function isSystemContext(): bool
    {
        return optional(Auth::user())->hasRole('super-admin');
    }

    /* ------------ setter ------------ */

    public function setTenant(Tenant $tenant, bool $dispatch = true): void
    {
        // 1) cache всередині request
        $this->request->attributes->set('current_tenant', $tenant);

        // 2) web-сесія (для Livewire / Jetstream)
        Session::put('current_tenant_id', $tenant->id);

        // 3) Postgres session variable for RLS
        try {
            DB::statement("SET app.current_tenant = '{$tenant->id}'");
        } catch (\Throwable $e) {
            Log::warning('[CurrentTenantService] PG variable not set: '.$e->getMessage());
        }

        // 4) зберегти у users.current_tenant_id (не впливає на токени API)
        if (Auth::check()) {
            Auth::user()->updateQuietly(['current_tenant_id' => $tenant->id]);
        }

        // 5) Event
        if ($dispatch) {
            Event::dispatch(new TenantSwitched($tenant, Auth::user()));
        }
    }

    /* ------------ forget ------------ */

    public function forgetTenant(): void
    {
        $this->request->attributes->remove('current_tenant');
        Session::forget('current_tenant_id');

        try {
            DB::statement('RESET app.current_tenant');
        } catch (\Throwable $e) {
            // silence
        }

        if (Auth::check()) {
            Auth::user()->updateQuietly(['current_tenant_id' => null]);
        }
    }
}
