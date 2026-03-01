<?php

namespace Database\Factories;

use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pond>
 */
class PondFactory extends Factory
{
    protected $model = Pond::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'tenant_id' => static fn (array $attributes): int => (int) Farm::withoutGlobalScopes()->findOrFail($attributes['farm_id'])->tenant_id,
            'code' => strtoupper(fake()->unique()->bothify('P##??')),
            'name' => 'Pond '.fake()->numberBetween(1, 20),
            'area_ha' => fake()->randomFloat(2, 0.8, 7.5),
            'avg_depth_m' => fake()->randomFloat(2, 0.8, 2.5),
            'is_active' => true,
        ];
    }
}
