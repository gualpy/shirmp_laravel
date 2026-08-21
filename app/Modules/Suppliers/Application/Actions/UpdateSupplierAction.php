<?php

namespace App\Modules\Suppliers\Application\Actions;

use App\Modules\Shared\Application\Actions\BaseAction;
use App\Modules\Suppliers\Application\DTO\SupplierDataDTO;
use App\Modules\Suppliers\Domain\Models\Supplier;

final class UpdateSupplierAction extends BaseAction
{
    public function execute(Supplier $supplier, SupplierDataDTO $dto): Supplier
    {
        $supplier->update($dto->toArray());

        return $supplier->refresh();
    }
}
