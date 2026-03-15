<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryUnit: string
{
    case KG = 'kg';
    case LB = 'lb';
    case LITER = 'liter';
    case GALLON = 'gallon';
    case UNIT = 'unit';
    case SACK = 'sack';
    case OTHER = 'other';
}
