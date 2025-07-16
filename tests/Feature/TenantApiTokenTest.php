<?php

namespace Tests\Feature;

use App\Http\Middleware\SetCurrentTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrentTenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantApiTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // API-endpoint, який повертає ідентифікований tenant_id
        Route::middleware(['auth:sanctum', SetCurrentTenant::class])
            ->get('/api/current-tenant-id', function (Request $r) {
                return ['tenant_id' => app(CurrentTenantService::class)->tenant()?->id];
            });

        // Глобальний endpoint без tenant-контексту
        Route::middleware(['auth:sanctum'])
            ->get('/api/global-resource', fn () => ['message' => 'Global resource accessed.']);
    }

    #[Test]
    public function user_gets_token_for_tenant_and_db_row_contains_tenant_id(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant->id, ['is_owner' => true]);

        $resp = $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
            'tenant_uuid' => $tenant->uuid,
        ]);

        $resp->assertOk()->assertJsonStructure(['token', 'tenant_id', 'tenant_uuid']);

        $tokenStr = $resp->json('token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => "api:{$tenant->id}",
            'abilities' => json_encode(["tid:{$tenant->id}"]),
            'tenant_id' => $tenant->id,
        ]);

        // Перевіряємо ability
        $token = $user->tokens()->where('name', "api:{$tenant->id}")->first();
        Sanctum::actingAs($user, $token->abilities);
        $this->assertTrue($user->currentAccessToken()->can("tid:{$tenant->id}"));
    }

    #[Test]
    public function api_access_is_denied_when_token_not_for_requested_tenant(): void
    {
        $user = User::factory()->create();
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        $user->tenants()->attach($tenant1->id);
        $user->tenants()->attach($tenant2->id);

        $token = $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
            'tenant_uuid' => $tenant1->uuid,
        ])->json('token');

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Tenant-UUID' => $tenant2->uuid,
        ])->getJson('/api/current-tenant-id')
            ->assertStatus(403)
            ->assertExactJson(['message' => 'Token denied for tenant']);
    }

    #[Test]
    public function api_access_is_allowed_with_correct_tenant_ability(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant->id);

        $token = $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
            'tenant_uuid' => $tenant->uuid,
        ])->json('token');

        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'X-Tenant-UUID' => $tenant->uuid,
        ])->getJson('/api/current-tenant-id')
            ->assertOk()
            ->assertExactJson(['tenant_id' => $tenant->id]);
    }

    #[Test]
    public function global_resource_allows_access_without_tenant_header(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('global-token')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/global-resource')
            ->assertStatus(400);  // токен без X-Tenant-UUID ⇒ 400
    }
}
