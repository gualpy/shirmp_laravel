<?php

namespace App\Modules\Inventory\Domain\Enums;

enum InventoryReferenceType: string
{
    case FEED_ENTRY = 'feed_entry';
    case WATER_TREATMENT = 'water_treatment';
    case MANUAL = 'manual';
    case PURCHASE = 'purchase';
    case OTHER = 'other';
}
