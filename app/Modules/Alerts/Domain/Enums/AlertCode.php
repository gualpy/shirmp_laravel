<?php

namespace App\Modules\Alerts\Domain\Enums;

enum AlertCode: string
{
    case LOW_GROWTH = 'LOW_GROWTH';
    case HIGH_FCR = 'HIGH_FCR';
    case FEED_DEVIATION = 'FEED_DEVIATION';
    case HIGH_BIOMASS = 'HIGH_BIOMASS';
    case HIGH_DAILY_MORTALITY = 'HIGH_DAILY_MORTALITY';
    case DO_LOW = 'DO_LOW';
    case PH_OUT_OF_RANGE = 'PH_OUT_OF_RANGE';
}
