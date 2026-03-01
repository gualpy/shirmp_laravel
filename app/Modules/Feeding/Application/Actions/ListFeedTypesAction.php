<?php

namespace App\Modules\Feeding\Application\Actions;

use App\Modules\Feeding\Domain\Models\FeedType;
use App\Modules\Shared\Application\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;

final class ListFeedTypesAction extends BaseAction
{
    public function execute(): Collection
    {
        return FeedType::query()->orderBy('name')->get();
    }
}
