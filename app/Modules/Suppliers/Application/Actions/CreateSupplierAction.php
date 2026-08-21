<?php

namespace App\Modules\Suppliers\Application\Actions;

use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\Suppliers\Application\DTO\SupplierDataDTO;
use App\Modules\Suppliers\Domain\Models\Supplier;

final class CreateSupplierAction extends BaseAction
{
    public function execute(SupplierDataDTO $dto): Supplier
    {
        return Supplier::query()->create($dto->toArray());
    }
}
