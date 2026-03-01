<?php

namespace App\Modules\Production\Domain\Models;

use App\Models\Tenant;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pond extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'farm_id',
        'code',
        'name',
        'area_ha',
        'avg_depth_m',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'area_ha' => 'decimal:2',
            'avg_depth_m' => 'decimal:2',
            'is_active' => 'bool',
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

    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class);
    }
}
