<?php

namespace Database\Factories;

use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stocking>
 */
class StockingFactory extends Factory
{
    protected $model = Stocking::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(50000, 450000);

        return [
            'cycle_id' => Cycle::factory(),
            'tenant_id' => static fn (array $attributes): int => (int) Cycle::withoutGlobalScopes()->findOrFail($attributes['cycle_id'])->tenant_id,
            'stocked_at' => now()->format('Y-m-d'),
            'pl_qty' => $qty,
            'supplier_id' => null,
            'batch_code' => fake()->optional()->bothify('B-####'),
            'initial_pp_grams' => fake()->optional()->randomFloat(2, 0.01, 0.20),
            'density_pl_ha' => 0,
            'density_pl_m2' => 0,
        ];
    }
}
