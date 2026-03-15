<?php

namespace App\Modules\Billing\Domain\Enums;

enum BillingInvoiceStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case VOID = 'void';
}
