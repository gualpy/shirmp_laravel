<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class DailyMortalityDTO extends BaseDTO
{
    public function __construct(
        public readonly int $pondId,
        public readonly string $recordedAt,
        public readonly int $mortalityCount,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            pondId: (int) $data['pond_id'],
            recordedAt: (string) $data['recorded_at'],
            mortalityCount: (int) $data['mortality_count'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            createdBy: isset($data['created_by']) ? (int) $data['created_by'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'pond_id' => $this->pondId,
            'recorded_at' => $this->recordedAt,
            'mortality_count' => $this->mortalityCount,
            'notes' => $this->notes,
            'created_by' => $this->createdBy,
        ];
    }
}
