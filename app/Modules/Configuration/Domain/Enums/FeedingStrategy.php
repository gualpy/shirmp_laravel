<?php

namespace App\Modules\Configuration\Domain\Enums;

enum FeedingStrategy: string
{
    case BIOMASS_PERCENTAGE = 'biomass_percentage';
    case GROWTH_TABLE = 'growth_table';
    case MANUAL_ADJUSTMENT = 'manual_adjustment';
}
