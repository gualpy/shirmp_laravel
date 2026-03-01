<?php

namespace App\Modules\Feeding\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class FeedEntryDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $fedAt,
        public readonly int $feedTypeId,
        public readonly float $amountKg,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fedAt: (string) $data['fed_at'],
            feedTypeId: (int) $data['feed_type_id'],
            amountKg: (float) $data['amount_kg'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'fed_at' => $this->fedAt,
            'feed_type_id' => $this->feedTypeId,
            'amount_kg' => $this->amountKg,
            'notes' => $this->notes,
        ];
    }
}
