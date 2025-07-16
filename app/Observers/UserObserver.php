<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event. Trigger when the model is changed.
     */
    public function updated(User $user): void
    {
        if ($user->isDirty('status')) {
            $old = $user->getOriginal('status');
            $new = $user->status;
            Log::info("User #{$user->id} status changed: {$old} → {$new}");

            if (in_array($new, ['suspended', 'inactive'], true)) {
                $this->revokeUserAccess($user, "status change to '{$new}'");
            }
        }
    }

    /**
     * Handle the User "soft-delete" event.
     */
    public function deleted(User $user): void
    {
        $this->revokeUserAccess($user, 'soft-delete');
        $user->tenants()->detach();
        Log::info("User #{$user->id} detached from all tenants (soft-delete).");
    }

    // ───────────────────────── helpers ──────────────────────────

    /**
     * Знімає всі ролі, прямі дозволи та токени – глобально й по тенантах.
     */
    protected function revokeUserAccess(User $user, string $reason): void
    {
        $registrar = app(PermissionRegistrar::class);

        // 1. Глобальні
        $registrar->setPermissionsTeamId(null);
        $user->syncRoles([]);
        $user->syncPermissions([]);
        Log::info("Global roles & perms revoked for user #{$user->id} ({$reason}).");

        // 2. Тенант-специфічні
        foreach ($user->tenants as $tenant) {
            $registrar->setPermissionsTeamId($tenant->id);
            $user->syncRoles([]);
            $user->syncPermissions([]);
            Log::info("Tenant roles & perms revoked for user #{$user->id} in tenant #{$tenant->id}.");
        }

        /* 4. повернулись у «global» — щоб не залишати side-effect’ів */
        $registrar->setPermissionsTeamId(null);
        $registrar->forgetCachedPermissions();
        Log::info("Permissions cache cleared after revoking access for user #{$user->id}.");

        // 5. Sanctum tokens
        $user->tokens()->delete();
        Log::warning("All Sanctum tokens revoked for user #{$user->id} ({$reason}).");
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
