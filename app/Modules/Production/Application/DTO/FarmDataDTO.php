<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class FarmDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $location,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            location: isset($data['location']) ? (string) $data['location'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'location' => $this->location,
            'notes' => $this->notes,
        ];
    }
}
