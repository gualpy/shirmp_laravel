<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Modules\Production\Domain\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Farm>
 */
class FarmFactory extends Factory
{
    protected $model = Farm::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company().' Farm',
            'location' => fake()->city(),
            'notes' => fake()->sentence(),
        ];
    }
}
