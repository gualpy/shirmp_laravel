<?php

namespace App\Modules\Production\Domain\Enums;

enum CycleStatus: string
{
    case ACTIVE = 'active';
    case HARVESTED = 'harvested';
    case CANCELLED = 'cancelled';
}
