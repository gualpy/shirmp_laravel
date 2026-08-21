<?php

namespace App\Modules\Backoffice\Application\Services;

use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Production\Application\Services\MetricsService;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\SaaS\Application\Services\LicenseService;
use App\Multitenancy\TenantContext;

final class BackofficeCycleFeedingService
{
    public function __construct(
        private readonly MetricsService $metricsService,
        private readonly TenantContext $tenantContext,
        private readonly LicenseService $licenseService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Cycle $cycle): array
    {
        $cycle->loadMissing(['pond.farm', 'feedEntries.feedType']);

        $tenant = $this->tenantContext->currentTenant();
        abort_if($tenant === null, 400, 'Tenant could not be resolved.');

        return [
            'header' => [
                'cycle_id' => $cycle->id,
                'farm_name' => (string) ($cycle->pond?->farm?->name ?? 'N/A'),
                'pond_code' => (string) ($cycle->pond?->code ?? 'N/A'),
                'started_at' => $cycle->started_at?->format('Y-m-d'),
            ],
            'summary' => [
                'total_feed_kg' => $this->metricsService->total_feed_kg($cycle),
            ],
            'rows' => $cycle->feedEntries()
                ->with('feedType:id,name')
                ->orderByDesc('fed_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn ($entry): array => [
                    'id' => $entry->id,
                    'fed_at' => $entry->fed_at?->format('Y-m-d H:i'),
                    'feed_type' => (string) ($entry->feedType?->name ?? 'N/A'),
                    'amount_kg' => (float) $entry->amount_kg,
                    'notes' => $entry->notes,
                ])
                ->values()
                ->all(),
            'feed_types' => FeedType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (FeedType $type): array => ['id' => $type->id, 'name' => $type->name])
                ->values()
                ->all(),
            'read_only_mode' => (bool) $this->licenseService->requireActiveOrGrace($tenant)['read_only_mode'],
            'defaults' => [
                'fed_at' => now()->format('Y-m-d\TH:i'),
            ],
        ];
    }
}
