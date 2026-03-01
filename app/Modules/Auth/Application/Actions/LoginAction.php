<?php

namespace App\Modules\Auth\Application\Actions;

use App\Models\Tenant;
use App\Modules\Auth\Application\DTO\LoginDTO;
use App\Modules\Auth\Application\Services\AuthService;
use App\Modules\Shared\Application\Actions\BaseAction;

final class LoginAction extends BaseAction
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    /**
     * @return array{token_type: string, access_token: string, user: \App\Models\User}|null
     */
    public function execute(LoginDTO $dto, Tenant $tenant): ?array
    {
        $user = $this->authService->authenticate($dto, $tenant);

        if ($user === null) {
            return null;
        }

        return [
            'token_type' => 'Bearer',
            'access_token' => $this->authService->createToken($user, $dto->deviceName),
            'user' => $user,
        ];
    }
}
