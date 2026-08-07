<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Multitenancy\TenantScopeBypass;
use App\Modules\Feeding\Domain\Models\FeedEntry;
use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\DailyMortality;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Crea una piscina nueva con un ciclo activo sembrado hace 3 meses,
 * junto con muestreos, alimentacion y mortalidad diaria hasta hoy,
 * para poder visualizar métricas y curvas en el front sin esperar meses reales.
 *
 * Uso: php artisan db:seed --class=ThreeMonthPondSeeder
 */
class ThreeMonthPondSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        app(TenantScopeBypass::class)->run(function (): void {
            $tenant = Tenant::query()->firstOrFail();
            $farm = Farm::withoutGlobalScopes()->where('tenant_id', $tenant->id)->firstOrFail();

            $pond = Pond::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'code' => 'P04',
                ],
                [
                    'farm_id' => $farm->id,
                    'name' => 'Piscina Nueva',
                    'area_ha' => 3.50,
                    'avg_depth_m' => 1.40,
                    'is_active' => true,
                ],
            );

            $start = CarbonImmutable::now()->subMonths(3)->startOfDay();
            $today = CarbonImmutable::now()->startOfDay();
            $totalDays = max(7, (int) $start->diffInDays($today));

            $cycle = Cycle::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'pond_id' => $pond->id,
                    'status' => CycleStatus::ACTIVE->value,
                ],
                [
                    'started_at' => $start->toDateString(),
                    'notes' => 'Ciclo demo sembrado 3 meses atrás para revisar resultados en el front.',
                ],
            );

            $cycle->samplings()->delete();
            $cycle->feedEntries()->delete();
            $cycle->dailyMortalities()->delete();

            $plQty = 350000;
            $areaHa = (float) $pond->area_ha;

            Stocking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                ],
                [
                    'stocked_at' => $start->addDay()->toDateString(),
                    'pl_qty' => $plQty,
                    'hatchery_code' => 'BC',
                    'batch_code' => 'TA-'.$start->format('Y').'-P04',
                    'initial_pp_grams' => 0.05,
                    'density_pl_ha' => round($plQty / $areaHa, 2),
                    'density_pl_m2' => round($plQty / ($areaHa * 10000), 4),
                ],
            );

            for ($day = 7; $day <= $totalDays; $day += 7) {
                Sampling::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'sampled_at' => $start->addDays($day)->toDateString(),
                    'pp_grams' => $this->cumulativeGrowthGrams($day),
                    'notes' => 'Muestreo semana '.intdiv($day, 7).'.',
                ]);
            }

            $starterFeed = FeedType::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Balanceado Crecimiento 38%'],
                ['brand' => 'Biomar', 'protein_pct' => 38.00, 'cost_per_kg' => 1.5800, 'is_active' => true],
            );
            $growerFeed = FeedType::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => 'Balanceado Engorde 35%'],
                ['brand' => 'Biomar', 'protein_pct' => 35.00, 'cost_per_kg' => 1.4200, 'is_active' => true],
            );

            for ($day = 2; $day <= $totalDays; $day++) {
                FeedEntry::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'feed_type_id' => $day <= 30 ? $starterFeed->id : $growerFeed->id,
                    'fed_at' => $start->addDays($day)->toDateString(),
                    'amount_kg' => $this->feedAmountForDay($day),
                    'notes' => 'Registro diario consolidado de alimentación.',
                ]);
            }

            for ($day = 7; $day <= $totalDays; $day += 7) {
                DailyMortality::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'pond_id' => $pond->id,
                    'recorded_at' => $start->addDays($day)->toDateString(),
                    'mortality_count' => $this->mortalityForWeek(intdiv($day, 7)),
                    'notes' => 'Retiro semanal de mortalidad.',
                ]);
            }
        });
    }

    private function cumulativeGrowthGrams(int $day): float
    {
        $grams = 0.05;

        for ($d = 1; $d <= $day; $d++) {
            $grams += match (true) {
                $d <= 20 => 0.04,
                $d <= 40 => 0.12,
                $d <= 60 => 0.22,
                $d <= 80 => 0.30,
                default => 0.35,
            };
        }

        return round($grams, 2);
    }

    private function feedAmountForDay(int $day): float
    {
        return match (true) {
            $day <= 7 => 4.0,
            $day <= 14 => 6.5,
            $day <= 21 => 10.0,
            $day <= 28 => 14.5,
            $day <= 35 => 21.0,
            $day <= 42 => 30.0,
            $day <= 49 => 41.5,
            $day <= 56 => 56.0,
            $day <= 63 => 73.5,
            $day <= 70 => 93.0,
            $day <= 77 => 114.0,
            $day <= 84 => 136.0,
            $day <= 91 => 159.0,
            default => 180.0,
        };
    }

    private function mortalityForWeek(int $week): int
    {
        return (int) round(90 * $week + 15 * ($week ** 2));
    }
}
