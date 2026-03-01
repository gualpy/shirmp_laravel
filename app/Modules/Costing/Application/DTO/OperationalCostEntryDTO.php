<?php

namespace App\Modules\Costing\Application\DTO;

use App\Modules\Costing\Domain\Enums\OperationalCostType;
use App\Modules\Shared\Application\DTO\BaseDTO;

final class OperationalCostEntryDTO extends BaseDTO
{
    public function __construct(
        public readonly OperationalCostType $costType,
        public readonly float $amount,
        public readonly string $occurredAt,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            costType: OperationalCostType::from((string) $data['cost_type']),
            amount: (float) $data['amount'],
            occurredAt: (string) $data['occurred_at'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'cost_type' => $this->costType->value,
            'amount' => $this->amount,
            'occurred_at' => $this->occurredAt,
            'notes' => $this->notes,
        ];
    }
}
