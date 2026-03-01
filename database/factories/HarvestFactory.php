<?php

namespace Database\Factories;

use App\Modules\Production\Domain\Enums\HarvestType;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Harvest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Harvest>
 */
class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    public function definition(): array
    {
        return [
            'cycle_id' => Cycle::factory(),
            'tenant_id' => static fn (array $attributes): int => (int) Cycle::withoutGlobalScopes()->findOrFail($attributes['cycle_id'])->tenant_id,
            'harvested_at' => now()->format('Y-m-d'),
            'type' => HarvestType::PARTIAL->value,
            'total_lbs' => fake()->randomFloat(2, 500, 50000),
            'avg_pp_grams' => fake()->optional()->randomFloat(2, 10, 45),
            'guide_number' => fake()->optional()->bothify('G-#####'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
