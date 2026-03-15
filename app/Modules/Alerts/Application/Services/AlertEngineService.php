<?php

namespace App\Modules\Alerts\Application\Services;

use App\Modules\Alerts\Domain\Enums\AlertCode;
use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Alerts\Domain\Models\AlertRule;
use App\Modules\Configuration\Application\Services\FeedingRecommendationService;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Services\BaseService;
use App\Modules\WaterQuality\Domain\Models\WaterQualityEntry;
use Carbon\Carbon;

final class AlertEngineService extends BaseService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly FeedingRecommendationService $feedingRecommendationService,
    ) {
    }

    /**
     * @return array<int, AlertEvent>
     */
    public function evaluateCycle(Cycle $cycle, ?Carbon $date = null): array
    {
        $evaluationDate = ($date ?? now())->copy();

        $events = [];
        $rules = $this->resolvedRules($cycle);

        $growth = $this->metricsService->growth_g_per_week($cycle);
        $fcr = $this->metricsService->fcr($cycle);
        $totalHarvestKg = $this->metricsService->total_harvest_kg($cycle);
        $biomassKg = $this->metricsService->biomass_kg($cycle, 1.0);
        $pondAreaHa = (float) $cycle->pond->area_ha;
        $biomassKgPerHa = ($biomassKg !== null && $pondAreaHa > 0) ? ($biomassKg / $pondAreaHa) : null;

        $recommendation = $this->feedingRecommendationService->recommendDailyFeedKg($cycle);
        $recommendedFeedKg = $recommendation['recommended_feed_kg_per_day'];
        $feedTodayKg = (float) $cycle->feedEntries()
            ->whereDate('fed_at', $evaluationDate->toDateString())
            ->sum('amount_kg');
        $waterEntriesToday = WaterQualityEntry::query()
            ->where('cycle_id', $cycle->id)
            ->whereDate('measured_at', $evaluationDate->toDateString())
            ->orderByDesc('measured_at')
            ->get();

        foreach ($rules as $rule) {
            $params = $rule['params'];
            $code = AlertCode::from($rule['code']);

            if ($code === AlertCode::LOW_GROWTH) {
                $threshold = (float) ($params['threshold'] ?? 0.8);
                if ($growth !== null && $growth < $threshold) {
                    $events[] = $this->emit(
                        cycle: $cycle,
                        ruleCode: $code,
                        severity: AlertSeverity::from($rule['severity']),
                        title: 'Low growth detected',
                        message: 'Growth g/week is below threshold.',
                        detectedAt: $evaluationDate,
                        context: ['growth_g_per_week' => $growth, 'threshold' => $threshold],
                    );
                }
                continue;
            }

            if ($code === AlertCode::HIGH_FCR) {
                $threshold = (float) ($params['threshold'] ?? 1.7);
                if ($totalHarvestKg > 0 && $fcr > $threshold) {
                    $events[] = $this->emit(
                        cycle: $cycle,
                        ruleCode: $code,
                        severity: AlertSeverity::from($rule['severity']),
                        title: 'High FCR detected',
                        message: 'Feed conversion ratio exceeds threshold.',
                        detectedAt: $evaluationDate,
                        context: ['fcr' => $fcr, 'threshold' => $threshold, 'total_harvest_kg' => $totalHarvestKg],
                    );
                }
                continue;
            }

            if ($code === AlertCode::FEED_DEVIATION) {
                $deviationPct = (float) ($params['deviation_pct'] ?? 0.2);
                if ($recommendedFeedKg !== null && $recommendedFeedKg > 0) {
                    $upper = $recommendedFeedKg * (1 + $deviationPct);
                    $lower = $recommendedFeedKg * (1 - $deviationPct);

                    if ($feedTodayKg > $upper || $feedTodayKg < $lower) {
                        $events[] = $this->emit(
                            cycle: $cycle,
                            ruleCode: $code,
                            severity: AlertSeverity::from($rule['severity']),
                            title: 'Feed deviation detected',
                            message: 'Daily feed deviates from recommendation.',
                            detectedAt: $evaluationDate,
                            context: [
                                'feed_today_kg' => $feedTodayKg,
                                'recommended_feed_kg' => $recommendedFeedKg,
                                'lower' => $lower,
                                'upper' => $upper,
                            ],
                        );
                    }
                }
                continue;
            }

            if ($code === AlertCode::HIGH_BIOMASS) {
                $threshold = (float) ($params['threshold'] ?? 4000);
                if ($biomassKgPerHa !== null && $biomassKgPerHa > $threshold) {
                    $events[] = $this->emit(
                        cycle: $cycle,
                        ruleCode: $code,
                        severity: AlertSeverity::from($rule['severity']),
                        title: 'High biomass risk',
                        message: 'Biomass per hectare exceeds threshold.',
                        detectedAt: $evaluationDate,
                        context: [
                            'biomass_kg_per_ha' => $biomassKgPerHa,
                            'threshold' => $threshold,
                        ],
                    );
                }

                continue;
            }

            if ($code === AlertCode::DO_LOW) {
                $threshold = (float) ($params['threshold'] ?? 3.5);
                $criticalThreshold = (float) ($params['critical_threshold'] ?? 3.0);
                $lowestDo = $waterEntriesToday
                    ->filter(fn (WaterQualityEntry $entry) => $entry->dissolved_oxygen_mg_l !== null)
                    ->sortBy('dissolved_oxygen_mg_l')
                    ->first();

                if ($lowestDo !== null && (float) $lowestDo->dissolved_oxygen_mg_l < $threshold) {
                    $severity = (float) $lowestDo->dissolved_oxygen_mg_l < $criticalThreshold
                        ? AlertSeverity::CRITICAL
                        : AlertSeverity::WARNING;

                    $events[] = $this->emit(
                        cycle: $cycle,
                        ruleCode: $code,
                        severity: $severity,
                        title: 'Low dissolved oxygen detected',
                        message: 'Dissolved oxygen is below the safe threshold.',
                        detectedAt: $evaluationDate,
                        context: [
                            'dissolved_oxygen_mg_l' => (float) $lowestDo->dissolved_oxygen_mg_l,
                            'threshold' => $threshold,
                            'critical_threshold' => $criticalThreshold,
                            'measured_at' => $lowestDo->measured_at?->toISOString(),
                        ],
                    );
                }

                continue;
            }

            if ($code === AlertCode::PH_OUT_OF_RANGE) {
                $min = (float) ($params['min'] ?? 7.2);
                $max = (float) ($params['max'] ?? 8.8);
                $outside = $waterEntriesToday
                    ->filter(fn (WaterQualityEntry $entry) => $entry->ph !== null)
                    ->first(fn (WaterQualityEntry $entry) => (float) $entry->ph < $min || (float) $entry->ph > $max);

                if ($outside !== null) {
                    $events[] = $this->emit(
                        cycle: $cycle,
                        ruleCode: $code,
                        severity: AlertSeverity::WARNING,
                        title: 'pH out of range',
                        message: 'Measured pH is outside the configured range.',
                        detectedAt: $evaluationDate,
                        context: [
                            'ph' => (float) $outside->ph,
                            'min' => $min,
                            'max' => $max,
                            'measured_at' => $outside->measured_at?->toISOString(),
                        ],
                    );
                }
            }
        }

        return array_values(array_filter($events));
    }

    public function emitHighDailyMortalityAlert(Cycle $cycle, Carbon $date, int $mortalityCount): ?AlertEvent
    {
        $stockingQty = (int) ($cycle->stocking?->pl_qty ?? 0);

        if ($stockingQty <= 0) {
            return null;
        }

        $thresholdPct = 0.02;
        $thresholdCount = (int) ceil($stockingQty * $thresholdPct);

        if ($mortalityCount <= $thresholdCount) {
            return null;
        }

        return $this->emit(
            cycle: $cycle,
            ruleCode: AlertCode::HIGH_DAILY_MORTALITY,
            severity: AlertSeverity::CRITICAL,
            title: 'High daily mortality detected',
            message: 'Daily mortality exceeds the default 2% threshold.',
            detectedAt: $date->copy(),
            context: [
                'mortality_count' => $mortalityCount,
                'threshold_pct' => $thresholdPct,
                'threshold_count' => $thresholdCount,
            ],
        );
    }

    /**
     * @return array<int, array{code: string, severity: string, params: array<string, mixed>}>
     */
    private function resolvedRules(Cycle $cycle): array
    {
        $fromDb = AlertRule::withoutGlobalScopes()
            ->where('tenant_id', $cycle->tenant_id)
            ->where('is_active', true)
            ->where(function ($query) use ($cycle): void {
                $query->whereNull('farm_id')->orWhere('farm_id', $cycle->pond->farm_id);
            })
            ->get();

        if ($fromDb->isNotEmpty()) {
            return $fromDb->map(fn (AlertRule $rule): array => [
                'code' => $rule->code,
                'severity' => (string) ($rule->severity?->value ?? AlertSeverity::WARNING->value),
                'params' => is_array($rule->params_json) ? $rule->params_json : [],
            ])->values()->all();
        }

        return [
            ['code' => AlertCode::LOW_GROWTH->value, 'severity' => AlertSeverity::WARNING->value, 'params' => ['threshold' => 0.8]],
            ['code' => AlertCode::HIGH_FCR->value, 'severity' => AlertSeverity::WARNING->value, 'params' => ['threshold' => 1.7]],
            ['code' => AlertCode::FEED_DEVIATION->value, 'severity' => AlertSeverity::INFO->value, 'params' => ['deviation_pct' => 0.2]],
            ['code' => AlertCode::HIGH_BIOMASS->value, 'severity' => AlertSeverity::CRITICAL->value, 'params' => ['threshold' => 4000]],
            ['code' => AlertCode::HIGH_DAILY_MORTALITY->value, 'severity' => AlertSeverity::CRITICAL->value, 'params' => ['threshold_pct' => 0.02]],
            ['code' => AlertCode::DO_LOW->value, 'severity' => AlertSeverity::WARNING->value, 'params' => ['threshold' => 3.5, 'critical_threshold' => 3.0]],
            ['code' => AlertCode::PH_OUT_OF_RANGE->value, 'severity' => AlertSeverity::WARNING->value, 'params' => ['min' => 7.2, 'max' => 8.8]],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function emit(
        Cycle $cycle,
        AlertCode $ruleCode,
        AlertSeverity $severity,
        string $title,
        string $message,
        Carbon $detectedAt,
        array $context,
    ): ?AlertEvent {
        $exists = AlertEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $cycle->tenant_id)
            ->where('cycle_id', $cycle->id)
            ->where('rule_code', $ruleCode->value)
            ->whereDate('detected_at', $detectedAt->toDateString())
            ->exists();

        if ($exists) {
            return null;
        }

        return AlertEvent::query()->create([
            'tenant_id' => $cycle->tenant_id,
            'farm_id' => $cycle->pond->farm_id,
            'cycle_id' => $cycle->id,
            'rule_code' => $ruleCode->value,
            'severity' => $severity->value,
            'title' => $title,
            'message' => $message,
            'detected_at' => $detectedAt,
            'context_json' => $context,
        ]);
    }
}
