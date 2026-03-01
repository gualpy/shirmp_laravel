<?php

namespace App\Modules\Auth\Application\DTO;

use App\Modules\Auth\Domain\Enums\UserRole;
use App\Modules\Shared\Application\DTO\BaseDTO;

final class RegisterUserDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly UserRole $role,
        public readonly string $deviceName,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            email: strtolower((string) $data['email']),
            password: (string) $data['password'],
            role: isset($data['role']) ? UserRole::from((string) $data['role']) : UserRole::READ_ONLY,
            deviceName: (string) ($data['device_name'] ?? 'api'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'device_name' => $this->deviceName,
        ];
    }
}
