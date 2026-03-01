<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Production\Domain\Enums\HarvestType;
use App\Modules\Shared\Application\DTO\BaseDTO;

final class HarvestDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $harvestedAt,
        public readonly HarvestType $type,
        public readonly float $totalLbs,
        public readonly ?float $avgPpGrams,
        public readonly ?string $guideNumber,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            harvestedAt: (string) $data['harvested_at'],
            type: isset($data['type']) ? HarvestType::from((string) $data['type']) : HarvestType::PARTIAL,
            totalLbs: (float) $data['total_lbs'],
            avgPpGrams: isset($data['avg_pp_grams']) ? (float) $data['avg_pp_grams'] : null,
            guideNumber: isset($data['guide_number']) ? (string) $data['guide_number'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'harvested_at' => $this->harvestedAt,
            'type' => $this->type->value,
            'total_lbs' => $this->totalLbs,
            'avg_pp_grams' => $this->avgPpGrams,
            'guide_number' => $this->guideNumber,
            'notes' => $this->notes,
        ];
    }
}
