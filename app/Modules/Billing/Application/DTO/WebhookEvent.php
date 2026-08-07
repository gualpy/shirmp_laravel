<?php

namespace App\Modules\Billing\Application\DTO;

final class WebhookEvent
{
    /**
     * @param 'payment_succeeded'|'payment_failed'|'other' $type
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $sessionId,
        public readonly ?string $providerReference,
    ) {
    }
}
