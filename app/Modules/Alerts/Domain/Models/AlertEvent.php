<?php

namespace App\Modules\Alerts\Domain\Models;

use App\Models\Tenant;
use App\Models\User;
use App\Multitenancy\Traits\HasTenant;
use App\Modules\Alerts\Domain\Enums\AlertSeverity;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Farm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertEvent extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'cycle_id',
        'rule_code',
        'severity',
        'title',
        'message',
        'detected_at',
        'context_json',
        'is_acknowledged',
        'acknowledged_by_user_id',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'severity' => AlertSeverity::class,
            'detected_at' => 'datetime',
            'context_json' => 'array',
            'is_acknowledged' => 'bool',
            'acknowledged_at' => 'datetime',
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

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }
}
