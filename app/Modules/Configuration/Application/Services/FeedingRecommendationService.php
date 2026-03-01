<?php

namespace App\Modules\Configuration\Application\Services;

use App\Modules\Configuration\Domain\Enums\FeedingStrategy;
use App\Modules\Configuration\Domain\Models\FeedingGrowthTable;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Services\BaseService;
use Illuminate\Support\Carbon;

final class FeedingRecommendationService extends BaseService
{
    public function __construct(
        private readonly SettingsResolverService $settingsResolver,
        private readonly MetricsService $metricsService,
    ) {
    }

    /**
     * @return array{recommended_feed_kg_per_day: float|null, feeding_strategy_used: string}
     */
    public function recommendDailyFeedKg(Cycle $cycle): array
    {
        $settings = $this->settingsResolver->resolveFarmSettings($cycle->pond->farm);
        $strategy = (string) $settings['feeding_strategy'];
        $precision = (int) $settings['decimals_precision'];

        if ($strategy === FeedingStrategy::MANUAL_ADJUSTMENT->value) {
            return [
                'recommended_feed_kg_per_day' => null,
                'feeding_strategy_used' => $strategy,
            ];
        }

        $biomassKg = $this->metricsService->biomass_kg($cycle, 1.0);

        if ($biomassKg === null || $biomassKg <= 0) {
            return [
                'recommended_feed_kg_per_day' => null,
                'feeding_strategy_used' => $strategy,
            ];
        }

        $feedPct = null;

        if ($strategy === FeedingStrategy::BIOMASS_PERCENTAGE->value) {
            $latestPp = $this->metricsService->latest_pp_grams($cycle);

            if ($latestPp === null) {
                return [
                    'recommended_feed_kg_per_day' => null,
                    'feeding_strategy_used' => $strategy,
                ];
            }

            if ($latestPp < 10) {
                $feedPct = (float) $settings['feeding_pct_small'];
            } elseif ($latestPp <= 15) {
                $feedPct = (float) $settings['feeding_pct_medium'];
            } else {
                $feedPct = (float) $settings['feeding_pct_large'];
            }
        }

        if ($strategy === FeedingStrategy::GROWTH_TABLE->value) {
            $dayOfCycle = max(1, Carbon::parse($cycle->started_at)->diffInDays(now()) + 1);
            $latestPp = $this->metricsService->latest_pp_grams($cycle);

            $table = FeedingGrowthTable::query()
                ->where('is_active', true)
                ->where(function ($query) use ($cycle): void {
                    $query
                        ->where('farm_id', $cycle->pond->farm_id)
                        ->orWhereNull('farm_id');
                })
                ->orderByRaw('case when farm_id is null then 1 else 0 end')
                ->latest('id')
                ->first();

            if ($table !== null) {
                $row = $table->rows()
                    ->where('day_from', '<=', $dayOfCycle)
                    ->where('day_to', '>=', $dayOfCycle)
                    ->where(function ($query) use ($latestPp): void {
                        if ($latestPp === null) {
                            $query->whereNull('pp_from_grams')->whereNull('pp_to_grams');

                            return;
                        }

                        $query->where(function ($sub) use ($latestPp): void {
                            $sub
                                ->whereNull('pp_from_grams')
                                ->orWhere('pp_from_grams', '<=', $latestPp);
                        })->where(function ($sub) use ($latestPp): void {
                            $sub
                                ->whereNull('pp_to_grams')
                                ->orWhere('pp_to_grams', '>=', $latestPp);
                        });
                    })
                    ->orderByDesc('feed_pct')
                    ->first();

                if ($row !== null) {
                    $feedPct = (float) $row->feed_pct;
                }
            }
        }

        if ($feedPct === null) {
            return [
                'recommended_feed_kg_per_day' => null,
                'feeding_strategy_used' => $strategy,
            ];
        }

        return [
            'recommended_feed_kg_per_day' => round($biomassKg * ($feedPct / 100), $precision),
            'feeding_strategy_used' => $strategy,
        ];
    }
}
