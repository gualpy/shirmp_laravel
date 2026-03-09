<?php

namespace Database\Factories;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Sampling;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sampling>
 */
class SamplingFactory extends Factory
{
    protected $model = Sampling::class;

    public function definition(): array
    {
        return [
            'cycle_id' => Cycle::factory(),
            'tenant_id' => static fn (array $attributes): int => (int) Cycle::withoutGlobalScopes()->findOrFail($attributes['cycle_id'])->tenant_id,
            'sampled_at' => now()->format('Y-m-d'),
            'pp_grams' => fake()->randomFloat(2, 1, 35),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
