<?php

namespace App\Modules\Suppliers\Application\DTO;

use App\Modules\Shared\Application\DTO\BaseDTO;
use App\Modules\Suppliers\Domain\Enums\SupplierType;

final class SupplierDataDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $code,
        public readonly ?string $contactName,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $notes,
        public readonly bool $isActive,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            type: isset($data['type']) && $data['type'] instanceof SupplierType
                ? $data['type']->value
                : (string) ($data['type'] ?? 'other'),
            code: isset($data['code']) ? (string) $data['code'] : null,
            contactName: isset($data['contact_name']) ? (string) $data['contact_name'] : null,
            phone: isset($data['phone']) ? (string) $data['phone'] : null,
            email: isset($data['email']) ? (string) $data['email'] : null,
            notes: isset($data['notes']) ? (string) $data['notes'] : null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'code' => $this->code,
            'contact_name' => $this->contactName,
            'phone' => $this->phone,
            'email' => $this->email,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
