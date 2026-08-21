<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class StockingDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $stockedAt,
        public readonly int $plQty,
        public readonly ?int $supplierId,
        public readonly ?string $batchCode,
        public readonly ?float $initialPpGrams,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            stockedAt: (string) $data['stocked_at'],
            plQty: (int) $data['pl_qty'],
            supplierId: isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            batchCode: isset($data['batch_code']) ? (string) $data['batch_code'] : null,
            initialPpGrams: isset($data['initial_pp_grams']) ? (float) $data['initial_pp_grams'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'stocked_at' => $this->stockedAt,
            'pl_qty' => $this->plQty,
            'supplier_id' => $this->supplierId,
            'batch_code' => $this->batchCode,
            'initial_pp_grams' => $this->initialPpGrams,
        ];
    }
}
