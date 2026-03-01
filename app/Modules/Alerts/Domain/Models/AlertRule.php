<?php

namespace App\Modules\Alerts\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Production\Domain\Models\Farm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertRule extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'code',
        'is_active',
        'params_json',
        'severity',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
            'params_json' => 'array',
            'severity' => AlertSeverity::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
