<?php

namespace App\Modules\SaaS\Domain\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case TRIAL = 'trial';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
}

