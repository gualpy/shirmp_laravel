<?php

namespace App\Modules\Feeding\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class FeedTypeDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $brand,
        public readonly ?float $proteinPct,
        public readonly ?string $notes,
        public readonly bool $isActive,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            brand: isset($data['brand']) ? (string) $data['brand'] : null,
            proteinPct: isset($data['protein_pct']) ? (float) $data['protein_pct'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'brand' => $this->brand,
            'protein_pct' => $this->proteinPct,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
