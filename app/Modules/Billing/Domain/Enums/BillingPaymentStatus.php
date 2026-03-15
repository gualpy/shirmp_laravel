<?php

namespace App\Modules\Billing\Domain\Enums;

enum BillingPaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
}
