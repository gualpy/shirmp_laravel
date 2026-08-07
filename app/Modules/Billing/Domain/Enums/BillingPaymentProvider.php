<?php

namespace App\Modules\Billing\Domain\Enums;

enum BillingPaymentProvider: string
{
    case MANUAL = 'manual';
    case STRIPE = 'stripe';
    case PAYPAL = 'paypal';
    case ONPREM = 'onprem';
    case OTHER = 'other';
}
