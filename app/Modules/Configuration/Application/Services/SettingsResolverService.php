<?php

namespace App\Modules\Configuration\Application\Services;

use App\Models\Tenant;
use App\Modules\Configuration\Domain\Enums\FeedingStrategy;
use App\Modules\Configuration\Domain\Enums\UnitSystem;
use App\Modules\Configuration\Domain\Models\FarmSetting;
use App\Modules\Configuration\Domain\Models\TenantSetting;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Services\BaseService;

final class SettingsResolverService extends BaseService
{
    /**
     * @return array<string, mixed>
     */
    public function systemDefaults(): array
    {
        return [
            'feeding_strategy' => FeedingStrategy::BIOMASS_PERCENTAGE->value,
            'feeding_pct_small' => 3.00,
            'feeding_pct_medium' => 2.50,
            'feeding_pct_large' => 2.00,
            'allow_post_close_adjustments' => false,
            'unit_system' => UnitSystem::METRIC->value,
            'decimals_precision' => 2,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveTenantSettings(Tenant $tenant): array
    {
        $defaults = $this->systemDefaults();
        $tenantSetting = TenantSetting::query()->where('tenant_id', $tenant->id)->first();

        return array_merge($defaults, $this->normalizeModelData($tenantSetting));
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveFarmSettings(Farm $farm): array
    {
        $tenantSettings = $this->resolveTenantSettings($farm->tenant);
        $farmSetting = FarmSetting::query()->where('farm_id', $farm->id)->first();

        return array_merge($tenantSettings, $this->normalizeModelData($farmSetting));
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeModelData(?object $model): array
    {
        if ($model === null) {
            return [];
        }

        $fields = [
            'feeding_strategy',
            'feeding_pct_small',
            'feeding_pct_medium',
            'feeding_pct_large',
            'allow_post_close_adjustments',
            'unit_system',
            'decimals_precision',
        ];

        $data = [];
        foreach ($fields as $field) {
            $value = $model->{$field};
            if ($value === null) {
                continue;
            }

            $data[$field] = is_object($value) && property_exists($value, 'value') ? $value->value : $value;
        }

        return $data;
    }
}
