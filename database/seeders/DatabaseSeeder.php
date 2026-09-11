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
use App\Modules\Production\Domain\Models\DailyMortality;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Production\Domain\Models\Sampling;
use App\Modules\Production\Domain\Models\Stocking;
use App\Modules\Suppliers\Domain\Enums\SupplierType;
use App\Modules\Suppliers\Domain\Models\Supplier;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Carbon\CarbonImmutable;
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

            $hatcheries = collect(['Bioceanica', 'Larvas del Pacifico', 'Camaronera del Golfo'])
                ->map(fn (string $name): Supplier => Supplier::withoutGlobalScopes()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $name],
                    ['type' => SupplierType::HATCHERY->value, 'is_active' => true],
                ));

            $activePond = $ponds->first();

            $cycle = Cycle::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'pond_id' => $activePond->id,
                    'status' => CycleStatus::ACTIVE->value,
                ],
                [
                    'started_at' => now()->subDays(77)->format('Y-m-d'),
                    'notes' => 'Ciclo demo narrativo de once semanas con operacion diaria, alertas, costos y proyeccion.',
                ],
            );

            $cycle->samplings()->delete();
            $cycle->feedEntries()->delete();
            $cycle->operationalCosts()->delete();
            $cycle->waterQualityEntries()->delete();
            $cycle->alerts()->delete();
            $cycle->survivalEstimates()->delete();
            $cycle->dailyMortalities()->delete();
            $cycle->harvests()->delete();

            $timelineStart = CarbonImmutable::parse($cycle->started_at);

            Stocking::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                ],
                [
                    'stocked_at' => $timelineStart->addDay()->toDateString(),
                    'pl_qty' => 420000,
                    'supplier_id' => $hatcheries->first()->id,
                    'batch_code' => 'TA-2026-001',
                    'initial_pp_grams' => 0.05,
                    'density_pl_ha' => round(420000 / 4.50, 2),
                    'density_pl_m2' => round(420000 / (4.50 * 10000), 4),
                ],
            );

            $samplings = [
                [7, 0.32, 'Inicio de engorde con supervivencia visual estable.'],
                [14, 0.78, 'Ajuste de racion por respuesta temprana.'],
                [21, 1.62, 'Crecimiento parejo y buen llenado intestinal.'],
                [28, 2.85, 'Semana de transicion a alimento intermedio.'],
                [35, 4.38, 'Biomasa subiendo sin desviaciones fuertes.'],
                [42, 6.12, 'Mantiene crecimiento solido tras recambio parcial.'],
                [49, 8.35, 'Consumo estable y conversión visual aceptable.'],
                [56, 10.92, 'Ajuste fino de racion por comportamiento en bandeja.'],
                [63, 13.84, 'Peso comercial intermedio rumbo a meta de 20 g.'],
                [70, 16.45, 'Ultimo muestreo demo para dashboard y proyeccion.'],
            ];

            foreach ($samplings as [$dayOffset, $ppGrams, $notes]) {
                Sampling::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'sampled_at' => $timelineStart->addDays($dayOffset)->toDateString(),
                    'pp_grams' => $ppGrams,
                    'notes' => $notes,
                ]);
            }

            $starterFeed = FeedType::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Balanceado Crecimiento 38%',
                ],
                [
                    'brand' => 'Biomar',
                    'protein_pct' => 38.00,
                    'cost_per_kg' => 1.5800,
                    'notes' => 'Alimento de arranque para primeras semanas del ciclo demo.',
                    'is_active' => true,
                ],
            );

            $growerFeed = FeedType::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Balanceado Engorde 35%',
                ],
                [
                    'brand' => 'Biomar',
                    'protein_pct' => 35.00,
                    'cost_per_kg' => 1.4200,
                    'notes' => 'Alimento principal del ciclo demo en fase media y final.',
                    'is_active' => true,
                ],
            );

            for ($day = 2; $day <= 76; $day++) {
                $feedDate = $timelineStart->addDays($day);
                $amountKg = $this->demoFeedAmountForDay($day);
                $feedTypeId = $day <= 30 ? $starterFeed->id : $growerFeed->id;

                FeedEntry::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'feed_type_id' => $feedTypeId,
                    'fed_at' => $feedDate->toDateString(),
                    'amount_kg' => $amountKg,
                    'notes' => $day >= 67
                        ? 'Racion total del dia ajustada por biomasa observada.'
                        : 'Registro diario consolidado de alimentacion.',
                ]);
            }

            $mortalityTimeline = [
                [10, 120, 'Mortalidad de arranque dentro de lo esperado.'],
                [17, 180, 'Ajuste normal de primera semana completa.'],
                [24, 240, 'Rutina de retiro diario sin hallazgos relevantes.'],
                [31, 310, 'Ligero incremento por cambio de clima.'],
                [38, 420, 'Controlado tras incremento de biomasa.'],
                [45, 510, 'Monitoreo normal con retiro temprano.'],
                [52, 620, 'Pequeño repunte tras lluvias.'],
                [59, 760, 'Seguimiento reforzado en bordes del pond.'],
                [66, 900, 'Mortalidad aun operativamente manejable.'],
                [73, 1080, 'Ultimo corte demo previo a muestreo mas reciente.'],
            ];

            foreach ($mortalityTimeline as [$dayOffset, $mortalityCount, $notes]) {
                DailyMortality::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'pond_id' => $activePond->id,
                    'recorded_at' => $timelineStart->addDays($dayOffset)->toDateString(),
                    'mortality_count' => $mortalityCount,
                    'notes' => $notes,
                    'created_by' => $owner->id,
                ]);
            }

            $operationalCosts = [
                [OperationalCostType::LABOR, 420.00, 9, 'Cuadrilla de preparacion y manejo inicial.'],
                [OperationalCostType::CHEMICALS, 285.75, 16, 'Mineralizacion preventiva y ajuste de fondo.'],
                [OperationalCostType::ENERGY, 390.20, 27, 'Aireacion nocturna y bombeo de recambio.'],
                [OperationalCostType::FUEL, 248.60, 39, 'Combustible para motobomba y rondas operativas.'],
                [OperationalCostType::MAINTENANCE, 315.40, 51, 'Cambio de bandas y servicio de blower.'],
                [OperationalCostType::ENERGY, 462.10, 63, 'Mayor demanda por biomasa creciente.'],
                [OperationalCostType::OTHER, 190.00, 72, 'Analisis externo y soporte tecnico puntual.'],
            ];

            foreach ($operationalCosts as [$type, $amount, $dayOffset, $notes]) {
                OperationalCostEntry::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'cost_type' => $type->value,
                    'amount' => $amount,
                    'occurred_at' => $timelineStart->addDays($dayOffset)->toDateString(),
                    'notes' => $notes,
                ]);
            }

            $waterEntries = [
                [12, '05:20', 5.60, 7.72, 29.10, 15.80, 126.00, 0.070, 0.020, 'Lectura de madrugada dentro de rango.'],
                [19, '05:10', 5.10, 7.78, 29.20, 15.70, 124.00, 0.080, 0.021, 'Consistencia entre aireacion y bandejas.'],
                [26, '05:30', 4.70, 7.83, 29.30, 15.50, 123.00, 0.090, 0.025, 'Semana estable con leve caida de oxigeno.'],
                [33, '05:00', 4.20, 7.90, 29.60, 15.20, 122.00, 0.110, 0.028, 'Biomasa al alza, aireacion reforzada.'],
                [40, '05:15', 3.25, 7.88, 29.90, 15.00, 120.00, 0.140, 0.032, 'Evento de oxigeno bajo en madrugada tras nubosidad.'],
                [47, '05:25', 4.85, 7.95, 29.70, 15.10, 121.00, 0.120, 0.029, 'Recuperacion tras ajuste operativo.'],
                [54, '05:05', 4.45, 8.02, 29.80, 14.90, 119.00, 0.130, 0.031, 'Rango aceptable con biomasa creciente.'],
                [61, '13:40', 5.20, 8.92, 30.40, 14.70, 116.00, 0.170, 0.040, 'pH alto en lectura de tarde con fuerte radiacion.'],
                [68, '05:18', 4.30, 8.08, 29.95, 14.80, 118.00, 0.150, 0.034, 'Manejo estable despues del evento de pH.'],
                [75, '05:12', 4.55, 7.96, 29.60, 15.00, 119.00, 0.135, 0.030, 'Ultima lectura demo previa a revision gerencial.'],
            ];

            foreach ($waterEntries as [$dayOffset, $hour, $do, $ph, $temp, $salinity, $alkalinity, $ammonia, $nitrite, $notes]) {
                WaterQualityEntry::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'cycle_id' => $cycle->id,
                    'pond_id' => $activePond->id,
                    'measured_at' => CarbonImmutable::parse($timelineStart->addDays($dayOffset)->toDateString().' '.$hour),
                    'dissolved_oxygen_mg_l' => $do,
                    'ph' => $ph,
                    'temp_c' => $temp,
                    'salinity_ppt' => $salinity,
                    'alkalinity_mg_l' => $alkalinity,
                    'ammonia_mg_l' => $ammonia,
                    'nitrite_mg_l' => $nitrite,
                    'notes' => $notes,
                    'measured_by_user_id' => $owner->id,
                ]);
            }

            $alerts = [
                [
                    'rule_code' => 'DO_LOW',
                    'severity' => AlertSeverity::CRITICAL,
                    'title' => 'Oxigeno disuelto bajo en madrugada',
                    'message' => 'La lectura de 3.25 mg/L quedo por debajo del umbral seguro y exigio aireacion reforzada.',
                    'detected_at' => $timelineStart->addDays(40)->setTime(5, 0),
                    'context_json' => ['dissolved_oxygen_mg_l' => 3.25, 'threshold' => 3.5],
                    'is_acknowledged' => true,
                    'acknowledged_by_user_id' => $owner->id,
                    'acknowledged_at' => $timelineStart->addDays(40)->setTime(7, 15),
                ],
                [
                    'rule_code' => 'PH_OUT_OF_RANGE',
                    'severity' => AlertSeverity::WARNING,
                    'title' => 'pH alto por radiacion de tarde',
                    'message' => 'La lectura de pH 8.92 salio del rango operativo y se programo seguimiento al anochecer.',
                    'detected_at' => $timelineStart->addDays(61)->setTime(13, 45),
                    'context_json' => ['ph' => 8.92, 'max' => 8.8],
                    'is_acknowledged' => true,
                    'acknowledged_by_user_id' => $owner->id,
                    'acknowledged_at' => $timelineStart->addDays(61)->setTime(15, 10),
                ],
                [
                    'rule_code' => 'FEED_DEVIATION',
                    'severity' => AlertSeverity::INFO,
                    'title' => 'Consumo ligeramente por encima de recomendacion',
                    'message' => 'La racion del dia quedo 9% sobre la recomendacion calculada para sostener respuesta de bandeja.',
                    'detected_at' => $timelineStart->addDays(72)->setTime(17, 30),
                    'context_json' => ['recommended_feed_kg' => 124.80, 'actual_feed_kg' => 136.20],
                    'is_acknowledged' => false,
                ],
                [
                    'rule_code' => 'LOW_GROWTH',
                    'severity' => AlertSeverity::WARNING,
                    'title' => 'Crecimiento por debajo de la meta semanal',
                    'message' => 'En la semana cinco el crecimiento observado quedo por debajo de la meta interna y se reajusto protocolo.',
                    'detected_at' => $timelineStart->addDays(35)->setTime(10, 0),
                    'context_json' => ['growth_g_per_week' => 1.53, 'threshold' => 1.7],
                    'is_acknowledged' => true,
                    'acknowledged_by_user_id' => $owner->id,
                    'acknowledged_at' => $timelineStart->addDays(35)->setTime(11, 0),
                    'resolved_by_user_id' => $owner->id,
                    'resolved_at' => $timelineStart->addDays(49)->setTime(9, 30),
                ],
            ];

            foreach ($alerts as $alertData) {
                AlertEvent::withoutGlobalScopes()->create(array_merge($alertData, [
                    'tenant_id' => $tenant->id,
                    'farm_id' => $farm->id,
                    'cycle_id' => $cycle->id,
                ]));
            }
        });
    }

    private function demoFeedAmountForDay(int $day): float
    {
        return match (true) {
            $day <= 7 => 5.5,
            $day <= 14 => 8.0,
            $day <= 21 => 12.5,
            $day <= 28 => 18.0,
            $day <= 35 => 27.5,
            $day <= 42 => 39.0,
            $day <= 49 => 54.0,
            $day <= 56 => 73.0,
            $day <= 63 => 96.0,
            $day <= 70 => 118.0,
            default => 134.0,
        };
    }
}
