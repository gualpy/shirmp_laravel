<?php

namespace App\Modules\Alerts\Application\Actions;

use App\Models\User;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Shared\Application\Actions\BaseAction;

final class AcknowledgeAlertAction extends BaseAction
{
    public function execute(AlertEvent $alert, User $user): AlertEvent
    {
        if (! $alert->is_acknowledged) {
            $alert->update([
                'is_acknowledged' => true,
                'acknowledged_by_user_id' => $user->id,
                'acknowledged_at' => now(),
            ]);
        }

        return $alert->refresh();
    }
}
