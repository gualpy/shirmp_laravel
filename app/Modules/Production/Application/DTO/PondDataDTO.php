<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class PondDataDTO extends BaseDTO
{
    public function __construct(
        public readonly int $farmId,
        public readonly string $code,
        public readonly ?string $name,
        public readonly float $areaHa,
        public readonly ?float $avgDepthM,
        public readonly bool $isActive,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            farmId: (int) $data['farm_id'],
            code: (string) $data['code'],
            name: isset($data['name']) ? (string) $data['name'] : null,
            areaHa: (float) $data['area_ha'],
            avgDepthM: isset($data['avg_depth_m']) ? (float) $data['avg_depth_m'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'farm_id' => $this->farmId,
            'code' => $this->code,
            'name' => $this->name,
            'area_ha' => $this->areaHa,
            'avg_depth_m' => $this->avgDepthM,
            'is_active' => $this->isActive,
        ];
    }
}
