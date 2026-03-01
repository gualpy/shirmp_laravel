<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;

final class DeletePondAction extends BaseAction
{
    public function execute(Pond $pond): void
    {
        $pond->delete();
    }
}
