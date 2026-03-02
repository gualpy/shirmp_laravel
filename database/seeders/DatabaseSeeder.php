<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demo Tenant', 'is_active' => true],
        );

        User::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'email' => 'owner@demo.local',
            ],
            [
                'name' => 'Demo Owner',
                'password' => Hash::make('password123'),
                'role' => UserRole::OWNER->value,
            ],
        );

        $farm = Farm::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Farm',
            ],
            [
                'location' => 'Guayaquil',
                'notes' => 'Seed demo farm',
            ],
        );

        $ponds = collect([
            ['code' => 'P01', 'name' => 'North Pond', 'area_ha' => 4.50],
            ['code' => 'P02', 'name' => 'Center Pond', 'area_ha' => 3.20],
            ['code' => 'P03', 'name' => 'South Pond', 'area_ha' => 2.80],
        ])->map(function (array $pondData) use ($tenant, $farm): Pond {
            return Pond::query()->firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => $pondData['code'],
                ],
                [
                    'farm_id' => $farm->id,
                    'name' => $pondData['name'],
                    'area_ha' => $pondData['area_ha'],
                    'avg_depth_m' => 1.40,
                    'is_active' => true,
                ],
            );
        });

        $activePond = $ponds->first();

        $cycle = Cycle::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'pond_id' => $activePond->id,
                'status' => CycleStatus::ACTIVE->value,
            ],
            [
                'started_at' => now()->subDays(40)->format('Y-m-d'),
                'notes' => 'Demo active cycle',
            ],
        );

        Stocking::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'cycle_id' => $cycle->id,
            ],
            [
                'stocked_at' => now()->subDays(39)->format('Y-m-d'),
                'pl_qty' => 420000,
                'hatchery_code' => 'BC',
                'batch_code' => 'DEMO-001',
                'initial_pp_grams' => 0.05,
                'density_pl_ha' => round(420000 / 4.50, 2),
                'density_pl_m2' => round(420000 / (4.50 * 10000), 4),
            ],
        );

        Sampling::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'cycle_id' => $cycle->id,
                'sampled_at' => now()->subDays(20)->format('Y-m-d'),
            ],
            [
                'pp_grams' => 7.40,
                'notes' => 'First demo sampling',
            ],
        );

        Sampling::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'cycle_id' => $cycle->id,
                'sampled_at' => now()->subDays(10)->format('Y-m-d'),
            ],
            [
                'pp_grams' => 11.80,
                'notes' => 'Second demo sampling',
            ],
        );

        $this->call(SaaSPlanSeeder::class);
    }
}
