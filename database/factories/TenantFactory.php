<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->company,
            'domain' => $this->faker->unique()->domainName,
        ];
    }

    /**
     * Стан без UUID (для negative-тестів)
     */
    public function withoutUuid(): self
    {
        return $this->state(fn () => ['uuid' => null]);
    }
}
