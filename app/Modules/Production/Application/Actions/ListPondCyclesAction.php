<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Pond;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListPondCyclesAction extends BaseAction
{
    public function execute(Pond $pond): Collection
    {
        return $pond->cycles()->latest('started_at')->get();
    }
}
