<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function observer_revokes_everything_on_suspend(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['status' => 'active']);
        $user->tenants()->attach($tenant);

        /* ── шаблонні (tenant-agnostic) роли/дозволи ──────────────── */
        Role::firstOrCreate([
            'name' => 'editor',
            'guard_name' => 'web',
        ]);
        Permission::firstOrCreate([
            'name' => 'create_post',
            'guard_name' => 'web',
        ]);

        /* ── призначення у контексті конкретного тенанта ─────────────────── */
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->id);

        $user->assignRole('editor');
        $user->givePermissionTo('create_post');

        // повертаємо у global
        $registrar->setPermissionsTeamId(null);

        $tokenId = $user->createToken('api')->accessToken->id;

        /* sanity-check: запис у pivot є */
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        /* ── тригер: зміна статусу ───────────────────────────────────────── */
        $user->update(['status' => 'suspended']);

        /* ── перевірка: ролі/дозволи й токени видалені ───────────────────── */
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseMissing('model_has_permissions', [
            'model_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);

    }
}
