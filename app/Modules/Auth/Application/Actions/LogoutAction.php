<?php

namespace App\Modules\Auth\Application\Actions;

use App\Models\User;
use App\Modules\Auth\Application\Services\AuthService;
use App\Modules\Shared\Application\Actions\BaseAction;

final class LogoutAction extends BaseAction
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function execute(User $user): void
    {
        $this->authService->revokeCurrentToken($user);
    }
}
