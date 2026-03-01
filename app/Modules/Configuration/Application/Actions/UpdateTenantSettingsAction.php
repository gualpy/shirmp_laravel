<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Models\Tenant;
use App\Modules\Configuration\Application\DTO\SettingPatchDTO;
use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\Configuration\Domain\Models\TenantSetting;
use App\Modules\Shared\Application\Actions\BaseAction;

final class UpdateTenantSettingsAction extends BaseAction
{
    public function __construct(private readonly SettingsResolverService $resolver)
    {
    }

    /** @return array<string, mixed> */
    public function execute(Tenant $tenant, SettingPatchDTO $dto): array
    {
        $setting = TenantSetting::query()->firstOrCreate(['tenant_id' => $tenant->id]);
        $setting->fill($dto->toArray());
        $setting->save();

        return $this->resolver->resolveTenantSettings($tenant);
    }
}
