<?php

namespace App\Modules\Suppliers\Domain\Enums;

enum SupplierType: string
{
    case HATCHERY = 'hatchery';
    case FEED = 'feed';
    case INPUT = 'input';
    case EQUIPMENT = 'equipment';
    case SERVICE = 'service';
    case OTHER = 'other';
}
