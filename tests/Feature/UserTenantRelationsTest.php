<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTenantRelationsTest extends TestCase
{
    use RefreshDatabase;

    // ... (існуючі тести для tenant_users, не повинно бути 'role')

    #[Test]
    public function user_can_have_a_current_tenant(): void
    {
        $user = User::factory()->create();
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user->tenants()->attach($tenant1->id);
        $user->tenants()->attach($tenant2->id);

        $user->update(['current_tenant_id' => $tenant1->id]);
        $user->refresh();

        $this->assertNotNull($user->currentTenant);
        $this->assertEquals($tenant1->id, $user->currentTenant->id);
    }

    #[Test]
    public function current_tenant_is_nulled_on_tenant_soft_delete(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant->id);
        $user->update(['current_tenant_id' => $tenant->id]);
        $user->refresh();

        $this->assertNotNull($user->currentTenant);

        $tenant->delete(); // Soft delete tenant
        $user->refresh();

        $this->assertNull($user->currentTenant); // current_tenant_id має стати NULL
        $this->assertSoftDeleted($tenant);
    }
}
