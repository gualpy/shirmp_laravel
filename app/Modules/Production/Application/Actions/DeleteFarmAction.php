<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;

final class DeleteFarmAction extends BaseAction
{
    public function execute(Farm $farm): void
    {
        $farm->delete();
    }
}
