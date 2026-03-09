<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Multitenancy\TenantScopeBypass;
use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Costing\Domain\Enums\OperationalCostType;
use App\Modules\Costing\Domain\Models\OperationalCostEntry;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\Production\Domain\Models\SurvivalEstimate;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(SaaSPlanSeeder::class);

        app(TenantScopeBypass::class)->run(function (): void {
            $tenant = Tenant::query()->updateOrCreate(
                ['slug' => 'tenant-a'],
                ['name' => 'Tenant A', 'is_active' => true],
            );

            $owner = User::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => 'owner@a.local',
                ],
                [
                    'name' => 'Owner',
                    'password' => Hash::make('password123'),
                    'role' => UserRole::OWNER->value,
                ],
            );

            $farm = Farm::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Demo Farm',
                ],
                [
                    'location' => 'Guayaquil',
                    'notes' => 'Seed de demo para validar backoffice y metricas.',
                ],
            );

            $ponds = collect([
                ['code' => 'P01', 'name' => 'North Pond', 'area_ha' => 4.50],
                ['code' => 'P02', 'name' => 'Center Pond', 'area_ha' => 3.20],
                ['code' => 'P03', 'name' => 'South Pond', 'area_ha' => 2.80],
            ])->map(function (array $pondData) use ($tenant, $farm): Pond {
                return Pond::withoutGlobalScopes()->updateOrCreate(
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

            $cycle = Cycle::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'pond_id' => $activePond->id,
                    'status' => CycleStatus::ACTIVE->value,
                ],
                [
                    'started_at' => now()->subDays(40)->format('Y-m-d'),
                    'notes' => 'Ciclo demo activo para validar operacion diaria.',
                ],
            );

            Stocking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                ],
                [
                    'stocked_at' => now()->subDays(39)->format('Y-m-d'),
                    'pl_qty' => 420000,
                    'hatchery_code' => 'BC',
                    'batch_code' => 'TA-2026-001',
                    'initial_pp_grams' => 0.05,
                    'density_pl_ha' => round(420000 / 4.50, 2),
                    'density_pl_m2' => round(420000 / (4.50 * 10000), 4),
                ],
            );

            Sampling::withoutGlobalScopes()->updateOrCreate(
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

            Sampling::withoutGlobalScopes()->updateOrCreate(
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

            SurvivalEstimate::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'estimated_at' => now()->subDays(8)->format('Y-m-d'),
                ],
                [
                    'survival_pct' => 72.50,
                    'notes' => 'Estimación operativa usada para proyección demo.',
                ],
            );

            $feedType = FeedType::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Balanceado Engorde 35%',
                ],
                [
                    'brand' => 'Biomar',
                    'protein_pct' => 35.00,
                    'cost_per_kg' => 1.4200,
                    'notes' => 'Alimento principal del ciclo demo.',
                    'is_active' => true,
                ],
            );

            $feedEntries = [
                [now()->subDays(9)->format('Y-m-d'), 280.500, 'Ajuste por biomasa proyectada.'],
                [now()->subDays(8)->format('Y-m-d'), 292.750, 'Racion matutina y vespertina.'],
                [now()->subDays(7)->format('Y-m-d'), 301.250, 'Consumo estable.'],
                [now()->subDays(6)->format('Y-m-d'), 309.800, 'Clima favorable.'],
            ];

            foreach ($feedEntries as [$fedAt, $amountKg, $notes]) {
                FeedEntry::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'cycle_id' => $cycle->id,
                        'feed_type_id' => $feedType->id,
                        'fed_at' => $fedAt,
                    ],
                    [
                        'amount_kg' => $amountKg,
                        'notes' => $notes,
                    ],
                );
            }

            OperationalCostEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'cost_type' => OperationalCostType::ENERGY->value,
                    'occurred_at' => now()->subDays(5)->format('Y-m-d'),
                ],
                [
                    'amount' => 185.50,
                    'notes' => 'Aireacion nocturna y bombeo.',
                ],
            );

            WaterQualityEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'pond_id' => $activePond->id,
                    'measured_at' => now()->subHours(6)->startOfHour(),
                ],
                [
                    'dissolved_oxygen_mg_l' => 4.10,
                    'ph' => 7.85,
                    'temp_c' => 29.40,
                    'salinity_ppt' => 15.20,
                    'alkalinity_mg_l' => 128.00,
                    'ammonia_mg_l' => 0.120,
                    'nitrite_mg_l' => 0.030,
                    'notes' => 'Lectura estable de madrugada.',
                    'measured_by_user_id' => $owner->id,
                ],
            );

            AlertEvent::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'rule_code' => 'FEED_DEVIATION',
                    'detected_at' => now()->subDay()->startOfDay(),
                ],
                [
                    'farm_id' => $farm->id,
                    'severity' => AlertSeverity::WARNING->value,
                    'title' => 'Consumo por encima de recomendacion',
                    'message' => 'El consumo diario quedo 8% por encima de la recomendacion calculada.',
                    'context_json' => [
                        'recommended_feed_kg' => 286.40,
                        'actual_feed_kg' => 309.80,
                    ],
                    'is_acknowledged' => false,
                ],
            );
        });
    }
}
