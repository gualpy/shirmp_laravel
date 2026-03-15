<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryCategory: string
{
    case FEED = 'feed';
    case PROBIOTIC = 'probiotic';
    case CHEMICAL = 'chemical';
    case FUEL = 'fuel';
    case SPARE_PART = 'spare_part';
    case OTHER = 'other';
}
