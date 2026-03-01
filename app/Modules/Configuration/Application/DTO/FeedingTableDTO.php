<?php

namespace App\Modules\Configuration\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class FeedingTableDTO extends BaseDTO
{
    public function __construct(
        public readonly ?int $farmId,
        public readonly string $name,
        public readonly bool $isActive,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            farmId: isset($data['farm_id']) ? (int) $data['farm_id'] : null,
            name: (string) $data['name'],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'farm_id' => $this->farmId,
            'name' => $this->name,
            'is_active' => $this->isActive,
        ];
    }
}
