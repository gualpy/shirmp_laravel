<?php

namespace App\Modules\WaterQuality\Domain\Models;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Production\Domain\Models\Cycle;
use App\Modules\Production\Domain\Models\Pond;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterQualityEntry extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'pond_id',
        'measured_at',
        'dissolved_oxygen_mg_l',
        'ph',
        'temp_c',
        'salinity_ppt',
        'alkalinity_mg_l',
        'ammonia_mg_l',
        'nitrite_mg_l',
        'notes',
        'measured_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'measured_at' => 'datetime',
            'dissolved_oxygen_mg_l' => 'decimal:2',
            'ph' => 'decimal:2',
            'temp_c' => 'decimal:2',
            'salinity_ppt' => 'decimal:2',
            'alkalinity_mg_l' => 'decimal:2',
            'ammonia_mg_l' => 'decimal:3',
            'nitrite_mg_l' => 'decimal:3',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(Pond::class);
    }

    public function measuredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'measured_by_user_id');
    }
}

