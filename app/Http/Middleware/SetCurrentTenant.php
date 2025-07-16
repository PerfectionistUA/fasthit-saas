<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\CurrentTenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Встановлює «поточний» Tenant для кожного HTTP‑запиту.
 *
 * Порядок пріоритету
 * ──────────────────
 * 1. **API + Sanctum‑токен**  → достовірний tenant за ability `tid:{id}`.
 * 2. **WEB‑сесія**            → `current_tenant_id` або єдиний tenant користувача.
 * 3. **Домен / Субдомен**    → публічний режим (white‑label CNAME / *.fasthit.com.ua).
 */
class SetCurrentTenant
{
    public function __construct(
        protected CurrentTenantService $currentTenant,
    ) {}

    /**
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /* ────────────────────────── 1. API + Sanctum ───────────────────────── */
        if ($request->expectsJson() && Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            $token = $user->currentAccessToken();

            $uuid = $request->header('X-Tenant-UUID');
            if (! $uuid) {
                return response()->json(['message' => 'X-Tenant-UUID required'], 400);
            }

            $tenant = Tenant::firstWhere('uuid', $uuid);
            if (! $tenant || ! $user->tenants->contains($tenant)) {
                return response()->json(['message' => 'Tenant mismatch'], 403);
            }

            if (! $token?->can("tid:{$tenant->id}")) {
                return response()->json(['message' => 'Token denied for tenant'], 403);
            }

            $this->currentTenant->setTenant($tenant);
        }

        /* ────────────────────────── 2. WEB‑сесія ───────────────────────────── */
        elseif (Auth::check()) {
            $user = Auth::user();

            // 2.1 Збережений tenant
            if ($user->current_tenant_id && ($tenant = Tenant::find($user->current_tenant_id)) && $user->tenants->contains($tenant)) {
                $this->currentTenant->setTenant($tenant);
            }
            // 2.2 Єдиний tenant
            elseif ($user->relationLoaded('tenants') ? $user->tenants->count() === 1 : $user->tenants()->count() === 1) {
                $this->currentTenant->setTenant($user->tenants()->first());
            }
            // 2.3 UI сам запропонує вибір
        }

        /* ────────────────────────── 3. Домен / Субдомен ────────────────────── */
        if (! $this->currentTenant->hasTenant()) {
            $host = $request->getHost();                 // club-alpha.fasthit.com.ua або club-alpha.com
            $rootDomain = config('app.saas_root');             // fasthit.com.ua (APP_SAAS_ROOT)

            // 3.1 Custom CNAME / white‑label: повний хост НЕ закінчується на кореневий домен
            if (! str_ends_with($host, ".{$rootDomain}")) {
                if ($tenant = Tenant::firstWhere('domain', $host)) {  // custom домен теж зберігаємо у 'domain'
                    $this->currentTenant->setTenant($tenant);
                }
            }
            // 3.2 Платформений піддомен *.fasthit.com.ua
            else {
                $sub = substr($host, 0, -strlen(".{$rootDomain}"));
                if ($tenant = Tenant::firstWhere('domain', $sub)) {
                    $this->currentTenant->setTenant($tenant);
                }
            }
        }

        return $next($request);
        ($request);

        Log::debug('Tenant set', ['tenant_id' => optional($this->currentTenant->tenant())->id]);
    }
}
