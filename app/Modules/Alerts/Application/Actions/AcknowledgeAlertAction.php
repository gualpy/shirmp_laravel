<?php

namespace App\Modules\Alerts\Application\Actions;

use App\Models\User;
use App\Modules\Audit\Application\Services\AuditLogService;
use App\Modules\Alerts\Domain\Models\AlertEvent;
use App\Modules\Shared\Application\Actions\BaseAction;

final class AcknowledgeAlertAction extends BaseAction
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function execute(AlertEvent $alert, User $user): AlertEvent
    {
        if (! $alert->is_acknowledged) {
            $alert->update([
                'is_acknowledged' => true,
                'acknowledged_by_user_id' => $user->id,
                'acknowledged_at' => now(),
            ]);

            $this->auditLogService->record(
                actionKey: 'alert.acknowledged',
                entityType: 'AlertEvent',
                entityId: $alert->id,
                context: [
                    'cycle_id' => $alert->cycle_id,
                    'severity' => is_string($alert->severity) ? $alert->severity : $alert->severity->value,
                ],
                tenant: $alert->tenant,
                user: $user,
            );
        }

        return $alert->refresh();
    }
}
