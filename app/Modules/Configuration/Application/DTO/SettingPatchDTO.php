<?php

namespace App\Modules\Configuration\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class SettingPatchDTO extends BaseDTO
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public readonly array $attributes,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
