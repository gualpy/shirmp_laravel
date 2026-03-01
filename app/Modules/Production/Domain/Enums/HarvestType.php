<?php

namespace App\Modules\Production\Domain\Enums;

enum HarvestType: string
{
    case PARTIAL = 'partial';
    case FINAL = 'final';
}
