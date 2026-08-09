<?php

namespace App\Modules\Billing\Application\DTO;

final class CheckoutSession
{
    public function __construct(
        public readonly string $sessionId,
        public readonly string $redirectUrl,
    ) {
    }
}
