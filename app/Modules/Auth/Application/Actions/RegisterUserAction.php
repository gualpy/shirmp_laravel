<?php

namespace App\Modules\Auth\Application\Actions;

use App\Models\Tenant;
use App\Modules\Auth\Application\DTO\RegisterUserDTO;
use App\Modules\Auth\Application\Services\AuthService;
use App\Modules\SaaS\Application\Services\SaaSService;
use App\Modules\Shared\Application\Actions\BaseAction;

final class RegisterUserAction extends BaseAction
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SaaSService $saasService,
    ) {
    }

    /**
     * @return array{token_type: string, access_token: string, user: \App\Models\User}
     */
    public function execute(RegisterUserDTO $dto, Tenant $tenant): array
    {
        $this->saasService->enforceLimitOrFail($tenant, 'max_users');

        $user = $this->authService->register($dto, $tenant);
        $token = $this->authService->createToken($user, $dto->deviceName);

        return [
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $user,
        ];
    }
}
