<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Application\DTO\SettingPatchDTO;
use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\Configuration\Domain\Models\FarmSetting;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;

final class UpdateFarmSettingsAction extends BaseAction
{
    public function __construct(private readonly SettingsResolverService $resolver)
    {
    }

    /** @return array<string, mixed> */
    public function execute(Farm $farm, SettingPatchDTO $dto): array
    {
        $setting = FarmSetting::query()->firstOrCreate([
            'tenant_id' => $farm->tenant_id,
            'farm_id' => $farm->id,
        ]);

        $setting->fill($dto->toArray());
        $setting->save();

        return $this->resolver->resolveFarmSettings($farm);
    }
}
