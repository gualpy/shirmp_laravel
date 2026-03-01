<?php

namespace App\Modules\Production\Domain\Models;

use App\Models\Tenant;
use App\Modules\Production\Domain\Enums\HarvestType;
use App\Multitenancy\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    use HasFactory;
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'cycle_id',
        'harvested_at',
        'type',
        'total_lbs',
        'avg_pp_grams',
        'guide_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'harvested_at' => 'date',
            'type' => HarvestType::class,
            'total_lbs' => 'decimal:2',
            'avg_pp_grams' => 'decimal:2',
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
}
