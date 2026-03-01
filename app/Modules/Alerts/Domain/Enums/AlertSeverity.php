<?php

namespace App\Modules\Alerts\Domain\Enums;

enum AlertSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case CRITICAL = 'critical';
}
