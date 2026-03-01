<?php

namespace App\Modules\WaterQuality\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class WaterQualityEntryDTO extends BaseDTO
{
    public function __construct(
        public readonly string $measuredAt,
        public readonly ?float $dissolvedOxygenMgL,
        public readonly ?float $ph,
        public readonly ?float $tempC,
        public readonly ?float $salinityPpt,
        public readonly ?float $alkalinityMgL,
        public readonly ?float $ammoniaMgL,
        public readonly ?float $nitriteMgL,
        public readonly ?string $notes,
        public readonly ?int $measuredByUserId,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            measuredAt: (string) $data['measured_at'],
            dissolvedOxygenMgL: isset($data['dissolved_oxygen_mg_l']) ? (float) $data['dissolved_oxygen_mg_l'] : null,
            ph: isset($data['ph']) ? (float) $data['ph'] : null,
            tempC: isset($data['temp_c']) ? (float) $data['temp_c'] : null,
            salinityPpt: isset($data['salinity_ppt']) ? (float) $data['salinity_ppt'] : null,
            alkalinityMgL: isset($data['alkalinity_mg_l']) ? (float) $data['alkalinity_mg_l'] : null,
            ammoniaMgL: isset($data['ammonia_mg_l']) ? (float) $data['ammonia_mg_l'] : null,
            nitriteMgL: isset($data['nitrite_mg_l']) ? (float) $data['nitrite_mg_l'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            measuredByUserId: isset($data['measured_by_user_id']) ? (int) $data['measured_by_user_id'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'measured_at' => $this->measuredAt,
            'dissolved_oxygen_mg_l' => $this->dissolvedOxygenMgL,
            'ph' => $this->ph,
            'temp_c' => $this->tempC,
            'salinity_ppt' => $this->salinityPpt,
            'alkalinity_mg_l' => $this->alkalinityMgL,
            'ammonia_mg_l' => $this->ammoniaMgL,
            'nitrite_mg_l' => $this->nitriteMgL,
            'notes' => $this->notes,
            'measured_by_user_id' => $this->measuredByUserId,
        ];
    }
}

