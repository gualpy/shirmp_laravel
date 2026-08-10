<?php

namespace App\Modules\Billing\Application\Services;

use App\Modules\Billing\Application\Contracts\PaymentGatewayInterface;
use App\Modules\Billing\Application\Gateways\PayPalGateway;
use App\Modules\Billing\Domain\Enums\BillingPaymentProvider;
use InvalidArgumentException;

final class PaymentGatewayFactory
{
    public function make(BillingPaymentProvider $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            BillingPaymentProvider::PAYPAL => app(PayPalGateway::class),
            default => throw new InvalidArgumentException(sprintf('No automatic payment gateway wired for provider `%s`.', $provider->value)),
        };
    }
}
