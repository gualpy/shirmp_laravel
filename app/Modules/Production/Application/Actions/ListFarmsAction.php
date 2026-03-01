<?php

namespace App\Modules\Production\Application\Actions;

use App\Modules\Production\Domain\Models\Farm;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListFarmsAction extends BaseAction
{
    public function execute(): Collection
    {
        return Farm::query()->orderBy('name')->get();
    }
}
