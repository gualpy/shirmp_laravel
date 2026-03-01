<?php

namespace App\Modules\Production\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class HealthCheckDTO extends BaseDTO
{
    public static function fromArray(array $data = []): self
    {
        return new self();
    }

    public function toArray(): array
    {
        return [];
    }
}
