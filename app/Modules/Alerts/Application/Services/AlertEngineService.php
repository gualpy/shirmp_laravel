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
use Illuminate\Support\Carbon;

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
            }
        }

        return array_values(array_filter($events));
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
