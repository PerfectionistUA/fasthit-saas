<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantUuidTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_assigns_a_valid_uuid_on_creation(): void
    {
        $tenant = Tenant::factory()->create();

        $this->assertSame(36, strlen($tenant->uuid));
        $this->assertTrue(Str::isUuid($tenant->uuid));
    }

    #[Test]
    public function it_can_be_resolved_via_route_model_binding(): void
    {
        $tenant = Tenant::factory()->create();

        // Тимчасовий маршрут тільки для тесту
        // Явно реєструємо бінд та одразу додаємо middleware класом
        Route::bind('tenant', fn ($value) => Tenant::whereUuid($value)->firstOrFail());

        Route::get('/testing/tenants/{tenant}', fn (Tenant $tenant) => $tenant->uuid)
            ->middleware(SubstituteBindings::class);           // жодних alias-рядків

        $response = $this->get("/testing/tenants/{$tenant->uuid}");

        $response->assertOk();                                        // 200
        $this->assertSame($tenant->uuid, $response->getContent());    // точний збіг
    }

    #[Test]
    public function uuid_must_be_unique(): void
    {
        Tenant::factory()->create(['uuid' => '11111111-1111-1111-1111-111111111111']);

        $this->expectException(QueryException::class);
        Tenant::factory()->create(['uuid' => '11111111-1111-1111-1111-111111111111']);
    }
}
