<?php

namespace App\Modules\Auth\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;

final class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $deviceName,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: strtolower((string) $data['email']),
            password: (string) $data['password'],
            deviceName: (string) ($data['device_name'] ?? 'api'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'device_name' => $this->deviceName,
        ];
    }
}
