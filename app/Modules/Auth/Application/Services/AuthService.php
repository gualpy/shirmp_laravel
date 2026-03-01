<?php

namespace App\Modules\Auth\Application\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Application\DTO\LoginDTO;
use App\Modules\Auth\Application\DTO\RegisterUserDTO;
use App\Modules\Shared\Application\Services\BaseService;
use Illuminate\Support\Facades\Hash;

final class AuthService extends BaseService
{
    public function register(RegisterUserDTO $dto, Tenant $tenant): User
    {
        return User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role->value,
        ]);
    }

    public function authenticate(LoginDTO $dto, Tenant $tenant): ?User
    {
        $user = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $dto->email)
            ->first();

        if ($user === null || ! Hash::check($dto->password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function createToken(User $user, string $deviceName): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    public function revokeCurrentToken(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }
}
