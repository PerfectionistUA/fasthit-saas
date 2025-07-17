<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantHierarchyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cannot_be_its_own_parent(): void
    {
        $t = Tenant::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A tenant cannot be its own parent.');

        $t->parent_id = $t->id;
        $t->save();
    }

    #[Test]
    public function cannot_form_cyclic_dependency(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create(['parent_id' => $a->id]);
        $c = Tenant::factory()->create(['parent_id' => $b->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cyclic dependency detected');

        $a->parent_id = $c->id;
        $a->save();
    }

    #[Test]
    public function can_set_valid_parent_and_children_relationship(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create();

        $b->parent_id = $a->id;
        $b->save();

        $this->assertTrue($b->parent->is($a));
        $this->assertCount(1, $a->children);
        $this->assertTrue($a->children->first()->is($b));
    }

    #[Test]
    public function is_descendant_of_works_correctly(): void
    {
        $a = Tenant::factory()->create();
        $b = Tenant::factory()->create(['parent_id' => $a->id]);
        $c = Tenant::factory()->create(['parent_id' => $b->id]);
        $d = Tenant::factory()->create();

        $this->assertTrue($c->isDescendantOf($a->id));
        $this->assertTrue($b->isDescendantOf($a->id));
        $this->assertTrue($a->isDescendantOf($a->id));

        $this->assertFalse($a->isDescendantOf($b->id));
        $this->assertFalse($a->isDescendantOf($c->id));
        $this->assertFalse($a->isDescendantOf($d->id));
    }
}
