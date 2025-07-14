<?php

namespace Tests\Unit;

use App\Events\TenantSwitched;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrentTenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CurrentTenantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CurrentTenantService $service;

    protected User $user;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Сідери
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // demo user + tenant
        $this->user = User::factory()->create();
        $this->tenant = Tenant::factory()->create();
        $this->user->tenants()->attach($this->tenant->id);

        // Логін користувача
        $this->actingAs($this->user);

        // mock request
        // 1) Request (singleton на час тесту)
        $request = Request::create('/');
        $this->app->instance(Request::class, $request);

        // 2) PG-statement (щоб CI не впав, коли БД відповість “cannot set …”)
        DB::shouldReceive('statement')->andReturnTrue();

        // 3) Сервіс
        $this->service = $this->app->make(CurrentTenantService::class);

    }

    #[Test]
    public function it_sets_and_gets_current_tenant(): void
    {
        $this->service->setTenant($this->tenant);

        $this->assertTrue($this->service->hasTenant());
        $this->assertEquals($this->tenant->id, $this->service->tenant()?->id);

        // session() повинна містити ID
        $this->assertEquals($this->tenant->id, Session::get('current_tenant_id'));

        // current_tenant_id у users також оновлено
        $this->assertEquals($this->tenant->id, $this->user->fresh()->current_tenant_id);
    }

    #[Test]
    public function it_dispatches_tenant_switched_event(): void
    {
        Event::fake();

        $this->service->setTenant($this->tenant);

        Event::assertDispatched(
            TenantSwitched::class,
            fn (TenantSwitched $event) => $event->tenant->is($this->tenant) &&
                $event->user->is($this->user)
        );
    }

    #[Test]
    public function it_resets_everything_on_forget(): void
    {
        $this->service->setTenant($this->tenant);

        $this->service->forgetTenant();

        $this->assertFalse($this->service->hasTenant());
        $this->assertNull(Session::get('current_tenant_id'));
        $this->assertNull($this->user->fresh()->current_tenant_id);
    }

    #[Test]
    public function it_detects_system_context(): void
    {
        // звичайний користувач
        $this->assertFalse($this->service->isSystemContext());

        // ① гарантуємо наявність ролі
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        // ② користувач-суперадмін
        $super = User::factory()->create();
        $super->assignRole('super-admin');

        $this->actingAs($super);

        $this->assertTrue(resolve(CurrentTenantService::class)->isSystemContext());
    }
}
