<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetFarmSettingsAction extends BaseAction
{
    public function __construct(private readonly SettingsResolverService $resolver)
    {
    }

    /** @return array<string, mixed> */
    public function execute(Farm $farm): array
    {
        return $this->resolver->resolveFarmSettings($farm);
    }
}
