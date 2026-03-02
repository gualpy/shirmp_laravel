<?php

namespace App\Modules\SaaS\Domain\Enums;

enum PlanBillingType: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case LIFETIME = 'lifetime';
    case ONPREM = 'onprem';
}

