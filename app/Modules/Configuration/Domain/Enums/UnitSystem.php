<?php

namespace App\Modules\Configuration\Domain\Enums;

enum UnitSystem: string
{
    case METRIC = 'metric';
    case IMPERIAL = 'imperial';
}
