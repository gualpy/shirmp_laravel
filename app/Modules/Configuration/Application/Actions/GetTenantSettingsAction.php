<?php

namespace App\Modules\Configuration\Application\Actions;

use App\Models\Tenant;
use App\Modules\Configuration\Application\Services\SettingsResolverService;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetTenantSettingsAction extends BaseAction
{
    public function __construct(private readonly SettingsResolverService $resolver)
    {
    }

    /** @return array<string, mixed> */
    public function execute(Tenant $tenant): array
    {
        return $this->resolver->resolveTenantSettings($tenant);
    }
}
