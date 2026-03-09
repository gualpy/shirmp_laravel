<?php

namespace Database\Factories;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cycle>
 */
class CycleFactory extends Factory
{
    protected $model = Cycle::class;

    public function definition(): array
    {
        return [
            'pond_id' => Pond::factory(),
            'tenant_id' => static fn (array $attributes): int => (int) Pond::withoutGlobalScopes()->findOrFail($attributes['pond_id'])->tenant_id,
            'status' => CycleStatus::ACTIVE->value,
            'started_at' => fake()->dateTimeBetween('-120 days', '-30 days')->format('Y-m-d'),
            'ended_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
