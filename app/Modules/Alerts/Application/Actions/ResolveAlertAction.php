<?php

namespace App\Modules\Alerts\Application\Actions;

use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Shared\Application\Actions\BaseAction;

final class ResolveAlertAction extends BaseAction
{
    public function execute(AlertEvent $alert, User $user): AlertEvent
    {
        if ($alert->resolved_at === null) {
            $alert->update([
                'is_acknowledged' => true,
                'acknowledged_by_user_id' => $alert->acknowledged_by_user_id ?? $user->id,
                'acknowledged_at' => $alert->acknowledged_at ?? now(),
                'resolved_by_user_id' => $user->id,
                'resolved_at' => now(),
            ]);
        }

        return $alert->refresh();
    }
}
