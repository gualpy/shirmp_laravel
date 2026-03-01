<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Configuration\Application\Services\FeedingRecommendationService;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetCycleMetricsAction extends BaseAction
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly FeedingRecommendationService $feedingRecommendationService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Cycle $cycle, ?float $survivalEstimate = null): array
    {
        $recommendation = $this->feedingRecommendationService->recommendDailyFeedKg($cycle);

        return [
            'densities' => [
                'pl_m2' => $this->metricsService->density_pl_m2($cycle),
                'pl_ha' => $this->metricsService->density_pl_ha($cycle),
            ],
            'sampling' => [
                'latest_pp_grams' => $this->metricsService->latest_pp_grams($cycle),
                'growth_g_per_week' => $this->metricsService->growth_g_per_week($cycle),
            ],
            'feed' => [
                'total_feed_kg' => $this->metricsService->total_feed_kg($cycle),
            ],
            'harvest' => [
                'total_lbs' => $this->metricsService->total_harvest_lbs($cycle),
                'total_kg' => $this->metricsService->total_harvest_kg($cycle),
            ],
            'fcr' => $this->metricsService->fcr($cycle),
            'biomass_kg' => $this->metricsService->biomass_kg($cycle, $survivalEstimate),
            'recommended_feed_kg_per_day' => $recommendation['recommended_feed_kg_per_day'],
            'feeding_strategy_used' => $recommendation['feeding_strategy_used'],
        ];
    }
}
