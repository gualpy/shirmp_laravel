<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Production\Domain\Enums\CycleStatus;
use App\Modules\Shared\Application\DTO\BaseDTO;

final class CycleDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $startedAt,
        public readonly CycleStatus $status,
        public readonly ?string $endedAt,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            startedAt: (string) $data['started_at'],
            status: isset($data['status']) ? CycleStatus::from((string) $data['status']) : CycleStatus::ACTIVE,
            endedAt: isset($data['ended_at']) ? (string) $data['ended_at'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'started_at' => $this->startedAt,
            'status' => $this->status->value,
            'ended_at' => $this->endedAt,
            'notes' => $this->notes,
        ];
    }
}
