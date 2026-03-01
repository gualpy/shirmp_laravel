<?php

namespace App\Modules\Auth\Domain\Enums;

enum UserRole: string
{
    case OWNER = 'Owner';
    case ADMIN = 'Admin';
    case PRODUCTION = 'Production';
    case INVENTORY = 'Inventory';
    case FINANCE = 'Finance';
    case READ_ONLY = 'ReadOnly';
}
