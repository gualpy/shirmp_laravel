<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Shared\Application\DTO\BaseDTO;

final class CycleUpdateDTO extends BaseDTO
{
    public function __construct(
        public readonly ?CycleStatus $status,
        public readonly ?string $endedAt,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: isset($data['status']) ? CycleStatus::from((string) $data['status']) : null,
            endedAt: isset($data['ended_at']) ? (string) $data['ended_at'] : null,
            notes: array_key_exists('notes', $data)
                ? ($data['notes'] !== null ? (string) $data['notes'] : null)
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status?->value,
            'ended_at' => $this->endedAt,
            'notes' => $this->notes,
        ];
    }
}
