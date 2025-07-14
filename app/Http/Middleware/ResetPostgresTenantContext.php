<?php

namespace App\Http\Middleware;

use App\Services\CurrentTenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetPostgresTenantContext
{
    protected CurrentTenantService $currentTenantService;

    public function __construct(CurrentTenantService $currentTenantService)
    {
        // This middleware is responsible for resetting the tenant context
        // after each request, ensuring that the tenant context does not persist
        // across requests.
        $this->currentTenantService = $currentTenantService; //
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        // Reset the tenant context in the request
        $this->currentTenantService->forgetTenant();
    }
}
