<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class SamplingDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $sampledAt,
        public readonly float $ppGrams,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sampledAt: (string) $data['sampled_at'],
            ppGrams: (float) $data['pp_grams'],
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'sampled_at' => $this->sampledAt,
            'pp_grams' => $this->ppGrams,
            'notes' => $this->notes,
        ];
    }
}
