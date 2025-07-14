<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\CurrentTenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function __construct(
        protected CurrentTenantService $currentTenant
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ➜ приклад: беремо субдомен foo.example.com  → foo
        $subdomain = explode('.', $request->getHost())[0] ?? null;

        if ($subdomain && $tenant = Tenant::where('domain', $subdomain)->first()) {
            $this->currentTenant->setTenant($tenant);   // ⚠️ подія TenantSwitched з-під капота
        }

        return $next($request);
    }
}
