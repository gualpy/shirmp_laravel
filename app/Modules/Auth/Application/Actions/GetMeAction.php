<?php

namespace App\Modules\Auth\Application\Actions;

use App\Models\User;
use App\Modules\Shared\Application\Actions\BaseAction;

final class GetMeAction extends BaseAction
{
    public function execute(User $user): User
    {
        return $user;
    }
}
