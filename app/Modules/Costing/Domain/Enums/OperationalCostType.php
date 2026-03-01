<?php

namespace App\Modules\Costing\Domain\Enums;

enum OperationalCostType: string
{
    case LABOR = 'labor';
    case ENERGY = 'energy';
    case FUEL = 'fuel';
    case MAINTENANCE = 'maintenance';
    case CHEMICALS = 'chemicals';
    case OTHER = 'other';
}
