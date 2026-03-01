<?php

namespace App\Modules\Shared\Application\DTO;

abstract class BaseDTO
{
    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
