<?php

namespace App\Modules\Auth\Application\Services;

use App\Models\User;
use App\Modules\Auth\Domain\Enums\UserRole;

final class RolePermissionService
{
    /** @var array<string, array{view: list<string>, manage: list<string>}> */
    private const MODULE_MATRIX = [
        'dashboard' => [
            'view' => ['Owner', 'Admin', 'Production', 'Inventory', 'Finance', 'ReadOnly'],
            'manage' => [],
        ],
        'production' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'water' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'mortality' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'feeding' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'sampling' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'harvest' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'alerts' => [
            'view' => ['Owner', 'Admin', 'Production', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Production'],
        ],
        'costs' => [
            'view' => ['Owner', 'Admin', 'Finance', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Finance'],
        ],
        'inventory' => [
            'view' => ['Owner', 'Admin', 'Inventory', 'ReadOnly'],
            'manage' => ['Owner', 'Admin', 'Inventory'],
        ],
        'billing' => [
            'view' => ['Owner', 'Finance'],
            'manage' => ['Owner', 'Finance'],
        ],
        'audit' => [
            'view' => ['Owner', 'Admin'],
            'manage' => [],
        ],
        'settings' => [
            'view' => ['Owner', 'Admin'],
            'manage' => ['Owner', 'Admin'],
        ],
        'reports' => [
            'view' => ['Owner', 'Admin', 'Production', 'Finance', 'ReadOnly'],
            'manage' => [],
        ],
        'admin' => [
            'view' => ['SuperAdmin'],
            'manage' => ['SuperAdmin'],
        ],
    ];

    public function canView(?User $user, string $module): bool
    {
        return $this->allows($user, $module, false);
    }

    public function canManage(?User $user, string $module): bool
    {
        return $this->allows($user, $module, true);
    }

    /** @return array<string, bool> */
    public function permissionMap(?User $user): array
    {
        $map = [];

        foreach (array_keys(self::MODULE_MATRIX) as $module) {
            $map[$module.'.view'] = $this->canView($user, $module);
            $map[$module.'.manage'] = $this->canManage($user, $module);
        }

        return $map;
    }

    private function allows(?User $user, string $module, bool $write): bool
    {
        $role = $this->normalizeRole($user?->role);

        if ($role === null) {
            return false;
        }

        if ($role === UserRole::SUPER_ADMIN->value) {
            return $module === 'admin';
        }

        $matrix = self::MODULE_MATRIX[$module] ?? null;
        if ($matrix === null) {
            return false;
        }

        $bucket = $write ? 'manage' : 'view';

        return in_array($role, $matrix[$bucket], true);
    }

    private function normalizeRole(mixed $role): ?string
    {
        if ($role instanceof UserRole) {
            return $role->value;
        }

        return is_string($role) ? $role : null;
    }
}
