<?php

namespace App\Modules\SaaS\Domain\Enums;

enum VerificationSource: string
{
    case CLOUD = 'cloud';
    case ONPREM = 'onprem';
    case MANUAL = 'manual';
}

